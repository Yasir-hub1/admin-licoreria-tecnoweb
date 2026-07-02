<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8 max-w-4xl">
            <div class="mb-6">
                <h1 class="text-3xl font-bold mb-2">Verificación para Crédito</h1>
                <p class="text-gray-600">Sube los documentos necesarios para solicitar aprobación de crédito</p>
            </div>

            <!-- Estado de Verificación -->
            <div v-if="cliente.estado_verificacion && cliente.estado_verificacion !== 'pendiente'"
                 class="mb-6 p-4 rounded-lg"
                 :class="{
                     'bg-blue-50 border border-blue-200': cliente.estado_verificacion === 'en_revision',
                     'bg-green-50 border border-green-200': cliente.estado_verificacion === 'aprobado',
                     'bg-red-50 border border-red-200': cliente.estado_verificacion === 'rechazado'
                 }">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">
                        <span v-if="cliente.estado_verificacion === 'en_revision'">⏳</span>
                        <span v-else-if="cliente.estado_verificacion === 'aprobado'">✅</span>
                        <span v-else-if="cliente.estado_verificacion === 'rechazado'">❌</span>
                    </span>
                    <div class="flex-1">
                        <h3 class="font-semibold mb-1">
                            <span v-if="cliente.estado_verificacion === 'en_revision'">En Revisión</span>
                            <span v-else-if="cliente.estado_verificacion === 'aprobado'">Aprobado</span>
                            <span v-else-if="cliente.estado_verificacion === 'rechazado'">Rechazado</span>
                        </h3>
                        <p v-if="cliente.observaciones_verificacion" class="text-sm text-gray-700">
                            {{ cliente.observaciones_verificacion }}
                        </p>
                        <p v-if="cliente.estado_verificacion === 'aprobado'" class="text-sm text-green-700 mt-2">
                            ¡Felicidades! Tu solicitud de crédito ha sido aprobada. Ya puedes realizar compras a crédito.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario de Documentos -->
            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6 space-y-6">
                <!-- Carnet Anverso -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Carnet de Identidad - Anverso <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <div v-if="preview.carnet_anverso || cliente.carnet_anverso" class="flex-1">
                            <img
                                :src="preview.carnet_anverso || storageUrl(cliente.carnet_anverso)"
                                alt="Carnet Anverso"
                                class="w-full max-w-xs h-48 object-contain border rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                @change="handleFileChange('carnet_anverso', $event)"
                                accept="image/*"
                                :disabled="compressing.carnet_anverso"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 disabled:opacity-50"
                            />
                            <div v-if="compressing.carnet_anverso" class="mt-2 text-sm text-blue-600 flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Comprimiendo imagen...
                            </div>
                            <p v-if="compressionInfo.carnet_anverso" class="text-xs text-gray-500 mt-1">{{ compressionInfo.carnet_anverso }}</p>
                            <p v-else class="text-xs text-gray-500 mt-1">Formato: JPG, PNG. Tamaño máximo: 5MB</p>
                        </div>
                    </div>
                    <p v-if="form.errors.carnet_anverso" class="mt-1 text-sm text-red-600">{{ form.errors.carnet_anverso }}</p>
                </div>

                <!-- Carnet Reverso -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Carnet de Identidad - Reverso <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <div v-if="preview.carnet_reverso || cliente.carnet_reverso" class="flex-1">
                            <img
                                :src="preview.carnet_reverso || storageUrl(cliente.carnet_reverso)"
                                alt="Carnet Reverso"
                                class="w-full max-w-xs h-48 object-contain border rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                @change="handleFileChange('carnet_reverso', $event)"
                                accept="image/*"
                                :disabled="compressing.carnet_reverso"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 disabled:opacity-50"
                            />
                            <div v-if="compressing.carnet_reverso" class="mt-2 text-sm text-blue-600 flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Comprimiendo imagen...
                            </div>
                            <p v-if="compressionInfo.carnet_reverso" class="text-xs text-gray-500 mt-1">{{ compressionInfo.carnet_reverso }}</p>
                            <p v-else class="text-xs text-gray-500 mt-1">Formato: JPG, PNG. Tamaño máximo: 5MB</p>
                        </div>
                    </div>
                    <p v-if="form.errors.carnet_reverso" class="mt-1 text-sm text-red-600">{{ form.errors.carnet_reverso }}</p>
                </div>

                <!-- Foto de Luz -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Factura de Luz <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <div v-if="preview.foto_luz || cliente.foto_luz" class="flex-1">
                            <img
                                :src="preview.foto_luz || storageUrl(cliente.foto_luz)"
                                alt="Factura de Luz"
                                class="w-full max-w-xs h-48 object-contain border rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                @change="handleFileChange('foto_luz', $event)"
                                accept="image/*"
                                :disabled="compressing.foto_luz"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 disabled:opacity-50"
                            />
                            <div v-if="compressing.foto_luz" class="mt-2 text-sm text-blue-600 flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Comprimiendo imagen...
                            </div>
                            <p v-if="compressionInfo.foto_luz" class="text-xs text-gray-500 mt-1">{{ compressionInfo.foto_luz }}</p>
                            <p v-else class="text-xs text-gray-500 mt-1">Formato: JPG, PNG. Tamaño máximo: 5MB</p>
                        </div>
                    </div>
                    <p v-if="form.errors.foto_luz" class="mt-1 text-sm text-red-600">{{ form.errors.foto_luz }}</p>
                </div>

                <!-- Foto de Agua -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Factura de Agua <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <div v-if="preview.foto_agua || cliente.foto_agua" class="flex-1">
                            <img
                                :src="preview.foto_agua || storageUrl(cliente.foto_agua)"
                                alt="Factura de Agua"
                                class="w-full max-w-xs h-48 object-contain border rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                @change="handleFileChange('foto_agua', $event)"
                                accept="image/*"
                                :disabled="compressing.foto_agua"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 disabled:opacity-50"
                            />
                            <div v-if="compressing.foto_agua" class="mt-2 text-sm text-blue-600 flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Comprimiendo imagen...
                            </div>
                            <p v-if="compressionInfo.foto_agua" class="text-xs text-gray-500 mt-1">{{ compressionInfo.foto_agua }}</p>
                            <p v-else class="text-xs text-gray-500 mt-1">Formato: JPG, PNG. Tamaño máximo: 5MB</p>
                        </div>
                    </div>
                    <p v-if="form.errors.foto_agua" class="mt-1 text-sm text-red-600">{{ form.errors.foto_agua }}</p>
                </div>

                <!-- Foto de Garantía -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Foto del Objeto de Garantía <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <div v-if="preview.foto_garantia || cliente.foto_garantia" class="flex-1">
                            <img
                            :src="preview.foto_garantia || storageUrl(cliente.foto_garantia)"
                            alt="Objeto de Garantía"
                                class="w-full max-w-xs h-48 object-contain border rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                @change="handleFileChange('foto_garantia', $event)"
                                accept="image/*"
                                :disabled="compressing.foto_garantia"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 disabled:opacity-50"
                            />
                            <div v-if="compressing.foto_garantia" class="mt-2 text-sm text-blue-600 flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Comprimiendo imagen...
                            </div>
                            <p v-if="compressionInfo.foto_garantia" class="text-xs text-gray-500 mt-1">{{ compressionInfo.foto_garantia }}</p>
                            <p v-else class="text-xs text-gray-500 mt-1">Formato: JPG, PNG. Tamaño máximo: 5MB</p>
                        </div>
                    </div>
                    <p v-if="form.errors.foto_garantia" class="mt-1 text-sm text-red-600">{{ form.errors.foto_garantia }}</p>
                </div>

                <!-- Información Adicional -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">📋 Información Importante</h3>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li>Todos los documentos deben estar claramente visibles</li>
                        <li>Las facturas deben ser recientes (últimos 3 meses)</li>
                        <li>El objeto de garantía debe estar claramente fotografiado</li>
                        <li>Una vez subidos, tus documentos serán revisados por un administrador</li>
                        <li>Recibirás una notificación cuando se complete la revisión</li>
                    </ul>
                </div>

                <!-- Botones -->
                <div class="flex gap-4 pt-4">
                    <Link :href="route('customer.profile')" class="flex-1">
                        <Button
                            type="button"
                            variant="outline"
                            full-width
                            size="lg"
                        >
                            Cancelar
                        </Button>
                    </Link>
                    <Button
                        type="submit"
                        :loading="form.processing"
                        :disabled="form.processing || !todosDocumentosSubidos"
                        variant="primary"
                        full-width
                        size="lg"
                    >
                        {{ cliente.estado_verificacion === 'pendiente' || !cliente.estado_verificacion ? 'Enviar para Verificación' : 'Actualizar Documentos' }}
                    </Button>
                </div>
            </form>
        </div>
    </ShopLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import Button from '@/Components/Button.vue';
import { useStorage } from '@/composables/useStorage';
const { storageUrl } = useStorage();


const props = defineProps({
    cliente: Object
});

const preview = ref({
    carnet_anverso: null,
    carnet_reverso: null,
    foto_luz: null,
    foto_agua: null,
    foto_garantia: null
});

const compressing = ref({
    carnet_anverso: false,
    carnet_reverso: false,
    foto_luz: false,
    foto_agua: false,
    foto_garantia: false
});

const compressionInfo = ref({
    carnet_anverso: null,
    carnet_reverso: null,
    foto_luz: null,
    foto_agua: null,
    foto_garantia: null
});

const form = useForm({
    carnet_anverso: null,
    carnet_reverso: null,
    foto_luz: null,
    foto_agua: null,
    foto_garantia: null
});

// Función para comprimir imagen usando Canvas API (igual que en ImageUpload.vue)
const compressImage = (file, field) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onload = (e) => {
            const img = new Image();

            img.onload = () => {
                compressing.value[field] = true;

                // Configuración de compresión (igual que en productos)
                const maxWidth = 1200;
                const maxHeight = 1200;
                const quality = 0.85;

                // Calcular nuevas dimensiones manteniendo la proporción
                let width = img.width;
                let height = img.height;

                if (width > maxWidth || height > maxHeight) {
                    const ratio = Math.min(maxWidth / width, maxHeight / height);
                    width = width * ratio;
                    height = height * ratio;
                }

                // Crear canvas para redimensionar y comprimir
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Convertir a blob con compresión
                canvas.toBlob(
                    (blob) => {
                        compressing.value[field] = false;

                        if (!blob) {
                            reject(new Error('Error al comprimir la imagen'));
                            return;
                        }

                        // Crear nuevo File desde el blob
                        const compressedFile = new File([blob], file.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });

                        // Calcular información de compresión
                        const originalSize = (file.size / 1024 / 1024).toFixed(2);
                        const compressedSize = (blob.size / 1024 / 1024).toFixed(2);
                        const reduction = ((1 - blob.size / file.size) * 100).toFixed(1);

                        compressionInfo.value[field] = `Original: ${originalSize}MB → Comprimido: ${compressedSize}MB (${reduction}% reducción)`;

                        resolve(compressedFile);
                    },
                    'image/jpeg',
                    quality
                );
            };

            img.onerror = () => {
                compressing.value[field] = false;
                reject(new Error('Error al cargar la imagen'));
            };

            img.src = e.target.result;
        };

        reader.onerror = () => {
            compressing.value[field] = false;
            reject(new Error('Error al leer el archivo'));
        };

        reader.readAsDataURL(file);
    });
};

const handleFileChange = async (field, event) => {
    const file = event.target.files[0];

    if (!file) {
        form[field] = null;
        preview.value[field] = null;
        compressionInfo.value[field] = null;
        return;
    }

    // Validar tipo
    if (!file.type.startsWith('image/')) {
        alert('Solo se permiten archivos de imagen.');
        event.target.value = '';
        return;
    }

    // Validar tamaño original (antes de comprimir) - permitir hasta 10MB para comprimir
    if (file.size > 10 * 1024 * 1024) {
        alert('El archivo es demasiado grande. El tamaño máximo es 10MB.');
        event.target.value = '';
        return;
    }

    try {
        // Comprimir la imagen
        const compressedFile = await compressImage(file, field);

        // Crear preview
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.value[field] = e.target.result;
        };
        reader.readAsDataURL(compressedFile);

        // Asignar el archivo comprimido al formulario
        form[field] = compressedFile;

    } catch (err) {
        alert(err.message || 'Error al procesar la imagen');
        event.target.value = '';
        preview.value[field] = null;
        compressionInfo.value[field] = null;
        compressing.value[field] = false;
    }
};

const todosDocumentosSubidos = computed(() => {
    return (form.carnet_anverso || props.cliente.carnet_anverso) &&
           (form.carnet_reverso || props.cliente.carnet_reverso) &&
           (form.foto_luz || props.cliente.foto_luz) &&
           (form.foto_agua || props.cliente.foto_agua) &&
           (form.foto_garantia || props.cliente.foto_garantia);
});

const submit = () => {
    form.post(route('customer.subir-documentos'), {
        forceFormData: true,
        onSuccess: () => {
            // Limpiar previews si se subieron nuevos archivos
            Object.keys(preview.value).forEach(key => {
                if (form[key]) {
                    preview.value[key] = null;
                }
            });
        }
    });
};
</script>

