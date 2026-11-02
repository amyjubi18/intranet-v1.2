<div>
    <div class="flex flex-wrap mt-4">
        <div class="w-full sm:w-1/2">
            <div class="bg-white shadow-md rounded-lg p-4">
                <h4 class="text-center text-theme-1 font-bold text-lg">
                    TOP 5 MAS VENDIDOS
                </h4>
                <div id="chartTop5"></div>
            </div>
        </div>
        <div class="w-full sm:w-1/2">
            <div class="bg-white shadow-md rounded-lg p-4">
                <h4 class="text-center text-theme-1 font-bold text-lg">
                    VENTAS DE LA SEMANA
                </h4>
                <div id="areaChart"></div>
            </div>
        </div>
    </div>
    <div class="pt-5">
        <div class="w-full">
            <div class="bg-white shadow-md rounded-lg p-4">
                <h4 class="text-center text-theme-1 font-bold text-lg">
                    VENTAS ANUALES {{$year}}
                </h4>
                <div id="chartMonth"></div>
            </div>
        </div>
    </div>
    @include('livewire.dashboard.script')
</div>
