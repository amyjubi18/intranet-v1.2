document.addEventListener('DOMContentLoaded', function() {
    // Verificar si FullCalendar está disponible antes de inicializar
    if (typeof FullCalendar === 'undefined') {
        console.log('FullCalendar no disponible - agenda deshabilitada');
        return; // Salir si FullCalendar no está cargado
    }

    var calendarEl = document.getElementById('agenda');
    if (!calendarEl) {
        console.log('Elemento agenda no encontrado - agenda deshabilitada');
        return; // Salir si no hay elemento agenda en la página
    }

const formulario = document.querySelector('#formularioEventos');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      locale:'es',
      displayEventTime: false,
      selectable: true,
       themeSystem: 'bootstrap5',
      headerToolbar:{
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,listWeek',

      },
      //events:baseURL+"/evento/mostrar",
      eventSources:{
        url: baseURL+"/evento/mostrar",
        method:"POST",
        extraParams: {
            _token: formulario._token.value,
        }
      },
      dateClick: function(info){
        formulario.reset();
        formulario.start.value=info.dateStr;
        formulario.end.value=info.dateStr;
        $("#evento").modal("show");
      },
      eventClick:function(info){
        var evento = info.event;
        console.log(evento);
        axios.post('/evento/editar/'+info.event.id).then(
            (respuesta) =>{
                formulario.id.value = respuesta.data.id;
                formulario.title.value = respuesta.data.title;
                formulario.descripcion.value = respuesta.data.descripcion;
                formulario.start.value = respuesta.data.start;
                formulario.end.value = respuesta.data.end;
                console.log('Evento editado correctamente:', respuesta.data);
            $("#evento").modal("show");
        }
        ).catch(
            error=>{
                if(error.response){
                    console.error('Error al agregar evento:', error.response ? error.response.data : error.message);
                // Show error message to user
                alert('Hubo un error al editar el evento. Por favor, inténtelo nuevamente.');
                }
            }
        )
      }
    });

    calendar.render();


    document.getElementById("btnGuardar").addEventListener("click", function(){

        enviarDatos("/evento/agregar");
    });


    document.getElementById("btnEliminar").addEventListener("click", function(){
        enviarDatos("/evento/borrar/"+formulario.id.value);

    });
    document.getElementById("btnModificar").addEventListener("click", function(){
        enviarDatos("/evento/actualizar/"+formulario.id.value);

    });
    function enviarDatos(url){
        const datos = new FormData(formulario);

        nuevaURL = baseURL+url;
         axios.post(nuevaURL, datos).then(
            (respuesta) =>{
                calendar.refetchEvents();
                console.log('Evento agregado correctamente:', respuesta.data);
            $("#evento").modal("hide");
        }
        ).catch(
            error=>{
                if(error.response){
                    console.error('Error ', error.response ? error.response.data : error.message);
                // Show error message to user
                alert('Hubo un error . Por favor, inténtelo nuevamente.');
                }
            }
        )
    }
  });
