

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="row sales layout-top-spacing">

    <div class="col-sm-12">
        <div class="widget">
            <div class="widget-heading">
            </div>

            <div class="widget-content">
                <div class="row">
                    <div class="col-sm-12 col-md-3">
                        <div class="row">
                            <div class="col-sm-12">
                                <h6>Elige el usuario</h6>
                                <div class="form-group">
                                    <select wire:model="usuario_de_usos_id" wire:model.defer="usuario_de_usos_id" class="form-control">
                                        <option value="0">Todos</option>
                                        @foreach($usuario_de_usos as $u)
                                        <option value="{{$u->id}}">{{$u->usuario_de_uso}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <h6>Elige el tipo de reporte</h6>
                                <div class="form-group">
                                    <select wire:model="reportType" class="form-control">
                                        <option value="0">Por Dia día</option>
                                        <option value="1">Por Fecha fecha</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 mt-2">
                                <h6>Fecha desde</h6>
                                <div class="form-group">
                                    <input type="text" wire:model="dateFrom" class="form-control flatpickr" placeholder="Click para elegir">
                                </div>
                            </div>
                            <div class="col-sm-12 mt-2">
                                <h6>Fecha hasta</h6>
                                <div class="form-group">
                                    <input type="text" wire:model="dateTo" class="form-control flatpickr" placeholder="Click para elegir">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <button wire:click="$refresh" class="btn btn-block">
                                    Consultar
                                </button>



                                <a  class="btn  btn-block {{count($data) <1 ? 'disabled' : '' }}"
                                href="{{ url('reporte/excel' . '/' . $usuario_de_uso_id . '/' . $reportType . '/' . $dateFrom . '/' . $dateTo) }}" target="_blank">Exportar a Excel</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-9">
                        <!--TABLAE-->
                        <div class="table-responsive">
                            <table class="">
                                <thead class="text-white" style="background: #3B3F5C">
                                    <tr>
                                        <th class="table-fixed text-white text-center text-bold">SEDE</th>
                                        <th class="table-fixed text-white text-center">UNIDAD ADMINISTRATIVA</th>
                                        <th  class="table-fixed text-white text-center">USUARIO ADMINISTRATIVO</th>
                                        <th class="table-fixed text-white text-center">USUARIO DE USO</th>
                                        <th class="table-fixed text-white text-center">COD INTERNO DEL BIEN</th>
                                        <th class="table-fixed text-white text-center">DESCRIPCION</th>
                                        <th class="table-fixed text-white text-center">FORMA DE ADQUISICIÒN</th>
                                        <th class="table-fixed text-white text-center">FECHA DE ADQUISICION</th>
                                        <th class="table-fixed text-white text-center">Nº DOCUMENTO</th>
                                        <th class="table-fixed text-white text-center">VALOR ADQUISICIÒN</th>
                                        <th class="table-fixed text-white text-center">MONEDA</th>
                                        <th class="table-fixed text-white text-center">ESTADO DEL USO</th>
                                        <th class="table-fixed text-white text-center">CONDICIÓN FÍSICA</th>
                                        <th class="table-fixed text-white text-center">MARCA</th>
                                        <th class="table-fixed text-white text-center">MODELO</th>
                                        <th class="table-fixed text-white text-center">COLOR</th>
                                        <th class="table-fixed text-white text-center">SERIAL</th>
                                        <th class="table-fixed text-white text-center">COMENTARIOS</th>
                                        <th class="table-fixed text-white text-center">FECHA DE COMENTARIO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if(count($data) <1)
                                    <tr>
                                        <td colspan="7"><h5>Sin Resultados</h5></td>
                                    </tr>
                                    @endif

                                    @foreach($data as $d)
                                    <tr>
                                        <td class="text-center">{{$d->sede}}</td>
                                        <td class="text-center">{{$d->unidad_administrativa_id}}</td>
                                        <td class="text-center">{{$d->usuario_administrativo_id}}</td>
                                        <td class="text-center">{{$d->usuario_de_uso_id}}</td>
                                        <td class="text-center">{{$d->n_interno_bien}}</td>
                                        <td class="text-center">{{$d->descripcion}}</td>
                                        <td class="text-center">{{$d->n_factura}}</td>
                                        <td class="text-center">{{$d->fecha_adquisicion}}</td>
                                        <td class="text-center">{{$d->valor_factura}}</td>
                                        <td class="text-center">{{$d->moneda}}</td>
                                        <td class="text-center">{{$d->estado_del_uso_id}}</td>
                                        <td class="text-center">{{$d->condicion_id}}</td>
                                        <td class="text-center">{{$d->marca}}</td>
                                        <td class="text-center">{{$d->modelo}}</td>
                                        <td class="text-center">{{$d->color_id}}</td>
                                        <td class="text-center">{{$d->serial}}</td>
                                        <td class="text-center">{{$d->comentario}}</td>
                                        <td class="text-center">{{$d->fecha_comentario}}</td>
                                        <td class="text-center">

                                                {{\Carbon\Carbon::parse($d->fecha_adquisicion)->format('d-m-Y')}}

                                        </td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(document.getElementsByClassName('flatpickr'), {
            enableTime: false,
            dateFormat: 'Y-m-d',
            locale: {
                firstDayofWeek: 1,
                weekdays: {
                    shorthand: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
                    longhand: [
                        "Domingo",
                        "Lunes",
                        "Martes",
                        "Miércoles",
                        "Jueves",
                        "Viernes",
                        "Sábado",
                    ],
                },
                months: {
                    shorthand: [
                        "Ene",
                        "Feb",
                        "Mar",
                        "Abr",
                        "May",
                        "Jun",
                        "Jul",
                        "Ago",
                        "Sep",
                        "Oct",
                        "Nov",
                        "Dic",
                    ],
                    longhand: [
                        "Enero",
                        "Febrero",
                        "Marzo",
                        "Abril",
                        "Mayo",
                        "Junio",
                        "Julio",
                        "Agosto",
                        "Septiembre",
                        "Octubre",
                        "Noviembre",
                        "Diciembre",
                    ],
                },

            }

        })

    })

</script>
