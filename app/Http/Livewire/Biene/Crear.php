<?php

namespace App\Http\Livewire\Biene;
use App\Models\User;
use App\Models\Biene;
use App\Models\UsuarioDeUso;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Http\Livewire;
use Illuminate\Support\Facades\Gate;
use App\Models\UsuarioAdministrativo;
use App\Models\UnidadAdministrativa;
use App\Models\FormaAdquisicion;
use App\Models\EstadoDelUso;
use App\Models\Color;
use App\Models\Condicion;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Support\Facades\Validator;

class Crear extends Component
{
    public $bienes;
    public $usuario_de_usos = [];
    public $usuario_administrativos;
    public $unidad_administrativas;
    public $forma_adquisicions;
    public $estado_del_usos;
    public $colors;
    public $condicions;

    // Nueva propiedad para los usuarios de uso filtrados
    public $filtered_usuario_de_usos = [];

    public $sede;
    public $unidad_administrativa_id;
    public $usuario_administrativo_id;
    public $usuario_de_uso_id;
    public $n_interno_bien = '';
    public $descripcion;
    public $forma_adquisicion_id;
    public $fecha_adquisicion;
    public $n_factura;
    public $valor_factura;
    public $moneda = 'BOLÍVAR';
    public $estado_del_uso_id;
    public $condicion_id;
    public $marca;
    public $modelo;
    public $color_id;
    public $serial;
    public $comentario;
    public $fecha_comentario;

    protected function checkNInternoBienExists($n_interno_bien)
{
    return Biene::where('n_interno_bien', $n_interno_bien)->exists();
}
    protected $rules = [
        'n_interno_bien' => 'required_if:n_interno_bien,S/I',
        // ... (add other validation rules for other fields)
    ];

    protected function rules()
    {
    return [
        'n_interno_bien' => [
            new RequiredIf(function () {
                return $this->n_interno_bien !== 'S/I';
            }),
            function ($attribute, $value, $fail) {
                if ($value === 'S/I') {
                    return true; // No hacer nada si el valor es 'S/I'
                }

                // Agregar más lógica de validación aquí si es necesario

                return true;
            },
        ],
        // ... otras reglas de validación
    ];
    }


    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function mount(){
        $this->usuario_administrativos = UsuarioAdministrativo::all();
        $this->unidad_administrativas = UnidadAdministrativa::all();
        $this->usuario_de_usos = UsuarioDeUso::all();
        $this->forma_adquisicions = FormaAdquisicion::all();
        $this->estado_del_usos = EstadoDelUso::all();
        $this->colors = Color::orderBy('color', 'asc')->get();
        $this->condicions = Condicion::all();
        $this->bienes = Biene::all();
    }

    public function updatedUnidadAdministrativaId($value)
    {
        $this->filtered_usuario_de_usos = UsuarioDeUso::where('unidad_administrativa_id', $value)->get();

        // Filtrar usuarios administrativos
    $this->usuario_administrativos = UsuarioAdministrativo::where('unidad_administrativa_id', $value)->get();
    }
    public function render()
    {
        return view('livewire.biene.crear');
    }
    public function submit(){
        $this->validate();

    if ($this->n_interno_bien !== 'S/I') {
        // Validaciones adicionales si el valor no es 'S/I'
        $validator = Validator::make(
            ['n_interno_bien' => $this->n_interno_bien],
            [
                'n_interno_bien' => [
                    function ($attribute, $value, $fail) {
                        if ($this->checkNInternoBienExists($value)) {
                            $fail('El número interno del bien ya existe.');
                        }
                    },
                ],
            ]
        );

        if ($validator->fails()) {
            $this->addError('n_interno_bien', $validator->errors()->first('n_interno_bien'));
            return;
        }
    }

        Biene::create([
            'sede' => $this->sede,
            'unidad_administrativa_id' => $this->unidad_administrativa_id,
            'usuario_administrativo_id' => $this->usuario_administrativo_id,
            'usuario_de_uso_id' => $this->usuario_de_uso_id,
            'n_interno_bien' => $this->n_interno_bien,
            'descripcion' => $this->descripcion,
            'forma_adquisicion_id' => $this->forma_adquisicion_id,
            'fecha_adquisicion' => $this->fecha_adquisicion,
            'n_factura' => $this->n_factura,
            'valor_factura' => $this->valor_factura,
            'moneda' => $this->moneda,
            'estado_del_uso_id' => $this->estado_del_uso_id,
            'condicion_id' => $this->condicion_id,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'color_id' => $this->color_id,
            'serial' => $this->serial,
            'comentario' => $this->comentario,
            'fecha_comentario' => $this->fecha_comentario
        ]);

        return redirect()->route('administracion.bienes_publicos.index')->with('success', 'Salida realizada exitosamente.');
    }
}
