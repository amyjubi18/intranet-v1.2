@section('title', 'Perfil')
@section('subtitle', 'Información y configuración de la cuenta')
<x-app-layout>

    <x-slot name="scripts">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    </x-slot>
    <script src="/_sdk/element_sdk.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;family=Space+Grotesk:wght@500;600;700&amp;display=swap" rel="stylesheet">
  <style>
    body {
      box-sizing: border-box;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      width: 100%;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 50%, #dbeafe 100%);
      overflow-x: hidden;
    }

    .main-wrapper {
      height: 100%;
      width: 100%;
      overflow-y: auto;
      position: relative;
    }

    /* Patrón de fondo sutil */
    .background-pattern {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image:
        radial-gradient(circle at 20% 30%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.06) 0%, transparent 50%);
      pointer-events: none;
      z-index: 1;
    }

    /* Contenedor principal */
    .content-wrapper {
      position: relative;
      z-index: 10;
      max-width: 1200px;
      margin: 0 auto;
      padding: 2.5rem;
      min-height: 100%;
    }

    /* Header del módulo */
    .module-header {
      background: white;
      border-radius: 1.5rem;
      padding: 2rem 2.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .header-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1.5rem;
    }

    .header-title {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .header-icon {
      width: 48px;
      height: 48px;
      background: linear-gradient(135deg, #1e3a8a 0%, #1e3a8a 100%);
      border-radius: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .header-icon svg {
      width: 24px;
      height: 24px;
      color: white;
    }

    .header-text h1 {
      font-size: 1.75rem;
      font-weight: 800;
      color: #1e293b;
      font-family: 'Space Grotesk', sans-serif;
      margin-bottom: 0.25rem;
    }

    .header-text p {
      font-size: 0.875rem;
      color: #64748b;
      font-weight: 500;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.625rem 1.25rem;
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      border-radius: 2rem;
      font-size: 0.875rem;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .status-dot {
      width: 8px;
      height: 8px;
      background: white;
      border-radius: 50%;
      animation: pulse-dot 2s ease-in-out infinite;
    }

    @keyframes pulse-dot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.7; transform: scale(1.1); }
    }

    /* Sección de foto de perfil */
    .profile-photo-section {
      background: white;
      border-radius: 1.5rem;
      padding: 2.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .section-header {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 2rem;
      padding-bottom: 1.25rem;
      border-bottom: 2px solid #f1f5f9;
    }

    .section-header svg {
      width: 24px;
      height: 24px;
      color: #14368b;
    }

    .section-header h2 {
      font-size: 1.25rem;
      font-weight: 700;
      color: #1e293b;
      font-family: 'Space Grotesk', sans-serif;
      flex: 1;
    }

    .edit-info-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.25rem;
      background: linear-gradient(135deg, #14368b 0%, #4f46e5 100%);
      color: white;
      border: none;
      border-radius: 0.75rem;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
      margin-left: auto;
    }

    .edit-info-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
    }

    .edit-info-btn svg {
      width: 18px;
      height: 18px;
    }

    .edit-info-btn.editing {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .edit-info-btn.editing:hover {
      box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
    }

    .photo-upload-container {
      display: flex;
      align-items: center;
      gap: 2.5rem;
      flex-wrap: wrap;
    }

    .photo-wrapper {
      position: relative;
    }

    .profile-photo {
      width: 140px;
      height: 140px;
      border-radius: 1.5rem;
      background: linear-gradient(135deg, #14368b 0%, #8b5cf6 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
      overflow: hidden;
      border: 4px solid white;
      position: relative;
    }

    .profile-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .profile-photo svg {
      width: 64px;
      height: 64px;
      color: white;
    }

    .change-photo-btn {
      position: absolute;
      bottom: -12px;
      right: -12px;
      width: 48px;
      height: 48px;
      background: linear-gradient(135deg, #14368b 0%, #4f46e5 100%);
      border: 4px solid white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    }

    .change-photo-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.5);
    }

    .change-photo-btn svg {
      width: 20px;
      height: 20px;
      color: white;
    }

    .photo-info {
      flex: 1;
      min-width: 250px;
    }

    .photo-info h3 {
      font-size: 1.5rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.5rem;
    }

    .photo-info p {
      font-size: 0.875rem;
      color: #64748b;
      line-height: 1.6;
      margin-bottom: 1rem;
    }

    .upload-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.5rem;
      background: linear-gradient(135deg, #14368b 0%, #4f46e5 100%);
      color: white;
      border: none;
      border-radius: 0.75rem;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .upload-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
    }

    .upload-btn svg {
      width: 18px;
      height: 18px;
    }

    input[type="file"] {
      display: none;
    }

    /* Grid de información */
    .info-grid-section {
      background: white;
      border-radius: 1.5rem;
      padding: 2.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
    }

    .info-field {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .info-label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.8rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .info-label svg {
      width: 16px;
      height: 16px;
      color: #14368b;
    }

    .info-value {
      padding: 1rem 1.25rem;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border: 2px solid #e2e8f0;
      border-radius: 0.75rem;
      font-size: 0.95rem;
      font-weight: 600;
      color: #1e293b;
      transition: all 0.3s ease;
    }

    .info-value:hover {
      border-color: #cbd5e1;
      background: #f8fafc;
    }

    /* Sección de rol con permisos */
    .role-section {
      background: white;
      border-radius: 1.5rem;
      padding: 2.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .role-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1.5rem;
      flex-wrap: wrap;
    }

    .role-info {
      flex: 1;
    }

    .role-title {
      font-size: 0.8rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.75rem;
    }

    .role-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      padding: 1rem 1.75rem;
      background: linear-gradient(135deg, #14368b 0%, #4f46e5 100%);
      color: white;
      border-radius: 1rem;
      font-size: 1.125rem;
      font-weight: 700;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .role-badge svg {
      width: 24px;
      height: 24px;
    }

    .view-permissions-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.625rem;
      padding: 1rem 1.75rem;
      background: white;
      color: #14368b;
      border: 2px solid #14368b;
      border-radius: 0.75rem;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .view-permissions-btn:hover {
      background: #14368b;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
    }

    .view-permissions-btn svg {
      width: 20px;
      height: 20px;
      transition: transform 0.3s ease;
    }

    .view-permissions-btn.expanded svg {
      transform: rotate(180deg);
    }

    .permissions-panel {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease, opacity 0.4s ease, margin-top 0.4s ease;
      opacity: 0;
    }

    .permissions-panel.expanded {
      max-height: 1000px;
      opacity: 1;
      margin-top: 2rem;
    }

    .permissions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1rem;
      padding: 1.5rem;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border-radius: 1rem;
      border: 2px solid #e2e8f0;
    }

    .permission-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.875rem 1.125rem;
      background: white;
      border-radius: 0.75rem;
      font-size: 0.875rem;
      font-weight: 500;
      color: #334155;
      border: 1px solid #e2e8f0;
      transition: all 0.3s ease;
    }

    .permission-item:hover {
      border-color: #14368b;
      transform: translateX(4px);
    }

    .permission-item svg {
      width: 18px;
      height: 18px;
      color: #10b981;
      flex-shrink: 0;
    }

    /* Sección de cambio de contraseña */
    .password-section {
      background: white;
      border-radius: 1.5rem;
      padding: 2.5rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .form-grid {
      display: grid;
      gap: 1.5rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.625rem;
    }

    .form-group label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      font-weight: 700;
      color: #334155;
      letter-spacing: 0.3px;
    }

    .form-group label svg {
      width: 18px;
      height: 18px;
      color: #14368b;
    }

    .input-wrapper {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 1.125rem;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      transition: color 0.3s ease;
      pointer-events: none;
    }

    .input-icon svg {
      width: 20px;
      height: 20px;
    }

    .form-input {
      width: 100%;
      padding: 1rem 1rem 1rem 3.25rem;
      border: 2px solid #e2e8f0;
      border-radius: 0.75rem;
      font-size: 0.95rem;
      font-weight: 500;
      color: #1e293b;
      background: white;
      transition: all 0.3s ease;
      font-family: 'Inter', sans-serif;
    }

    .form-input:focus {
      outline: none;
      border-color: #14368b;
      box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .form-input:focus ~ .input-icon {
      color: #14368b;
    }

    .form-input::placeholder {
      color: #94a3b8;
      font-weight: 400;
    }

    .form-actions {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }

    .btn-save {
      flex: 1;
      padding: 1.125rem 2rem;
      background: linear-gradient(135deg, #14368b 0%, #4f46e5 100%);
      color: white;
      border: none;
      border-radius: 0.75rem;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.625rem;
    }

    .btn-save:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(26, 91, 175, 0.4);
    }

    .btn-save:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .btn-save svg {
      width: 20px;
      height: 20px;
    }

    .btn-cancel {
      padding: 1.125rem 2rem;
      background: white;
      color: #64748b;
      border: 2px solid #e2e8f0;
      border-radius: 0.75rem;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-cancel:hover {
      border-color: #cbd5e1;
      color: #475569;
      background: #f8fafc;
    }

    /* Toast de éxito */
    .success-toast {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      padding: 1.25rem 1.75rem;
      border-radius: 1rem;
      box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
      display: none;
      align-items: center;
      gap: 1rem;
      z-index: 1000;
      animation: slideInUp 0.4s ease-out;
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .success-toast.show {
      display: flex;
    }

    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(60px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .success-toast svg {
      width: 28px;
      height: 28px;
      flex-shrink: 0;
    }

    .toast-content {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }

    .toast-title {
      font-weight: 700;
      font-size: 1rem;
    }

    .toast-message {
      font-size: 0.875rem;
      opacity: 0.95;
    }

    /* Modal de permisos overlay alternativo */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.7);
      backdrop-filter: blur(4px);
      z-index: 999;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      animation: fadeIn 0.3s ease-out;
    }

    .modal-overlay.show {
      display: flex;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .modal-content {
      background: white;
      border-radius: 1.5rem;
      max-width: 700px;
      width: 100%;
      max-height: 85%;
      overflow-y: auto;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
      animation: slideInScale 0.4s ease-out;
    }

    @keyframes slideInScale {
      from {
        opacity: 0;
        transform: scale(0.9) translateY(30px);
      }
      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    .modal-header {
      padding: 2rem 2.5rem;
      border-bottom: 2px solid #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .modal-title {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 1.5rem;
      font-weight: 700;
      color: #1a4384;
      font-family: 'Space Grotesk', sans-serif;
    }

    .modal-title svg {
      width: 28px;
      height: 28px;
      color: #0f1054;
    }

    .modal-close {
      width: 40px;
      height: 40px;
      border: none;
      background: #f1f5f9;
      border-radius: 0.75rem;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .modal-close:hover {
      background: #e2e8f0;
      transform: rotate(90deg);
    }

    .modal-close svg {
      width: 20px;
      height: 20px;
      color: #64748b;
    }

    .modal-body {
      padding: 2rem 2.5rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1.5rem;
      }

      .module-header {
        padding: 1.5rem;
      }

      .header-content {
        flex-direction: column;
        align-items: flex-start;
      }

      .header-text h1 {
        font-size: 1.5rem;
      }

      .profile-photo-section,
      .info-grid-section,
      .role-section,
      .password-section {
        padding: 1.5rem;
      }

      .photo-upload-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }

      .info-grid {
        grid-template-columns: 1fr;
      }

      .role-header {
        flex-direction: column;
        align-items: stretch;
      }

      .view-permissions-btn {
        width: 100%;
        justify-content: center;
      }

      .form-actions {
        flex-direction: column;
      }

      .success-toast {
        bottom: 1rem;
        right: 1rem;
        left: 1rem;
      }
    }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
  </style>


    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="w-full">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div> --}}
        </div>
    </div>
</x-app-layout>
