import { ref, computed } from 'vue';

const THEME_STORAGE_KEY = 'tecnoweb-theme';
const CONTRAST_STORAGE_KEY = 'tecnoweb-contrast';
const currentTheme = ref('adultos');
const currentContrast = ref('normal');

const themes = {
    ninios: {
        name: 'Niños',
        icon: '🧒',
        description: 'Colores vibrantes y alegres',
        body: 'bg-gradient-to-br from-yellow-100 via-pink-100 to-purple-100',
        sidebar: 'bg-gradient-to-b from-purple-600 via-pink-500 to-yellow-500',
        header: 'bg-yellow-50/90 border-pink-300',
        card: 'bg-white border-pink-200',
        text: 'text-purple-900',
        textSecondary: 'text-pink-700',
        input: 'bg-white border-pink-300',
        titleGradient: 'from-purple-700 to-pink-600',
        focusRing: 'focus:ring-pink-500',
        buttonPrimary: 'bg-pink-500 hover:bg-pink-600 text-white',
        buttonSecondary: 'bg-purple-500 hover:bg-purple-600 text-white'
    },
    jovenes: {
        name: 'Jóvenes',
        icon: '👨‍🎓',
        description: 'Estilo moderno y dinámico',
        body: 'bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50',
        sidebar: 'bg-gradient-to-b from-indigo-700 via-blue-600 to-purple-600',
        header: 'bg-blue-50/90 border-indigo-300',
        card: 'bg-white border-indigo-200',
        text: 'text-indigo-900',
        textSecondary: 'text-blue-700',
        input: 'bg-white border-indigo-300',
        titleGradient: 'from-indigo-700 to-purple-600',
        focusRing: 'focus:ring-indigo-500',
        buttonPrimary: 'bg-indigo-600 hover:bg-indigo-700 text-white',
        buttonSecondary: 'bg-blue-600 hover:bg-blue-700 text-white'
    },
    adultos: {
        name: 'Adultos',
        icon: '👔',
        description: 'Diseño profesional y elegante',
        body: 'bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100',
        sidebar: 'bg-gradient-to-b from-slate-800 via-gray-800 to-slate-900',
        header: 'bg-white/90 border-gray-300',
        card: 'bg-white border-gray-200',
        text: 'text-gray-900',
        textSecondary: 'text-gray-700',
        input: 'bg-white border-gray-400',
        titleGradient: 'from-slate-800 to-gray-700',
        focusRing: 'focus:ring-blue-500',
        buttonPrimary: 'bg-blue-600 hover:bg-blue-700 text-white',
        buttonSecondary: 'bg-gray-600 hover:bg-gray-700 text-white'
    }
};

const contrastLevels = {
    normal: {
        name: 'Normal',
        icon: '👁️',
        textContrast: '',
        borderContrast: '',
        bgContrast: ''
    },
    alto: {
        name: 'Alto Contraste',
        icon: '🔍',
        textContrast: 'font-bold',
        borderContrast: 'border-2',
        bgContrast: ''
    },
    muyAlto: {
        name: 'Muy Alto Contraste',
        icon: '🔎',
        textContrast: 'font-extrabold',
        borderContrast: 'border-4',
        bgContrast: 'bg-opacity-100'
    }
};

export function useTheme() {
    const loadTheme = () => {
        if (typeof window !== 'undefined') {
            const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
            const savedContrast = localStorage.getItem(CONTRAST_STORAGE_KEY);

            if (savedTheme && themes[savedTheme]) {
                currentTheme.value = savedTheme;
            } else {
                currentTheme.value = 'adultos';
            }

            if (savedContrast && contrastLevels[savedContrast]) {
                currentContrast.value = savedContrast;
            } else {
                currentContrast.value = 'normal';
            }

            applyTheme(currentTheme.value);
            applyContrast(currentContrast.value);
        }
    };

    const setTheme = (themeName) => {
        if (themes[themeName]) {
            currentTheme.value = themeName;
            if (typeof window !== 'undefined') {
                localStorage.setItem(THEME_STORAGE_KEY, themeName);
            }
            applyTheme(themeName);
        }
    };

    const setContrast = (contrastLevel) => {
        if (contrastLevels[contrastLevel]) {
            currentContrast.value = contrastLevel;
            if (typeof window !== 'undefined') {
                localStorage.setItem(CONTRAST_STORAGE_KEY, contrastLevel);
            }
            applyContrast(contrastLevel);
        }
    };

    const applyTheme = (themeName) => {
        if (typeof document === 'undefined') return;

        const theme = themes[themeName];
        if (!theme) return;

        const root = document.documentElement;
        root.setAttribute('data-theme', themeName);
    };

    const applyContrast = (contrastLevel) => {
        if (typeof document === 'undefined') return;

        const contrast = contrastLevels[contrastLevel];
        if (!contrast) return;

        const root = document.documentElement;
        root.setAttribute('data-contrast', contrastLevel);
    };

    // Cargar tema al inicializar
    if (typeof window !== 'undefined') {
        loadTheme();
    }

    return {
        currentTheme: computed(() => currentTheme.value),
        currentContrast: computed(() => currentContrast.value),
        themes,
        contrastLevels,
        setTheme,
        setContrast,
        loadTheme,
        getThemeClasses: () => {
            const theme = themes[currentTheme.value] || themes.adultos;
            const contrast = contrastLevels[currentContrast.value] || contrastLevels.normal;

            return {
                ...theme,
                contrast: {
                    text: contrast.textContrast,
                    border: contrast.borderContrast,
                    bg: contrast.bgContrast
                }
            };
        }
    };
}
