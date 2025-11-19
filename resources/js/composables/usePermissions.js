import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    const tienePermiso = (permiso) => {
        const permisos = page.props.auth?.user?.permisos || [];
        const rolNombre = page.props.auth?.user?.rol?.nombre;

        // Debug - siempre mostrar para diagnosticar
        console.log('[usePermissions] Verificando permiso:', permiso);
        console.log('[usePermissions] Permisos del usuario:', permisos);
        console.log('[usePermissions] Tipo de permisos:', Array.isArray(permisos) ? 'Array' : typeof permisos);
        console.log('[usePermissions] Rol del usuario:', rolNombre);
        console.log('[usePermissions] Usuario completo:', page.props.auth?.user);

        // Propietario tiene todos los permisos
        if (rolNombre === 'propietario') {
            console.log('[usePermissions] Es propietario, acceso permitido');
            return true;
        }

        const tiene = Array.isArray(permisos) && permisos.includes(permiso);

        console.log('[usePermissions] Resultado:', tiene);
        if (!tiene) {
            console.warn('[usePermissions] Permiso denegado. Permisos disponibles:', permisos);
        }

        return tiene;
    };

    /**
     * Verificar si el usuario tiene algún permiso de un módulo
     */
    const tieneAccesoModulo = (modulo) => {
        const permisos = page.props.auth?.user?.permisos || [];
        const rolNombre = page.props.auth?.user?.rol?.nombre;

        console.log('[usePermissions] Verificando acceso al módulo:', modulo);
        console.log('[usePermissions] Permisos disponibles:', permisos);

        // Propietario tiene acceso a todo
        if (rolNombre === 'propietario') {
            console.log('[usePermissions] Es propietario, acceso al módulo permitido');
            return true;
        }

        // Verificar si tiene algún permiso del módulo
        const tiene = Array.isArray(permisos) && permisos.some(permiso => permiso && permiso.startsWith(modulo + '.'));
        console.log('[usePermissions] Acceso al módulo', modulo + ':', tiene);

        return tiene;
    };

    /**
     * Verificar si es propietario
     */
    const esPropietario = computed(() => {
        return page.props.auth?.user?.rol?.nombre === 'propietario';
    });

    return {
        tienePermiso,
        tieneAccesoModulo,
        esPropietario
    };
}

