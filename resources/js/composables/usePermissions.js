import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();

    // Hacer reactivos los permisos usando computed
    const permisos = computed(() => {
        const permisosArray = page.props.auth?.user?.permisos || [];
        
        // Asegurar que siempre sea un array
        if (!Array.isArray(permisosArray)) {
            console.warn('[usePermissions] Los permisos no son un array:', typeof permisosArray, permisosArray);
            return [];
        }
        
        return permisosArray;
    });

    const rolNombre = computed(() => {
        return page.props.auth?.user?.rol?.nombre || null;
    });

    /**
     * Verificar si el usuario tiene un permiso específico
     * Ahora siempre verifica los permisos asignados, incluso para propietarios
     * Esta función es reactiva porque lee de permisos.value que es un computed
     */
    const tienePermiso = (permiso) => {
        if (!permiso || typeof permiso !== 'string') {
            return false;
        }

        // Limpiar el permiso de espacios en blanco
        const permisoLimpio = permiso.trim();
        const permisosArray = permisos.value;
        
        // Verificar que permisosArray sea un array válido
        if (!Array.isArray(permisosArray) || permisosArray.length === 0) {
            if (process.env.NODE_ENV === 'development') {
                console.warn(`[usePermissions] No hay permisos disponibles para verificar: ${permisoLimpio}`);
            }
            return false;
        }
        
        // Debug temporal para verificar
        if (process.env.NODE_ENV === 'development') {
            console.log(`[usePermissions] Verificando permiso: "${permisoLimpio}"`);
            console.log(`[usePermissions] Rol: ${rolNombre.value}`);
            console.log(`[usePermissions] Permisos disponibles (${permisosArray.length}):`, permisosArray);
        }

        // Comparar exacta (case-sensitive) y también verificar si hay espacios
        const tiene = permisosArray.some(p => {
            if (!p || typeof p !== 'string') return false;
            return p.trim() === permisoLimpio;
        });
        
        if (process.env.NODE_ENV === 'development') {
            console.log(`[usePermissions] Resultado para "${permisoLimpio}":`, tiene ? '✅ SI' : '❌ NO');
            if (!tiene) {
                console.log(`[usePermissions] Permisos similares encontrados:`, permisosArray.filter(p => p && p.includes(permisoLimpio.split('.')[0])));
            }
        }

        return tiene;
    };

    /**
     * Verificar si el usuario tiene algún permiso de un módulo
     * Ahora siempre verifica los permisos asignados, incluso para propietarios
     * Esta función es reactiva porque lee de permisos.value que es un computed
     */
    const tieneAccesoModulo = (modulo) => {
        if (!modulo || typeof modulo !== 'string') {
            return false;
        }

        // Limpiar el módulo de espacios en blanco
        const moduloLimpio = modulo.trim();
        const permisosArray = permisos.value;

        // Verificar que permisosArray sea un array válido
        if (!Array.isArray(permisosArray) || permisosArray.length === 0) {
            if (process.env.NODE_ENV === 'development') {
                console.warn(`[usePermissions] No hay permisos disponibles para verificar módulo: ${moduloLimpio}`);
            }
            return false;
        }

        // Debug temporal para verificar
        if (process.env.NODE_ENV === 'development') {
            console.log(`[usePermissions] Verificando acceso al módulo: "${moduloLimpio}"`);
            console.log(`[usePermissions] Rol: ${rolNombre.value}`);
            console.log(`[usePermissions] Permisos disponibles (${permisosArray.length}):`, permisosArray);
        }

        // Verificar si tiene algún permiso del módulo
        const prefijo = moduloLimpio + '.';
        const tiene = permisosArray.some(permiso => {
            if (!permiso || typeof permiso !== 'string') return false;
            return permiso.trim().startsWith(prefijo);
        });
        
        if (process.env.NODE_ENV === 'development') {
            console.log(`[usePermissions] Acceso al módulo "${moduloLimpio}":`, tiene ? '✅ SI' : '❌ NO');
            if (tiene) {
                const permisosModulo = permisosArray.filter(p => p && p.trim().startsWith(prefijo));
                console.log(`[usePermissions] Permisos del módulo encontrados:`, permisosModulo);
            }
        }

        return tiene;
    };

    /**
     * Verificar si es propietario
     */
    const esPropietario = computed(() => {
        return rolNombre.value === 'propietario';
    });

    return {
        tienePermiso,
        tieneAccesoModulo,
        esPropietario,
        permisos,
        rolNombre
    };
}

