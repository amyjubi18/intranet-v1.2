<?php

namespace App\Http\Livewire;

use App\Models\SalidaImpresora;
use App\Models\Cartucho;
use App\Models\Colore;
use App\Models\Impresora;
use App\Models\Fabricante;
use App\Models\Area;
use App\Models\Gerencia;
use App\Models\Fabricantes;
use App\Models\InventarioImpresora;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Rules\{Rule, RuleActions};
use PowerComponents\LivewirePowerGrid\Traits\{ActionButton, WithExport};
use PowerComponents\LivewirePowerGrid\Filters\Filter;
use PowerComponents\LivewirePowerGrid\{Button, Column, Exportable, Footer, Header, PowerGrid, PowerGridComponent, PowerGridColumns};

final class SalidaImpresoraTable extends PowerGridComponent
{
    use ActionButton;
    use WithExport;

    protected $isExporting = false;

    /*
    |--------------------------------------------------------------------------
    |  Features Setup
    |--------------------------------------------------------------------------
    | Setup Table's general features
    |
    */
    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            Header::make()/* ->showSearchInput() */,
            /* @can('exportar_excel') */

            Exportable::make('Salida_toners')
                ->striped()
                ->type(Exportable::TYPE_XLS)->columnWidth([
                    1 => 20,
                    2 => 20,
                    3 => 10,
                    4 => 30,
                    5 => 10,
                    6 => 30,
                    7 => 25,
                    8 => 25,
                    9 => 25,
                    10 => 25,
                    11 => 25,
                    12 => 25,
                    13 => 25,
                    14 => 25,
                    15 => 20,
                    16 => 20,
                    17 => 25,
                    18 => 25,

                ]) ,
               /*  @endcan */


            Footer::make()
                ->showPerPage()
                ->showRecordCount( )
        ];
    }


    public function isExporting(): bool
    {
        return $this->isExporting;
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
     * @return Builder<\App\Models\SalidaImpresora>
     */
    public function datasource(): Builder
    {
        return SalidaImpresora::query()->with('areas')->with('gerencias')->with('cartuchos')->with('colores')->with('impresoras')->with('inventario_impresoras')->with('fabricantes');
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

        ->addColumn('fecha_ingreso_formatted', fn ($salida_impresora) => optional($salida_impresora->inventario_impresoras)->fecha_ingreso ? Carbon::parse($salida_impresora->inventario_impresoras->fecha_ingreso)->timezone('America/Caracas')->format('d-m-Y') : '')

        ->addColumn('fecha_salida_formatted', fn ($salida_impresora) => Carbon::parse($salida_impresora->fecha_salida)->timezone('America/Caracas')->format('d-m-Y'))


        ->addColumn('cartuchos.id', function (SalidaImpresora $salida_impresora) {
            return str_pad($salida_impresora->cartucho_id, 4, '0', STR_PAD_LEFT); // Formatea el ID a 4 dígitos
        })
        ->addColumn('cartucho_name', fn($salida_impresora) => e($salida_impresora->cartuchos?->cartucho ?? 'N/A'))
        ->addColumn('color_name', fn($salida_impresora) => e($salida_impresora->colores?->colore ?? 'N/A'))
        ->addColumn('impresora_name', fn($salida_impresora) => e($salida_impresora->impresoras?->impresora ?? 'N/A'))

        ->addColumn('fabricante_name', fn($salida_impresora) => e($salida_impresora->fabricantes?->fabricante ?? 'N/A'))

        ->addColumn('cartuchos.stock_min', function (SalidaImpresora $salida_impresora) {
            // Verifica si el artículo existe
            if ($salida_impresora->cartuchos) {
                $value = $salida_impresora->cartuchos->stock_min; // Obtiene el valor del stock_min

                // Compara el stock mínimo con las unidades ingresadas
                if ($salida_impresora->historico_unidades_disponibles <= $value){
                    // Si son iguales, devuelve el valor con un estilo en línea solo para la vista
                    if (!$this->isExporting()) { // Verifica si no se está exportando
                        return "<span style='background-color: red; color: white; text-align: center; padding: 15px; padding-left: 20px;     padding-right: 20px;'>{$value}</span>";
                    }
                }
                // Devuelve solo el valor para la exportación
                return $value; // Solo devuelve el valor si no hay alerta o si se está exportando
            }
            return "N/A"; // Retorna un valor por defecto si no hay artículo
        })
        ->addColumn('gerencia_name', fn($salida_impresora) => e($salida_impresora->gerencias?->gerencia ?? 'N/A'))
        ->addColumn('area_name', fn($salida_impresora) => e ($salida_impresora->areas?->area ?? 'N/A'))
            ->addColumn('cantidad')
            ->addColumn('historico_unidades_disponibles')
            ->addColumn('totalDisponible', function(SalidaImpresora $salida_impresora){
                return $salida_impresora->totalDisponible;
            })
            ->addColumn('ajuste_inventario');
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



            Column::make('Fecha de Ingreso', 'fecha_ingreso_formatted')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Fecha salida', 'fecha_salida_formatted', 'fecha_salida')->searchable()->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Item', 'cartuchos.id')->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Impresora', 'impresora_name')->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Consumible', 'cartucho_name')->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Tipo Consumible', 'fabricante_name')->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Color', 'color_name')->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Gerencia', 'gerencia_name' )->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Área', 'area_name' )->visibleInExport(true)->bodyAttribute('text-center'),


            Column::make('Stock Min', 'cartuchos.stock_min')->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Unidades Ingresadas', 'historico_unidades_disponibles')->visibleInExport(true)
            ->bodyAttribute('text-center', 'color:blue'),

            Column::make('Cantidad Solicitada', 'cantidad')
            ->visibleInExport(true)
            ->bodyAttribute('text-center', 'color:red'),

            Column::make('Unidades Disponibles', 'totalDisponible')
            ->bodyAttribute('text-center', 'color:green')->visibleInExport(true),

            Column::make('Ajuste inventario', 'ajuste_inventario')
                ->sortable()
                ->searchable()->visibleInExport(true),

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

            Filter::multiSelect('fecha_ingreso_formatted', 'inventario_impresora_id')
            ->dataSource(InventarioImpresora::all())
            ->optionValue('id')
            ->optionLabel('fecha_ingreso'),

            Filter::datetimepicker('fecha_salida')->params([
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

            Filter::multiSelect('fabricante_name', 'fabricante_id')
            ->dataSource(Fabricante::all())
            ->optionValue('id')
            ->optionLabel('fabricante'),

            Filter::multiSelect('cartucho_name', 'cartucho_id')
            ->dataSource(Cartucho::all())
            ->optionValue('id')
            ->optionLabel('cartucho'),

            Filter::multiSelect('color_name', 'colore_id')
            ->dataSource(Colore::all())
            ->optionValue('id')
            ->optionLabel('colore'),

            Filter::multiSelect('impresora_name', 'impresora_id')
            ->dataSource(Impresora::all())
            ->optionValue('id')
            ->optionLabel('impresora'),

            Filter::multiSelect('gerencia_name', 'gerencia_id')
            ->dataSource(Gerencia::all())
            ->optionValue('id')
            ->optionLabel('gerencia'),

            Filter::multiSelect('area_name', 'area_id')
            ->dataSource(Area::all())
            ->optionValue('id')
            ->optionLabel('area'),

            Filter::multiSelect('ajuste_inventario', 'ajuste_inventario')
                ->dataSource(SalidaImpresora::select('ajuste_inventario')->distinct()->get())
                ->optionLabel('ajuste_inventario')
                ->optionValue('ajuste_inventario'),


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
     * PowerGrid SalidaImpresora Action Buttons.
     *
     * @return array<int, Button>
     */

    /*
    public function actions(): array
    {
       return [
           Button::make('edit', 'Edit')
               ->class('bg-indigo-500 cursor-pointer text-white px-3 py-2.5 m-1 rounded text-sm')
               ->route('salida-impresora.edit', function(\App\Models\SalidaImpresora $model) {
                    return $model->id;
               }),

           Button::make('destroy', 'Delete')
               ->class('bg-red-500 cursor-pointer text-white px-3 py-2 m-1 rounded text-sm')
               ->route('salida-impresora.destroy', function(\App\Models\SalidaImpresora $model) {
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
     * PowerGrid SalidaImpresora Action Rules.
     *
     * @return array<int, RuleActions>
     */

    /*
    public function actionRules(): array
    {
       return [

           //Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($salida-impresora) => $salida-impresora->id === 1)
                ->hide(),
        ];
    }
    */
}
