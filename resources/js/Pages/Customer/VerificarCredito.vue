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
                                :src="preview.carnet_anverso || `/storage/${cliente.carnet_anverso}`" 
                                alt="Carnet Anverso"
                                class="w-full max-w-xs h-48 object-contain border rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                @change="handleFileChange('carnet_anverso', $event)"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <p class="text-xs text-gray-500 mt-1">Formato: JPG, PNG. Tamaño máximo: 5MB</p>
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
                                :src="preview.carnet_reverso || `/storage/${cliente.carnet_reverso}`" 
                                alt="Carnet Reverso"
                                class="w-full max-w-xs h-48 object-contain border rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                @change="handleFileChange('carnet_reverso', $event)"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <p class="text-xs text-gray-500 mt-1">Formato: JPG, PNG. Tamaño máximo: 5MB</p>
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
                                :src="preview.foto_luz || `/storage/${cliente.foto_luz}`" 
                                alt="Factura de Luz"
                                class="w-full max-w-xs h-48 object-contain border rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                @change="handleFileChange('foto_luz', $event)"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <p class="text-xs text-gray-500 mt-1">Formato: JPG, PNG. Tamaño máximo: 5MB</p>
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
                                :src="preview.foto_agua || `/storage/${cliente.foto_agua}`" 
                                alt="Factura de Agua"
                                class="w-full max-w-xs h-48 object-contain border rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                @change="handleFileChange('foto_agua', $event)"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <p class="text-xs text-gray-500 mt-1">Formato: JPG, PNG. Tamaño máximo: 5MB</p>
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
                                :src="preview.foto_garantia || `/storage/${cliente.foto_garantia}`" 
                                alt="Objeto de Garantía"
                                class="w-full max-w-xs h-48 object-contain border rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                @change="handleFileChange('foto_garantia', $event)"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <p class="text-xs text-gray-500 mt-1">Formato: JPG, PNG. Tamaño máximo: 5MB</p>
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
                    <Link href="/profile" class="flex-1">
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
import ShopLayout from '@/Layouts/ShopLayout.vue';
import Button from '@/Components/Button.vue';

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

const form = useForm({
    carnet_anverso: null,
    carnet_reverso: null,
    foto_luz: null,
    foto_agua: null,
    foto_garantia: null
});

const handleFileChange = (field, event) => {
    const file = event.target.files[0];
    if (file) {
        // Validar tamaño (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('El archivo es demasiado grande. El tamaño máximo es 5MB.');
            event.target.value = '';
            return;
        }

        // Validar tipo
        if (!file.type.startsWith('image/')) {
            alert('Solo se permiten archivos de imagen.');
            event.target.value = '';
            return;
        }

        form[field] = file;

        // Crear preview
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.value[field] = e.target.result;
        };
        reader.readAsDataURL(file);
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
    form.post('/verificar-credito', {
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

