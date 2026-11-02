<?php

namespace App\Http\Livewire;
use App\Models\Area;
use App\Models\Cartucho;
use App\Models\Colore;
use App\Models\Impresora;
use App\Models\Fabricante;
use App\Models\Gerencia;
use App\Models\Proveedore;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\InventarioImpresora;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Rules\{Rule, RuleActions};
use PowerComponents\LivewirePowerGrid\Traits\{ActionButton, WithExport};
use PowerComponents\LivewirePowerGrid\Filters\Filter;
use PowerComponents\LivewirePowerGrid\{Button, Column, Exportable, Footer, Header, PowerGrid, PowerGridComponent, PowerGridColumns};

final class InventarioImpresoraTable extends PowerGridComponent
{
    use ActionButton;
    use WithExport;

    /*
    |--------------------------------------------------------------------------
    |  Features Setup
    |--------------------------------------------------------------------------
    | Setup Table's general features
    |
    */
    public string $sortField = 'fecha_ingreso';

      public string $sortDirection = 'desc';
    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            Exportable::make('ingresos-oficina')
            ->striped()
            ->type(Exportable::TYPE_XLS)->columnWidth([
                1 => 20,
                2 => 10,
                3 => 35,
                4 => 20,
                5 => 30,
                6 => 25,
                7 => 25,
                8 => 25,
                9 => 25,
                10 => 25,
                11 => 25,
                12 => 25,
                13 => 20,
                14 => 20,
                15 => 20,
                16 => 20,
                17 => 25,
                18 => 25,

            ]) ,


        Footer::make()
            ->showPerPage(10)
            ->showRecordCount( mode: 'full' )
            /* ->pagination('layouts.pagination') */
    ];
    }

    /*
    |--------------------------------------------------------------------------
    |  Datasource
    |--------------------------------------------------------------------------
    | Provides data to your Table using a Model or Collection
    |
    */

    /**
     * PowerGrid datasource.
     *
     * @return Builder<\App\Models\InventarioImpresora>
     */
    public function datasource(): Builder
    {
        return InventarioImpresora::query()->with(['cartuchos:id,cartucho,colore_id,impresora_id,fabricante_id'])->with(['colores:id,colore'])->with(['impresoras:id,impresora,imagen'])->with(['fabricantes:id,fabricante'])->with(['proveedores:id,proveedor'])->with(['areas:id,area'])->with(['gerencias:id,gerencia,area_id']) ;;
    }

    /*
    |--------------------------------------------------------------------------
    |  Relationship Search
    |--------------------------------------------------------------------------
    | Configure here relationships to be used by the Search and Table Filters.
    |
    */

    /**
     * Relationship search.
     *
     * @return array<string, array<int, string>>
     */
    public function relationSearch(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    |  Add Column
    |--------------------------------------------------------------------------
    | Make Datasource fields available to be used as columns.
    | You can pass a closure to transform/modify the data.
    |
    | ❗ IMPORTANT: When using closures, you must escape any value coming from
    |    the database using the `e()` Laravel Helper function.
    |
    */
    public function addColumns(): PowerGridColumns
    {
        return PowerGrid::columns()
        ->addColumn('id', function (InventarioImpresora $inventario_impresora){
            return $inventario_impresora->id;
        })
        ->addColumn('fecha_ingreso_formatted', fn ($inventario_impresora) => Carbon::parse($inventario_impresora->fecha_ingreso)->timezone('America/Caracas')->format('d-m-Y'))

            ->addColumn('colores.colore')


            ->addColumn('cartuchos.id', function (InventarioImpresora $inventario_impresora) {
                return str_pad($inventario_impresora->cartucho_id, 4, '0', STR_PAD_LEFT); // Formatea el ID a 4 dígitos
            })
            ->addColumn('cartuchos.cartucho')
            ->addColumn('impresoras.impresora')
            ->addColumn('impresoras.imagen', function (InventarioImpresora $inventario_impresora) {
                if ($inventario_impresora->impresoras && $inventario_impresora->impresoras->imagen) {
                    return '<img src="' . asset('storage/' . $inventario_impresora->impresoras->imagen) . '" alt="Imagen" class="w-16 h-16 object-cover">';
                }
                return 'Sin imagen';
            })
            ->addColumn('gerencias.gerencia')
            ->addColumn('proveedores.proveedor')
            ->addColumn('areas.area')
            ->addColumn('fabricantes.fabricante')
            ->addColumn('n_factura')
            ->addColumn('unidades', function (InventarioImpresora $inventario_impresora){
                return $inventario_impresora->unidades;
            })
            ->addColumn('unidades_disponibles', function (InventarioImpresora $inventario_impresora) {

                $cartuchoId = $inventario_impresora->cartucho_id;
                $coloreId = $inventario_impresora->colore_id;

                $results = DB::table('inventario_impresoras')
                  ->select(DB::raw('SUM(unidades_disponibles) AS total_unidades_disponible'))
                  ->where('cartucho_id', '=',  $cartuchoId )
                  ->where('colore_id', '=', $coloreId)
                  ->first();

                return $totalUnidadesDisponibles = $results ? $results->total_unidades_disponible : 0;

            });

    }

    /*
    |--------------------------------------------------------------------------
    |  Include Columns
    |--------------------------------------------------------------------------
    | Include the columns added columns, making them visible on the Table.
    | Each column can be configured with properties, filters, actions...
    |
    */

     /**
      * PowerGrid Columns.
      *
      * @return array<int, Column>
      */
    public function columns(): array
    {
        return [
            Column::make('Fecha de Ingreso', 'fecha_ingreso_formatted', 'inventario_oficinas.fecha_ingreso')->searchable()->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Item', 'cartuchos.id')->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Consumible', 'cartuchos.cartucho')->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Color', 'colores.colore')->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Impresora', 'impresoras.impresora')->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Imagen Impresora', 'impresoras.imagen')->visibleInExport(false),
            Column::make('Tipo de Consumible', 'fabricantes.fabricante')->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Proveedor', 'proveedores.proveedor')->visibleInExport(true)->bodyAttribute('text-center '),
            Column::make('Gerencia', 'gerencias.gerencia')->visibleInExport(true)->bodyAttribute('text-center '),
            Column::make('Area', 'areas.area')->visibleInExport(true)->bodyAttribute('text-center '),
            Column::make('Unidades disponibles por impresora', 'unidades', 'inventario_oficinas.unidades')->searchable()->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Unidades disponibles total', 'unidades_disponibles', 'inventario_oficinas.unidades_disponibles')
            ->searchable()->visibleInExport(true)->bodyAttribute('text-center',  'color:blue'),
            Column::make('Nº factura', 'n_factura', 'inventario_oficinas.n_factura')->searchable(),

        ];
    }

    /**
     * PowerGrid Filters.
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [
            Filter::datetimepicker('fecha_ingreso_formatted', 'fecha_ingreso')->params([
                'timezone' => 'America/Caracas',
            ]),

            Filter::multiSelect('cartuchos.id', 'cartucho_id')
        ->dataSource(Cartucho::all()->map(function ($cartucho) {
            return [
                'id' => str_pad($cartucho->id, 4, '0', STR_PAD_LEFT), // Formatea el ID
                'cartucho' => $cartucho->cartucho,
            ];
        }))
        ->optionValue('id')
        ->optionLabel('id'),

            Filter::multiSelect('cartuchos.cartucho', 'cartucho_id')
              ->dataSource(Cartucho::all())
              ->optionValue('id')
              ->optionLabel('cartucho'),

              Filter::multiSelect('colores.colore', 'colore_id')
              ->dataSource(Colore::all())
              ->optionValue('id')
              ->optionLabel('colore'),

              Filter::multiSelect('impresoras.impresora', 'impresora_id')
              ->dataSource(Impresora::all())
              ->optionValue('id')
              ->optionLabel('impresora'),

              Filter::multiSelect('fabricantes.fabricante', 'fabricante_id')
              ->dataSource(Fabricante::all())
              ->optionValue('id')
              ->optionLabel('fabricante'),
              Filter::multiSelect('proveedores.proveedor', 'proveedore_id')
              ->dataSource(Proveedore::all())
              ->optionValue('id')
              ->optionLabel('proveedor'),

              Filter::multiSelect('gerencias.gerencia', 'gerencia_id')
              ->dataSource(Gerencia::all())
              ->optionValue('id')
              ->optionLabel('gerencia'),

              Filter::multiSelect('areas.area', 'area_id')
              ->dataSource(Area::all())
              ->optionValue('id')
              ->optionLabel('area'),

            Filter::multiSelect('n_factura', 'n_factura')
            ->dataSource(InventarioImpresora::select('n_factura')->distinct()->get())
            ->optionValue('n_factura')
            ->optionLabel('n_factura'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Actions Method
    |--------------------------------------------------------------------------
    | Enable the method below only if the Routes below are defined in your app.
    |
    */

    /**
     * PowerGrid InventarioImpresora Action Buttons.
     *
     * @return array<int, Button>
     */
    public function actions(): array
    {
        $canClickButton = true;


return [

       Button::make('edit', '<svg xmlns="http://www.w3.org/2000/svg"  width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="m16 2.012 3 3L16.713 7.3l-3-3zM4 14v3h3l8.299-8.287-3-3zm0 6h16v2H4z"></path></svg>')
       ->class('w-fit  justify-center items-center  shadow-sm shadow-gray-500 px-2 py-2 font-semibold text-white bg-green-700 rounded-lg transition duration-150 ease-in-out  hover:text-white hover:bg-green-500 focus:outline-none text-sm flex')
       ->route('administracion.inventario_impresoras.edit', ['inventario_impresora'=> 'id'])->can($canClickButton),

       Button::make('destroy', '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg>')
       ->class('w-fit px-2 py-2  justify-center items-center shadow-sm shadow-gray-500 rounded-lg font-semibold text-center text-white bg-red-600  transition duration-150 ease-in-out  hover:text-white hover:bg-red-500 focus:outline-none text-sm flex')->route('administracion.inventario_impresoras.destroy', ['inventario_impresora' => 'id'])
       ->method('delete')];


           }
    /*
    public function actions(): array
    {
       return [
           Button::make('edit', 'Edit')
               ->class('bg-indigo-500 cursor-pointer text-white px-3 py-2.5 m-1 rounded text-sm')
               ->route('inventario-impresora.edit', function(\App\Models\InventarioImpresora $model) {
                    return $model->id;
               }),

           Button::make('destroy', 'Delete')
               ->class('bg-red-500 cursor-pointer text-white px-3 py-2 m-1 rounded text-sm')
               ->route('inventario-impresora.destroy', function(\App\Models\InventarioImpresora $model) {
                    return $model->id;
               })
               ->method('delete')
        ];
    }
    */

    /*
    |--------------------------------------------------------------------------
    | Actions Rules
    |--------------------------------------------------------------------------
    | Enable the method below to configure Rules for your Table and Action Buttons.
    |
    */

    /**
     * PowerGrid InventarioImpresora Action Rules.
     *
     * @return array<int, RuleActions>
     */

    /*
    public function actionRules(): array
    {
       return [

           //Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($inventario-impresora) => $inventario-impresora->id === 1)
                ->hide(),
        ];
    }
    */
}
