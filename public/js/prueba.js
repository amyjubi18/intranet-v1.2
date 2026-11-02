document.addEventListener('DOMContentLoaded', function() {
    let formulario = document.getElementById("form");

    var calendarEl = document.getElementById('agenda');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        timeZone:'UTC',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
          },
          
          events: "/evento/mostrar",
          selectable: true,
          displayEventTime: false,
          dateClick: function(info) {
            /* $("#evento").modal("show"); */
            formulario.reset();

            formulario.start.value=info.dateStr;
            formulario.end.value=info.dateStr;
            mostrarModal();
          },
          eventClick:function(info){
            var evento = info.event;
            console.log(evento);
            axios.post(baseURL+"/evento/editar/"+info.event.id).then(
                (respuesta) =>{
                    formulario.id.value = respuesta.data.id;
                    formulario.title.value = respuesta.data.title;
                    formulario.descripcion.value = respuesta.data.descripcion;
                    formulario.start.value = respuesta.data.start;
                    formulario.end.value = respuesta.data.end;
                    formulario.color.value = respuesta.data.color;
   
                }
            ).catch(
                error=>{
                    if(error.response){
                        console.log(error.response.data);
                    }
                }
            ); 

            
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
        const datos= new FormData(formulario);
        const nuevaURL =  baseURL+url;
       
         axios.post(nuevaURL, datos).then(
            (respuesta) =>{
                calendar.refetch.Events();
            }
        ).catch(
            error=>{
                if(error.response){
                    console.log(error.response.data);
                }
            }); 
        
    }
  });