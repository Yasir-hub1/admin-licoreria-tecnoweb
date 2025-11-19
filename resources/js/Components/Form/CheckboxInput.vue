<template>
    <div class="mb-4">
        <label class="flex items-center cursor-pointer group">
            <div class="relative">
                <input
                    :id="id || name"
                    type="checkbox"
                    :checked="modelValue"
                    @change="handleChange"
                    :disabled="disabled"
                    :class="[
                        'w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 transition-all duration-200',
                        error ? 'border-red-500' : '',
                        disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                    ]"
                />
            </div>
            <span v-if="label" class="ml-3 text-sm font-medium" :class="error ? 'text-red-700' : 'text-gray-700'">
                {{ label }}
                <span v-if="required" class="text-red-500">*</span>
            </span>
        </label>
        <transition name="fade">
            <p v-if="error" class="mt-1 text-sm text-red-600 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ error }}
            </p>
        </transition>
        <p v-if="hint && !error" class="mt-1 text-xs text-gray-500 ml-8">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: Boolean,
    label: String,
    name: String,
    id: String,
    required: Boolean,
    disabled: Boolean,
    hint: String,
    error: String
});

const emit = defineEmits(['update:modelValue', 'change']);

const handleChange = (event) => {
    emit('update:modelValue', event.target.checked);
    emit('change', event);
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
