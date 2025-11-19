<template>
    <div class="mb-4">
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="space-y-2">
            <label
                v-for="option in options"
                :key="getOptionValue(option)"
                class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all duration-200 hover:bg-gray-50"
                :class="[
                    modelValue === getOptionValue(option)
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-300',
                    error ? 'border-red-500' : ''
                ]"
            >
                <input
                    type="radio"
                    :name="name"
                    :value="getOptionValue(option)"
                    :checked="modelValue === getOptionValue(option)"
                    @change="handleChange"
                    :disabled="disabled"
                    class="mr-3 w-4 h-4 text-blue-600 focus:ring-2 focus:ring-blue-500"
                />
                <div class="flex-1">
                    <div class="font-medium text-gray-900">{{ getOptionLabel(option) }}</div>
                    <div v-if="getOptionDescription(option)" class="text-sm text-gray-600">
                        {{ getOptionDescription(option) }}
                    </div>
                </div>
            </label>
        </div>
        <transition name="fade">
            <p v-if="error" class="mt-1 text-sm text-red-600 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ error }}
            </p>
        </transition>
        <p v-if="hint && !error" class="mt-1 text-xs text-gray-500">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    name: String,
    options: {
        type: Array,
        required: true
    },
    optionValue: String,
    optionLabel: String,
    optionDescription: String,
    required: Boolean,
    disabled: Boolean,
    hint: String,
    error: String
});

const emit = defineEmits(['update:modelValue', 'change']);

const getOptionValue = (option) => {
    if (typeof option === 'object') {
        return props.optionValue ? option[props.optionValue] : option.value || option.id;
    }
    return option;
};

const getOptionLabel = (option) => {
    if (typeof option === 'object') {
        return props.optionLabel ? option[props.optionLabel] : option.label || option.nombre || option.name || option.value || option.id;
    }
    return option;
};

const getOptionDescription = (option) => {
    if (typeof option === 'object') {
        return props.optionDescription ? option[props.optionDescription] : option.description || option.descripcion;
    }
    return null;
};

const handleChange = (event) => {
    emit('update:modelValue', event.target.value);
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
