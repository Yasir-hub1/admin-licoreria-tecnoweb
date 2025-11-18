<template>
    <Link
        :href="link"
        class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200"
    >
        <div class="p-6">
            <div class="flex items-center">
                <div class="text-4xl mr-4">{{ icon }}</div>
                <div>
                    <div class="text-sm text-gray-500">{{ title }}</div>
                    <div class="text-2xl font-semibold text-gray-800">{{ value }}</div>
                </div>
            </div>
        </div>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: String,
    value: [Number, String],
    icon: String,
    link: String,
});
</script>
