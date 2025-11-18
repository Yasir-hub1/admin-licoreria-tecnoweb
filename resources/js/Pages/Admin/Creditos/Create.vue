<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="mb-6">
                <Link href="/admin/creditos" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a Créditos
                </Link>
            </div>

            <h1 class="text-3xl font-bold mb-6">Nuevo Crédito</h1>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-yellow-800 text-sm">
                    <strong>Nota:</strong> Los créditos generalmente se crean automáticamente al realizar una venta a crédito.
                    Este formulario es para casos especiales.
                </p>
            </div>

            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6 max-w-2xl">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Venta Relacionada *</label>
                    <select
                        v-model="form.venta_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    >
                        <option value="">Seleccione una venta</option>
                        <option v-for="venta in ventas" :key="venta.id" :value="venta.id">
                            Venta #{{ venta.nro_venta }} - {{ venta.cliente?.nombre }} - Bs. {{ Number(venta.monto_total).toFixed(2) }}
                        </option>
                    </select>
                    <span v-if="form.errors.venta_id" class="text-red-500 text-sm">{{ form.errors.venta_id }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Monto Total (Bs.) *</label>
                    <input
                        v-model.number="form.monto_total"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.monto_total" class="text-red-500 text-sm">{{ form.errors.monto_total }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Número de Cuotas *</label>
                    <select
                        v-model.number="form.numero_cuotas"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    >
                        <option value="">Seleccione</option>
                        <option value="2">2 cuotas</option>
                        <option value="3">3 cuotas</option>
                        <option value="4">4 cuotas</option>
                        <option value="6">6 cuotas</option>
                        <option value="12">12 cuotas</option>
                    </select>
                    <span v-if="form.errors.numero_cuotas" class="text-red-500 text-sm">{{ form.errors.numero_cuotas }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Fecha de Inicio *</label>
                    <input
                        v-model="form.fecha_inicio"
                        type="date"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.fecha_inicio" class="text-red-500 text-sm">{{ form.errors.fecha_inicio }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Estado *</label>
                    <select
                        v-model="form.estado"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    >
                        <option value="activo">Activo</option>
                        <option value="pagado">Pagado</option>
                        <option value="mora">Mora</option>
                    </select>
                    <span v-if="form.errors.estado" class="text-red-500 text-sm">{{ form.errors.estado }}</span>
                </div>

                <div v-if="form.monto_total && form.numero_cuotas" class="mb-4 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>Cuota mensual estimada:</strong> Bs. {{ (form.monto_total / form.numero_cuotas).toFixed(2) }}
                    </p>
                </div>

                <div class="flex gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50"
                    >
                        <span v-if="form.processing">Guardando...</span>
                        <span v-else>Guardar Crédito</span>
                    </button>
                    <Link
                        href="/admin/creditos"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium"
                    >
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </MainLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    ventas: Array
});

const form = useForm({
    venta_id: '',
    monto_total: 0,
    numero_cuotas: null,
    fecha_inicio: new Date().toISOString().split('T')[0],
    estado: 'activo'
});

const submit = () => {
    form.post('/admin/creditos');
};
</script>

