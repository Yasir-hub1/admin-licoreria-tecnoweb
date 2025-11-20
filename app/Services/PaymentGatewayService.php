<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Venta;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

date_default_timezone_set('America/La_Paz');

class PaymentGatewayService
{
    private $client_guzzle;
    private $url_login;
    private $url_qr;
    private $url_consultar;
    private $clientCode;
    private $callbackUrl;
    private $tokenService;
    private $tokenSecret;
    private const CACHE_KEY_ACCESS_TOKEN = 'pagofacil_access_token';
    private const CACHE_KEY_TOKEN_EXPIRES = 'pagofacil_token_expires';

    public function __construct()
    {
        $this->client_guzzle = new Client();
        $this->url_login = "https://masterqr.pagofacil.com.bo/api/services/v2/login";
        $this->url_qr = "https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr";
        $this->url_consultar = "https://serviciostigomoney.pagofacil.com.bo/api/servicio/consultartransaccion";

        // Obtener credenciales del .env
        $this->tokenService = env('PAGO_FACIL_TCTOKEN_SERVICE');
        $this->tokenSecret = env('PAGO_FACIL_TCTOKEN_SECRET');
        $this->clientCode = env('PAGO_FACIL_CLIENT_CODE', env('PAGO_FACIL_COMERCE_ID')); // Código de cliente
        $this->callbackUrl = env('PAGO_FACIL_CALLBACK_URL', 'https://db17d557a193.ngrok-free.app/payment/callback');
    }

    /**
     * Autenticarse en la API de PagoFacil y obtener accessToken
     */
    private function authenticate()
    {
        try {
            if (!$this->tokenService || !$this->tokenSecret) {
                throw new \Exception('Credenciales de PagoFacil no configuradas. Verifica PAGO_FACIL_TCTOKEN_SERVICE y PAGO_FACIL_TCTOKEN_SECRET en .env');
            }

            $headers = [
                'Content-Type' => 'application/json',
                'tcTokenService' => $this->tokenService,
                'tcTokenSecret' => $this->tokenSecret
            ];

            Log::info('Autenticando en PagoFacil API', ['url' => $this->url_login]);

            $response = $this->client_guzzle->post($this->url_login, [
                'headers' => $headers
            ]);

            $result = json_decode($response->getBody()->getContents());

            if (isset($result->error) && $result->error == 1) {
                throw new \Exception('Error en autenticación: ' . ($result->message ?? 'Error desconocido'));
            }

            if (!isset($result->values->accessToken)) {
                throw new \Exception('No se recibió accessToken en la respuesta de autenticación');
            }

            $accessToken = $result->values->accessToken;
            $expiresInMinutes = $result->values->expiresInMinutes ?? 200; // Por defecto 200 minutos si no viene

            // Guardar token en cache con tiempo de expiración (con margen de 5 minutos antes)
            $cacheTime = (int) ($expiresInMinutes - 5) * 60; // Convertir a segundos y restar 5 minutos de margen

            Cache::put(self::CACHE_KEY_ACCESS_TOKEN, $accessToken, $cacheTime);
            Cache::put(self::CACHE_KEY_TOKEN_EXPIRES, now()->addMinutes($expiresInMinutes), $cacheTime);

            Log::info('Autenticación exitosa en PagoFacil', [
                'expiresInMinutes' => $expiresInMinutes,
                'cacheTime' => $cacheTime
            ]);

            return $accessToken;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Error en autenticación PagoFacil', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw new \Exception('Error al autenticarse en PagoFacil: ' . $e->getMessage());
        }
    }

    /**
     * Obtener accessToken válido (desde cache o renovarlo si es necesario)
     */
    private function getAccessToken()
    {
        // Verificar si hay token en cache y si aún es válido
        $accessToken = Cache::get(self::CACHE_KEY_ACCESS_TOKEN);
        $expiresAt = Cache::get(self::CACHE_KEY_TOKEN_EXPIRES);

        // Si no hay token o está expirado, autenticarse de nuevo
        if (!$accessToken || !$expiresAt || now()->greaterThan($expiresAt)) {
            Log::info('Token expirado o no existe, renovando autenticación');
            return $this->authenticate();
        }

        return $accessToken;
    }

    /**
     * Procesar pago con pasarela QR
     */
    public function processQRPayment(Venta $venta, $cliente)
    {
        DB::beginTransaction();
        try {
            // Generar número de pago único
            $nroPago = $this->generateNroPago();

            // Cargar relación usuario si no está cargada
            if (!$cliente->relationLoaded('usuario')) {
                $cliente->load('usuario');
            }

            // Crear registro de pago
            $pago = Pago::create([
                'venta_id' => $venta->id,
                'nro_pago' => $nroPago,
                'tipo_pago' => 'qr',
                'estado' => 'procesando',
                'monto' => $venta->monto_total,
                'nombre_persona' => $cliente->nombre,
                'email' => $cliente->usuario->email ?? null,
                'telefono' => $cliente->telefono,
                'nit' => $cliente->ci,
                'fecha_pago' => now()
            ]);

            // Preparar detalles del pago
            $detalles = $this->preparePaymentDetails($venta);

            // Generar QR
            $result = $this->generateQR($pago, $detalles, $cliente);

            // Verificar si hay error en la respuesta (nueva API puede usar diferentes estructuras)
            if (isset($result->error) && $result->error == 1) {
                DB::rollBack();
                throw new \Exception($result->message ?? $result->data->message ?? 'Error al generar QR');
            }

            // Verificar si la respuesta indica error o fallo
            if (isset($result->success) && $result->success === false) {
                DB::rollBack();
                throw new \Exception($result->message ?? $result->data->message ?? 'Error al generar QR');
            }

            // Verificar si no hay datos de QR en la respuesta
            if (!isset($result->data->qrImage) && !isset($result->qrImage) && !isset($result->data->qrCode) && !isset($result->qrCode)) {
                Log::warning('Respuesta de API QR sin imagen', ['response' => $result]);
                // No hacer rollback aquí, puede que la API devuelva el QR de otra forma
            }

            // Guardar imagen QR - La nueva API devuelve en result->values->qrBase64
            Log::info('Procesando respuesta QR', [
                'error' => $result->error ?? null,
                'status' => $result->status ?? null,
                'message' => $result->message ?? null,
                'has_values' => isset($result->values)
            ]);

            $qrImage = null;
            $transactionId = null;

            // La API devuelve el QR en result->values->qrBase64 (base64)
            if (isset($result->values->qrBase64)) {
                $qrImage = $result->values->qrBase64;
            } elseif (isset($result->values->qrImage)) {
                $qrImage = $result->values->qrImage;
            } elseif (isset($result->data->qrBase64)) {
                $qrImage = $result->data->qrBase64;
            } elseif (isset($result->data->qrImage)) {
                $qrImage = $result->data->qrImage;
            } elseif (isset($result->qrImage)) {
                $qrImage = $result->qrImage;
            }

            if ($qrImage) {
                // Decodificar base64 y guardar
                $binaryData = base64_decode($qrImage);
                if ($binaryData !== false && strlen($binaryData) > 0) {
                    $fileName = time() . '_' . $nroPago . '.png';
                    Storage::disk('public')->put('pagos/qr/' . $fileName, $binaryData, 'public');
                    $pago->qr_image = Storage::url('pagos/qr/' . $fileName);
                    Log::info('QR guardado exitosamente', ['file' => $fileName, 'size' => strlen($binaryData)]);
                } else {
                    Log::warning('Error decodificando QR base64 o datos vacíos');
                }
            } else {
                Log::warning('No se encontró QR en la respuesta', [
                    'result_keys' => array_keys((array)$result),
                    'has_values' => isset($result->values),
                    'values_keys' => isset($result->values) ? array_keys((array)$result->values) : null
                ]);
            }

            // Guardar número de transacción si viene en la respuesta
            if (isset($result->values->transactionId)) {
                $transactionId = $result->values->transactionId;
            } elseif (isset($result->values->paymentMethodTransactionId)) {
                $transactionId = $result->values->paymentMethodTransactionId;
            } elseif (isset($result->data->transactionId)) {
                $transactionId = $result->data->transactionId;
            } elseif (isset($result->transactionId)) {
                $transactionId = $result->transactionId;
            }

            if ($transactionId) {
                $pago->nro_transaccion = $transactionId;
            }

            $pago->estado = 'pendiente';
            $pago->save();

            // Actualizar estado de la venta
            $venta->estado_pago = 'pendiente';
            $venta->save();

            DB::commit();

            return [
                'pago' => $pago,
                'result' => $result,
                'message' => 'QR generado exitosamente'
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error en PaymentGatewayService (QR): ' . $th->getMessage());
            throw $th;
        }
    }

    /**
     * Procesar pago en efectivo (sin pasarela)
     */
    public function processCashPayment(Venta $venta, $cliente)
    {
        DB::beginTransaction();
        try {
            $nroPago = $this->generateNroPago();

            // Cargar relación usuario si no está cargada
            if (!$cliente->relationLoaded('usuario')) {
                $cliente->load('usuario');
            }

            $pago = Pago::create([
                'venta_id' => $venta->id,
                'nro_pago' => $nroPago,
                'tipo_pago' => 'efectivo',
                'estado' => 'completado',
                'monto' => $venta->monto_total,
                'nombre_persona' => $cliente->nombre,
                'email' => $cliente->usuario->email ?? null,
                'telefono' => $cliente->telefono,
                'nit' => $cliente->ci,
                'fecha_pago' => now(),
                'fecha_confirmacion' => now()
            ]);

            // Actualizar estado de la venta
            $venta->estado_pago = 'completado';
            if ($venta->tipo === 'contado') {
                $venta->estado = 'completado';
            }
            $venta->save();

            DB::commit();

            return $pago;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error procesando pago en efectivo: ' . $th->getMessage());
            throw $th;
        }
    }

    /**
     * Confirmar pago desde callback
     */
    public function confirmPayment($nroPago)
    {
        // Buscar por nro_pago (puede venir como string o número)
        $pago = Pago::where('nro_pago', $nroPago)
                    ->orWhere('nro_pago', (string) $nroPago)
                    ->with(['venta', 'cuotaPago.credito'])
                    ->first();

        if (!$pago) {
            Log::warning('Pago no encontrado en callback', ['nro_pago' => $nroPago]);
            throw new \Exception('Pago no encontrado con número: ' . $nroPago);
        }

        DB::beginTransaction();
        try {
            $pago->estado = 'completado';
            $pago->fecha_confirmacion = now();
            $pago->save();

            $venta = $pago->venta;
            if ($venta) {
                $venta->estado_pago = 'completado';
                if ($venta->tipo === 'contado') {
                    $venta->estado = 'completado';
                }
                $venta->save();
            }

            // Si es un pago de cuota de crédito, registrar el pago
            if ($pago->cuotaPago) {
                $cuotaPago = $pago->cuotaPago;
                $credito = $cuotaPago->credito;

                if ($credito && !$cuotaPago->fecha_pago) {
                    // Registrar el pago de la cuota
                    $cuotaPago->update([
                        'fecha_pago' => now(),
                        'monto' => $pago->monto,
                        'metodo' => $pago->tipo_pago,
                        'nro_transaccion' => $pago->nro_transaccion ?? $cuotaPago->nro_transaccion,
                        'observacion' => 'Pago confirmado desde pasarela'
                    ]);

                    // Actualizar saldo del crédito
                    $credito->saldo -= $pago->monto;
                    if ($credito->saldo <= 0) {
                        $credito->saldo = 0;
                        $credito->estado = 'pagado';
                        if ($credito->venta) {
                            $credito->venta->update([
                                'saldo' => 0,
                                'estado' => 'completado'
                            ]);
                        }
                    }
                    $credito->save();

                    // Actualizar estado del crédito
                    $creditService = app(\App\Services\CreditService::class);
                    $creditService->updateCreditStatus($credito->id);
                }
            }

            DB::commit();

            return $pago;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error confirmando pago: ' . $th->getMessage());
            throw $th;
        }
    }

    /**
     * Consultar estado de pago
     */
    public function consultPaymentStatus(Pago $pago)
    {
        if (!$pago->nro_transaccion) {
            return null;
        }

        try {
            // Obtener accessToken válido
            $accessToken = $this->getAccessToken();

            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken
            ];

            $body = ["TransaccionDePago" => $pago->nro_transaccion];

            $response = $this->client_guzzle->post($this->url_consultar, [
                'headers' => $headers,
                'json' => $body
            ]);

            $result = json_decode($response->getBody()->getContents());

            // Estado 3 = Completado, Estado 0 = Pendiente
            if (isset($result->values->estadoPago) && $result->values->estadoPago == 3) {
                $this->confirmPayment($pago->nro_pago);
            }

            return $result;
        } catch (\Throwable $th) {
            Log::error('Error consultando estado de pago: ' . $th->getMessage());
            return null;
        }
    }

    /**
     * Generar QR en la pasarela usando la nueva API v2
     */
    private function generateQR(Pago $pago, $detalles, $cliente)
    {
        // Preparar detalles de la orden según la nueva estructura
        $orderDetail = [];
        $serial = 1;
        foreach ($detalles as $detalle) {
            $orderDetail[] = [
                "serial" => $serial++,
                "product" => $detalle['producto'],
                "quantity" => $detalle['cantidad'],
                "price" => $detalle['precio'],
                "discount" => 0,
                "total" => $detalle['total']
            ];
        }

        // Obtener accessToken válido (se renueva automáticamente si es necesario)
        $accessToken = $this->getAccessToken();

        // Headers con Authorization Bearer usando el accessToken de PagoFacil
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken
        ];

        // Body según la nueva estructura de la API
        $body = [
            "paymentMethod" => 4, // 4 = QR
            "clientName" => $pago->nombre_persona,
            "documentType" => 1, // 1 = CI
            "documentId" => $pago->nit,
            "phoneNumber" => $pago->telefono,
            "email" => $pago->email ?? '',
            "paymentNumber" => (string) $pago->nro_pago,
            "amount" => (float) $pago->monto,
            "currency" => 2, // 2 = Bs (Bolivianos)
            "clientCode" => $this->clientCode,
            "callbackUrl" => $this->callbackUrl,
            "orderDetail" => $orderDetail
        ];

        Log::info('Generando QR con nueva API', [
            'url' => $this->url_qr,
            'paymentNumber' => $pago->nro_pago,
            'amount' => $pago->monto,
            'callbackUrl' => $this->callbackUrl
        ]);

        try {
            $response = $this->client_guzzle->post($this->url_qr, [
                'headers' => $headers,
                'json' => $body
            ]);

            $responseBody = $response->getBody()->getContents();
            $result = json_decode($responseBody);

            Log::info('Respuesta de API QR', [
                'status' => $response->getStatusCode(),
                'response' => $result
            ]);

            return $result;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Error en petición a API QR', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw new \Exception('Error al comunicarse con la pasarela de pagos: ' . $e->getMessage());
        }
    }

    /**
     * Preparar detalles del pago desde la venta
     */
    private function preparePaymentDetails(Venta $venta)
    {
        $detalles = [];

        // Si la venta tiene detalles, usarlos
        if ($venta->detalles && $venta->detalles->count() > 0) {
            foreach ($venta->detalles as $detalle) {
                $detalles[] = [
                    'producto' => $detalle->producto->nombre ?? 'Producto',
                    'cantidad' => $detalle->cantidad,
                    'precio' => $detalle->precio_unitario,
                    'total' => $detalle->subtotal
                ];
            }
        } else {
            // Si no tiene detalles (venta temporal de cuota), crear un detalle genérico
            $detalles[] = [
                'producto' => 'Pago de Cuota de Crédito',
                'cantidad' => 1,
                'precio' => $venta->monto_total,
                'total' => $venta->monto_total
            ];
        }

        return $detalles;
    }

    /**
     * Generar número de pago único
     * Retorna string para compatibilidad con la API
     */
    private function generateNroPago()
    {
        return (string) rand(188888889, 999999999);
    }
}
