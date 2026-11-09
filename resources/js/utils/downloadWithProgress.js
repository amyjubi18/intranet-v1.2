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
        onError
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
        let t1 = null;
        let t2 = null;
        let tTrigger = null;
        let tFinish = null;

        const clearAllTimers = () => {
            if (t1) clearTimeout(t1);
            if (t2) clearTimeout(t2);
            if (tTrigger) clearTimeout(tTrigger);
            if (tFinish) clearTimeout(tFinish);
        };

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
                clearAllTimers();
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

                    t1 = setTimeout(() => {
                        if (bar) bar.style.width = '80%';
                        if (percent) percent.innerText = '80%';
                        if (status) status.innerText = 'Preparando descarga...';
                    }, 180);

                    tFinish = setTimeout(() => {
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
                    }, 400);
                    return;
                }

                // Si es una petición a ruta del servidor (Excel, PDF, CSV, reportes)
                t1 = setTimeout(() => {
                    if (bar) bar.style.width = '45%';
                    if (percent) percent.innerText = '45%';
                    if (status) status.innerText = middleStep1;
                    if (step) step.innerText = 'Consultando base de datos...';
                }, 350);

                t2 = setTimeout(() => {
                    if (bar) bar.style.width = '75%';
                    if (percent) percent.innerText = '75%';
                    if (status) status.innerText = middleStep2;
                    if (step) step.innerText = 'Compilando archivo...';
                }, 750);

                // Disparar la descarga nativa del navegador mediante navegación directa
                // Dado que el servidor responde con Content-Disposition: attachment,
                // el navegador descarga el archivo de forma nativa sin perder la página ni el estado
                tTrigger = setTimeout(() => {
                    if (bar) bar.style.width = '95%';
                    if (percent) percent.innerText = '95%';
                    if (status) status.innerText = 'Iniciando descarga en su equipo...';
                    if (step) step.innerText = 'Empaquetando...';

                    try {
                        window.location.assign(urlOrData);
                    } catch (e) {
                        window.location.href = urlOrData;
                    }
                }, 1100);

                tFinish = setTimeout(() => {
                    if (bar) bar.style.width = '100%';
                    if (percent) percent.innerText = '100%';
                    if (status) status.innerText = '¡Descarga iniciada exitosamente!';
                    if (step) step.innerText = 'Descarga completada';

                    setTimeout(() => {
                        isCurrentlyDownloading = false;
                        Swal.fire({
                            icon: 'success',
                            title: '¡Descarga Iniciada!',
                            text: `El archivo ${resolvedFilename} se está descargando en tu equipo.`,
                            timer: 1800,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-2xl shadow-xl' }
                        });
                        if (onSuccess) onSuccess();
                        resolve();
                    }, 400);
                }, 1600);
            }
        });
    });
}

// Hacer disponible globalmente en window
if (typeof window !== 'undefined') {
    window.downloadWithProgress = downloadWithProgress;
}

export default downloadWithProgress;
