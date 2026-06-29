export function useStorage() {
    const baseUrl = 'https://www.tecnoweb.org.bo/inf513/grupo22sa/proyecto2/public';

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
