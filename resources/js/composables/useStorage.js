import { usePage } from '@inertiajs/vue3';

export function useStorage() {
    const page = usePage();
    const baseUrl = (page.props.app?.url ?? window.location.origin).replace(/\/$/, '');
    const normalizePath = (path) => {
        if (!path) return '';
        let cleanPath = path.replace(/^\/+/, '');
        if (cleanPath.startsWith('storage/')) {
            cleanPath = cleanPath.substring(8);
        }
        return cleanPath;
    };

    // Función normal para productos, documentos, etc.
    const storageUrl = (path) => {
        const cleanPath = normalizePath(path);
        if (!cleanPath) return '';
        return `${baseUrl}/files/${cleanPath}`;
    };

    // Función especial para QR y otros casos donde el path puede venir con 'storage/' incluido
    const storageUrlSafe = (path) => {
        const cleanPath = normalizePath(path);
        if (!cleanPath) return '';
        return `${baseUrl}/files/${cleanPath}`;
    };

    return {
        storageUrl,      // Para uso normal
        storageUrlSafe   // Para casos especiales como QR
    };
}
