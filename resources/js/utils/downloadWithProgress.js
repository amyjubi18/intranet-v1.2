import Swal from 'sweetalert2';

// Mutex global para evitar que se abran múltiples modales simultáneos
let isCurrentlyDownloading = false;

/**
 * Descarga archivos (PDF, Excel, Imágenes, etc.) con modal de progreso en tiempo real y SweetAlert2.
 * Funciona tanto con URLs remotas del servidor como con Data URLs (Base64) y Blobs.
 *
 * @param {string} urlOrData - URL del archivo o Data URI (base64)
 * @param {Object} options - Opciones de descarga
 * @param {string} [options.filename] - Nombre de archivo personalizado
 * @param {'excel'|'pdf'|'image'|'file'} [options.type] - Tipo de archivo explícito
 * @param {string} [options.title] - Título personalizado para el modal
 * @param {Function} [options.onSuccess] - Callback ejecutado tras descarga exitosa
 * @param {Function} [options.onError] - Callback ejecutado en caso de error
 * @param {Object} [options.fetchOptions] - Opciones adicionales para fetch
 * @returns {Promise<void>}
 */
export function downloadWithProgress(urlOrData, options = {}) {
    if (!urlOrData) return Promise.reject(new Error('URL o archivo no especificado'));

    // Evitar descargas duplicadas o modales concurrentes
    if (isCurrentlyDownloading) {
        console.warn('downloadWithProgress: ya existe una descarga en curso, omitiendo petición duplicada.');
        return Promise.resolve();
    }
    isCurrentlyDownloading = true;

    const {
        filename: customFilename,
        type: explicitType,
        title: customTitle,
        onSuccess,
        onError,
        fetchOptions = {}
    } = options;

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
        middleStep2 = 'Renderizando páginas con DomPDF...';
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
        let progress = 15;

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
            },
            didOpen: () => {
                const bar = document.getElementById('global-export-progress-bar');
                const percent = document.getElementById('global-export-progress-percent');
                const status = document.getElementById('global-export-status-text');
                const step = document.getElementById('global-export-step-text');

                // Si es un dataURI directo (como código QR o imagen en base64)
                if (isDataUri) {
                    if (bar) bar.style.width = '35%';
                    if (percent) percent.innerText = '35%';
                    if (status) status.innerText = 'Generando archivo de imagen...';

                    setTimeout(() => {
                        if (bar) bar.style.width = '80%';
                        if (percent) percent.innerText = '80%';
                        if (status) status.innerText = 'Preparando descarga...';
                    }, 180);

                    setTimeout(() => {
                        if (bar) bar.style.width = '100%';
                        if (percent) percent.innerText = '100%';
                        if (status) status.innerText = '¡Descarga completada!';
                        if (step) step.innerText = 'Guardado';

                        const a = document.createElement('a');
                        a.href = urlOrData;
                        a.download = resolvedFilename;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();

                        setTimeout(() => {
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
                    }, 400);
                    return;
                }

                // Si es una petición al servidor
                const interval = setInterval(() => {
                    if (progress < 85) {
                        progress += Math.floor(Math.random() * 8) + 4;
                        if (progress > 85) progress = 85;
                        if (bar) bar.style.width = `${progress}%`;
                        if (percent) percent.innerText = `${progress}%`;
                        if (status) {
                            if (progress > 25 && progress <= 55) {
                                status.innerText = middleStep1;
                                if (step) step.innerText = 'Consultando datos...';
                            } else if (progress > 55) {
                                status.innerText = middleStep2;
                                if (step) step.innerText = 'Comprimiendo y empaquetando...';
                            }
                        }
                    }
                }, 350);

                fetch(urlOrData, {
                    headers: {
                        'Accept': '*/*',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...fetchOptions.headers
                    },
                    ...fetchOptions
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error en el servidor (${response.status})`);
                        }

                        // Intentar obtener filename de Content-Disposition si existe
                        let serverFilename = resolvedFilename;
                        const disposition = response.headers.get('Content-Disposition');
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                            const matches = filenameRegex.exec(disposition);
                            if (matches != null && matches[1]) {
                                serverFilename = matches[1].replace(/['"]/g, '');
                            }
                        }

                        return response.blob().then(blob => ({ blob, serverFilename }));
                    })
                    .then(({ blob, serverFilename }) => {
                        clearInterval(interval);
                        if (bar) bar.style.width = '100%';
                        if (percent) percent.innerText = '100%';
                        if (status) status.innerText = '¡Archivo generado exitosamente! Descargando...';
                        if (step) step.innerText = 'Descarga completada';

                        const blobUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = blobUrl;
                        a.download = serverFilename || resolvedFilename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(blobUrl);
                        a.remove();

                        setTimeout(() => {
                            isCurrentlyDownloading = false;
                            Swal.fire({
                                icon: 'success',
                                title: '¡Descarga Finalizada!',
                                text: `El archivo ${serverFilename || resolvedFilename} se ha descargado correctamente.`,
                                timer: 1800,
                                showConfirmButton: false,
                                customClass: { popup: 'rounded-2xl shadow-xl' }
                            });
                            if (onSuccess) onSuccess();
                            resolve();
                        }, 500);
                    })
                    .catch(err => {
                        clearInterval(interval);
                        isCurrentlyDownloading = false;
                        console.error('Error al descargar/exportar:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Descarga',
                            text: 'No se pudo generar o descargar el archivo. Por favor intente nuevamente.',
                            confirmButtonColor: '#14368b'
                        });
                        if (onError) onError(err);
                        reject(err);
                    });
            }
        });
    });
}

// Hacer disponible globalmente en window
if (typeof window !== 'undefined') {
    window.downloadWithProgress = downloadWithProgress;
}

export default downloadWithProgress;
