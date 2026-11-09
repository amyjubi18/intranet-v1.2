@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegación de páginas" class="flex items-center justify-between py-2">
        {{-- Versión móvil --}}
        <div class="flex justify-between flex-1 sm:hidden gap-2">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed rounded-lg">
                    &laquo; Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    &laquo; Anterior
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    Siguiente &raquo;
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed rounded-lg">
                    Siguiente &raquo;
                </span>
            @endif
        </div>

        {{-- Versión escritorio y tablet --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-gray-600 font-medium">
                    Mostrando
                    @if ($paginator->firstItem())
                        <span class="font-bold text-blue-700">{{ $paginator->firstItem() }}</span>
                        a
                        <span class="font-bold text-blue-700">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-bold text-blue-700">{{ $paginator->count() }}</span>
                    @endif
                    de
                    <span class="font-bold text-blue-700">{{ $paginator->total() }}</span>
                    resultados
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px border border-gray-200 overflow-hidden bg-white">
                    {{-- Botón Anterior (<) --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Anterior" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-gray-50 cursor-not-allowed border-r border-gray-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-white hover:bg-blue-50 hover:text-blue-600 transition-colors border-r border-gray-200" aria-label="Anterior">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Elementos de Paginación --}}
                    @foreach ($elements as $element)
                        {{-- Separador de 3 puntos "..." --}}
                        @if (is_string($element))
                            <span aria-disabled="true" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-50 cursor-default border-r border-gray-200">{{ $element }}</span>
                        @endif

                        {{-- Lista de Números de Página --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-blue-600 cursor-default border-r border-blue-600 shadow-inner">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-blue-50 hover:text-blue-600 transition-colors border-r border-gray-200" aria-label="Ir a página {{ $page }}">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Botón Siguiente (>) --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-white hover:bg-blue-50 hover:text-blue-600 transition-colors" aria-label="Siguiente">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Siguiente" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-gray-50 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
