<template>
    <Link
        :href="link"
        class="group relative overflow-hidden bg-white rounded-2xl shadow-lg p-6 border-2 border-gray-100 hover:shadow-2xl hover:scale-[1.02] transition-all duration-300"
    >
        <!-- Background Gradient -->
        <div
            :class="[
                'absolute top-0 right-0 w-32 h-32 rounded-full opacity-10 blur-3xl transition-opacity duration-300 group-hover:opacity-20',
                `bg-gradient-to-br ${color}`
            ]"
        ></div>

        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div :class="[
                    'w-14 h-14 rounded-xl flex items-center justify-center text-2xl shadow-lg transition-transform duration-300 group-hover:scale-110',
                    `bg-gradient-to-br ${color}`
                ]">
                    {{ icon }}
                </div>
                <div v-if="trend" class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-lg">
                    {{ trend }}
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-1 font-medium">{{ title }}</p>
                <p class="text-3xl font-bold text-gray-800">{{ formatValue(value) }}</p>
            </div>

            <div class="mt-4 flex items-center text-sm text-gray-500 group-hover:text-blue-600 transition-colors">
                <span>Ver detalles</span>
                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </Link>
</template>

<script setup>
defineProps({
    title: String,
    value: [Number, String],
    icon: String,
    color: {
        type: String,
        default: 'from-blue-500 to-cyan-500'
    },
    link: String,
    trend: String
});

const formatValue = (value) => {
    if (typeof value === 'number') {
        return value.toLocaleString('es-ES');
    }
    return value;
};
</script>

