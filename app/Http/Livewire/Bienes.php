<?php

namespace App\Http\Livewire;
use App\Models\Biene;
use App\Models\CategoriaEspecifica;
use App\Models\CategoriaGeneral;
use App\Models\EstadoDelUso;
use App\Models\FormaAdquisicion;
use App\Models\SubCategoria;
use App\Models\UnidadAdministrativa;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Filters\Filter\Facades;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Rules\{Rule, RuleActions};
use PowerComponents\LivewirePowerGrid\Traits\{ActionButton, WithExport};
use PowerComponents\LivewirePowerGrid\Filters\Filter;
use PowerComponents\LivewirePowerGrid\{Button, Column, Exportable, Footer, Header, PowerGrid, PowerGridComponent, PowerGridEloquent, FilterMakeCommand, MultiSelect};

final class Bienes extends PowerGridComponent
{
     use WithExport;
/*     public string $sortField = 'bienes.id';
 */   /*  protected function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                'bulkDelete-'.$this->tableName => 'bulkDelete',
            ]
        );
    }

    public function bulkDelete(): void
    {
        $this->emit('openModal', 'delete-biene', [
            'BieneIds' => $this->checkboxValues,
            'confirmationTitle' => 'Eliminar',
            'confirmationDescription' => '¿Esta seguro de eliminar esta fila?',
        ]);
    } */
    /*
    |--------------------------------------------------------------------------
    |  Features Setup
    |--------------------------------------------------------------------------
    | Setup Table's general features
    |
    */
     /* public bool $multiSort = true;
    public int $perPage = 100;
    public array $perPageValues = [0, 5, 10, 20, 50]; */
    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            Exportable::make('export')
            ->csvSeparator('|')
            ->csvDelimiter("'")
                ->striped('#000000')
                ->columnWidth([
                    2 => 30,
                    4 => 20,
                ])
                ->type(Exportable::TYPE_XLS)/* ->queues(6)->onQueue('bienes')->onConnection('redis') */,
            Header::make()->showSearchInput()->showToggleColumns(),
            Footer::make()
                /* ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(), */
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
        return Biene::query()
        ->join('unidad_administrativas', function ($unidad_administrativas) {
            $unidad_administrativas->on('bienes.unidad_administrativa_id', '=', 'unidad_administrativas.id');
        })
        ->join('forma_adquisicions', function ($forma_adquisicions) {
            $forma_adquisicions->on('bienes.forma_adquisicion_id', '=', 'forma_adquisicions.id');
        })
        ->join('estado_del_usos', function ($estado_del_usos) {
            $estado_del_usos->on('bienes.estado_del_uso_id', '=', 'estado_del_usos.id');
        })
        ->join('categoria_generals', function ($categoria_generals) {
            $categoria_generals->on('bienes.categoria_general_id', '=', 'categoria_generals.id');
        })
        ->join('sub_categorias', function ($sub_categorias) {
            $sub_categorias->on('bienes.sub_categoria_id', '=', 'sub_categorias.id');
        })
        ->join('categoria_especificas', function ($categoria_especificas) {
            $categoria_especificas->on('bienes.categoria_especifica_id', '=', 'categoria_especificas.id');
        })
        ->select([
            'bienes.id',
            'bienes.sede',
            'unidad_administrativas.unidad_administrativa as unidad',
            'bienes.n_interno_bien',
            'bienes.descripcion',
            'forma_adquisicions.forma_adquisicion as forma',
            'bienes.fecha_adquisicion',
            'bienes.n_factura',
            'bienes.valor_factura',
            'bienes.moneda',
            'estado_del_usos.estado_del_uso as estado',
            'bienes.condicion_fisica',
            'bienes.marca',
            'bienes.modelo',
            'bienes.serial',
            'categoria_generals.categoria_general as categoriage',
            'sub_categorias.sub_categoria as subcategoria',
            'categoria_especificas.categoria_especifica as categoriaes',
            'bienes.codigo_contable'

        ]);

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
        return PowerGrid::eloquent()
            ->addColumn('id')
            ->addColumn('sede')

           /** Example of custom column using a closure **/
            ->addColumn('sede_lower', fn (Biene $model) => strtolower(e($model->sede)))
        /*- >addColumn('unidad_administrativa_id', fn ($model)=> $model ->unidad_administrativa_id)->addColumn('unidad_administrativa', fn($model)=>$model->unidad_administrativas->unidad_administrativa) */
            ->addColumn('unidad_administrativa_id')
            ->addColumn('n_interno_bien')
            ->addColumn('descripcion')
            ->addColumn('forma_adquisicion_id')
            ->addColumn('fecha_adquisicion_formatted', fn (Biene $model) => Carbon::parse($model->fecha_adquisicion)->format('d/m/Y'))
            ->addColumn('n_factura')
            ->addColumn('valor_factura')
            ->addColumn('moneda')
            ->addColumn('estado_del_uso_id')
            ->addColumn('condicion_fisica')
            ->addColumn('marca')
            ->addColumn('modelo')
            ->addColumn('serial')
            ->addColumn('categoria_general_id')
            ->addColumn('sub_categoria_id')
            ->addColumn('categoria_especifica_id')
            ->addColumn('codigo_contable');
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
      Column::make('Id', 'id')->sortable(),
           Column::make('Sede', 'sede')

                ->searchable(),

                Column::make('Unidad Administrativa', 'unidad')
                ->searchable(),
                 Column::make('N interno bien', 'n_interno_bien')
                ->searchable(),

            Column::make('Descripcion', 'descripcion')
                ->searchable(),
            Column::make('Forma adquisicion', 'forma'),
            Column::make('Fecha adquisicion', 'fecha_adquisicion_formatted', 'fecha_adquisicion')
                ->sortable(),

            Column::make('N factura', 'n_factura')
                ->searchable(),

            Column::make('Valor factura', 'valor_factura')
                ->searchable(),

            Column::make('Moneda', 'moneda')
                ->searchable(),

            Column::make('Estado del uso', 'estado'),
            Column::make('Condicion fisica', 'condicion_fisica')
                ->searchable(),

            Column::make('Marca', 'marca')
                ->searchable(),

            Column::make('Modelo', 'modelo')
                ->searchable(),

            Column::make('Serial', 'serial')
                ->searchable(),

            Column::make('Categoria general', 'categoriage'),
            Column::make('Sub categoria', 'subcategoria'),
            Column::make('Categoria especifica', 'categoriaes'),
            Column::make('Codigo contable', 'codigo_contable')
                ->searchable(),


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
            Filter::inputText('sede')->operators(['contains']),
            Filter::inputText('n_interno_bien')->operators(['contains']),
            Filter::inputText('descripcion')->operators(['contains']),
            Filter::datepicker('fecha_adquisicion_formatted'),
            Filter::inputText('n_factura')->operators(['contains']),
            Filter::inputText('valor_factura')->operators(['contains']),
            Filter::inputText('moneda')->operators(['contains']),
            Filter::inputText('condicion_fisica')->operators(['contains']),
            Filter::inputText('marca')->operators(['contains']),
            Filter::inputText('modelo')->operators(['contains']),
            Filter::inputText('serial')->operators(['contains']),
            Filter::inputText('codigo_contable')->operators(['contains']),
            Filter::select('unidad', 'unidad_administrativa_id')
            ->dataSource(UnidadAdministrativa::all())
            ->optionValue('id')
            ->optionLabel('unidad_administrativa'),

            Filter::select('forma', 'forma_adquisicion_id')
            ->dataSource(FormaAdquisicion::all())
            ->optionValue('id')
            ->optionLabel('forma_adquisicion'),

            Filter::select('estado', 'estado_del_uso_id')
            ->dataSource(EstadoDelUso::all())
            ->optionValue('id')
            ->optionLabel('estado_del_uso'),
            Filter::select('categoriage', 'categoria_general_id')
            ->dataSource(CategoriaGeneral::all())
            ->optionValue('id')
            ->optionLabel('categoria_general'),

            Filter::select('subcategoria', 'sub_categoria_id')
            ->dataSource(SubCategoria::all())
            ->optionValue('id')
            ->optionLabel('sub_categoria'),


            Filter::select('categoriaes', 'categoria_especifica_id')
            ->dataSource(CategoriaEspecifica::all())
            ->optionValue('id')
            ->optionLabel('categoria_especifica'),
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
     * PowerGrid Biene Action Buttons.
     *
     * @return array<int, Button>
     */

     /* public function header(): array
     {
         return [
             Button::add('bulk-delete')
                 ->class('hidden')
                 ->caption(__('Eliminar (<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)'))
                 ->class('cursor-pointer block bg-white-200 text-gray-700 border border-gray-300 rounded py-2 px-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-600 dark:border-gray-500 dark:bg-gray-500 2xl:dark:placeholder-gray-300 dark:text-gray-200 dark:text-gray-300')
                 ->emit('bulkDelete-'.$this->tableName, []),
         ];
     } */
    /* public function actions(): array
    {
        $canClickButton = true;

       return [
           Button::make('edit', 'Editar')
               ->class('w-fit inline-flex flex justify-around px-4 py-2.5 font-semibold text-white bg-green-700 rounded-lg transition duration-150 ease-in-out  hover:text-white hover:bg-green-500 focus:outline-none')
 ->route('administracion.bienes_publicos.edit', ['biene' => 'id'])->can($canClickButton),
  function(Biene $model) {
                    return $model->id;
               },

            Button::make('destroy', 'Delete')
               ->class('inline-flex px-4 py-2.5 mt-3 items-center rounded-lg font-semibold text-center text-white bg-red-600  transition duration-150 ease-in-out  hover:text-white hover:bg-red-500 focus:outline-none')->openModal('')
               ->route('administracion.bienes_publicos.destroy', function(\App\Models\Biene $model) {
                    return $model->id;
               })
               ->method('delete'),
               Button::make('destroy', 'Eliminar')
                ->class('inline-flex px-4 py-2.5 mt-3 items-center rounded-lg font-semibold text-center text-white bg-red-600  transition duration-150 ease-in-out  hover:text-white hover:bg-red-500 focus:outline-none')
                ->openModal('delete-biene', [
                    'BieneId' => 'id',
                    'confirmationTitle' => 'Eliminar',
                    'confirmationDescription' => '¿Esta seguro de eliminar esta fila?',
                ])->can($canDelete),
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
     * PowerGrid Biene Action Rules.
     *
     * @return array<int, RuleActions>
     */


    /* public function actionRules(): array
    {
       return [

           //Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($biene) => $biene->id === 1)
                ->hide(),
        ];
    } */


}
