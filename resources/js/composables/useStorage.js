import { usePage } from '@inertiajs/vue3';

export function useStorage() {
    const page = usePage();
    const baseUrl = (page.props.app?.url ?? window.location.origin).replace(/\/$/, '');

    // Función normal para productos, documentos, etc.
    const storageUrl = (path) => {
        if (!path) return '';
        const cleanPath = path.replace(/^\/+/, '');
        return `${baseUrl}/storage/${cleanPath}`;
    };

    // Función especial para QR y otros casos donde el path puede venir con 'storage/' incluido
    const storageUrlSafe = (path) => {
        if (!path) return '';

        let cleanPath = path.replace(/^\/+/, ''); // Remover slashes iniciales

        // Si el path ya incluye 'storage/', no duplicarlo
        if (cleanPath.startsWith('storage/')) {
            cleanPath = cleanPath.substring(8); // Remover 'storage/'
        }

        return `${baseUrl}/storage/${cleanPath}`;
    };

    return {
        storageUrl,      // Para uso normal
        storageUrlSafe   // Para casos especiales como QR
    };
}
