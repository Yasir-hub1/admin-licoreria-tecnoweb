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
    private $url_list_methods;
    private $url_qr;
    private $url_query;
    private $clientCode;
    private $callbackUrl;
    private $tokenService;
    private $tokenSecret;
    private const CACHE_KEY_ACCESS_TOKEN = 'pagofacil_access_token';
    private const CACHE_KEY_TOKEN_EXPIRES = 'pagofacil_token_expires';
    private const CACHE_KEY_PAYMENT_METHOD_ID = 'pagofacil_payment_method_id';

    public function __construct()
    {
        $this->client_guzzle = new Client();
        $this->url_login = "https://masterqr.pagofacil.com.bo/api/services/v2/login";
        $this->url_list_methods = "https://masterqr.pagofacil.com.bo/api/services/v2/list-enabled-services";
        $this->url_qr = "https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr";
        $this->url_query = "https://masterqr.pagofacil.com.bo/api/services/v2/query-transaction";

        // Obtener credenciales del .env
        $this->tokenService = env('PAGO_FACIL_TCTOKEN_SERVICE');
        $this->tokenSecret = env('PAGO_FACIL_TCTOKEN_SECRET');
        $this->clientCode = env('PAGO_FACIL_CLIENT_CODE', env('PAGO_FACIL_COMERCE_ID')); // Código de cliente
        $this->callbackUrl = env('PAGO_FACIL_CALLBACK_URL', 'http://64.227.111.150:8085/payment/callback');
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
     * Cabeceras comunes para endpoints protegidos de PagoFácil API v2.
     */
    private function getApiHeaders(string $accessToken): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
            'Response-Language' => 'es',
        ];
    }

    /**
     * PASO 2: Listar métodos de pago habilitados y obtener paymentMethodId.
     * Manual v1.1.0: POST /list-enabled-services
     */
    private function getPaymentMethodId(bool $forceRefresh = false): int
    {
        if (!$forceRefresh) {
            $cachedId = Cache::get(self::CACHE_KEY_PAYMENT_METHOD_ID);
            if ($cachedId !== null) {
                return (int) $cachedId;
            }
        }

        $configuredId = env('PAGO_FACIL_PAYMENT_METHOD_ID');
        if ($configuredId !== null && $configuredId !== '') {
            return (int) $configuredId;
        }

        try {
            $accessToken = $this->getAccessToken();

            Log::info('Listando métodos de pago habilitados', ['url' => $this->url_list_methods]);

            $response = $this->client_guzzle->post($this->url_list_methods, [
                'headers' => $this->getApiHeaders($accessToken),
            ]);

            $result = json_decode($response->getBody()->getContents());

            if (isset($result->error) && (int) $result->error === 1) {
                throw new \Exception($result->message ?? 'Error al listar métodos de pago');
            }

            if (!isset($result->values) || !is_array($result->values) || count($result->values) === 0) {
                throw new \Exception('No hay métodos de pago QR habilitados para su comercio en PagoFácil.');
            }

            Log::info('Métodos QR habilitados recibidos', ['methods' => $result->values]);

            $paymentMethodId = null;
            $paymentMethodName = null;

            foreach ($result->values as $method) {
                $name = $method->paymentMethodName ?? '';
                if (stripos($name, 'QR') !== false) {
                    $paymentMethodId = (int) $method->paymentMethodId;
                    $paymentMethodName = $name;
                    break;
                }
            }

            if ($paymentMethodId === null) {
                $paymentMethodId = (int) $result->values[0]->paymentMethodId;
                $paymentMethodName = $result->values[0]->paymentMethodName ?? null;
            }

            Log::info('paymentMethodId seleccionado para generate-qr', [
                'paymentMethodId' => $paymentMethodId,
                'paymentMethodName' => $paymentMethodName,
            ]);

            Cache::put(self::CACHE_KEY_PAYMENT_METHOD_ID, $paymentMethodId, 86400);

            return $paymentMethodId;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

            Log::error('Error al listar métodos de pago PagoFácil', [
                'message' => $e->getMessage(),
                'response' => $responseBody,
            ]);

            throw new \Exception(
                'No se pudo obtener el método de pago habilitado en PagoFácil: ' .
                ($responseBody ?: $e->getMessage())
            );
        }
    }

    /**
     * Procesar pago con pasarela QR
     *
     * @param  float|null  $monto  Monto a cobrar (ej. primera cuota). Si es null, usa monto_total de la venta.
     * @param  string|null  $descripcionCuota  Descripción para orderDetail cuando el monto es de una cuota.
     */
    public function processQRPayment(Venta $venta, $cliente, ?float $monto = null, ?string $descripcionCuota = null)
    {
        DB::beginTransaction();
        try {
            $montoPago = $monto ?? (float) $venta->monto_total;

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
                'monto' => $montoPago,
                'nombre_persona' => $cliente->nombre,
                'email' => $cliente->usuario->email ?? null,
                'telefono' => $cliente->telefono,
                'nit' => $cliente->ci,
                'fecha_pago' => now()
            ]);

            // Preparar detalles del pago (cuota: un solo ítem con el monto exacto del QR)
            $detalles = $descripcionCuota
                ? $this->prepareCuotaPaymentDetails($montoPago, $descripcionCuota)
                : $this->preparePaymentDetails($venta);

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

            // Según documentación oficial, el QR viene en result->values->qrBase64 o result->qrBase64
            // También puede venir en result->data->qrBase64 según versiones anteriores
            if (isset($result->values->qrBase64)) {
                $qrImage = $result->values->qrBase64;
            } elseif (isset($result->qrBase64)) {
                $qrImage = $result->qrBase64;
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
                    $pago->qr_image = 'pagos/qr/' . $fileName;
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

            // Guardar número de transacción según documentación oficial
            // transactionId = ID de PagoFácil (lo usaremos para consultar estado)
            // paymentMethodTransactionId = nuestro ID interno (paymentNumber)
            if (isset($result->values->transactionId)) {
                $transactionId = $result->values->transactionId;
                $pago->nro_transaccion = $transactionId;
            } elseif (isset($result->transactionId)) {
                $transactionId = $result->transactionId;
                $pago->nro_transaccion = $transactionId;
            }

            // Guardar también nuestro ID interno si viene separado
            if (isset($result->values->paymentMethodTransactionId)) {
                // Este debería ser igual a nuestro paymentNumber
                Log::info('PaymentMethodTransactionId recibido', [
                    'paymentMethodTransactionId' => $result->values->paymentMethodTransactionId,
                    'nro_pago' => $pago->nro_pago
                ]);
            }

            // Guardar fecha de expiración del QR si viene (opcional, solo si el campo existe en BD)
            if (isset($result->values->expirationDate)) {
                try {
                    $expirationDate = \Carbon\Carbon::parse($result->values->expirationDate);
                    // Solo guardar si el campo existe en el modelo
                    if (in_array('qr_expires_at', $pago->getFillable()) || $pago->getConnection()->getSchemaBuilder()->hasColumn($pago->getTable(), 'qr_expires_at')) {
                        $pago->qr_expires_at = $expirationDate;
                    } else {
                        Log::info('Campo qr_expires_at no existe en tabla, guardando en observaciones', [
                            'expirationDate' => $result->values->expirationDate
                        ]);
                        // Guardar en observaciones como alternativa
                        $pago->observaciones = ($pago->observaciones ?? '') . ' | QR expira: ' . $expirationDate->format('Y-m-d H:i:s');
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parseando expirationDate', ['date' => $result->values->expirationDate, 'error' => $e->getMessage()]);
                }
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
     * PASO 5B: Consultar estado de pago manualmente
     * Según documentación oficial: /query-transaction
     * Se usa cuando no hay callback o para verificar manualmente
     */
    public function consultPaymentStatus(Pago $pago)
    {
        try {
            // Obtener accessToken válido
            $accessToken = $this->getAccessToken();

            $headers = $this->getApiHeaders($accessToken);

            $body = [];

            if ($pago->nro_transaccion) {
                $body['pagofacilTransactionId'] = $pago->nro_transaccion;
            } else {
                $body['companyTransactionId'] = $pago->nro_pago;
            }

            Log::info('Consultando estado de pago', [
                'url' => $this->url_query,
                'body' => $body
            ]);

            $response = $this->client_guzzle->post($this->url_query, [
                'headers' => $headers,
                'json' => $body
            ]);

            $result = json_decode($response->getBody()->getContents());

            Log::info('Respuesta de consulta de estado', ['result' => $result]);

            // Según documentación oficial:
            // paymentStatus: 1 = PAGADO, 2 = PENDIENTE, 3 = EXPIRADO, 4 = CANCELADO
            $paymentInfo = null;

            if (isset($result->values)) {
                $values = $result->values;
                $paymentStatus = $values->paymentStatus ?? null;

                // Si el pago está completado (status = 1) y aún no está confirmado en nuestro sistema
                if ($paymentStatus == 1 && $pago->estado !== 'completado') {
                    Log::info('Pago confirmado desde consulta manual', [
                        'nro_pago' => $pago->nro_pago,
                        'paymentStatus' => $paymentStatus
                    ]);
                    $this->confirmPayment($pago->nro_pago);
                }

                // Actualizar información adicional si viene
                if (isset($values->amount)) {
                    $pago->monto = $values->amount;
                }
                if (isset($values->paymentDate) && isset($values->paymentTime)) {
                    try {
                        $pago->fecha_confirmacion = \Carbon\Carbon::parse(
                            $values->paymentDate . ' ' . $values->paymentTime
                        );
                    } catch (\Exception $e) {
                        Log::warning('Error parseando fecha de pago', [
                            'date' => $values->paymentDate,
                            'time' => $values->paymentTime
                        ]);
                    }
                }
                $pago->save();

                // Extraer datos relevantes para mostrar en el modal
                $paymentInfo = [
                    'paymentStatus' => $paymentStatus,
                    'paymentStatusDescription' => $values->paymentStatusDescription ?? null,
                    'amount' => $values->amount ?? null,
                    'currencyCode' => $values->currencyCode ?? 'BOB',
                    'paymentMethodId' => $values->paymentMethodId ?? null,
                    'paymentMethodDetail' => $values->paymentMethodDetail ?? null,
                    'pagofacilTransactionId' => $values->pagofacilTransactionId ?? null,
                    'companyTransactionId' => $values->companyTransactionId ?? null,
                    'requestDate' => $values->requestDate ?? null,
                    'requestTime' => $values->requestTime ?? null,
                    'paymentDate' => $values->paymentDate ?? null,
                    'paymentTime' => $values->paymentTime ?? null,
                    'payerName' => $values->payerName ?? null,
                    'payerDocument' => $values->payerDocument ?? null,
                    'payerAccount' => $values->payerAccount ?? null,
                    'payerBank' => $values->payerBank ?? null,
                ];
            }

            return [
                'result' => $result,
                'paymentInfo' => $paymentInfo
            ];
        } catch (\Throwable $th) {
            Log::error('Error consultando estado de pago: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);
            return [
                'result' => null,
                'paymentInfo' => null
            ];
        }
    }

    /**
     * PASO 3: Generar QR en la pasarela usando la nueva API v2
     * Según documentación oficial: /generate-qr
     */
    private function generateQR(Pago $pago, $detalles, $cliente, bool $retry = false)
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

        // PASO 2: Obtener paymentMethodId real del comercio (POST /list-enabled-services)
        $paymentMethodId = $this->getPaymentMethodId($retry);

        $headers = $this->getApiHeaders($accessToken);

        // Body según documentación oficial
        // paymentNumber = ID interno nuestro del pedido
        // callbackUrl = URL donde PagoFácil enviará el resultado del pago
        $body = [
            "paymentMethod" => $paymentMethodId,
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
            'paymentMethod' => $paymentMethodId,
            'paymentNumber' => $pago->nro_pago,
            'amount' => $pago->monto,
            'callbackUrl' => $this->callbackUrl,
            'retry' => $retry,
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
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

            Log::error('Error en petición a API QR', [
                'message' => $e->getMessage(),
                'response' => $responseBody,
                'paymentMethod' => $paymentMethodId,
            ]);

            if (
                !$retry
                && $responseBody
                && stripos($responseBody, 'Payment Method ID') !== false
            ) {
                Cache::forget(self::CACHE_KEY_PAYMENT_METHOD_ID);
                Log::warning('paymentMethodId inválido en caché, reintentando con list-enabled-services');

                return $this->generateQR($pago, $detalles, $cliente, true);
            }

            $apiMessage = $responseBody ? (json_decode($responseBody)->message ?? $responseBody) : $e->getMessage();
            throw new \Exception('Error al comunicarse con la pasarela de pagos: ' . $apiMessage);
        }
    }

    /**
     * Detalle de pago para una cuota (monto debe coincidir con amount del QR).
     */
    private function prepareCuotaPaymentDetails(float $monto, string $descripcion): array
    {
        return [[
            'producto' => $descripcion,
            'cantidad' => 1,
            'precio' => $monto,
            'total' => $monto,
        ]];
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
