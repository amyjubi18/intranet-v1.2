import React, { useState, useEffect, useRef, useCallback } from 'react';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

/**
 * Componente Modal para Escaneo de Códigos de Barras y Códigos QR en vivo usando la cámara.
 * Soporta cámara trasera/frontal, linterna (torch), bip sonoro, vibración háptica y fallback por imagen.
 */
export default function CameraScannerModal({
    isOpen,
    onClose,
    onScan,
    title = 'Escanear con Cámara',
    subtitle = 'Apunta la cámara al código de barras o código QR',
    allowedFields = null, // ['serial', 'n_interno_bien', 'qr_code'] si se desea selector de destino
    defaultTargetField = null
}) {
    const [cameras, setCameras] = useState([]);
    const [selectedCameraId, setSelectedCameraId] = useState('');
    const [isScanning, setIsScanning] = useState(false);
    const [cameraError, setCameraError] = useState(null);
    const [torchAvailable, setTorchAvailable] = useState(false);
    const [torchOn, setTorchOn] = useState(false);
    const [lastScannedCode, setLastScannedCode] = useState(null);
    const [targetField, setTargetField] = useState(defaultTargetField);

    const html5QrCodeRef = useRef(null);
    const isStoppingRef = useRef(false);

    // Reproducir bip sonoro usando Web Audio API nativo
    const playBeep = useCallback(() => {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) {
                const ctx = new AudioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                gain.gain.setValueAtTime(0.18, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.15);
            }
        } catch {
            // Ignorar si el navegador bloquea audio sin interacción
        }
        if (navigator.vibrate) {
            try {
                navigator.vibrate([60, 40, 60]);
            } catch {
                // Silencioso
            }
        }
    }, []);

    // Detener la cámara con seguridad
    const stopCamera = useCallback(async () => {
        if (!html5QrCodeRef.current || isStoppingRef.current) return;
        try {
            isStoppingRef.current = true;
            if (html5QrCodeRef.current.isScanning) {
                await html5QrCodeRef.current.stop();
            }
            await html5QrCodeRef.current.clear();
        } catch (err) {
            console.warn('Error al detener la cámara:', err);
        } finally {
            html5QrCodeRef.current = null;
            isStoppingRef.current = false;
            setIsScanning(false);
            setTorchOn(false);
            setTorchAvailable(false);
        }
    }, []);

    // Iniciar el escáner
    const startCamera = useCallback(async (cameraId = null) => {
        if (!isOpen) return;
        setCameraError(null);

        // Si ya hay una instancia previa activa, detenerla primero
        if (html5QrCodeRef.current && html5QrCodeRef.current.isScanning) {
            await stopCamera();
        }

        try {
            // Esperar que el div contenedor exista en el DOM
            const container = document.getElementById('camera-reader-viewport');
            if (!container) return;

            const qrCode = new Html5Qrcode('camera-reader-viewport', {
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E,
                    Html5QrcodeSupportedFormats.ITF,
                    Html5QrcodeSupportedFormats.CODABAR,
                    Html5QrcodeSupportedFormats.DATA_MATRIX
                ],
                verbose: false
            });

            html5QrCodeRef.current = qrCode;

            // Obtener lista de cámaras
            try {
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length > 0) {
                    setCameras(devices);
                    if (!selectedCameraId) {
                        // Priorizar cámara trasera si existe
                        const backCamera = devices.find(d =>
                            d.label.toLowerCase().includes('back') ||
                            d.label.toLowerCase().includes('trasera') ||
                            d.label.toLowerCase().includes('environment')
                        );
                        setSelectedCameraId(backCamera ? backCamera.id : devices[0].id);
                    }
                }
            } catch {
                // Si getCameras falla, se continuará usando facingMode
            }

            const config = {
                fps: 15,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    const qrboxSize = Math.floor(minEdge * 0.75);
                    return { width: qrboxSize, height: Math.floor(qrboxSize * 0.7) };
                },
                aspectRatio: 1.0
            };

            const cameraTarget = cameraId
                ? { deviceId: { exact: cameraId } }
                : selectedCameraId
                    ? { deviceId: { exact: selectedCameraId } }
                    : { facingMode: 'environment' };

            await qrCode.start(
                cameraTarget,
                config,
                (decodedText, decodedResult) => {
                    playBeep();
                    setLastScannedCode(decodedText);
                    // Notificar al padre
                    if (onScan) {
                        onScan(decodedText, targetField, decodedResult);
                    }
                },
                () => {
                    // Ignorar frames sin código detectado
                }
            );

            setIsScanning(true);

            // Verificar si soporta linterna (torch)
            try {
                const capabilities = qrCode.getRunningTrackCapabilities();
                if (capabilities && capabilities.torch) {
                    setTorchAvailable(true);
                }
            } catch {
                setTorchAvailable(false);
            }

        } catch (err) {
            console.error('Error al iniciar cámara:', err);
            let msg = 'No se pudo acceder a la cámara.';
            if (err.name === 'NotAllowedError' || err.toString().includes('Permission')) {
                msg = 'Permiso de cámara denegado. Permite el acceso a la cámara en los permisos de tu navegador.';
            } else if (err.name === 'NotFoundError') {
                msg = 'No se detectó ninguna cámara en este dispositivo.';
            } else if (err.name === 'NotReadableError') {
                msg = 'La cámara está siendo utilizada por otra aplicación.';
            }
            setCameraError(msg);
            setIsScanning(false);
        }
    }, [isOpen, selectedCameraId, onScan, targetField, playBeep, stopCamera]);

    // Alternar linterna
    const toggleTorch = async () => {
        if (!html5QrCodeRef.current || !torchAvailable) return;
        try {
            const nextState = !torchOn;
            await html5QrCodeRef.current.applyVideoConstraints({
                advanced: [{ torch: nextState }]
            });
            setTorchOn(nextState);
        } catch (err) {
            console.warn('Error alternando linterna:', err);
        }
    };

    // Cambiar de cámara
    const handleCameraChange = async (newId) => {
        setSelectedCameraId(newId);
        await stopCamera();
        setTimeout(() => {
            startCamera(newId);
        }, 150);
    };

    // Escanear archivo de imagen (fallback sin cámara)
    const handleFileScan = async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        try {
            setCameraError(null);
            let qrCode = html5QrCodeRef.current;
            if (!qrCode) {
                qrCode = new Html5Qrcode('camera-reader-viewport');
                html5QrCodeRef.current = qrCode;
            }

            const decodedText = await qrCode.scanFile(file, true);
            playBeep();
            setLastScannedCode(decodedText);
            if (onScan) {
                onScan(decodedText, targetField);
            }
        } catch (err) {
            console.error('Error decodificando imagen:', err);
            setCameraError('No se pudo detectar ningún código de barras o QR en la imagen seleccionada.');
        } finally {
            e.target.value = '';
        }
    };

    // Iniciar y detener según isOpen
    useEffect(() => {
        if (isOpen) {
            setLastScannedCode(null);
            setTargetField(defaultTargetField);
            const timer = setTimeout(() => {
                startCamera();
            }, 200);
            return () => {
                clearTimeout(timer);
                stopCamera();
            };
        } else {
            stopCamera();
        }
    }, [isOpen, defaultTargetField, startCamera, stopCamera]);

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-black/75 backdrop-blur-sm p-3 sm:p-4 animate-modal-backdrop">
            <div className="bg-slate-900 border border-slate-700/80 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden text-white flex flex-col animate-modal-expand">
                {/* Cabecera */}
                <div className="flex items-center justify-between p-4 sm:p-5 border-b border-slate-800">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-2xl bg-blue-600/30 border border-blue-500/40 flex items-center justify-center text-xl">
                            📷
                        </div>
                        <div>
                            <h3 className="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                                <span>{title}</span>
                            </h3>
                            <p className="text-xs text-slate-400 mt-0.5">
                                {subtitle}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-300 hover:text-white transition-colors"
                        title="Cerrar escáner"
                    >
                        ✕
                    </button>
                </div>

                {/* Selector de campo destino (si aplica) */}
                {allowedFields && allowedFields.length > 1 && (
                    <div className="px-5 pt-3 pb-1 flex items-center gap-2 flex-wrap bg-slate-950/40 border-b border-slate-800/80">
                        <span className="text-[11px] font-semibold text-slate-400">Aplicar a:</span>
                        {allowedFields.includes('serial') && (
                            <button
                                type="button"
                                onClick={() => setTargetField('serial')}
                                className={`px-2.5 py-1 text-xs rounded-lg font-semibold transition-all ${
                                    targetField === 'serial'
                                        ? 'bg-blue-600 text-white shadow-xs'
                                        : 'bg-slate-800 text-slate-300 hover:bg-slate-700'
                                }`}
                            >
                                🔢 Serial
                            </button>
                        )}
                        {allowedFields.includes('n_interno_bien') && (
                            <button
                                type="button"
                                onClick={() => setTargetField('n_interno_bien')}
                                className={`px-2.5 py-1 text-xs rounded-lg font-semibold transition-all ${
                                    targetField === 'n_interno_bien'
                                        ? 'bg-blue-600 text-white shadow-xs'
                                        : 'bg-slate-800 text-slate-300 hover:bg-slate-700'
                                }`}
                            >
                                🏷️ N° Interno
                            </button>
                        )}
                        {allowedFields.includes('qr_code') && (
                            <button
                                type="button"
                                onClick={() => setTargetField('qr_code')}
                                className={`px-2.5 py-1 text-xs rounded-lg font-semibold transition-all ${
                                    targetField === 'qr_code'
                                        ? 'bg-blue-600 text-white shadow-xs'
                                        : 'bg-slate-800 text-slate-300 hover:bg-slate-700'
                                }`}
                            >
                                📱 Código QR / Barras
                            </button>
                        )}
                    </div>
                )}

                {/* Área de Cámara / Visor */}
                <div className="relative p-4 flex-1 flex flex-col items-center justify-center bg-black/60">
                    <div className="w-full relative aspect-square max-h-[320px] rounded-2xl overflow-hidden bg-black flex items-center justify-center border-2 border-slate-700">
                        <div id="camera-reader-viewport" className="w-full h-full" />

                        {/* Retículo de enfoque y láser animado */}
                        {isScanning && (
                            <div className="absolute inset-0 pointer-events-none flex items-center justify-center">
                                {/* Esquinas del visor */}
                                <div className="w-56 h-48 border-2 border-transparent relative rounded-xl">
                                    <div className="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-blue-400 rounded-tl-lg" />
                                    <div className="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-blue-400 rounded-tr-lg" />
                                    <div className="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-blue-400 rounded-bl-lg" />
                                    <div className="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-blue-400 rounded-br-lg" />

                                    {/* Línea láser de escaneo */}
                                    <div
                                        className="absolute left-1 right-1 h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-[0_0_12px_#34d399]"
                                        style={{
                                            animation: 'scanLaser 2s ease-in-out infinite alternate'
                                        }}
                                    />
                                </div>
                            </div>
                        )}

                        {/* Mensaje de error de cámara */}
                        {cameraError && (
                            <div className="absolute inset-0 bg-slate-900/95 p-6 flex flex-col items-center justify-center text-center">
                                <span className="text-3xl mb-2">⚠️</span>
                                <p className="text-sm text-red-400 font-semibold mb-3">{cameraError}</p>
                                <button
                                    type="button"
                                    onClick={() => startCamera(selectedCameraId)}
                                    className="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-xs font-bold rounded-xl transition-all"
                                >
                                    Reintentar conexión
                                </button>
                            </div>
                        )}
                    </div>

                    {/* Resultado recién escaneado */}
                    {lastScannedCode && (
                        <div className="mt-3 w-full p-2.5 bg-emerald-950/70 border border-emerald-500/60 rounded-xl flex items-center justify-between gap-2">
                            <div className="flex items-center gap-2 overflow-hidden">
                                <span className="text-emerald-400 text-lg">✓</span>
                                <div className="truncate">
                                    <span className="text-[10px] text-emerald-300 font-bold uppercase tracking-wider block">Escaneado con éxito</span>
                                    <span className="font-mono text-xs font-bold text-white truncate block">{lastScannedCode}</span>
                                </div>
                            </div>
                            <span className="text-[10px] bg-emerald-500 text-slate-950 font-extrabold px-2 py-0.5 rounded-full flex-shrink-0">
                                Listo
                            </span>
                        </div>
                    )}
                </div>

                {/* Barra de Controles Inferiores */}
                <div className="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between gap-2 flex-wrap">
                    {/* Selector de cámaras si hay más de 1 */}
                    {cameras.length > 1 ? (
                        <select
                            value={selectedCameraId}
                            onChange={(e) => handleCameraChange(e.target.value)}
                            className="text-xs bg-slate-800 text-slate-200 border border-slate-700 rounded-xl px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-500 max-w-[170px] truncate"
                        >
                            {cameras.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.label || `Cámara ${c.id.slice(0, 5)}`}
                                </option>
                            ))}
                        </select>
                    ) : (
                        <span className="text-xs text-slate-500">Cámara activa</span>
                    )}

                    <div className="flex items-center gap-2">
                        {/* Linterna si está disponible */}
                        {torchAvailable && (
                            <button
                                type="button"
                                onClick={toggleTorch}
                                className={`px-2.5 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1 transition-all ${
                                    torchOn ? 'bg-amber-500 text-black shadow-xs' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'
                                }`}
                                title="Encender / Apagar linterna"
                            >
                                <span>{torchOn ? '🔦 ON' : '🔦 OFF'}</span>
                            </button>
                        )}

                        {/* Cargar archivo de imagen como alternativa */}
                        <label className="px-2.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 flex items-center gap-1 cursor-pointer transition-all border border-slate-700" title="Cargar imagen con código">
                            <span>🖼️ Foto</span>
                            <input
                                type="file"
                                accept="image/*"
                                onChange={handleFileScan}
                                className="hidden"
                            />
                        </label>

                        {/* Botón Cerrar */}
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white transition-all shadow-xs"
                        >
                            Aceptar
                        </button>
                    </div>
                </div>
            </div>

            {/* Estilos para animación del láser */}
            <style>{`
                @keyframes scanLaser {
                    0% { top: 6%; opacity: 0.7; }
                    50% { top: 92%; opacity: 1; }
                    100% { top: 6%; opacity: 0.7; }
                }
            `}</style>
        </div>
    );
}
