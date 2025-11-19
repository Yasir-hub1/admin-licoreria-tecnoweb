<template>
    <div class="mb-4">
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative">
            <input
                :id="id || name"
                :type="type"
                :value="modelValue"
                @input="handleInput"
                @blur="handleBlur"
                @focus="handleFocus"
                :placeholder="placeholder"
                :required="required"
                :disabled="disabled"
                :min="min"
                :max="max"
                :minlength="minLength"
                :maxlength="maxLength"
                :pattern="pattern"
                :class="[
                    'w-full px-4 py-2.5 border rounded-lg transition-all duration-200 focus:outline-none focus:ring-2',
                    error ? 'border-red-500 focus:border-red-500 focus:ring-red-200' :
                    (focused && !error ? 'border-blue-500 focus:border-blue-500 focus:ring-blue-200' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200'),
                    disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
                ]"
            />
            <div v-if="error" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div v-else-if="valid && showValidIcon" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
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
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    name: String,
    id: String,
    type: {
        type: String,
        default: 'text'
    },
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
    min: [String, Number],
    max: [String, Number],
    minLength: Number,
    maxLength: Number,
    pattern: String,
    hint: String,
    error: String,
    validationRules: Array,
    showValidIcon: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'validate']);

const focused = ref(false);
const localError = ref('');

const error = computed(() => props.error || localError.value);
const valid = computed(() => !error.value && props.modelValue);

const validateInput = (value) => {
    localError.value = '';

    if (props.required && (!value || value.toString().trim() === '')) {
        localError.value = `${props.label || 'Este campo'} es requerido`;
        return false;
    }

    if (value && props.minLength && value.toString().length < props.minLength) {
        localError.value = `Mínimo ${props.minLength} caracteres`;
        return false;
    }

    if (value && props.maxLength && value.toString().length > props.maxLength) {
        localError.value = `Máximo ${props.maxLength} caracteres`;
        return false;
    }

    if (value && props.pattern) {
        const regex = new RegExp(props.pattern);
        if (!regex.test(value)) {
            localError.value = 'Formato inválido';
            return false;
        }
    }

    // Validaciones específicas por tipo
    if (props.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            localError.value = 'Email inválido';
            return false;
        }
    }

    if (props.type === 'url' && value) {
        try {
            new URL(value);
        } catch {
            localError.value = 'URL inválida';
            return false;
        }
    }

    if (props.type === 'number' && value !== '' && value !== null && value !== undefined) {
        const num = Number(value);
        if (isNaN(num)) {
            localError.value = 'Debe ser un número válido';
            return false;
        }
        if (props.min !== undefined && num < Number(props.min)) {
            localError.value = `El valor mínimo es ${props.min}`;
            return false;
        }
        if (props.max !== undefined && num > Number(props.max)) {
            localError.value = `El valor máximo es ${props.max}`;
            return false;
        }
    }

    // Validaciones personalizadas
    if (props.validationRules && Array.isArray(props.validationRules)) {
        for (const rule of props.validationRules) {
            if (typeof rule === 'function') {
                const result = rule(value);
                if (result !== true) {
                    localError.value = result || 'Validación fallida';
                    return false;
                }
            }
        }
    }

    emit('validate', { valid: true, value });
    return true;
};

const handleInput = (event) => {
    const value = event.target.value;
    emit('update:modelValue', value);
    if (focused.value) {
        validateInput(value);
    }
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

watch(() => props.modelValue, (newValue) => {
    if (!focused.value && newValue) {
        validateInput(newValue);
    }
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
