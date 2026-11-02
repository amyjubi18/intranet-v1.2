<?php

namespace App\Http\Livewire;
use App\Models\User;
use App\Models\Biene;
use App\Models\UnidadAdministrativa;
use App\Models\EstadoDelUso;
use App\Models\FormaAdquisicion;
use App\Models\CategoriaEspecifica;
use App\Models\CategoriaGeneral;
use App\Models\SubCategoria;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Rules\{Rule, RuleActions};
use PowerComponents\LivewirePowerGrid\Traits\{ActionButton, WithExport};
use PowerComponents\LivewirePowerGrid\Filters\Filter;
use PowerComponents\LivewirePowerGrid\{Button, Column, Exportable, Footer, Header, PowerGrid, PowerGridComponent, PowerGridEloquent};

final class RelacionBienesTable extends PowerGridComponent
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
    public function setUp(): array
    {
          abort_if(Gate::denies('bienes_index'), 403);

        $this->showCheckBox();
        $this->persist(['columns', 'filters']);

        return [
            Exportable::make('relacion_de_bienes')
                ->striped()
                ->type(Exportable::TYPE_XLS)/* ->queues(6)->onQueue('bienes')->onConnection('redis') */->columnWidth([
                    2 => 20,
                    3 => 30,
                    4 => 20,
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

                ]) ,

            Header::make()->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
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

        return Biene::query()->with(['unidad_administrativas:id,unidad_administrativa'])->with(['forma_adquisicions:id,forma_adquisicion'])->with(['estado_del_usos:id,estado_del_uso'])->with(['categoria_generals:id,categoria_general'])->with(['categoria_especificas:id,categoria_especifica'])->with(['users:id,name']);
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
        /* ->addColumn('user_id', function (Biene $biene){
            return $biene->user_id;
        }) */
         ->addColumn('users.name')

      ->addColumn('unidad_administrativas.unidad_administrativa')
        ->addColumn('n_interno_bien', function (Biene $biene){
            return $biene->n_interno_bien;
        })
        ->addColumn('descripcion', function (Biene $biene){
            return $biene->descripcion;
        })


        ->addColumn('marca', function (Biene $biene){
            return $biene->marca;
        })
        ->addColumn('modelo', function (Biene $biene){
            return $biene->modelo;
        })
        ->addColumn('serial', function (Biene $biene){
            return $biene->serial;
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
            Column::make('Usuario', 'users.name')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),

                Column::make('Unidad Administrativa', 'unidad_administrativas.unidad_administrativa')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),
                 Column::make('N° interno del bien', 'n_interno_bien', 'bienes.n_interno_bien')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Descripción', 'descripcion', 'bienes.descripcion')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),


            Column::make('Marca', 'marca', 'bienes.marca')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Modelo', 'modelo', 'bienes.modelo')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),

            Column::make('Serial', 'serial', 'bienes.serial')
                ->searchable()->visibleInExport(true)->bodyAttribute('text-center'),


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
             Filter::multiSelect('users.name', 'user_id')
            ->dataSource(User::all())
            ->optionValue('id')
            ->optionLabel('name') ,


            Filter::inputText('n_interno_bien')->operators(['contains']),
            Filter::select('n_interno_bien', 'n_interno_bien')
            ->dataSource(Biene::select('n_interno_bien')->distinct()->get())
            ->optionValue('n_interno_bien')
            ->optionLabel('n_interno_bien'),

            Filter::select('descripcion', 'descripcion')
            ->dataSource(Biene::select('descripcion')->distinct()->get())
            ->optionValue('descripcion')
            ->optionLabel('descripcion'),

            Filter::select('marca', 'marca')
            ->dataSource(Biene::select('marca')->distinct()->get())
            ->optionValue('marca')
            ->optionLabel('marca'),

            Filter::select('modelo', 'modelo')
            ->dataSource(Biene::select('modelo')->distinct()->get())
            ->optionValue('modelo')
            ->optionLabel('modelo'),

            Filter::select('serial', 'serial')
            ->dataSource(Biene::select('serial')->distinct()->get())
            ->optionValue('serial')
            ->optionLabel('serial'),
            Filter::inputText('serial')->operators(['contains']),

            Filter::select('unidad_administrativas.unidad_administrativa', 'unidad_administrativa_id')
            ->dataSource(UnidadAdministrativa::all())
            ->optionValue('id')
            ->optionLabel('unidad_administrativa')];


    }

    /*
    |--------------------------------------------------------------------------
    | Actions Method
    |--------------------------------------------------------------------------
    | Enable the method below only if the Routes below are defined in your app.
    |
    */

    /**
     * PowerGrid Biene Action Buttons.
     *
     * @return array<int, Button>
     */

     /* public function actions(): array
     {
         $canClickButton = true;

        return [
            Button::make('edit', 'Editar')
                ->class('w-fit  items-center justify-around px-2 py-2 font-semibold text-white bg-green-700 rounded-lg transition duration-150 ease-in-out  hover:text-white hover:bg-green-500 focus:outline-none text-sm')
  ->route('administracion.bienes_publicos.edit', ['biene'=> 'id'])->can($canClickButton),

  Button::make('destroy', 'Eliminar')
                ->class(' px-2 py-2 mt-3 items-center  rounded-lg font-semibold text-center text-white bg-red-600  transition duration-150 ease-in-out  hover:text-white hover:bg-red-500 focus:outline-none text-sm')->route('administracion.bienes_publicos.destroy', ['biene' => 'id'])
                ->method('delete')];
            } */
  /*
    |--------------------------------------------------------------------------
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
