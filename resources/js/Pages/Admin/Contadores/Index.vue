<template>
    <AdminLayout title="Gestión de Contadores" subtitle="Administra los contadores del sistema">
        <div class="space-y-6">
            <!-- Acciones -->
            <div class="flex gap-4">
                <form @submit.prevent="sincronizar" class="inline">
                    <Button
                        type="submit"
                        :loading="sincronizando"
                        variant="primary"
                        size="md"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sincronizar con Base de Datos
                    </Button>
                </form>
            </div>

            <!-- Información -->
            <div v-motion-slide-bottom class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Información sobre los Contadores:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Los contadores se incrementan automáticamente al crear nuevas ventas, compras o productos.</li>
                            <li>La sincronización actualiza los contadores con los valores existentes en la base de datos.</li>
                            <li>Puedes ajustar manualmente el valor actual si es necesario.</li>
                            <li>Los cambios se aplican de forma thread-safe para evitar duplicados.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tabla de Contadores -->
            <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Prefijo
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Valor Actual
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Próximo Número
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Longitud
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="contador in contadores" :key="contador.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800': contador.tipo === 'venta',
                                            'bg-blue-100 text-blue-800': contador.tipo === 'compra',
                                            'bg-purple-100 text-purple-800': contador.tipo === 'producto'
                                        }">
                                        {{ contador.tipo.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                    {{ contador.prefijo }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ contador.valor_actual }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-blue-600">
                                    {{ getProximoNumero(contador) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ contador.longitud }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ contador.descripcion || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button
                                        @click="abrirModalEditar(contador)"
                                        class="text-blue-600 hover:text-blue-900 font-medium"
                                    >
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Editar Contador -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="mostrarModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="cerrarModal">
                <div
                    v-motion-pop
                    class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full border border-gray-100"
                >
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            Editar Contador
                        </h3>
                        <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="actualizarContador" class="space-y-5">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm font-medium text-gray-700 mb-1">Tipo:</p>
                            <p class="text-lg font-semibold text-gray-900">{{ contadorEditando?.tipo?.toUpperCase() }}</p>
                        </div>

                        <NumberInput
                            v-model="formEdit.valor_actual"
                            label="Valor Actual"
                            name="valor_actual"
                            placeholder="0"
                            required
                            :min="0"
                            :error="formEdit.errors?.valor_actual"
                            hint="El próximo número será: {{ getProximoNumero(contadorEditando) }}"
                        />

                        <TextInput
                            v-model="formEdit.prefijo"
                            label="Prefijo"
                            type="text"
                            name="prefijo"
                            placeholder="V-, C-, PROD-"
                            :max-length="10"
                            :error="formEdit.errors?.prefijo"
                            hint="Prefijo que se antepone al número"
                        />

                        <NumberInput
                            v-model="formEdit.longitud"
                            label="Longitud del Número"
                            name="longitud"
                            placeholder="6"
                            required
                            :min="1"
                            :max="10"
                            :error="formEdit.errors?.longitud"
                            hint="Número de dígitos (ej: 6 para 000001)"
                        />

                        <TextareaInput
                            v-model="formEdit.descripcion"
                            label="Descripción"
                            name="descripcion"
                            placeholder="Descripción del contador..."
                            :max-length="200"
                            :rows="2"
                            hint="Máximo 200 caracteres"
                        />

                        <div class="flex gap-3 pt-4">
                            <Button
                                type="button"
                                @click="cerrarModal"
                                variant="outline"
                                full-width
                            >
                                Cancelar
                            </Button>
                            <Button
                                type="submit"
                                :loading="formEdit.processing"
                                variant="primary"
                                full-width
                            >
                                Guardar Cambios
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/Button.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import NumberInput from '@/Components/Form/NumberInput.vue';
import TextareaInput from '@/Components/Form/TextareaInput.vue';

const props = defineProps({
    contadores: Array
});

const mostrarModal = ref(false);
const contadorEditando = ref(null);
const sincronizando = ref(false);

const formEdit = useForm({
    valor_actual: 0,
    prefijo: '',
    longitud: 6,
    descripcion: ''
});

const getProximoNumero = (contador) => {
    if (!contador) return '';
    const siguiente = contador.valor_actual + 1;
    return contador.prefijo + String(siguiente).padStart(contador.longitud, '0');
};

const abrirModalEditar = (contador) => {
    contadorEditando.value = contador;
    formEdit.valor_actual = contador.valor_actual;
    formEdit.prefijo = contador.prefijo;
    formEdit.longitud = contador.longitud;
    formEdit.descripcion = contador.descripcion || '';
    mostrarModal.value = true;
};

const cerrarModal = () => {
    mostrarModal.value = false;
    contadorEditando.value = null;
    formEdit.reset();
};

const actualizarContador = () => {
    if (!contadorEditando.value) return;

    formEdit.put(`/admin/contadores/${contadorEditando.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModal();
        }
    });
};

const sincronizar = () => {
    sincronizando.value = true;
    router.post('/admin/contadores/sincronizar', {}, {
        preserveScroll: true,
        onFinish: () => {
            sincronizando.value = false;
        }
    });
};
</script>

