<template>
    <AdminLayout title="Estadísticas y Reportes" subtitle="Análisis completo del negocio">
        <div class="space-y-6">
            <!-- Filtros y Exportación -->
            <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Filtros de Búsqueda</h3>
                    <Button @click="exportarExcel" variant="success" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar a Excel
                    </Button>
                </div>
                <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                        <input
                            v-model="filtros.fecha_inicio"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                        <input
                            v-model="filtros.fecha_fin"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                        <select
                            v-model="filtros.categoria_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        >
                            <option value="">Todas las categorías</option>
                            <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <Button type="submit" variant="primary" full-width>
                            Aplicar Filtros
                        </Button>
                    </div>
                </form>
            </div>

            <!-- KPIs Principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-motion-slide-bottom v-for="(kpi, index) in kpisCards" :key="index"
                     class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ kpi.label }}</p>
                            <p class="text-2xl font-bold mt-2" :class="kpi.color">{{ kpi.value }}</p>
                            <p v-if="kpi.subtitle" class="text-xs text-gray-500 mt-1">{{ kpi.subtitle }}</p>
                        </div>
                        <div class="text-4xl opacity-20" :class="kpi.iconColor">
                            {{ kpi.icon }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos Principales -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Ventas por Día -->
                <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
                    <h3 class="text-lg font-bold mb-4 text-gray-800">Ventas por Día</h3>
                    <div class="h-64">
                        <LineChart
                            :data="{ labels: ventasPorDia.labels, values: ventasPorDia.total }"
                            title="Ingresos por Día"
                            color="rgb(59, 130, 246)"
                        />
                    </div>
                </div>

                <!-- Ventas por Tipo -->
                <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
                    <h3 class="text-lg font-bold mb-4 text-gray-800">Ventas por Tipo de Pago</h3>
                    <div class="h-64">
                        <DoughnutChart
                            :data="{ labels: ventasPorTipo.labels, values: ventasPorTipo.total }"
                            title="Distribución de Ventas"
                        />
                    </div>
                </div>

                <!-- Ventas por Categoría -->
                <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
                    <h3 class="text-lg font-bold mb-4 text-gray-800">Ventas por Categoría</h3>
                    <div class="h-64">
                        <BarChart
                            :data="{ labels: ventasPorCategoria.labels, values: ventasPorCategoria.total }"
                            title="Ingresos por Categoría"
                        />
                    </div>
                </div>

                <!-- Compras vs Ventas -->
                <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
                    <h3 class="text-lg font-bold mb-4 text-gray-800">Compras vs Ventas</h3>
                    <div class="h-64">
                        <BarChart
                            :data="{
                                labels: comprasVsVentas.labels,
                                datasets: [
                                    { label: 'Ventas', data: comprasVsVentas.ventas },
                                    { label: 'Compras', data: comprasVsVentas.compras }
                                ]
                            }"
                            title="Comparación Mensual"
                        />
                    </div>
                </div>
            </div>

            <!-- Tablas de Datos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Productos Más Vendidos -->
                <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">Productos Más Vendidos</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="producto in productosMasVendidos" :key="producto.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ producto.nombre }}</div>
                                        <div class="text-xs text-gray-500">{{ producto.codigo }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ producto.cantidad_vendida }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-green-600">Bs. {{ producto.total_vendido.toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Clientes -->
                <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">Top Clientes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ventas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="cliente in topClientes" :key="cliente.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ cliente.nombre }}</div>
                                        <div class="text-xs text-gray-500">CI: {{ cliente.ci }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ cliente.total_ventas }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-blue-600">Bs. {{ cliente.total_gastado.toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Ingresos por Usuario -->
                <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">Ingresos por Usuario</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ventas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingresos</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="usuario in ingresosPorUsuario" :key="usuario.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ usuario.nombre }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ usuario.total_ventas }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-green-600">Bs. {{ usuario.total_ingresos.toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Productos con Bajo Stock -->
                <div v-motion-slide-bottom class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">Productos con Bajo Stock</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="producto in productosBajoStock" :key="producto.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ producto.nombre }}</div>
                                        <div class="text-xs text-gray-500">{{ producto.codigo }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ producto.categoria }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-red-100 text-red-800': producto.stock === 0,
                                                'bg-yellow-100 text-yellow-800': producto.stock > 0 && producto.stock <= 5,
                                                'bg-orange-100 text-orange-800': producto.stock > 5 && producto.stock <= 10
                                            }">
                                            {{ producto.stock }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import * as XLSX from 'xlsx';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/Button.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue';

const props = defineProps({
    kpis: Object,
    ventasPorDia: Object,
    ventasPorMes: Object,
    productosMasVendidos: Array,
    ventasPorCategoria: Object,
    ventasPorTipo: Object,
    topClientes: Array,
    estadoCreditos: Object,
    comprasVsVentas: Object,
    ingresosPorUsuario: Array,
    productosBajoStock: Array,
    categorias: Array,
    clientes: Array,
    filtros: Object
});

const filtros = ref({
    fecha_inicio: props.filtros?.fecha_inicio || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    fecha_fin: props.filtros?.fecha_fin || new Date().toISOString().split('T')[0],
    categoria_id: props.filtros?.categoria_id || '',
    cliente_id: props.filtros?.cliente_id || ''
});

const kpisCards = computed(() => [
    {
        label: 'Total de Ingresos',
        value: `Bs. ${props.kpis.total_ingresos.toLocaleString()}`,
        icon: '💰',
        color: 'text-green-600',
        iconColor: 'text-green-600'
    },
    {
        label: 'Total de Ventas',
        value: props.kpis.total_ventas,
        subtitle: `${props.kpis.ventas_hoy} ventas hoy`,
        icon: '📊',
        color: 'text-blue-600',
        iconColor: 'text-blue-600'
    },
    {
        label: 'Utilidad Neta',
        value: `Bs. ${props.kpis.utilidad.toLocaleString()}`,
        subtitle: `Ingresos: Bs. ${props.kpis.total_ingresos.toLocaleString()} - Gastos: Bs. ${props.kpis.total_gastado.toLocaleString()}`,
        icon: '📈',
        color: props.kpis.utilidad >= 0 ? 'text-green-600' : 'text-red-600',
        iconColor: props.kpis.utilidad >= 0 ? 'text-green-600' : 'text-red-600'
    },
    {
        label: 'Saldo Pendiente',
        value: `Bs. ${props.kpis.saldo_pendiente.toLocaleString()}`,
        subtitle: 'Créditos activos',
        icon: '💳',
        color: 'text-orange-600',
        iconColor: 'text-orange-600'
    }
]);

const aplicarFiltros = () => {
    router.get('/admin/estadisticas', filtros.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const exportarExcel = () => {
    const workbook = XLSX.utils.book_new();

    // Hoja 1: KPIs
    const kpisData = [
        ['Métrica', 'Valor'],
        ['Total de Ingresos', `Bs. ${props.kpis.total_ingresos.toLocaleString()}`],
        ['Total de Ventas', props.kpis.total_ventas],
        ['Ventas Hoy', props.kpis.ventas_hoy],
        ['Ingresos Hoy', `Bs. ${props.kpis.ingresos_hoy.toLocaleString()}`],
        ['Ingresos Contado', `Bs. ${props.kpis.ingresos_contado.toLocaleString()}`],
        ['Ingresos Crédito', `Bs. ${props.kpis.ingresos_credito.toLocaleString()}`],
        ['Saldo Pendiente', `Bs. ${props.kpis.saldo_pendiente.toLocaleString()}`],
        ['Total Compras', props.kpis.total_compras],
        ['Total Gastado', `Bs. ${props.kpis.total_gastado.toLocaleString()}`],
        ['Utilidad Neta', `Bs. ${props.kpis.utilidad.toLocaleString()}`],
        ['Total Clientes', props.kpis.total_clientes],
        ['Total Productos', props.kpis.total_productos],
    ];
    const kpisSheet = XLSX.utils.aoa_to_sheet(kpisData);
    XLSX.utils.book_append_sheet(workbook, kpisSheet, 'KPIs');

    // Hoja 2: Ventas por Día
    const ventasDiaData = [
        ['Fecha', 'Cantidad', 'Total (Bs.)'],
        ...props.ventasPorDia.labels.map((label, index) => [
            label,
            props.ventasPorDia.cantidad[index] || 0,
            props.ventasPorDia.total[index] || 0
        ])
    ];
    const ventasDiaSheet = XLSX.utils.aoa_to_sheet(ventasDiaData);
    XLSX.utils.book_append_sheet(workbook, ventasDiaSheet, 'Ventas por Día');

    // Hoja 3: Productos Más Vendidos
    const productosData = [
        ['Código', 'Producto', 'Categoría', 'Cantidad Vendida', 'Total Vendido (Bs.)'],
        ...props.productosMasVendidos.map(p => [
            p.codigo,
            p.nombre,
            p.categoria || 'Sin categoría',
            p.cantidad_vendida,
            p.total_vendido
        ])
    ];
    const productosSheet = XLSX.utils.aoa_to_sheet(productosData);
    XLSX.utils.book_append_sheet(workbook, productosSheet, 'Productos Más Vendidos');

    // Hoja 4: Top Clientes
    const clientesData = [
        ['CI', 'Cliente', 'Total Ventas', 'Total Gastado (Bs.)'],
        ...props.topClientes.map(c => [
            c.ci,
            c.nombre,
            c.total_ventas,
            c.total_gastado
        ])
    ];
    const clientesSheet = XLSX.utils.aoa_to_sheet(clientesData);
    XLSX.utils.book_append_sheet(workbook, clientesSheet, 'Top Clientes');

    // Hoja 5: Ventas por Categoría
    const categoriaData = [
        ['Categoría', 'Cantidad', 'Total (Bs.)'],
        ...props.ventasPorCategoria.labels.map((label, index) => [
            label,
            props.ventasPorCategoria.cantidad[index] || 0,
            props.ventasPorCategoria.total[index] || 0
        ])
    ];
    const categoriaSheet = XLSX.utils.aoa_to_sheet(categoriaData);
    XLSX.utils.book_append_sheet(workbook, categoriaSheet, 'Ventas por Categoría');

    // Hoja 6: Ventas por Tipo
    const tipoData = [
        ['Tipo', 'Cantidad', 'Total (Bs.)'],
        ...props.ventasPorTipo.labels.map((label, index) => [
            label,
            props.ventasPorTipo.cantidad[index] || 0,
            props.ventasPorTipo.total[index] || 0
        ])
    ];
    const tipoSheet = XLSX.utils.aoa_to_sheet(tipoData);
    XLSX.utils.book_append_sheet(workbook, tipoSheet, 'Ventas por Tipo');

    // Hoja 7: Ingresos por Usuario
    const usuariosData = [
        ['Usuario', 'Total Ventas', 'Total Ingresos (Bs.)'],
        ...props.ingresosPorUsuario.map(e => [
            e.nombre,
            e.total_ventas,
            e.total_ingresos
        ])
    ];
    const usuariosSheet = XLSX.utils.aoa_to_sheet(usuariosData);
    XLSX.utils.book_append_sheet(workbook, usuariosSheet, 'Ingresos por Usuario');

    // Hoja 8: Productos con Bajo Stock
    const stockData = [
        ['Código', 'Producto', 'Categoría', 'Stock'],
        ...props.productosBajoStock.map(p => [
            p.codigo,
            p.nombre,
            p.categoria || 'Sin categoría',
            p.stock
        ])
    ];
    const stockSheet = XLSX.utils.aoa_to_sheet(stockData);
    XLSX.utils.book_append_sheet(workbook, stockSheet, 'Productos Bajo Stock');

    // Hoja 9: Compras vs Ventas
    const comprasVentasData = [
        ['Mes', 'Ventas (Bs.)', 'Compras (Bs.)', 'Diferencia (Bs.)'],
        ...props.comprasVsVentas.labels.map((label, index) => [
            label,
            props.comprasVsVentas.ventas[index] || 0,
            props.comprasVsVentas.compras[index] || 0,
            (props.comprasVsVentas.ventas[index] || 0) - (props.comprasVsVentas.compras[index] || 0)
        ])
    ];
    const comprasVentasSheet = XLSX.utils.aoa_to_sheet(comprasVentasData);
    XLSX.utils.book_append_sheet(workbook, comprasVentasSheet, 'Compras vs Ventas');

    // Generar nombre del archivo con fecha
    const fecha = new Date().toISOString().split('T')[0];
    const nombreArchivo = `Estadisticas_${fecha}.xlsx`;

    // Descargar archivo
    XLSX.writeFile(workbook, nombreArchivo);
};
</script>

