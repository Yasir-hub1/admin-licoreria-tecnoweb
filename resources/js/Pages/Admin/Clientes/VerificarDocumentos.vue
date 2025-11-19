<template>
    <AdminLayout title="Verificación de Documentos" subtitle="Revisar y aprobar documentos de clientes para crédito">
        <div class="space-y-6">
            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por estado:</span>
                    <button
                        @click="filtroEstado = 'todos'"
                        :class="[
                            'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                            filtroEstado === 'todos' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                        ]"
                    >
                        Todos
                    </button>
                    <button
                        @click="filtroEstado = 'en_revision'"
                        :class="[
                            'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                            filtroEstado === 'en_revision' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                        ]"
                    >
                        En Revisión
                    </button>
                    <button
                        @click="filtroEstado = 'pendiente'"
                        :class="[
                            'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                            filtroEstado === 'pendiente' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                        ]"
                    >
                        Pendiente
                    </button>
                </div>
            </div>

            <!-- Lista de Clientes -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div
                    v-for="cliente in clientesFiltrados"
                    :key="cliente.id"
                    :id="`cliente-${cliente.id}`"
                    class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border transition-all duration-300"
                    :class="{
                        'border-blue-300 dark:border-blue-700': cliente.estado_verificacion === 'en_revision',
                        'border-yellow-300 dark:border-yellow-700': cliente.estado_verificacion === 'pendiente',
                        'ring-4 ring-purple-400 dark:ring-purple-600': clienteSeleccionado === cliente.id
                    }"
                >
                    <!-- Header del Cliente -->
                    <div class="p-4 border-b bg-gray-50 dark:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-gray-900 dark:text-white">{{ cliente.nombre }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">CI: {{ cliente.ci }}</p>
                            </div>
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': cliente.estado_verificacion === 'en_revision',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': cliente.estado_verificacion === 'pendiente'
                                }"
                            >
                                <span v-if="cliente.estado_verificacion === 'en_revision'">⏳ En Revisión</span>
                                <span v-else>📋 Pendiente</span>
                            </span>
                        </div>
                    </div>

                    <!-- Documentos -->
                    <div class="p-4 space-y-4">
                        <!-- Carnet Anverso -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Carnet Anverso
                            </label>
                            <div class="relative group">
                                <img
                                    :src="`/storage/${cliente.carnet_anverso}`"
                                    alt="Carnet Anverso"
                                    class="w-full h-32 object-contain border rounded-lg bg-gray-50 dark:bg-gray-700 cursor-pointer hover:opacity-90 transition-opacity"
                                    @click="abrirModal(cliente.carnet_anverso, 'Carnet Anverso')"
                                />
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 rounded-lg">
                                    <span class="text-white text-sm font-medium">Click para ampliar</span>
                                </div>
                            </div>
                        </div>

                        <!-- Carnet Reverso -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Carnet Reverso
                            </label>
                            <div class="relative group">
                                <img
                                    :src="`/storage/${cliente.carnet_reverso}`"
                                    alt="Carnet Reverso"
                                    class="w-full h-32 object-contain border rounded-lg bg-gray-50 dark:bg-gray-700 cursor-pointer hover:opacity-90 transition-opacity"
                                    @click="abrirModal(cliente.carnet_reverso, 'Carnet Reverso')"
                                />
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 rounded-lg">
                                    <span class="text-white text-sm font-medium">Click para ampliar</span>
                                </div>
                            </div>
                        </div>

                        <!-- Foto Luz -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Factura de Luz
                            </label>
                            <div class="relative group">
                                <img
                                    :src="`/storage/${cliente.foto_luz}`"
                                    alt="Factura de Luz"
                                    class="w-full h-32 object-contain border rounded-lg bg-gray-50 dark:bg-gray-700 cursor-pointer hover:opacity-90 transition-opacity"
                                    @click="abrirModal(cliente.foto_luz, 'Factura de Luz')"
                                />
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 rounded-lg">
                                    <span class="text-white text-sm font-medium">Click para ampliar</span>
                                </div>
                            </div>
                        </div>

                        <!-- Foto Agua -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Factura de Agua
                            </label>
                            <div class="relative group">
                                <img
                                    :src="`/storage/${cliente.foto_agua}`"
                                    alt="Factura de Agua"
                                    class="w-full h-32 object-contain border rounded-lg bg-gray-50 dark:bg-gray-700 cursor-pointer hover:opacity-90 transition-opacity"
                                    @click="abrirModal(cliente.foto_agua, 'Factura de Agua')"
                                />
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 rounded-lg">
                                    <span class="text-white text-sm font-medium">Click para ampliar</span>
                                </div>
                            </div>
                        </div>

                        <!-- Foto Garantía -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Objeto de Garantía
                            </label>
                            <div class="relative group">
                                <img
                                    :src="`/storage/${cliente.foto_garantia}`"
                                    alt="Objeto de Garantía"
                                    class="w-full h-32 object-contain border rounded-lg bg-gray-50 dark:bg-gray-700 cursor-pointer hover:opacity-90 transition-opacity"
                                    @click="abrirModal(cliente.foto_garantia, 'Objeto de Garantía')"
                                />
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 rounded-lg">
                                    <span class="text-white text-sm font-medium">Click para ampliar</span>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario de Aprobación/Rechazo -->
                        <div class="pt-4 border-t space-y-4">
                            <div v-if="accionCliente === cliente.id">
                                <div v-if="tipoAccion === 'aprobar'" class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Límite de Crédito (Bs.)
                                        </label>
                                        <input
                                            v-model="formAprobar.limite_credito"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            placeholder="Ej: 5000.00"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Observaciones (opcional)
                                        </label>
                                        <textarea
                                            v-model="formAprobar.observaciones"
                                            rows="2"
                                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            placeholder="Observaciones sobre la aprobación..."
                                        ></textarea>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="aprobarCliente(cliente.id)"
                                            :disabled="!formAprobar.limite_credito || formAprobar.processing"
                                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50"
                                        >
                                            Aprobar
                                        </button>
                                        <button
                                            @click="cancelarAccion"
                                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors"
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                                <div v-else-if="tipoAccion === 'rechazar'" class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Motivo del Rechazo <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="formRechazar.observaciones"
                                            rows="3"
                                            required
                                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            placeholder="Explica el motivo del rechazo..."
                                        ></textarea>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="rechazarCliente(cliente.id)"
                                            :disabled="!formRechazar.observaciones || formRechazar.processing"
                                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50"
                                        >
                                            Rechazar
                                        </button>
                                        <button
                                            @click="cancelarAccion"
                                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors"
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="flex gap-2">
                                <button
                                    @click="iniciarAprobar(cliente.id)"
                                    class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors"
                                >
                                    ✅ Aprobar
                                </button>
                                <button
                                    @click="iniciarRechazar(cliente.id)"
                                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors"
                                >
                                    ❌ Rechazar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="clientes.last_page > 1" class="flex justify-center">
                <div class="flex gap-2">
                    <Link
                        v-for="page in clientes.links"
                        :key="page.label"
                        :href="page.url"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors',
                            page.active
                                ? 'bg-blue-600 text-white'
                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                        ]"
                        v-html="page.label"
                    ></Link>
                </div>
            </div>

            <!-- Mensaje si no hay clientes -->
            <div v-if="clientesFiltrados.length === 0" class="text-center py-12">
                <div class="text-6xl mb-4">📋</div>
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    No hay clientes pendientes de verificación
                </h3>
                <p class="text-gray-500 dark:text-gray-400">
                    Todos los documentos han sido revisados
                </p>
            </div>
        </div>

        <!-- Modal para ampliar imagen -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="imagenModal"
                @click="cerrarModal"
                class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4"
            >
                <div @click.stop class="max-w-4xl w-full">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ tituloModal }}</h3>
                            <button
                                @click="cerrarModal"
                                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <img
                            :src="`/storage/${imagenModal}`"
                            :alt="tituloModal"
                            class="w-full h-auto max-h-[80vh] object-contain rounded-lg"
                        />
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    clientes: Object
});

const page = usePage();
const filtroEstado = ref('todos');
const imagenModal = ref(null);
const tituloModal = ref('');
const accionCliente = ref(null);
const tipoAccion = ref(null);
const clienteSeleccionado = ref(null);

const formAprobar = useForm({
    limite_credito: '',
    observaciones: ''
});

const formRechazar = useForm({
    observaciones: ''
});

const clientesFiltrados = computed(() => {
    if (filtroEstado.value === 'todos') {
        return props.clientes.data;
    }
    return props.clientes.data.filter(c => c.estado_verificacion === filtroEstado.value);
});

const abrirModal = (imagen, titulo) => {
    imagenModal.value = imagen;
    tituloModal.value = titulo;
};

const cerrarModal = () => {
    imagenModal.value = null;
    tituloModal.value = '';
};

const iniciarAprobar = (clienteId) => {
    accionCliente.value = clienteId;
    tipoAccion.value = 'aprobar';
    formAprobar.reset();
};

const iniciarRechazar = (clienteId) => {
    accionCliente.value = clienteId;
    tipoAccion.value = 'rechazar';
    formRechazar.reset();
};

const cancelarAccion = () => {
    accionCliente.value = null;
    tipoAccion.value = null;
    formAprobar.reset();
    formRechazar.reset();
};

const aprobarCliente = (clienteId) => {
    formAprobar.post(`/admin/clientes/${clienteId}/aprobar-documentos`, {
        onSuccess: () => {
            cancelarAccion();
        }
    });
};

const rechazarCliente = (clienteId) => {
    formRechazar.post(`/admin/clientes/${clienteId}/rechazar-documentos`, {
        onSuccess: () => {
            cancelarAccion();
        }
    });
};

// Detectar parámetro de cliente en la URL y hacer scroll
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const clienteId = urlParams.get('cliente');
    
    if (clienteId) {
        clienteSeleccionado.value = parseInt(clienteId);
        
        // Esperar a que el DOM se renderice y hacer scroll
        nextTick(() => {
            const elemento = document.getElementById(`cliente-${clienteId}`);
            if (elemento) {
                elemento.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Remover el highlight después de 3 segundos
                setTimeout(() => {
                    clienteSeleccionado.value = null;
                    // Limpiar el parámetro de la URL sin recargar
                    const nuevaUrl = window.location.pathname;
                    window.history.replaceState({}, '', nuevaUrl);
                }, 3000);
            }
        });
    }
});
</script>

