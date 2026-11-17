import Swal from 'sweetalert2';

// Mutex global para evitar que se abran múltiples modales simultáneos
let isCurrentlyDownloading = false;

/**
 * Descarga archivos (PDF, Excel, Imágenes, etc.) con modal de progreso en tiempo real y SweetAlert2.
 * Funciona tanto con URLs remotas del servidor como con Data URLs (Base64) y Blobs.
 *
 * @param {string} urlOrData - URL del archivo o Data URI (base64)
 * @param {Object|string} [options] - Opciones de descarga o nombre de archivo directo
 * @param {string} [options.filename] - Nombre de archivo personalizado
 * @param {'excel'|'pdf'|'image'|'file'} [options.type] - Tipo de archivo explícito
 * @param {string} [options.title] - Título personalizado para el modal
 * @param {Function} [options.onSuccess] - Callback ejecutado tras descarga exitosa
 * @param {Function} [options.onError] - Callback ejecutado en caso de error
 * @param {string} [legacyType] - Tipo de archivo si se usa la firma clásica (url, filename, type)
 * @returns {Promise<void>}
 */
export function downloadWithProgress(urlOrData, options = {}, legacyType = null) {
    if (!urlOrData) return Promise.reject(new Error('URL o archivo no especificado'));

    // Evitar descargas duplicadas o modales concurrentes
    if (isCurrentlyDownloading) {
        console.warn('downloadWithProgress: ya existe una descarga en curso, omitiendo petición duplicada.');
        return Promise.resolve();
    }
    isCurrentlyDownloading = true;

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

    // Configuración visual y textos según el tipo de archivo
    let formatTitle = 'Archivo';
    let icon = '📁';
    let defaultFilename = 'descarga';
    let initialStep = 'Preparando registros y aplicando filtros...';
    let middleStep1 = 'Recopilando datos de la base de datos...';
    let middleStep2 = 'Compilando y estructurando archivo...';

    if (detectedType === 'excel') {
        formatTitle = 'Excel';
        icon = '📊';
        defaultFilename = 'reporte.xlsx';
        initialStep = 'Preparando registros y filtros...';
        middleStep1 = 'Recopilando datos y calculando estadísticas...';
        middleStep2 = 'Formateando celdas de la hoja de cálculo...';
    } else if (detectedType === 'pdf') {
        formatTitle = 'PDF';
        icon = '📄';
        defaultFilename = 'documento.pdf';
        initialStep = 'Preparando registros y filtros...';
        middleStep1 = 'Consultando base de datos y estadísticas...';
        middleStep2 = 'Renderizando páginas y estilos...';
    } else if (detectedType === 'image') {
        formatTitle = 'Imagen';
        icon = '🖼️';
        defaultFilename = 'imagen.png';
        initialStep = 'Procesando imagen de alta resolución...';
        middleStep1 = 'Codificando píxeles y transparencia...';
        middleStep2 = 'Optimizando archivo PNG...';
    }

    const title = customTitle || (isDataUri ? `Descargando ${formatTitle}` : `Exportando a ${formatTitle}`);
    const resolvedFilename = customFilename || (cleanUrl ? cleanUrl.split('/').pop() : defaultFilename) || defaultFilename;

    return new Promise((resolve, reject) => {
        let progressInterval = null;
        let isAborted = false;
        const abortController = typeof AbortController !== 'undefined' ? new AbortController() : null;

        Swal.fire({
            title: `<div class="flex items-center justify-center gap-2 text-xl font-bold text-slate-800">
                <span>${icon}</span>
                <span>${title}</span>
            </div>`,
            html: `
                <div class="space-y-3.5 pt-2 text-left">
                    <p id="global-export-status-text" class="text-xs text-slate-600 font-medium">
                        ${initialStep}
                    </p>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden p-0.5 border border-slate-200 shadow-inner">
                        <div id="global-export-progress-bar" class="h-full rounded-full transition-all duration-300"
                            style="width: 15%; background: linear-gradient(135deg, #14368b 0%, #4f46e5 100%);">
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-[11px] text-slate-400 font-mono">
                        <span id="global-export-step-text">Generando archivo en el servidor...</span>
                        <span id="global-export-progress-percent" class="font-bold text-blue-900 text-xs">15%</span>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: { popup: 'rounded-2xl p-6 shadow-2xl' },
            didClose: () => {
                isCurrentlyDownloading = false;
                if (progressInterval) clearInterval(progressInterval);
                if (abortController) abortController.abort();
            },
            didOpen: async () => {
                const bar = document.getElementById('global-export-progress-bar');
                const percent = document.getElementById('global-export-progress-percent');
                const status = document.getElementById('global-export-status-text');
                const step = document.getElementById('global-export-step-text');

                // Si es un dataURI directo (como código QR o imagen en base64)
                if (isDataUri) {
                    if (bar) bar.style.width = '50%';
                    if (percent) percent.innerText = '50%';

                    setTimeout(() => {
                        const a = document.createElement('a');
                        a.href = urlOrData;
                        a.download = resolvedFilename;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();

                        if (bar) bar.style.width = '100%';
                        if (percent) percent.innerText = '100%';
                        if (status) status.innerText = '¡Descarga completada!';

                        setTimeout(() => {
                            isCurrentlyDownloading = false;
                            Swal.fire({
                                icon: 'success',
                                title: '¡Descarga Exitosa!',
                                text: `La imagen se ha guardado correctamente.`,
                                timer: 1700,
                                showConfirmButton: false,
                                customClass: { popup: 'rounded-2xl shadow-xl' }
                            });
                            if (onSuccess) onSuccess();
                            resolve();
                        }, 400);
                    }, 300);
                    return;
                }

                // Simulación suave de progreso mientras se espera la respuesta HTTP real
                let currentProgress = 20;
                progressInterval = setInterval(() => {
                    if (currentProgress < 85) {
                        currentProgress += 5;
                        if (bar) bar.style.width = `${currentProgress}%`;
                        if (percent) percent.innerText = `${currentProgress}%`;
                        if (currentProgress >= 45 && status) status.innerText = middleStep1;
                        if (currentProgress >= 70 && status) status.innerText = middleStep2;
                    }
                }, 250);

                try {
                    // Petición real al servidor para descargar el archivo
                    const response = await fetch(urlOrData, {
                        method: 'GET',
                        signal: abortController ? abortController.signal : undefined,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/pdf, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, */*'
                        }
                    });

                    if (progressInterval) clearInterval(progressInterval);

                    if (!response.ok) {
                        let errorMessage = `El servidor respondió con código ${response.status} (${response.statusText || 'Error'})`;
                        if (response.status === 502) {
                            errorMessage = 'Error 502: Bad Gateway en el servidor. El proceso de exportación excedió el tiempo o memoria disponible.';
                        } else if (response.status === 504) {
                            errorMessage = 'Error 504: Gateway Timeout. La generación del archivo tomó demasiado tiempo.';
                        } else if (response.status === 403) {
                            errorMessage = 'No tienes permisos asignados para exportar estos datos.';
                        } else if (response.status === 401) {
                            window.location.href = '/login?timeout=1';
                            return;
                        }
                        throw new Error(errorMessage);
                    }

                    if (bar) bar.style.width = '95%';
                    if (percent) percent.innerText = '95%';
                    if (status) status.innerText = 'Descargando archivo...';
                    if (step) step.innerText = 'Finalizando...';

                    const blob = await response.blob();
                    const blobUrl = window.URL.createObjectURL(blob);

                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = blobUrl;
                    a.download = resolvedFilename;
                    document.body.appendChild(a);
                    a.click();

                    // Disparador nativo complementario vía iframe invisible (100% infalible contra bloqueadores de descargas asíncronas en Chrome)
                    if (!isDataUri && urlOrData) {
                        try {
                            const iframe = document.createElement('iframe');
                            iframe.style.display = 'none';
                            iframe.src = urlOrData;
                            document.body.appendChild(iframe);
                            setTimeout(() => {
                                if (iframe.parentNode) iframe.parentNode.removeChild(iframe);
                            }, 40000);
                        } catch (e) {
                            console.warn('Iframe fallback download warning:', e);
                        }
                    }

                    setTimeout(() => {
                        if (a.parentNode) a.parentNode.removeChild(a);
                        window.URL.revokeObjectURL(blobUrl);
                    }, 2500);

                    if (bar) bar.style.width = '100%';
                    if (percent) percent.innerText = '100%';
                    if (status) status.innerText = '¡Descarga completada!';
                    if (step) step.innerText = 'Guardado';

                    setTimeout(() => {
                        isCurrentlyDownloading = false;
                        Swal.fire({
                            icon: 'success',
                            title: '¡Descarga Completada!',
                            text: `El archivo ${resolvedFilename} se ha descargado correctamente.`,
                            timer: 1800,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-2xl shadow-xl' }
                        });
                        if (onSuccess) onSuccess();
                        resolve();
                    }, 400);

                } catch (err) {
                    if (progressInterval) clearInterval(progressInterval);
                    isCurrentlyDownloading = false;

                    if (err.name === 'AbortError') {
                        resolve();
                        return;
                    }

                    console.error('Error al exportar archivo:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al exportar',
                        text: err.message || 'Ocurrió un error inesperado al procesar la exportación.',
                        confirmButtonColor: '#1e3a8a',
                        customClass: { popup: 'rounded-2xl' }
                    });

                    if (onError) onError(err);
                    reject(err);
                }
            }
        });
    });
}

// Hacer disponible globalmente en window
if (typeof window !== 'undefined') {
    window.downloadWithProgress = downloadWithProgress;
}

export default downloadWithProgress;
