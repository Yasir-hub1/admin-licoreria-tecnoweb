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
    BarElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

Chart.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    title: String,
    colors: {
        type: Array,
        default: () => ['rgb(59, 130, 246)', 'rgb(16, 185, 129)', 'rgb(245, 158, 11)', 'rgb(239, 68, 68)']
    }
});

const chartCanvas = ref(null);
let chartInstance = null;

const hasData = computed(() => {
    if (props.data?.datasets) {
        return props.data.datasets.some(ds => ds.data && ds.data.length > 0);
    }
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
    const datasets = [];

    if (props.data?.datasets) {
        props.data.datasets.forEach((dataset, index) => {
            datasets.push({
                label: dataset.label,
                data: dataset.data || [],
                backgroundColor: props.colors[index % props.colors.length] + '80',
                borderColor: props.colors[index % props.colors.length],
                borderWidth: 1
            });
        });
    } else {
        const values = props.data?.values || props.data?.total || [];
        if (labels.length === 0 || values.length === 0) {
            return;
        }
        datasets.push({
            label: props.title || 'Datos',
            data: values,
            backgroundColor: props.colors[0] + '80',
            borderColor: props.colors[0],
            borderWidth: 1
        });
    }

    chartInstance = new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels: props.data.labels || [],
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: datasets.length > 1 || !!props.title,
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
    if (hasData.value) {
        if (chartInstance) {
            const labels = props.data?.labels || [];
            chartInstance.data.labels = labels;
            if (props.data?.datasets) {
                props.data.datasets.forEach((dataset, index) => {
                    if (chartInstance.data.datasets[index]) {
                        chartInstance.data.datasets[index].data = dataset.data || [];
                        chartInstance.data.datasets[index].label = dataset.label;
                    }
                });
            } else {
                const values = props.data?.values || props.data?.total || [];
                chartInstance.data.datasets[0].data = values;
            }
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

