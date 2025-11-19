<template>
    <div class="mb-4">
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative">
            <select
                :id="id || name"
                :value="modelValue"
                @change="handleChange"
                @blur="handleBlur"
                @focus="handleFocus"
                :required="required"
                :disabled="disabled"
                :class="[
                    'w-full px-4 py-2.5 border rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 appearance-none bg-white',
                    error ? 'border-red-500 focus:border-red-500 focus:ring-red-200' :
                    (focused && !error ? 'border-blue-500 focus:border-blue-500 focus:ring-blue-200' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200'),
                    disabled ? 'bg-gray-100 cursor-not-allowed' : ''
                ]"
            >
                <option v-if="placeholder" value="">{{ placeholder }}</option>
                <slot>
                    <option v-for="option in options" :key="getOptionValue(option)" :value="getOptionValue(option)">
                        {{ getOptionLabel(option) }}
                    </option>
                </slot>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
            <div v-if="error" class="absolute right-8 top-1/2 transform -translate-y-1/2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
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
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    name: String,
    id: String,
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
    options: {
        type: Array,
        default: () => []
    },
    optionValue: String,
    optionLabel: String,
    hint: String,
    error: String
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'change']);

const focused = ref(false);
const localError = ref('');

const error = computed(() => props.error || localError.value);

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

const validateInput = (value) => {
    localError.value = '';

    if (props.required && (!value || value === '')) {
        localError.value = `${props.label || 'Este campo'} es requerido`;
        return false;
    }

    return true;
};

const handleChange = (event) => {
    const value = event.target.value;
    emit('update:modelValue', value);
    emit('change', event);
    validateInput(value);
};

const handleBlur = (event) => {
    focused.value = false;
    validateInput(event.target.value);
    emit('blur', event);
};

const handleFocus = (event) => {
    focused.value = true;
    emit('focus', event);
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
