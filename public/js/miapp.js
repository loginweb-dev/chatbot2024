
// const audio = document.createElement("audio");
// audio.preload = "auto";
// audio.src = "https://manzdev.github.io/codevember2017/assets/eye-tiger.mp3";
// audio.play();
// document.body.appendChild(audio);
var beat = new Audio('/success.mp3');
console.log('iniciando websocket ...')
	window.Echo.channel('messages')
    	.listen('MiEvent', (e) => {     
          // toastr.success("nuevo mensaje..");
            var midata = e.message
            // var mimedia = e.message.midata
			// var beat = new Audio('/success.mp3');
			beat.play();
            switch (midata.type) {
				case 'chat':
					toastr.success('CHAT: '+midata.body+'\n From: '+midata.from+'\n Author: '+midata.author+'\n NotifyName: '+midata.notifyName+'\n Subtipo: '+midata.subtype);
					console.log(midata);
					// beat.play();
					break;
				case 'image':
					toastr.success('IMAGE: '+midata.caption+'\n From: '+midata.from+'\n Author: '+midata.author+'\n NotifyName: '+midata.notifyName);
					console.log(midata);
					break;
				case 'video':
					toastr.success('VIDEO: '+midata.caption+'\n From: '+midata.from+'\n Author: '+midata.author+'\n NotifyName: '+midata.notifyName);
					console.log(midata);
					break;
				case 'ptt':
					toastr.success('Audio: '+midata.caption+'\n From: '+midata.from+'\n Author: '+midata.author+'\n NotifyName: '+midata.notifyName);
					console.log(midata);
					break;
				case 'location':
					toastr.success('Location: '+midata.caption+'\n From: '+midata.from+'\n Author: '+midata.author+'\n NotifyName: '+midata.notifyName+'\n Lat: '+midata.lat+'\n Lng: '+midata.lng);
					console.log(midata);
					break;
				default:
					// console.log("---------------------------------");
					// console.log(midata);
					toastr.success(midata.body)
					console.log(e.message);
			}
			
    })