<?php

namespace App\Http\Livewire;

use App\Models\SalidaOficina;
use App\Models\InventarioOficina;
use App\Models\Area;
use App\Models\Gerencia;
use App\Models\Articulo;
use App\Models\UnidadMedida;use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Rules\{Rule, RuleActions};
use PowerComponents\LivewirePowerGrid\Traits\{ActionButton, WithExport};
use PowerComponents\LivewirePowerGrid\Filters\Filter;
use PowerComponents\LivewirePowerGrid\{Button, Column, Exportable, Footer, Header, PowerGrid, PowerGridComponent, PowerGridColumns};



final class SalidaOficinaTable extends PowerGridComponent
{
    use ActionButton;
    use WithExport;
    // Propiedad para rastrear si se está exportando
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

            Exportable::make('Egresos_oficina')
                ->striped()
                ->type(Exportable::TYPE_XLS)
                ->columnWidth([
                    1 => 20,
                    2 => 20,
                    3 => 10,
                    4 => 35,
                    5 => 20,
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

                ])

                ,
               /*  @endcan */


            Footer::make()
                ->showPerPage()
                ->showRecordCount(),

        ];
    }


    // Método para verificar si se está exportando
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
     * @return Builder<\App\Models\SalidaOficina>
     */
    public function datasource(): Builder
    {
        return SalidaOficina::query()
        //->with(['unidad_medidas:id,unidad_medida'])
        ->with('unidad_medidas')
        ->with(['areas:id,area'])
        //->with(['inventario_oficinas:id,unidades,fecha_ingreso,descripcion'])
        //->with(['articulos:id,articulo,stock_min'])
        ->with('inventario_oficinas')
        ->with('articulos')
        ->with('gerencias');
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
        ->addColumn('fecha_ingreso_formatted', fn ($egreso_oficina) => optional($egreso_oficina->inventario_oficinas)->fecha_ingreso ? Carbon::parse($egreso_oficina->inventario_oficinas->fecha_ingreso)->timezone('America/Caracas')->format('d-m-Y') : '')

        ->addColumn('fecha_salida_formatted', fn ($egreso_oficina) => Carbon::parse($egreso_oficina->fecha_salida)->timezone('America/Caracas')->format('d-m-Y'))

        ->addColumn('unidad_medidas.unidad_medida')
        //->addColumn('unidad_name', fn ($egreso_oficina) => e($egreso_oficina->unidad_medidas->unidad_medida))
        ->addColumn('articulos.id', function (SalidaOficina $egreso_oficina) {
            return str_pad($egreso_oficina->articulo_id, 4, '0', STR_PAD_LEFT); // Formatea el ID a 4 dígitos
        })
        ->addColumn('articulos.articulo')
        /* ->addColumn('articulo_name', fn ($egreso_oficina) => e($egreso_oficina->articulos->articulo)) */

       /*  ->addColumn('articulos.stock_min', function (SalidaOficina $egreso_oficina) {
            // Verifica si el artículo existe
            if ($egreso_oficina->articulos) {
                $style = 'text-align: center;';
                $value = $egreso_oficina->articulos->stock_min; // Obtiene el valor del stock_min

                // Compara el stock mínimo con las unidades ingresadas
                if ($value == $egreso_oficina->historico_unidades_disponibles) {
                    // Si son iguales, añade el estilo adicional
                    $style .= '; background-color: red; color: white; text-align: center; padding: 20px;';
                }

                // Retorna el valor envuelto en un div con el estilo
                return "<div style='{$style}'>{$value}</div>";
            }
            return "<div style='text-align: center;'>N/A</div>"; // Retorna un valor por defecto si no hay artículo
        }) */
        /* ->addColumn('articulos.stock_min', function (SalidaOficina $egreso_oficina) {
            // Verifica si el artículo existe
            if ($egreso_oficina->articulos) {
                $value = $egreso_oficina->articulos->stock_min; // Obtiene el valor del stock_min

                // Compara el stock mínimo con las unidades ingresadas
                if ($value == $egreso_oficina->historico_unidades_disponibles) {
                    // Si son iguales, devuelve el valor con un estilo en línea o clase de Tailwind
                    //$estilo = "<span style='background-color: red; color: white; text-align: center; padding: 20px;'>{$value}</span>";
                    return "<span style='background-color: red; color: white; text-align: center; padding: 20px;'>{$value}</span>"; // Usando CSS en línea
                    // o
                    // return "<span class='text-red-500'>{$value}</span>"; // Usando Tailwind CSS
                }

                return $value; // Solo devuelve el valor si no hay alerta
            }
            return "N/A"; // Retorna un valor por defecto si no hay artículo
        }) */
        ->addColumn('articulos.stock_min', function (SalidaOficina $egreso_oficina) {
            // Verifica si el artículo existe
            if ($egreso_oficina->articulos) {
                $value = $egreso_oficina->articulos->stock_min; // Obtiene el valor del stock_min

                // Compara el stock mínimo con las unidades ingresadas
                if ($egreso_oficina->historico_unidades_disponibles <= $value){
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


        /* ->addColumn('stock_min', fn ($egreso_oficina) => e($egreso_oficina->articulos->stock_min)) */
        ->addColumn('gerencia_name', fn ($egreso_oficina) => e($egreso_oficina->gerencias->gerencia))
        ->addColumn('area_name', fn ($egreso_oficina) => e($egreso_oficina->areas->area))
        //->addColumn('descripcion', fn ($egreso_oficina) => e($egreso_oficina->inventario_oficinas->descripcion))
        ->addColumn('cantidad')
        ->addColumn('id')
        ->addColumn('historico_unidades_disponibles')
        ->addColumn('totalDisponible', function(SalidaOficina $egreso_oficina){
            return $egreso_oficina->totalDisponible;
        })
        ->addColumn('ajuste_inventario')
        ;
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
            //Column::make('id', 'id'),
            Column::make('Fecha de Ingreso', 'fecha_ingreso_formatted')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),


            Column::make('Fecha de Salida', 'fecha_salida_formatted', 'fecha_salida')->searchable()->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Item', 'articulos.id')->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Artículo', 'articulos.articulo' )->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Unidad de Medida', 'unidad_medidas.unidad_medida' )->visibleInExport(true)->bodyAttribute('text-center'),


            Column::make('Gerencia', 'gerencia_name' )->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Área', 'area_name' )->visibleInExport(true)->bodyAttribute('text-center'),


            Column::make('Stock Min', 'articulos.stock_min')->visibleInExport(true)->bodyAttribute('text-center'),



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

            Filter::multiSelect('fecha_ingreso_formatted', 'inventario_oficina_id')
            ->dataSource(InventarioOficina::all())
            ->optionValue('id')
            ->optionLabel('fecha_ingreso'),


            Filter::datetimepicker('fecha_salida')->params([
                'timezone' => 'America/Caracas',
            ]),

            Filter::multiSelect('inventario_oficinas.descripcion', 'inventario_oficina_id')
            ->dataSource(InventarioOficina::all())
            ->optionValue('id')
            ->optionLabel('descripcion'),

            Filter::multiSelect('gerencia_name', 'gerencia_id')
            ->dataSource(Gerencia::all())
            ->optionValue('id')
            ->optionLabel('gerencia'),

            Filter::multiSelect('area_name', 'area_id')
            ->dataSource(Area::all())
            ->optionValue('id')
            ->optionLabel('area'),

            // Filtro para el ID del artículo
        Filter::multiSelect('articulos.id', 'articulo_id')
        ->dataSource(Articulo::all()->map(function ($articulo) {
            return [
                'id' => str_pad($articulo->id, 4, '0', STR_PAD_LEFT), // Formatea el ID
                'articulo' => $articulo->articulo,
            ];
        }))
        ->optionValue('id')
        ->optionLabel('id'),

        Filter::multiSelect('articulos.articulo', 'articulo_id')
        ->dataSource(Articulo::all())
        ->optionValue('id')
        ->optionLabel('articulo'),

            Filter::multiSelect('unidad_medidas.unidad_medida', 'unidad_medida_id')
            ->dataSource(UnidadMedida::all())
            ->optionValue('id')
            ->optionLabel('unidad_medida'),

            Filter::multiSelect('ajuste_inventario', 'ajuste_inventario')
                ->dataSource(SalidaOficina::select('ajuste_inventario')->distinct()->get())
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
     * PowerGrid SalidaOficina Action Buttons.
     *
     * @return array<int, Button>
     */

    /*

    */

    /*
    |--------------------------------------------------------------------------
    | Actions Rules
    |--------------------------------------------------------------------------
    | Enable the method below to configure Rules for your Table and Action Buttons.
    |
    */

    /**
     * PowerGrid SalidaOficina Action Rules.
     *
     * @return array<int, RuleActions>
     */

    /*

    }
    */
}
