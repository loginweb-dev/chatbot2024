
@extends('voyager::master')

@php
    $miwhats = App\Whatsapp::find($dataTypeContent->getKey());
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
                    <h3> {{ $miwhats->nombre }}  <small>{{ $miwhats->codigo }}</small></h3>
                    <!-- <div class="info">{{ $miwhats->codigo }}</div> -->
                </div>
                <!-- <img src="{{ asset('icons/search.svg') }}" alt="" class="icon"> -->
                <img src="{{ asset('icons/status.svg') }}" alt=" Solo Estados" class="icon" onclick="miestados()">
                <img src="{{ asset('icons/new-chat.svg') }}" alt="Todos lo chats" class="icon" onclick="michats()">
                <!-- <img src="{{ asset('icons/filter.svg') }}" alt="" class="icon"> -->
                <div class="dropdown">
                    <img src="{{ asset('icons/communities.svg') }}" alt="Filtro por Grupos" class="icon">
                    <div class="dropdown-content contact-menu">
                        <a href="#" onclick="migrupo()">Chats</a>
                        <a href="#" onclick="migrupo2()">Multimedia</a>
                    </div>
                </div>
                
                <!-- <div class="dropdown"> -->
                    <!-- <img src="{{ asset('icons/menu.svg') }}" alt="" class="icon dropdown-button"> -->
                    <!-- <div class="dropdown-content contact-menu"> -->
                        <!-- <a href="#" onclick="init()">Re iniciar el BOT</a>
                        <a href="#" onclick="midelete()">Pausar el BOT</a> -->
                    <!-- </div> -->
                <!-- </div> -->
            </header>
            <div id="chat-window-contents">

                <div id="misocket"></div>

            </div>
        </section>            
    </main>

@stop

@section('javascript')
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(async function () {
            // $("#misocket").html("<img src='{{ asset('storage/kOnzy.gif') }}'>")  
            var miwhats = await axios.post("/api/whatsapp/listar", {
                'bot': '{{ $miwhats->codigo }}'
            })
            listar(miwhats.data)
        });

         
        var beat = new Audio("{{ asset('success.mp3') }}");

        window.Echo.channel('messages')
            .listen('MiEvent', async (e) => {  
                var miwhats = e.message
                if (miwhats.bot == "{{ $miwhats->codigo }}") {
                    var milink = "{{ asset('storage') }}" 
                    milink = milink+"/"+miwhats.file
                    $("#misocket").prepend("<hr style='border-top: 1px solid #2D353E;'>")
              
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
                            $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'>"+miwhats.fwhats+"</div></div>")                                                   
                            if(miwhats.extension == "video/mp4") {
                                $("#misocket").prepend("<video controls width='370'><source src='"+milink+"' type='"+miwhats.extension+"'></video>")
                            } else if(miwhats.extension == "audio/ogg; codecs=opus") {
                                $("#misocket").prepend("<audio controls><source src='"+milink+"' type='"+miwhats.extension+"'></audio>")
                            } else if(miwhats.extension == "audio/mp4") {
                                $("#misocket").prepend("<audio controls><source src='"+milink+"' type='"+miwhats.extension+"'></audio>")
                            } else if(miwhats.extension == "application/zip") {
                                $("#misocket").prepend("<a href='/storage/"+miwhats.file+"' class='btn btn-dark'>Descargar el ZIP</a>")
                            } else if(miwhats.extension == "application/pdf") {
                                $("#misocket").prepend("<iframe src='/storage/"+miwhats.file+"' style='width:50%; height:400px;'></iframe>")
                            } else {                                
                                $("#misocket").prepend("<img style='width: 50%' src='"+milink+"' />")   
                            }   
                            switch (miwhats.subtipo) {
                                case 'chat_private':
                                    var micliente = miwhats.micliente ? miwhats.micliente.nombre : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+micliente+"</span></div>")
                                    break;
                                case 'chat_group':
                                    var miauthor = miwhats.miauthor ? miwhats.miauthor.nombre : miwhats.author;
                                    var migrupo = miwhats.grupo ? miwhats.grupo.nombre : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+migrupo+" | "+miauthor+"</span></div>")
                                    break;
                                default:
                                    break;
                            }  
                            break;
                        case "chat_location":
                            switch (miwhats.subtipo) {
                                case 'chat_private':
                                    var micliente = miwhats.micliente ? miwhats.micliente.nombre : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+micliente+"</span></div>")                              
                                    break;
                                case 'chat_group':
                                    var miauthor = miwhats.miauthor ? miwhats.miauthor.nombre : miwhats.author;
                                    var migrupo = miwhats.grupo ? miwhats.grupo.nombre : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+migrupo+" | "+miauthor+"</span></div>")
                                    break;
                                default:
                                    break;
                            }   
                            var milanlog = JSON.parse(miwhats.datos)
                            $("#misocket").prepend("<img style='width: 40%' src='"+milink+"' />")
                            $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'><a href='https://maps.google.com/?ll="+milanlog.latitude+","+milanlog.longitude+"' target='_blank'>? IR AL MAPA</a><span class='chat-message-time'>"+miwhats.published+"</span></div></div>")                                            
                            break;
                        case "chat_private":            
                                var micliente = miwhats.micliente ? miwhats.micliente.nombre : miwhats.desde
                                $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+micliente+"</span></div>")
                            break;
                        case "chat_group":
                            var miauthor = miwhats.miauthor ? miwhats.miauthor.nombre : miwhats.author;
                            var migrupo = miwhats.grupo ? miwhats.grupo.nombre : miwhats.desde
                            $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+migrupo+" | "+miauthor+"</span></div>")
                            break;
                        case "qr":            
                                 $("#misocket").prepend("<img style='width: 50%' src='"+milink+"' />") 
                            break;
                        default:
                            break;
                    }                    
                
                                                                   
               }
            })
        

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
                                var micliente = miwhats[index].micliente ? miwhats[index].micliente.nombre : miwhats[index].desde
                                $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+micliente+"<select class='form-control miselectevent'><option><< Acciones >></option><option>Enviar Mensaje</option></select></span></div>")
                                break;
                            case 'chat_group':
                                    var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.nombre : miwhats[index].author;
                                    var migrupo = miwhats[index].grupo ? miwhats[index].grupo.nombre : miwhats[index].desde
                                    $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+migrupo+" | "+miauthor+"<select class='form-control miselectevent'><option><< Acciones >></option><option>Enviar Mensaje</option></select></span></div>")
                                break;
                            default:
                                break;
                        }                                                        
                        if(miwhats[index].extension == "video/mp4") {
                            $("#misocket").append("<video controls width='370'><source src='"+milink+"' type='"+miwhats[index].extension+"'></video>")
                        } else if(miwhats[index].extension == "audio/ogg; codecs=opus") {
                            $("#misocket").append("<audio controls><source src='"+milink+"' type='"+miwhats[index].extension+"'></audio>")
                        } else if(miwhats[index].extension == "audio/mp4") {
                            $("#misocket").append("<audio controls><source src='"+milink+"' type='"+miwhats[index].extension+"'></audio>")
                        } else if(miwhats[index].extension == "application/zip") {
                            $("#misocket").append("<a href='/storage/"+miwhats[index].file+"' class='btn btn-dark'>Descargar el ZIP</a>")
                        } else if(miwhats[index].extension == "application/pdf") {
                            $("#misocket").append("<iframe src='/storage/"+miwhats[index].file+"' style='width:50%; height:400px;'></iframe>")
                        } else {
                            $("#misocket").append("<img style='width: 50%' src='"+milink+"' />")   
                        }   
                        $("#misocket").append("<div class='chat-message-group'><div class='chat-message'>"+miwhats[index].fwhats+"</div></div>")     
                        break;
                    case "chat_location":
                        switch (miwhats[index].subtipo) {
                            case 'chat_private':
                                var micliente = miwhats[index].micliente ? miwhats[index].micliente.nombre : miwhats[index].desde                                    
                                $("#misocket").append("<div class='datestamp-container'><span class='datestamp'>"+micliente+"</span></div>")                   
                                break;
                            case 'chat_group':
                                var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.nombre : miwhats[index].author;
                                    var migrupo = miwhats[index].grupo ? miwhats[index].grupo.nombre : miwhats[index].desde
                                $("#misocket").append("<div class='datestamp-container'><span class='datestamp'>"+migrupo+" | "+miauthor+"</span></div>")
                                break;
                            default:
                                break;
                        }   
                        var milanlog = JSON.parse(miwhats[index].datos)
                        $("#misocket").append("<img style='width: 40%' src='"+milink+"' />")
                        $("#misocket").append("<div class='chat-message-group'><div class='chat-message'><a href='https://maps.google.com/?ll="+milanlog.latitude+","+milanlog.longitude+"' target='_blank'>? IR AL MAPA</a><span class='chat-message-time'>"+miwhats[index].published+"</span></div></div>")                                                     
                        break;
                    case "chat_private":             
                        var micliente = miwhats[index].micliente ? miwhats[index].micliente.nombre : miwhats[index].desde                                    
                        $("#misocket").append("<div class='datestamp-container'><span class='datestamp'>"+micliente+"</span></div>")
                        break;
                    case "chat_group":
                        var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.nombre : miwhats[index].author;
                        var migrupo = miwhats[index].grupo ? miwhats[index].grupo.nombre : miwhats[index].desde
                        $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+migrupo+" | "+miauthor+"</span></div>")
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
                    $("#misocket").append("<div class='chat-message-group'><div class='chat-message'>"+messages+"<span class='chat-message-time'>"+miwhats[index].fwhats+"</span></div></div>") 

                }
                $("#misocket").append("<hr style='border-top: 1px solid #2D353E;'>")
            }
            $("#misocket").prepend("<hr style='border-top: 1px solid #2D353E;'>")
            $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'>Mostrando los ultimos "+miwhats.length+" registros del dia de hoy</div></div>") 

 $(".miselectevent").change(function () {
  console.log('mierda')
Swal.fire({
  title: 'Error!',
  text: 'Do you want to continue',
  icon: 'error',
  confirmButtonText: 'Cool'
})
});
                                                                   
        }


                                                                   
    </script>
@stop