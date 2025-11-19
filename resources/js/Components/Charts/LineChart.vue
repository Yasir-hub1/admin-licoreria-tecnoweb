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
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

Chart.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    title: String,
    color: {
        type: String,
        default: 'rgb(59, 130, 246)'
    }
});

const chartCanvas = ref(null);
let chartInstance = null;

const hasData = computed(() => {
    const labels = props.data?.labels || [];
    const values = props.data?.values || props.data?.total || [];
    return labels.length > 0 && values.length > 0;
});

const createChart = () => {
    if (!chartCanvas.value) return;

    if (chartInstance) {
        chartInstance.destroy();
    }

    const labels = props.data?.labels || [];
    const values = props.data?.values || props.data?.total || [];

    // Si no hay datos, mostrar mensaje
    if (labels.length === 0 || values.length === 0) {
        return;
    }

    chartInstance = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: props.title || 'Datos',
                data: values,
                borderColor: props.color,
                backgroundColor: props.color + '20',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: !!props.title,
                    position: 'top',
                },
                title: {
                    display: !!props.title,
                    text: props.title
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Bs. ' + value.toLocaleString();
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
    if (chartInstance) {
        const labels = props.data?.labels || [];
        const values = props.data?.values || props.data?.total || [];

        if (labels.length > 0 && values.length > 0) {
            chartInstance.data.labels = labels;
            chartInstance.data.datasets[0].data = values;
            chartInstance.update();
        } else {
            chartInstance.destroy();
            chartInstance = null;
            createChart();
        }
    } else {
        createChart();
    }
}, { deep: true });

onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.destroy();
    }
});
</script>

