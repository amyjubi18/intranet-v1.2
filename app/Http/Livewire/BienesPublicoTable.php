<?php

namespace App\Http\Livewire;
use App\Models\User;
use App\Models\Biene;
use App\Models\UsuarioAdministrativo;
use App\Models\EstadoDelUso;
use App\Models\FormaAdquisicion;
use App\Models\UnidadAdministrativa;
use App\Models\UsuarioDeUso;
use App\Models\Color;
use App\Models\Condicion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Rules\{Rule, RuleActions};
use PowerComponents\LivewirePowerGrid\Traits\{ActionButton, WithExport};
use PowerComponents\LivewirePowerGrid\Filters\Filter;
use PowerComponents\LivewirePowerGrid\{Button, Column, Exportable, Footer, Header, PowerGrid, PowerGridComponent, PowerGridEloquent};

final class BienesPublicoTable extends PowerGridComponent
{
    use ActionButton;
    use WithExport;

    public string $sortField = 'fecha_adquisicion';

    public string $sortDirection = 'asc';

    /*
    |--------------------------------------------------------------------------
    |  Features Setup
    |--------------------------------------------------------------------------
    | Setup Table's general features
    |
    */
    public function setUp(): array
    {
        abort_if(Gate::denies('bienes_index'), 403);
        $this->showCheckBox('id');
/*         $this->persist(['columns', '']);
 */
        return [
            Header::make(),
            Exportable::make('bienes_muebles')
                ->striped()
                ->type(Exportable::TYPE_XLS)
                ->columnWidth([
                    2 => 25,
                    3 => 25,
                    4 => 25,
                    5 => 25,
                    6 => 20,
                    7 => 20,
                    8 => 20,
                    9 => 20,
                    10 => 20,
                    11 => 20,
                    12 => 20,
                    13 => 20,
                    14 => 20,
                    15 => 20,
                    16 => 20,
                    17 => 25,
                    18 => 25,
                    19 => 25,


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
     * @return Builder<\App\Models\Biene>
     */
    public function datasource(): ?Builder
    {

        return Biene::query()->with(['usuario_administrativos:id,usuario_administrativo'])->with(['forma_adquisicions:id,forma_adquisicion'])->with(['estado_del_usos:id,estado_del_uso']) ->with(['usuario_de_usos:id,usuario_de_uso,unidad_administrativa_id'])->with(['colors:id,color'])->with(['condicions:id,condicion'])->with(['unidad_administrativas:id,name']);
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
     public function addColumns(): PowerGridEloquent
    {
        abort_if(Gate::denies('bienes_index'), 403);

        return PowerGrid::eloquent()

        ->addColumn('id', function (Biene $biene){
            return $biene->id;
        })


        ->addColumn('sede', function (Biene $biene){
            return $biene->sede;
        })
      ->addColumn('usuario_administrativos.usuario_administrativo')
        ->addColumn('n_interno_bien', function (Biene $biene){
            return $biene->n_interno_bien;
        })
        ->addColumn('usuario_de_usos.usuario_de_uso')
        ->addColumn('unidad_administrativas.name')
        ->addColumn('descripcion', function (Biene $biene){
            return $biene->descripcion;
        })
        ->addColumn('forma_adquisicions.forma_adquisicion')

          ->addColumn('fecha_adquisicion_formatted', fn ($biene) => Carbon::parse($biene->fecha_adquisicion)->timezone('America/Caracas')->format('d-m-Y'))


        ->addColumn('n_factura', function (Biene $biene){
            return $biene->n_factura;
        })
        ->addColumn('valor_factura', function (Biene $biene){
            return $biene->valor_factura;
        })
        ->addColumn('moneda', function (Biene $biene){
            return $biene->moneda;
        })
        ->addColumn('estado_del_usos.estado_del_uso')
        ->addColumn('condicions.condicion')
        ->addColumn('colors.color')
        ->addColumn('marca', function (Biene $biene){
            return $biene->marca;
        })
        ->addColumn('modelo', function (Biene $biene){
            return $biene->modelo;
        })
        ->addColumn('serial', function (Biene $biene){
            return $biene->serial;
        })
        ->addColumn('comentario', function (Biene $biene){
            return $biene->comentario;
        })

          ->addColumn('fecha_comentario_formatted', fn ($biene) => Carbon::parse($biene->fecha_comentario)->timezone('America/Caracas')->format('d-m-Y'))


        ->addColumn('codigo_contable', function (Biene $biene){
            return $biene->codigo_contable;
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
         abort_if(Gate::denies('bienes_index'), 403);
        return [
         /* Column::make('Id', 'id', 'bienes.id')->sortable()->visibleInExport(true)->bodyAttribute('text-center'), */
         Column::make('Sede', 'sede', 'bienes.sede')->searchable()->visibleInExport(true)->bodyAttribute('text-center'),
         Column::make('Unidad Administrativa', 'unidad_administrativas.name')->searchable()->visibleInExport(true)->bodyAttribute('text-center'),




        Column::make('Usuario Administrativo', 'usuario_administrativos.usuario_administrativo')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),

                Column::make('Usuario de Uso', 'usuario_de_usos.usuario_de_uso')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),



            Column::make('Código interno del bien', 'n_interno_bien', 'bienes.n_interno_bien')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Descripción', 'descripcion', 'bienes.descripcion')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),
            Column::make('Forma adquisición', 'forma_adquisicions.forma_adquisicion')->visibleInExport(true)->bodyAttribute('text-center font-bold', 'background-color:#b3d9ff'),


            Column::make('Fecha adquisición', 'fecha_adquisicion_formatted', 'bienes.fecha_adquisicion')->searchable()->visibleInExport(true)->bodyAttribute('text-center'),


               /*  Column::make('Fecha adquisición', 'bienes.fecha_adquisicion')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'), */



            Column::make('N° de Factura', 'n_factura', 'bienes.n_factura')
               ->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Valor factura', 'valor_factura', 'bienes.valor_factura')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Moneda', 'moneda', 'bienes.moneda')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center font-bold' , 'background-color:#b3d9ff'),

            Column::make('Estado del Uso', 'estado_del_usos.estado_del_uso', )->visibleInExport(true)->bodyAttribute('text-center font-bold' , 'background-color:#b3d9ff'),

            Column::make('Condición física', 'condicions.condicion', )->visibleInExport(true)->bodyAttribute('text-center font-bold' , 'background-color:#b3d9ff'),



            Column::make('Marca', 'marca', 'bienes.marca')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Modelo', 'modelo', 'bienes.modelo')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),




            /* Column::make('Categoría General', 'categoria_generals.categoria_general')->visibleInExport(true)->bodyAttribute('text-center font-bold' , 'background-color:#b3d9ff'),
            Column::make('Subcategoría', 'sub_categorias.sub_categoria')->visibleInExport(true)->bodyAttribute('text-center font-bold' , 'background-color:#b3d9ff'),
            Column::make('Categoría Específica', 'categoria_especificas.categoria_especifica')->visibleInExport(true)->bodyAttribute('text-center font-bold' , 'background-color:#b3d9ff'), */
            Column::make('Color', 'colors.color', )->visibleInExport(true)->bodyAttribute('text-center font-bold' , 'background-color:#b3d9ff'),
                Column::make('Serial', 'serial', 'bienes.serial')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),
                Column::make('Comentarios', 'comentario', 'bienes.comentario')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),
                Column::make('Fecha Comentario', 'fecha_comentario_formatted', 'bienes.fecha_comentario')->searchable()->visibleInExport(true)->bodyAttribute('text-center'),



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
            Filter::multiSelect('sede', 'sede')
            ->dataSource(Biene::select('sede')->distinct()->get())
            ->optionValue('sede')
            ->optionLabel('sede'),

            Filter::multiSelect('n_factura', 'n_factura')
            ->dataSource(Biene::select('n_factura')->distinct()->get())
            ->optionValue('n_factura')
            ->optionLabel('n_factura'),

            Filter::multiSelect('usuario_administrativos.usuario_administrativo', 'usuario_administrativo_id')
            ->dataSource(UsuarioAdministrativo::all())
            ->optionValue('id')
            ->optionLabel('usuario_administrativo'),


            Filter::multiSelect('unidad_administrativas.name', 'unidad_administrativa_id')
            ->dataSource(UnidadAdministrativa::all())
            ->optionValue('id')
            ->optionLabel('name'),

            Filter::multiSelect('usuario_de_usos.usuario_de_uso', 'usuario_de_uso_id')
            ->dataSource(UsuarioDeUso::all())
            ->optionValue('id')
            ->optionLabel('usuario_de_uso'),

            Filter::multiSelect('n_interno_bien', 'n_interno_bien')
            ->dataSource(Biene::select('n_interno_bien')->distinct()->get())
            ->optionValue('n_interno_bien')
            ->optionLabel('n_interno_bien'),

            Filter::multiSelect('descripcion', 'id')
            ->dataSource(Biene::all())
            ->optionValue('id')
            ->optionLabel('descripcion'),

            Filter::multiSelect('forma_adquisicions.forma_adquisicion', 'forma_adquisicion_id')
            ->dataSource(FormaAdquisicion::all())
            ->optionValue('id')
            ->optionLabel('forma_adquisicion'),


            // Filter::datepicker('fecha_adquisicion_formatted', 'fecha_adquisicion'),
            Filter::datetimepicker('fecha_adquisicion_formatted', 'fecha_adquisicion')->params([
                'timezone' => 'America/Caracas',
            ]),

              /* Filter::multiSelect('fecha_adquisicion_formatted', 'id')
            ->dataSource(Biene::all())
            ->optionValue('id')
            ->optionLabel('fecha_adquisicion'), */

            Filter::multiSelect('valor_factura', 'valor_factura')
            ->dataSource(Biene::select('valor_factura')->distinct()->get())
            ->optionValue('valor_factura')
            ->optionLabel('valor_factura'),

            Filter::multiSelect('estado_del_usos.estado_del_uso', 'estado_del_uso_id')
            ->dataSource(EstadoDelUso::all())
            ->optionValue('id')
            ->optionLabel('estado_del_uso'),

            Filter::multiSelect('condicions.condicion', 'condicion_id')
            ->dataSource(Condicion::all())
            ->optionValue('id')
            ->optionLabel('condicion'),

            Filter::multiSelect('colors.color', 'color_id')
            ->dataSource(Color::all())
            ->optionValue('id')
            ->optionLabel('color'),

            Filter::multiSelect('marca', 'marca')
            ->dataSource(Biene::select('marca')->distinct()->get())
            ->optionValue('marca')
            ->optionLabel('marca'),

            Filter::multiSelect('modelo', 'id')
            ->dataSource(Biene::all())
            ->optionValue('id')
            ->optionLabel('modelo'),

            Filter::multiSelect('serial', 'id')
            ->dataSource(Biene::all())
            ->optionValue('id')
            ->optionLabel('serial'),

            Filter::multiSelect('comentario', 'comentario')
            ->dataSource(Biene::select('comentario')->distinct()->get())
            ->optionValue('comentario')
            ->optionLabel('comentario'),

            Filter::datetimepicker('fecha_comentario_formatted', 'fecha_comentario')->params([
                'timezone' => 'America/Caracas',
            ]),

            Filter::multiSelect('codigo_contable', 'id')
            ->dataSource(Biene::all())
            ->optionValue('id')
            ->optionLabel('codigo_contable'),




        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Actions Method
    |--------------------------------------------------------------------------
    | Enable the method below only if the Routes below are defined in your app.
    |
    */
    protected function getListeners()
    {
        return array_merge(
            parent::getListeners(),
            [
                'rowActionEvent',
            ]);
    }

    public function rowActionEvent(array $data): void
    {
        $message = '¿Esta seguro que desea eliminar?' . $data['id'];

        $this->dispatchBrowserEvent('showAlert', ['message' => $message]);
    }
    /**
     * PowerGrid Biene Action Buttons.
     *
     * @return array<int, Button>
     */

     public function actions(): array
     {
         $canClickButton = true;


        return [
            Button::make('edit', '<svg xmlns="http://www.w3.org/2000/svg"  width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="m16 2.012 3 3L16.713 7.3l-3-3zM4 14v3h3l8.299-8.287-3-3zm0 6h16v2H4z"></path></svg>')
            ->class('w-fit mx-20 items-center justify-around shadow-md px-2 py-2 font-semibold text-white bg-green-700 rounded-lg transition duration-150 ease-in-out  hover:text-white hover:bg-green-500 focus:outline-none text-sm flex shadow-sm shadow-gray-500')->route('administracion.bienes_publicos.edit', ['biene'=> 'id'])->can($canClickButton),

            Button::make('destroy', '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg>')
            ->class('w-fit px-2 py-2 mt-3 items-center shadow-md rounded-lg font-semibold text-center text-white bg-red-600  transition duration-150 ease-in-out  hover:text-white hover:bg-red-500 focus:outline-none text-sm shadow-sm shadow-gray-500')->route('administracion.bienes_publicos.destroy', ['biene' => 'id'])->method('delete'),

            /* Button::make('export_pdf', 'Exportar PDF')->method('exportPDF'), */
            ];

            }
  /*
    |--------------------------------------------------------------------------xz
    | Actions Rules
    |--------------------------------------------------------------------------
    | Enable the method below to configure Rules for your Table and Action Buttons.
    |
    */

    /**
     * PowerGrid Biene Action Rules.
     *
     * @return array<int, RuleActions>
     */

    /*
    public function actionRules(): array
    {
       return [

           //Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($biene) => $biene->id === 1)
                ->hide(),
        ];
    }
    */


}
