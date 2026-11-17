import Swal from 'sweetalert2';

// Mutex para evitar clicks dobles instantáneos
let isCurrentlyDownloading = false;

/**
 * Descarga archivos (PDF, Excel, Imágenes, etc.) de forma nativa e infalible,
 * mostrando un indicador visual con SweetAlert2.
 * Funciona tanto con URLs remotas del servidor como con Data URLs (Base64) y Blobs.
 *
 * @param {string} urlOrData - URL del archivo o Data URI (base64)
 * @param {Object|string} [options] - Opciones de descarga o nombre de archivo directo
 * @param {string} [options.filename] - Nombre de archivo personalizado
 * @param {'excel'|'pdf'|'image'|'file'} [options.type] - Tipo de archivo explícito
 * @param {string} [options.title] - Título personalizado para el modal
 * @param {Function} [options.onSuccess] - Callback ejecutado tras iniciar descarga
 * @param {Function} [options.onError] - Callback ejecutado en caso de error
 * @param {string} [legacyType] - Tipo de archivo si se usa la firma clásica
 * @returns {Promise<void>}
 */
export function downloadWithProgress(urlOrData, options = {}, legacyType = null) {
    if (!urlOrData) return Promise.reject(new Error('URL o archivo no especificado'));

    if (isCurrentlyDownloading) {
        console.warn('downloadWithProgress: ya existe una descarga en curso, omitiendo petición duplicada.');
        return Promise.resolve();
    }
    isCurrentlyDownloading = true;
    setTimeout(() => { isCurrentlyDownloading = false; }, 3000);

    let customFilename = null;
    let explicitType = null;
    let customTitle = null;
    let onSuccess = null;
    let onError = null;

    if (typeof options === 'string') {
        customFilename = options;
        explicitType = legacyType ? String(legacyType).toLowerCase() : null;
    } else if (options && typeof options === 'object') {
        customFilename = options.filename;
        explicitType = options.type;
        customTitle = options.title;
        onSuccess = options.onSuccess;
        onError = options.onError;
    }

    const isDataUri = typeof urlOrData === 'string' && (urlOrData.startsWith('data:') || urlOrData.startsWith('blob:'));
    const cleanUrl = isDataUri ? '' : urlOrData.split('?')[0].toLowerCase();

    // Detección automática del tipo de archivo
    let detectedType = explicitType;
    if (!detectedType) {
        if (cleanUrl.includes('excel') || cleanUrl.endsWith('.xlsx') || cleanUrl.endsWith('.xls') || cleanUrl.endsWith('.csv')) {
            detectedType = 'excel';
        } else if (cleanUrl.includes('pdf') || cleanUrl.endsWith('.pdf')) {
            detectedType = 'pdf';
        } else if (isDataUri || cleanUrl.endsWith('.png') || cleanUrl.endsWith('.jpg') || cleanUrl.endsWith('.jpeg') || cleanUrl.endsWith('.webp') || cleanUrl.endsWith('.svg')) {
            detectedType = 'image';
        } else {
            detectedType = 'file';
        }
    }

    let formatTitle = 'Archivo';
    let icon = '📁';
    let defaultFilename = 'descarga';

    if (detectedType === 'excel') {
        formatTitle = 'Excel';
        icon = '📊';
        defaultFilename = 'reporte.xlsx';
    } else if (detectedType === 'pdf') {
        formatTitle = 'PDF';
        icon = '📄';
        defaultFilename = 'documento.pdf';
    } else if (detectedType === 'image') {
        formatTitle = 'Imagen';
        icon = '🖼️';
        defaultFilename = 'imagen.png';
    }

    const resolvedFilename = customFilename || (cleanUrl ? cleanUrl.split('/').pop() : defaultFilename) || defaultFilename;

    // Si es un dataURI directo (como código QR o imagen en base64)
    if (isDataUri) {
        try {
            const a = document.createElement('a');
            a.href = urlOrData;
            a.download = resolvedFilename;
            document.body.appendChild(a);
            a.click();
            setTimeout(() => {
                if (a.parentNode) a.parentNode.removeChild(a);
            }, 1000);

            Swal.fire({
                icon: 'success',
                title: '¡Descarga Exitosa!',
                text: 'El archivo se ha guardado correctamente.',
                timer: 1700,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl shadow-xl' }
            });
            if (onSuccess) onSuccess();
            return Promise.resolve();
        } catch (err) {
            if (onError) onError(err);
            return Promise.reject(err);
        }
    }

    // Para URLs remotas del servidor (PDF, Excel, etc.):
    // 1. Mostrar feedback visual elegante con SweetAlert2
    Swal.fire({
        title: `<div class="flex items-center justify-center gap-2 text-xl font-bold text-slate-800">
            <span>${icon}</span>
            <span>Generando ${formatTitle}...</span>
        </div>`,
        html: `
            <div class="space-y-3.5 pt-2 text-left">
                <p class="text-xs text-slate-600 font-medium">
                    Consultando base de datos y preparando archivo...
                </p>
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden p-0.5 border border-slate-200 shadow-inner">
                    <div class="h-full rounded-full animate-pulse"
                        style="width: 100%; background: linear-gradient(135deg, #14368b 0%, #4f46e5 100%);">
                    </div>
                </div>
                <div class="flex justify-between items-center text-[11px] text-slate-400 font-mono">
                    <span>Iniciando descarga en el navegador...</span>
                    <span class="font-bold text-blue-900 text-xs">Listo</span>
                </div>
            </div>
        `,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: { popup: 'rounded-2xl p-6 shadow-2xl' }
    });

    // 2. Disparador <a> directo síncrono (aprovecha el gesto del usuario actual)
    try {
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = urlOrData;
        a.setAttribute('download', resolvedFilename);
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            if (a.parentNode) a.parentNode.removeChild(a);
        }, 2000);
    } catch (e) {
        console.warn('Link download error:', e);
    }

    // 3. Disparo nativo principal por window.location.href
    // Al navegar a una ruta con Content-Disposition: attachment, el navegador inicia la descarga
    // directamente en el gestor de descargas de Chrome/Firefox/Edge SIN recargar ni abandonar la página.
    setTimeout(() => {
        try {
            window.location.href = urlOrData;
        } catch (e) {
            window.location.assign(urlOrData);
        }
    }, 150);

    if (onSuccess) onSuccess();
    return Promise.resolve();
}

// Hacer disponible globalmente en window
if (typeof window !== 'undefined') {
    window.downloadWithProgress = downloadWithProgress;
}

export default downloadWithProgress;
