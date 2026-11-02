<?php

namespace App\Http\Livewire\SalidaOficina;

use Livewire\Component;
use App\Models\Articulo;
use App\Models\UnidadMedida;
use App\Models\SalidaOficina;
use App\Models\InventarioOficina;
use App\Models\Area;
use App\Models\Gerencia;

class Crear extends Component
{
    public $unidad_medidas;
    public $articulos;
    public $inventario_oficinas;
    public $areas;
    public $unidades;
    public $gerencias;


    // formulario
    public $unidad_medida_id;
    public $inventario_oficina_id;
    public $cantidad;
    public $articulo_id;
    public $fecha_salida;
    public $fecha_ingreso;
    public $gerencia_id;
    public $area_id;
    public $area_nombre; // Para almacenar el nombre del área
    public $ajuste_inventario;
    public $unidad_medida_nombre; // Para almacenar el nombre de la unidad de medida



    public function mount()
    {
        $this->unidad_medidas = UnidadMedida::all();
        $this->areas = Area::all();
        $this->gerencias = Gerencia::all();

        // Cargar solo artículos que tienen inventario con unidades_disponibles > 0
        // Usando subconsulta para obtener artículos con stock
        $this->articulos = Articulo::whereIn('id', function($query) {
            $query->select('articulo_id')
                ->from('inventario_oficinas')
                ->where('unidades_disponibles', '>', 0);
        })->orderBy('id')->get();

        // Cargar inventario con stock
        $this->inventario_oficinas = InventarioOficina::where('unidades_disponibles', '>', 0)->get();
    }

    public function updatedArticuloId()
{
    // Buscar el inventario del artículo seleccionado
    $inventario_oficina = $this->inventario_oficinas->where("articulo_id", $this->articulo_id)->first();

    if (!$inventario_oficina) {
        $this->unidades = 0;
        $this->inventario_oficina_id = null;
        $this->fecha_ingreso = null;
        $this->unidad_medida_id = null;
        $this->unidad_medida_nombre = '';
        return;
    }

    // Obtener el primer registro para establecer campos del formulario
    $this->inventario_oficina_id = $inventario_oficina->id;
    $this->fecha_ingreso = $inventario_oficina->fecha_ingreso;
    $this->unidad_medida_id = $inventario_oficina->unidad_medida_id;

    // Obtener el nombre de la unidad de medida
    $unidadMedida = UnidadMedida::find($this->unidad_medida_id);
    $this->unidad_medida_nombre = $unidadMedida ? $unidadMedida->unidad_medida : '';

    // Calcular unidades disponibles TOTALES usando el método estático
    // que suma las unidades_disponibles de TODOS los registros de inventario para este artículo
    $this->unidades = InventarioOficina::calcularUnidadesDisponibles(
        $this->articulo_id,
        $this->unidad_medida_id
    );
}

    public function updatedGerenciaId()
    {
        if ($this->gerencia_id) {
            $gerencia = Gerencia::with('area')->find($this->gerencia_id);
            if ($gerencia && $gerencia->area) {
                $this->area_id = $gerencia->area->id;
                $this->area_nombre = $gerencia->area->area;
            } else {
                $this->area_id = null;
                $this->area_nombre = '';
            }
        } else {
            $this->area_id = null;
            $this->area_nombre = '';
        }
    }

    public function render()
    {
        return view('livewire.salida-oficina.crear');
    }

    public function submit()
{
    // Validar fecha y cantidad
    if (empty($this->fecha_salida)) {
        session()->flash('error', 'La fecha de salida es requerida.');
        return;
    }

    $fechaSalida = \Carbon\Carbon::parse($this->fecha_salida);
    $hoy = \Carbon\Carbon::now()->startOfDay();

    if ($fechaSalida->isAfter($hoy)) {
        session()->flash('error', 'La fecha de salida no puede ser futura.');
        return;
    }

    if (empty($this->cantidad) || $this->cantidad < 1) {
        session()->flash('error', 'La cantidad debe ser mayor a 0.');
        return;
    }

    // Calcular unidades disponibles actuales
    $unidadesDisponibles = InventarioOficina::calcularUnidadesDisponibles(
        $this->articulo_id,
        $this->unidad_medida_id
    );

    // Validar que la cantidad solicitada no exceda las disponibles
    if ($this->cantidad > $unidadesDisponibles) {
        session()->flash('error', 'No puede retirar ' . $this->cantidad . ' unidades. Solo hay ' . $unidadesDisponibles . ' disponibles.');
        return;
    }

    $inventario_oficinas = InventarioOficina::where('articulo_id', $this->articulo_id)
        ->where('unidad_medida_id', $this->unidad_medida_id)
        ->get();

    if ($inventario_oficinas->isEmpty()) {
        // Handle the case where no inventory records are found
        session()->flash('error', 'No hay inventario disponible para este artículo.');
        return; // Exit the method early
    }

    $diferenciaSalida = $this->cantidad;
    foreach ($inventario_oficinas as $inventario_oficina) {
        if ($diferenciaSalida > $inventario_oficina->unidades_disponibles) {
            $diferenciaSalida -= $inventario_oficina->unidades_disponibles;
            $inventario_oficina->unidades_disponibles = 0;
        } else {
            $inventario_oficina->unidades_disponibles -= $diferenciaSalida;
            $diferenciaSalida = 0; // Set to 0 since we have fulfilled the request
        }
        $inventario_oficina->save();
    }

    SalidaOficina::create([
        'fecha_salida' => $this->fecha_salida,
        'unidad_medida_id' => $this->unidad_medida_id,
        'inventario_oficina_id' => $this->inventario_oficina_id,
        'cantidad' => $this->cantidad,
        'articulo_id' => $this->articulo_id,
        'gerencia_id' => $this->gerencia_id,
        'area_id' => $this->area_id,
        'ajuste_inventario' => $this->ajuste_inventario,
        'historico_unidades_disponibles' => $unidadesDisponibles,
        'user_id' => auth()->id(),
    ]);

    return redirect()->route('administracion.salida_oficina.index')->with('success', 'Salida realizada exitosamente. Stock restante: ' . ($unidadesDisponibles - $this->cantidad));
    }
}
