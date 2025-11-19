<template>
    <div class="bg-white rounded-lg p-4 h-full">
        <div v-if="!hasData" class="flex items-center justify-center h-full text-gray-500">
            <div class="text-center">
                <p class="text-sm">No hay datos para mostrar</p>
            </div>
        </div>
        <canvas v-else ref="chartCanvas"></canvas>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue';
import {
    Chart,
    ArcElement,
    Tooltip,
    Legend
} from 'chart.js';

Chart.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    title: String,
    colors: {
        type: Array,
        default: () => [
            'rgb(59, 130, 246)',
            'rgb(16, 185, 129)',
            'rgb(245, 158, 11)',
            'rgb(239, 68, 68)',
            'rgb(139, 92, 246)',
            'rgb(236, 72, 153)'
        ]
    }
});

const chartCanvas = ref(null);
let chartInstance = null;

const hasData = computed(() => {
    const labels = props.data?.labels || [];
    const values = props.data?.values || props.data?.total || [];
    return labels.length > 0 && values.length > 0 && values.some(v => v > 0);
});

const createChart = () => {
    if (!chartCanvas.value) return;

    if (chartInstance) {
        chartInstance.destroy();
    }

    const labels = props.data?.labels || [];
    const values = props.data?.values || props.data?.total || [];

    if (labels.length === 0 || values.length === 0) {
        return;
    }

    chartInstance = new Chart(chartCanvas.value, {
        type: 'doughnut',
        data: {
            labels: props.data.labels || [],
            datasets: [{
                data: props.data.values || [],
                backgroundColor: props.colors.slice(0, props.data.values?.length || 0),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                title: {
                    display: !!props.title,
                    text: props.title,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: Bs. ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
};

onMounted(() => {
    createChart();
});

watch(() => props.data, () => {
    if (hasData.value) {
        if (chartInstance) {
            const labels = props.data?.labels || [];
            const values = props.data?.values || props.data?.total || [];
            chartInstance.data.labels = labels;
            chartInstance.data.datasets[0].data = values;
            chartInstance.update();
        } else {
            createChart();
        }
    } else {
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }
    }
}, { deep: true });

onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.destroy();
    }
});
</script>

