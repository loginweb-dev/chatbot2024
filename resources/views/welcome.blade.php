<!DOCTYPE html>
<html>
<head>
    <title>Laravel 8 Server Side Datatables Tutorial</title>
    
</head>
<body>



<script src="{{ asset('js/app.js') }}"></script>
<script>
	console.log('iniciando websocket ...')
	window.Echo.channel('messages')
    	.listen('MiEvent', (e) => {     
            var midata = e.message
            console.log(e.message)
    })
  	</script>
</body>
</html>