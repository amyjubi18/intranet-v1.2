<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Biene extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table ='bienes';
    protected $fillable = [

    'sede',
    'n_interno_bien',
    'descripcion',
    'forma_adquisicion_id',
    'fecha_adquisicion',
    'n_factura',
    'valor_factura',
    'moneda',
    'estado_del_uso_id',
    'condicion_id',
    'marca',
    'modelo',
    'color_id',
    'unidad_administrativa_id',
    'usuario_administrativo_id',
    'usuario_de_uso_id',
    'serial',
    'comentario',
    'fecha_comentario',
    'qr_code',
    'name'
    /* 'user_id' */];
    public function usuario_administrativos()
    {
        return $this->belongsTo(UsuarioAdministrativo::class, 'usuario_administrativo_id' );
    }
    public function unidad_administrativas()
    {
        return $this->belongsTo(UnidadAdministrativa::class, 'unidad_administrativa_id');
    }
     public function usuario_de_usos()
    {
        return $this->belongsTo(UsuarioDeUso::class, 'usuario_de_uso_id');
    }


    public function estado_del_usos()
    {
        return $this->belongsTo(EstadoDelUso::class, 'estado_del_uso_id');
    }
    public function forma_adquisicions()
    {
        return $this->belongsTo(FormaAdquisicion::class,'forma_adquisicion_id');
    }

    public function colors()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }
    public function condicions()
    {
        return $this->belongsTo(Condicion::class, 'condicion_id');
    }
    /* public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    } */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['sede', 'n_interno_bien', 'descripcion', 'forma_adquisicion_id', 'fecha_adquisicion', 'n_factura', 'valor_factura', 'moneda', 'estado_del_uso_id', 'condicion_id', 'marca', 'modelo', 'color_id', 'unidad_administrativa_id', 'usuario_administrativo_id', 'usuario_de_uso_id', 'serial', 'comentario', 'fecha_comentario', 'qr_code', 'name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get icon based on description content
     */
    public function getIconForDescription(): string
    {
        $descripcion = strtolower($this->descripcion ?? '');

        // Muebles de oficina
        if (str_contains($descripcion, 'silla') || str_contains($descripcion, 'sillón') || str_contains($descripcion, 'butaca')) {
            return '🪑';
        }
        if (str_contains($descripcion, 'mesa') || str_contains($descripcion, 'escritorio') || str_contains($descripcion, 'tabla')) {
            return '🪵';
        }
        if (str_contains($descripcion, 'gabinete') || str_contains($descripcion, 'archivador') || str_contains($descripcion, 'armario')) {
            return '🗄️';
        }
        if (str_contains($descripcion, 'estante') || str_contains($descripcion, 'anaquel')) {
            return '📚';
        }
        if (str_contains($descripcion, 'corte') || str_contains($descripcion, 'cortina')) {
            if (str_contains($descripcion, 'aire') || str_contains($descripcion, 'a/a') || str_contains($descripcion, 'acondicionado')) {
                return '❄️';
            }
            return '🪟';
        }
        if (str_contains($descripcion, 'pizarra') || str_contains($descripcion, 'tabla')) {
            return '📋';
        }
        if (str_contains($descripcion, 'silla de ruedas') || str_contains($descripcion, 'rueda')) {
            return '🦽';
        }

        // Electrónica y tecnología
        if (str_contains($descripcion, 'monitor') || str_contains($descripcion, 'pantalla') || str_contains($descripcion, 'display')) {
            return '🖥️';
        }
        if (str_contains($descripcion, 'teclado')) {
            return '⌨️';
        }
        if (str_contains($descripcion, 'ratón') || str_contains($descripcion, 'mouse')) {
            return '🖱️';
        }
        if (str_contains($descripcion, 'computadora') || str_contains($descripcion, 'pc') || str_contains($descripcion, 'laptop') || str_contains($descripcion, 'portátil') || str_contains($descripcion, 'portatil')) {
            return '💻';
        }
        if (str_contains($descripcion, 'cpu') || str_contains($descripcion, 'torre') || str_contains($descripcion, 'gabinete de pc')) {
            return '🖥️';
        }
        if (str_contains($descripcion, 'tablet') || str_contains($descripcion, 'ipad') || str_contains($descripcion, 'tab')) {
            return '📱';
        }
        if (str_contains($descripcion, 'impresora')) {
            return '🖨️';
        }
        if (str_contains($descripcion, 'escáner') || str_contains($descripcion, 'scanner')) {
            return '📷';
        }
        if (str_contains($descripcion, 'proyector') || str_contains($descripcion, 'videobeam')) {
            return '📽️';
        }
        if (str_contains($descripcion, 'teléfono') || str_contains($descripcion, 'telefono')) {
            return '📞';
        }
        if (str_contains($descripcion, 'fax')) {
            return '📠';
        }
        if (str_contains($descripcion, 'radio') || str_contains($descripcion, 'transmisor')) {
            return '📻';
        }
        if (str_contains($descripcion, 'cámara') || str_contains($descripcion, 'camara') || str_contains($descripcion, 'cctv')) {
            return '📹';
        }
        if (str_contains($descripcion, 'altavoz') || str_contains($descripcion, 'bocina') || str_contains($descripcion, 'parlante')) {
            return '🔊';
        }
        if (str_contains($descripcion, 'micrófono') || str_contains($descripcion, 'microfono')) {
            return '🎤';
        }
        if (str_contains($descripcion, 'audio') || str_contains($descripcion, 'equipo de sonido')) {
            return '🔈';
        }
        if (str_contains($descripcion, 'televisor') || str_contains($descripcion, 'tv') || str_contains($descripcion, 'televisión')) {
            return '📺';
        }
        if (str_contains($descripcion, 'dvd') || str_contains($descripcion, 'blu-ray') || str_contains($descripcion, 'video')) {
            return '📀';
        }
        if (str_contains($descripcion, 'cargador')) {
            return '🔌';
        }
        if (str_contains($descripcion, 'batería') || str_contains($descripcion, 'pila')) {
            return '🔋';
        }
        if (str_contains($descripcion, 'ups') || str_contains($descripcion, 'regulador')) {
            return '⚡';
        }
        if (str_contains($descripcion, 'router') || str_contains($descripcion, 'switch') || str_contains($descripcion, 'red')) {
            return '🌐';
        }
        if (str_contains($descripcion, 'servidor')) {
            return '🖧';
        }

        // Aire acondicionado y ventilación
        if (str_contains($descripcion, 'aire acondicionado') || str_contains($descripcion, 'a/a') || str_contains($descripcion, 'acondicionador')) {
            return '❄️';
        }
        if (str_contains($descripcion, 'ventilador')) {
            return '𖣘';
        }
        if (str_contains($descripcion, 'extractor') || str_contains($descripcion, 'extraction')) {
            return '💨';
        }
        if (str_contains($descripcion, 'calefactor') || str_contains($descripcion, 'calefacción') || str_contains($descripcion, 'pistola de calor')) {
            return '🔥';
        }
        if (str_contains($descripcion, 'humidificador') || str_contains($descripcion, 'deshumidificador')) {
            return '💧';
        }

        // Electrodomésticos
        if (str_contains($descripcion, 'refrigerador') || str_contains($descripcion, 'nevera')) {
            return '🧊';
        }
        if (str_contains($descripcion, 'cafetera')) {
            return '☕';
        }
        if (str_contains($descripcion, 'microondas') || str_contains($descripcion, 'horno')) {
            return '🍳';
        }
        if (str_contains($descripcion, 'licuadora')) {
            return '🥤';
        }
        if (str_contains($descripcion, 'batidora')) {
            return '🥣';
        }
        if (str_contains($descripcion, ' Aspiradora') || str_contains($descripcion, 'aspiradora')) {
            return '🧹';
        }
        if (str_contains($descripcion, 'lavadora')) {
            return '🧺';
        }
        if (str_contains($descripcion, 'secadora')) {
            return '👕';
        }
        if (str_contains($descripcion, 'plancha')) {
            return '👔';
        }

        // Papelería y útiles
        if (str_contains($descripcion, 'papel') || str_contains($descripcion, 'resma')) {
            return '📄';
        }
        if (str_contains($descripcion, 'lápiz') || str_contains($descripcion, 'lapiz')) {
            return '✏️';
        }
        if (str_contains($descripcion, 'pluma') || str_contains($descripcion, 'bolígrafo') || str_contains($descripcion, 'boligrafo')) {
            return '🖊️';
        }
        if (str_contains($descripcion, 'cuaderno') || str_contains($descripcion, 'libro')) {
            return '📓';
        }
        if (str_contains($descripcion, 'carpeta')) {
            return '📁';
        }
        if (str_contains($descripcion, 'perforadora')) {
            return '🖇️';
        }
        if (str_contains($descripcion, 'engrapadora') || str_contains($descripcion, 'grapadora')) {
            return '📎';
        }
        if (str_contains($descripcion, 'tijera')) {
            return '✂️';
        }
        if (str_contains($descripcion, 'cinta') || str_contains($descripcion, 'masking')) {
            return '📏';
        }
        if (str_contains($descripcion, 'sello') || str_contains($descripcion, 'timbre')) {
            return '🏷️';
        }
        if (str_contains($descripcion, 'calculadora')) {
            return '🧮';
        }

        // Vehículos y transporte
        if (str_contains($descripcion, 'vehículo') || str_contains($descripcion, 'vehiculo') || str_contains($descripcion, 'auto') || str_contains($descripcion, 'automóvil')) {
            return '🚗';
        }
        if (str_contains($descripcion, 'motocicleta') || str_contains($descripcion, 'moto')) {
            return '🏍️';
        }
        if (str_contains($descripcion, 'bicicleta')) {
            return '🚲';
        }
        if (str_contains($descripcion, 'camión') || str_contains($descripcion, 'camion')) {
            return '🚛';
        }
        if (str_contains($descripcion, 'carrito') || str_contains($descripcion, 'carro')) {
            return '🛒';
        }
        if (str_contains($descripcion, 'escalera')) {
            return '🪜';
        }

        // Herramientas
        if (str_contains($descripcion, 'taladro')) {
            return '🔩';
        }
        if (str_contains($descripcion, 'martillo')) {
            return '🔨';
        }
        if (str_contains($descripcion, 'destornillador')) {
            return '🪛';
        }
        if (str_contains($descripcion, 'llave')) {
            return '🔧';
        }
        if (str_contains($descripcion, 'sierra')) {
            return '🪚';
        }
        if (str_contains($descripcion, 'pinza')) {
            return '🔩';
        }

        // Artefactos de iluminación
        if (str_contains($descripcion, 'lámpara') || str_contains($descripcion, 'lampara') || str_contains($descripcion, 'bombilla')) {
            return '💡';
        }
        if (str_contains($descripcion, 'foco') || str_contains($descripcion, 'tubo')) {
            return '💫';
        }

        // Equipos especializados
        if (str_contains($descripcion, 'médico') || str_contains($descripcion, 'medico') || str_contains($descripcion, 'hospital')) {
            return '🏥';
        }
        if (str_contains($descripcion, 'laboratorio') || str_contains($descripcion, 'científico')) {
            return '🔬';
        }
        if (str_contains($descripcion, 'dental') || str_contains($descripcion, 'odontología')) {
            return '🦷';
        }
        if (str_contains($descripcion, 'gimnasio') || str_contains($descripcion, 'deporte')) {
            return '🏋️';
        }
        if (str_contains($descripcion, 'musical') || str_contains($descripcion, 'instrumento')) {
            return '🎵';
        }
        if (str_contains($descripcion, 'cocina') || str_contains($descripcion, 'industrial')) {
            return '🍽️';
        }
        if (str_contains($descripcion, 'seguridad') || str_contains($descripcion, 'extintor')) {
            return '🛡️';
        }
        if (str_contains($descripcion, 'caja') || str_contains($descripcion, 'strong')) {
            return '🔐';
        }
        if (str_contains($descripcion, 'reloj') || str_contains($descripcion, 'time')) {
            return '⏰';
        }
        if (str_contains($descripcion, 'bandeja') || str_contains($descripcion, 'organizador')) {
            return '📦';
        }
        if (str_contains($descripcion, 'basura')) {
            return '🗑️';
        }
        if (str_contains($descripcion, 'sanitario') || str_contains($descripcion, 'baño') || str_contains($descripcion, 'lavabo')) {
            return '🚽';
        }
        if (str_contains($descripcion, 'ducha')) {
            return '🚿';
        }

        // Default icon
        if (str_contains($descripcion, 'puesto de trabajo') || str_contains($descripcion, 'puesto')) {
            return '💼';
        }
        return '📦';
    }

    /**
     * Get CSS color from Spanish color name
     */
    public function getColorCssAttribute(): string
    {
        if (!$this->colors) {
            return '#cccccc';
        }

        return \App\Models\Color::mapColorToCss($this->colors->color ?? '');
    }

    /**
     * Fallback: try to extract color from name using common patterns
     */
    private function getColorFromName(string $colorName): string
    {
        // Si el nombre ya es un color CSS válido
        $cssColors = ['red', 'blue', 'green', 'yellow', 'orange', 'purple', 'pink', 'black', 'white', 'gray', 'grey', 'brown', 'beige', 'cyan', 'magenta', 'lime', 'navy', 'teal', 'olive', 'maroon', 'aqua', 'fuchsia', 'silver', 'gold', 'coral', 'crimson', 'indigo', 'ivory', 'khaki', 'lavender', 'mint', 'orchid', 'peach', 'plum', 'salmon', 'sienna', 'skyblue', 'slategray', 'tan', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'whitesmoke'];

        if (in_array($colorName, $cssColors)) {
            return $colorName;
        }

        // Si el nombre contiene un color conocido, intentamos extraerlo
        foreach ($cssColors as $cssColor) {
            if (str_contains($colorName, $cssColor)) {
                return $cssColor;
            }
        }

        // Si no encontramos coincidencia, verificar si hay una palabra de color al inicio
        $words = explode(' ', $colorName);
        foreach ($words as $word) {
            if (in_array($word, $cssColors)) {
                return $word;
            }
        }

        // Por defecto, retornar gris claro
        return '#9ca3af';
    }
}
