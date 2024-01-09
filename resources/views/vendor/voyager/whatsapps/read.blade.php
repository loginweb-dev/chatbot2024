@extends('voyager::master')

@php
    $miwhats = App\Whatsapp::find($dataTypeContent->getKey());
    $micontactos = App\Contacto::where("bot", $miwhats->codigo)->get();
    $migrupos = App\Grupo::where("bot", $miwhats->codigo)->get();
    $mieventos = App\Evento::where("bot", $miwhats->codigo)->get();
    $sendcontacto = App\Contacto::where("bot", $miwhats->codigo)->where("send", false)->get();
    $sendgrupo = App\Grupo::where("bot", $miwhats->codigo)->where("send", false)->get();
@endphp

@section('page_header')
    <link rel="stylesheet" href="{{ asset('styles/core.css') }}">
	<link rel="stylesheet" href="{{ asset('styles/style.css') }}">
	<link rel="stylesheet" href="{{ asset('styles/sidebar.css') }}">
	<link rel="stylesheet" href="{{ asset('styles/chat-window.css') }}">
	<link rel="stylesheet" href="{{ asset('styles/chat-tile.css') }}">
	<link rel="stylesheet" href="{{ asset('styles/chat-message.css') }}">
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> -->
@stop

@section('content')

            <main>
                <section id="chat-window">
                    <header id="chat-window-header">
                        <img src="{{ asset('storage/'.$miwhats->logo) }}" alt="" class="avatar" id="profile-image">
                        <div id="active-chat-details">
                            <h2> BOT: {{ $miwhats->nombre }} | {{ $miwhats->telefono }}</h2>
                        </div> 
                    </header>

                    <div class="container">
                        <div class="row">
                            <div class="col-sm-8">
                                <div id="chat-window-contents">
                                    <div id="misocket"></div>
                                </div>
                            </div>
                            <div class="col-sm-4">
      

                                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                                    <div class="panel panel-default">
                                        <div class="panel-heading" role="tab" id="headingOne">
                                            <h4 class="panel-title">
                                                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                Opciones del Bot
                                                </a>
                                            </h4>
                                        </div>
                                            <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                                            <div class="panel-body">
                                                
                                                <h4 class="text-center">INFORMACION</h4>
                                                <code>
                                                    -> Nombre: {{ $miwhats->nombre }}
                                                    <br>
                                                    -> Codigo: {{ $miwhats->codigo }}
                                                    <br>
                                                    -> Telefono: {{ $miwhats->telefono }}
                                                    <br>
                                                    -> Creado: {{ $miwhats->created_at }}
                                                    <br>
                                                    -> Total Chats: N° {{ count($mieventos) }}
                                                </code>
                                                @if(!$miwhats->estado)
                                                    <a href="#" class="btn btn-danger btn-block" onclick="activar()" >ACTIVAR EL BOT</a>
                                                    <br>
                                                @else
                                                    <a href="#" class="btn btn-dark btn-block" onclick="stop()" >INACTIVAR</a>                  
                                                @endif

                                                <hr>
                                                <h4 class="text-center">CONSULTAS</h4>

                                                <a href="#" class="btn btn-dark btn-block" onclick="michats()" >Todos los Chats</a>
                                                <a href="#" class="btn btn-dark btn-block" onclick="miestados()" >Estados de Contactos</a>
                                                <a href="#" class="btn btn-dark btn-block" onclick="migrupo()" >Chats de Grupos</a>
                                                <a href="#" class="btn btn-dark btn-block" onclick="migrupo2()" >Multimedia de Grupos</a>

                                                <hr>
                                                <h4 class="text-center">Actualizar Registros</h4>
                                                <code>
                                                    -> Total Grupos: N° {{ count($migrupos) }}
                                                    <br>
                                                    -> Total Contactos: N° {{ count($micontactos) }}
                                                </code>                                        
                                                <a href="#" class="btn btn-dark btn-block" onclick="micontactos()" >Contactos</a>
                                                <a href="#" class="btn btn-dark btn-block" onclick="migrupos()" >Grupos</a>
                                                
                                                <hr>
                                                <h4 class="text-center">Envios Masivos</h4>
                                                <a href="#" class="btn btn-dark btn-block" onclick="misend('contacto')" >sms a contactos</a>
                                                <a href="#" class="btn btn-dark btn-block" onclick="misend('grupo')" >sms a grupos</a>

                                            </div>
                                        </div>
                                    </div>
                                 
                                </div>
                            </div>
                        </div>
                    </div>
                </section>            
            </main>





<!-- Modal -->
<div class="modal fade modal-primary" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="migrupos()">Save changes</button>
      </div>
    </div>
  </div>
</div>

@stop

@section('javascript')
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>

    <script>
        $(document).ready(async function () { 
            var miwhats = await axios.post("/api/whatsapp/listar", {
                'bot': '{{ $miwhats->codigo }}'
            })
            listar(miwhats.data)
        });

         

        window.Echo.channel('messages')
            .listen('MiEvent', async (e) => {  
                // console.log(e.message)
                var miwhats = e.message
                if (miwhats.bot == "{{ $miwhats->codigo }}") {
                    var milink = "{{ asset('storage') }}" 
                    milink = milink+"/"+miwhats.file
                    $("#misocket").prepend("<hr style='border-top: 1px solid #2D353E;'>")
                    console.log(e.message)
                    if (miwhats.mensaje) {
                        var messages = miwhats.mensaje
                        for(var i=0; i< messages.length; i++) {
                            messages = messages.replace(/\~(.*)\~/, "<del>$1</del>")
                                .replace(/\_(.*)\_/, "<em>$1</em>")
                                .replace(/\*(.*)\*/, "<strong>$1</strong>")
                        }
                        messages = messages.replace(/(\b(https?|):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig, "<a href='$1' target='_blank'>$1</a>");
                        $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'>"+messages+"<span class='chat-message-time'>"+miwhats.fwhats+"</span></div></div>") 
                    }

                    switch (miwhats.tipo) {
                        case "chat_multimedia":    
                            // $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'>"+miwhats.fwhats+"</div></div>")                                                   
                            if(miwhats.extension == "video/mp4") {
                                $("#misocket").prepend("<video controls class='chat-multimedia'><source src='"+milink+"' type='"+miwhats.extension+"'></video>")
                            } else if(miwhats.extension == "audio/ogg; codecs=opus") {
                                $("#misocket").prepend("<audio controls><source src='"+milink+"' type='"+miwhats.extension+"'></audio>")
                            } else if(miwhats.extension == "audio/mp4") {
                                $("#misocket").prepend("<audio controls><source src='"+milink+"' type='"+miwhats.extension+"'></audio>")
                            } else if(miwhats.extension == "application/zip") {
                                $("#misocket").prepend("<a href='/storage/"+miwhats.file+"' class='btn btn-dark'>Descargar el ZIP</a>")
                            } else if(miwhats.extension == "application/pdf") {
                                $("#misocket").prepend("<iframe src='/storage/"+miwhats.file+"' style='width:50%; height:400px;'></iframe>")
                            } else {                                
                                $("#misocket").prepend("<img class='chat-multimedia' src='"+milink+"' />")   
                            }   
                            switch (miwhats.subtipo) {
                                case 'chat_private':
                                    var micontacto = miwhats.contacto ? miwhats.contacto.name : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+micontacto+" | "+miwhats.fwhats+"</span></div>")
                                    break;
                                case 'chat_group':
                                    var miauthor = miwhats.miauthor ? miwhats.miauthor.name : miwhats.author;
                                    var migrupo = miwhats.grupo ? miwhats.grupo.name : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+miwhats.fwhats+"</span></div>")
                                    break;
                                case 'status':
                                    var micontacto = miwhats.contacto ? miwhats.contacto.name : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container micontext'><span class='datestamp'>"+micontacto+" | "+miwhats.fwhats+"</span></div>")
                                    break;
                                default:
                                    break;
                            }  
                            break;
                        case "chat_location":
                            switch (miwhats.subtipo) {
                                case 'chat_private':
                                    var micliente = miwhats.micliente ? miwhats.micliente.name : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+micliente+" | "+miwhats.fwhats+"</span></div>")                              
                                    break;
                                case 'chat_group':
                                    var miauthor = miwhats.miauthor ? miwhats.miauthor.name : miwhats.author;
                                    var migrupo = miwhats.grupo ? miwhats.grupo.name : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+miwhats.fwhats+"</span></div>")
                                    break;
                                default:
                                    break;
                            }   
                            var milanlog = JSON.parse(miwhats.datos)
                            $("#misocket").prepend("<img style='width: 40%' src='"+milink+"' />")
                            $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'><a href='https://maps.google.com/?ll="+milanlog.latitude+","+milanlog.longitude+"' target='_blank'>? IR AL MAPA</a><span class='chat-message-time'>"+miwhats.published+"</span></div></div>")                                            
                            break;
                        case "chat_private":            
                                var micontacto = miwhats.contacto ? miwhats.contacto.name : miwhats.desde
                                $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+micontacto+" | "+miwhats.fwhats+"</span></div>")
                            break;
                        case "chat_group":
                            var miauthor = miwhats.miauthor ? miwhats.miauthor.name : miwhats.author;
                            var migrupo = miwhats.grupo ? miwhats.grupo.name : miwhats.desde
                            $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+miwhats.fwhats+"</span></div>")
                            break;
                        case "qr":            
                            $("#misocket").prepend("<img class='chat-multimedia' src='"+milink+"' />") 
                            // location.reload()
                            break;
                        case "ready":            
                            location.reload()
                        case "destroy":            
                            location.reload()
                            break;
                        case "contactos":   
                            if (miwhats.file) {
                                $("#misocket").prepend("<img class='chat-multimedia' src='"+milink+"' />")
                            }           
                            break;                              
                        default:
                            break;
                    }                                  
               }
            }
        )
        
        
        function sleep(ms) {
            return new Promise(
                resolve => setTimeout(resolve, ms)
            );
        }

        async function misend(type){
            
            switch (type) {
                case "contacto":
                    @foreach($sendcontacto as $item)                        
                        var segundos = Math.floor(Math.random() * 60) + 60;
                        console.log("{{ $item->codigo }}")
                        console.log("{{ $miwhats->codigo }}")
                        console.log(segundos)
                        await axios.post("{{ env('APP_BOT') }}/send?phone={{ $item->codigo }}&slug={{ $miwhats->slug }}&bot={{ $miwhats->codigo }}&type="+type) 
                        console.log("{{ $item->codigo }}")
                        await sleep(segundos * 1000)
                    @endforeach
                    break;
                case "grupo":
                    console.log("{{ $sendgrupo }}")
                    @foreach($sendgrupo as $item)                        
                        var segundos = Math.floor(Math.random() * 60) + 60;
                        console.log("{{ $item->codigo }}")
                        console.log("{{ $miwhats->codigo }}")
                        console.log(segundos)
                        await axios.post("{{ env('APP_BOT') }}/send?phone={{ $item->codigo }}&slug={{ $miwhats->slug }}&bot={{ $miwhats->codigo }}&type="+type) 
                        console.log("{{ $item->codigo }}")
                        await sleep(segundos * 1000)
                    @endforeach
                    break;
                default:
                    break;
            }


        }

        async function activar(){
            await axios.post("{{ env('APP_BOT') }}/init?nombre={{ $miwhats->slug }}&codigo={{ $miwhats->codigo }}") 
        }

        async function stop(){
            var mirest = await axios.post("{{ env('APP_BOT') }}/stop?nombre={{ $miwhats->slug }}&codigo={{ $miwhats->codigo }}") 
        }

        async function micontactos(){
            await axios.post("{{ env('APP_BOT') }}/contactos?nombre={{ $miwhats->slug }}&codigo={{ $miwhats->codigo }}")
        }

        async function migrupos(){
            await axios.post("{{ env('APP_BOT') }}/historial?nombre={{ $miwhats->slug }}&codigo={{ $miwhats->codigo }}")
        }

        async function miestados(){
            var miwhats = await axios.post("/api/whatsapp/estados", {
                'codigo': '{{ $miwhats->codigo }}'
            })
            listar(miwhats.data)
        }

        async function michats(){
            var miwhats = await axios.post("/api/whatsapp/chats", {
                'codigo': '{{ $miwhats->codigo }}'
            })
            listar(miwhats.data)
        }

        async function migrupo(){
            var miwhats = await axios.post("/api/whatsapp/grupo", {
                'whatsapp': '{{ $miwhats->codigo }}'
            })
            listar(miwhats.data)
        }
        async function migrupo2(){
            var miwhats = await axios.post("/api/whatsapp/grupo2", {
                'whatsapp': '{{ $miwhats->codigo }}'
            })
            listar(miwhats.data)
        }

        async function listar(miwhats) {
            $("#misocket").html("")  
            for (let index = 0; index < miwhats.length; index++) {
                var milink = "{{ asset('storage') }}" 
                milink = milink+"/"+miwhats[index].file
                
                switch (miwhats[index].tipo) {
                    case "chat_multimedia":
                        // console.log(miwhats[index])
                        switch (miwhats[index].subtipo) {                            
                            case 'chat_private':
                                var micontacto = miwhats[index].contacto ? miwhats[index].contacto.name : miwhats[index].desde
                                $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+micontacto+" | "+miwhats[index].fwhats+"</span></div>")
                                break;
                            case 'chat_group':
                                    var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.name : miwhats[index].author;
                                    var migrupo = miwhats[index].grupo ? miwhats[index].grupo.name : miwhats[index].desde
                                    $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+miwhats[index].fwhats+"</span></div>")
                                break;
                            case 'status':
                                var micontacto = miwhats[index].contacto ? miwhats[index].contacto.name : miwhats[index].desde
                                $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+micontacto+" | "+miwhats[index].fwhats+"</span></div>")
                                break;
                            default:
                                break;
                        }                                                        
                        if(miwhats[index].extension == "video/mp4") {
                            $("#misocket").append("<video controls class='chat-multimedia'><source src='"+milink+"' type='"+miwhats[index].extension+"'></video>")
                        } else if(miwhats[index].extension == "audio/ogg; codecs=opus") {
                            $("#misocket").append("<audio controls><source src='"+milink+"' type='"+miwhats[index].extension+"'></audio>")
                        } else if(miwhats[index].extension == "audio/mp4") {
                            $("#misocket").append("<audio controls><source src='"+milink+"' type='"+miwhats[index].extension+"'></audio>")
                        } else if(miwhats[index].extension == "application/zip") {
                            $("#misocket").append("<a href='/storage/"+miwhats[index].file+"' class='btn btn-dark'>Descargar el ZIP</a>")
                        } else if(miwhats[index].extension == "application/pdf") {
                            $("#misocket").append("<iframe src='/storage/"+miwhats[index].file+"' style='width:50%; height:400px;'></iframe>")
                        } else {
                            $("#misocket").append("<img class='chat-multimedia' src='"+milink+"' />")   
                        }   
                        // $("#misocket").append("<div class='chat-message-group'><div class='chat-message'>"+miwhats[index].fwhats+"</div></div>")     
                        break;
                    case "chat_location":
                        switch (miwhats[index].subtipo) {
                            case 'chat_private':
                                var micontacto = miwhats[index].contacto ? miwhats[index].contacto.name : miwhats[index].desde                                    
                                $("#misocket").append("<div class='datestamp-container'><span class='datestamp'>"+micontacto+" | "+miwhats[index].fwhats+"</span></div>")                   
                                break;
                            case 'chat_group':
                                var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.name : miwhats[index].author;
                                    var migrupo = miwhats[index].grupo ? miwhats[index].grupo.name : miwhats[index].desde
                                $("#misocket").append("<div class='datestamp-container'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+miwhats[index].fwhats+"</span></div>")
                                break;
                            default:
                                break;
                        }   
                        var milanlog = JSON.parse(miwhats[index].datos)
                        $("#misocket").append("<img style='width: 40%' src='"+milink+"' />")
                        $("#misocket").append("<div class='chat-message-group'><div class='chat-message'><a href='https://maps.google.com/?ll="+milanlog.latitude+","+milanlog.longitude+"' target='_blank'>? IR AL MAPA</a><span class='chat-message-time'>"+miwhats[index].published+"</span></div></div>")                                                     
                        break;
                    case "chat_private":             
                        var micontacto = miwhats[index].contacto ? miwhats[index].contacto.name : miwhats[index].desde                                    
                        $("#misocket").append("<div class='datestamp-container'><span class='datestamp'>"+micontacto+" | "+miwhats[index].fwhats+"</span></div>")
                        break;
                    case "chat_group":
                        var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.name : miwhats[index].author;
                        var migrupo = miwhats[index].grupo ? miwhats[index].grupo.name : miwhats[index].desde
                        $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+miwhats[index].fwhats+"</span></div>")
                        break;
                    default:
                        break;
                }

                if (miwhats[index].mensaje) {
                    var messages = miwhats[index].mensaje
                    for(var i=0; i< messages.length; i++) {
                        messages = messages.replace(/\~(.*)\~/, "<del>$1</del>")
                            .replace(/\_(.*)\_/, "<em>$1</em>")
                            .replace(/\*(.*)\*/, "<strong>$1</strong>")
                    }
                    messages = messages.replace(/(\b(https?|):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig, "<a href='$1' target='_blank'>$1</a>");
                    $("#misocket").append("<div class='chat-message-group text-center'><div class='chat-message'>"+messages+"<span class='chat-message-time'>"+miwhats[index].fwhats+"</span></div></div>") 

                }
                $("#misocket").append("<hr style='border-top: 1px solid #2D353E;'>")
            }
            $("#misocket").prepend("<hr style='border-top: 1px solid #2D353E;'>")
            $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'>Mostrando los ultimos "+miwhats.length+" registros del dia de hoy</div></div>") 

        }
                                                                   
    </script>
@stop