const qrcode = require('qrcode-terminal');
const axios = require('axios');
var qr = require('qr-image');
var path = require('path');
const { Client, LocalAuth, MessageMedia} = require("whatsapp-web.js");
require('dotenv').config({ path: '../../.env' })
const fs = require("fs");
const cors = require('cors')
const express = require('express');
var sessionstorage = require('sessionstorage');

const YTDlpWrap = require('yt-dlp-wrap').default;
const ytDlpWrap = new YTDlpWrap('/usr/local/bin/yt-dlp');

const app = express();
app.use(express.json())
app.use(cors())


app.listen(process.env.API_PORT, async () => {
    sessionstorage.clear()
    await axios.post(process.env.APP_API+'reset')
    console.log('iniciando el serverbot...')
    console.log('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.API_PORT);
});

//------------API-----------------
//----------------------------------

app.get('/', async (req, res) => {
    res.send('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.API_PORT)
});

app.post('/init', async (req, res) => {
    console.log(req.query)

    const wbot = new Client({
        authStrategy: new LocalAuth({
            clientId: req.query.nombre
        }),
        puppeteer: {
            executablePath: '/usr/bin/google-chrome-stable',
            args: ['--no-sandbox']
        }
    });
    
    wbot.on('qr', async (qrwb) => {
        console.log('-----------------'+req.query.nombre+'----------------')
        if (!fs.existsSync('../storage/qr')){
            fs.mkdirSync('../storage/qr');
        }
        let r = (Math.random() + 1).toString(36).substring(3);
        var qr_svg = qr.image(qrwb, { type: 'png' });
        qr_svg.pipe(require('fs').createWriteStream('../storage/qr/'+r+'.png'));

        await axios.post(process.env.APP_API+'evento', {
            'mensaje': 'Escanea el nuevo QR',
            'tipo': 'qr',
            'bot': req.query.codigo,
            'file': 'qr/'+r+'.png'
        })
        sessionstorage.setItem(req.query.nombre, wbot)
    });

    wbot.on("authenticated", async session => {
        console.log('authenticated');
        // console.log('-----------------authenticated-----------------')
        try {
            await axios.post(process.env.APP_API+'estado', {
                'whatsapp': req.query.codigo,
                'estado': true
            })
        } catch (error) {
            console.log(error)
        }
        // console.log('-----------------authenticated-----------------')
    });
    
    wbot.on('auth_failure', async msg => {
        // Fired if session restore was unsuccessful
        console.error('AUTHENTICATION FAILURE', msg);
        // var miwbot = sessionstorage.getItem(req.query.nombre)
        wbot.destroy()
        sessionstorage.removeItem(req.query.nombre)
        await axios.post(process.env.APP_API+'estado', {
            'bot': req.query.codigo,
            'estado': false
        })
        await axios.post(process.env.APP_API+'evento', {
            'clase': 'input',
            'tipo': 'destroy',
            'bot': req.query.codigo,
            'mensaje': 'El bot '+req.query.nombre+', fue eliminado'
        })
        fs.rmSync('.wwebjs_auth/session-'+req.query.nombre, { recursive: true, force: true }); 
    });

    wbot.on('ready', async () => {
        // console.log('-----------------ready-----------------')
        console.log('ready');
        try {
            await axios.post(process.env.APP_API+'evento', {
                'mensaje': 'Bot '+req.query.nombre+' esta linea',
                'tipo': 'ready',
                'bot': req.query.codigo
            })
            await axios.post(process.env.APP_API+'estado', {
                'bot': req.query.codigo,
                'estado': true
            })
            sessionstorage.setItem(req.query.nombre, wbot)
        } catch (error) {
            console.log(error)
        }
        // console.log('-----------------ready-----------------')
    });

    wbot.on('message', async (msg) => {
        const chat = await msg.getChat();
        var mitipo = null
        var miauthor = null

        console.log(req.query)
        try {
            if (msg.from != "status@broadcast") {                       
                var misubtype = chat.lastMessage ? chat.lastMessage._data.subtype : null 
                if(chat.isGroup) {
                    mitipo = 'chat_group'
                    miauthor = chat.lastMessage ? chat.lastMessage.author : null
                }else{       
                    mitipo = 'chat_private'
                    miauthor = null
                }
        
                if(msg.hasMedia) {    
                    const media = await msg.downloadMedia(); 
                    if (media) {           
                            
                        let r = (Math.random() + 1).toString(36).substring(3);
                        let mifile = null   
                        
                        const imgBuffer = Buffer.from(media.data, 'base64');
                        if (!fs.existsSync('../storage/'+req.query.nombre)){
                            fs.mkdirSync('../storage/'+req.query.nombre);
                        }

                        switch (media.mimetype) {
                            case 'image/jpeg':
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.jpeg', imgBuffer);
                                mifile = req.query.nombre+'/'+r+'.jpeg'
                                break;
                            case 'image/webp':
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.webp', imgBuffer);
                                mifile = req.query.nombre+'/'+r+'.webp'
                                break;
                            case 'video/mp4':
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.mp4', imgBuffer);
                                mifile = req.query.nombre+'/'+r+'.mp4'
                                break;
                            case 'audio/ogg; codecs=opus':
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.ogg', imgBuffer);
                                mifile = req.query.nombre+'/'+r+'.ogg'
                                break;
                            case 'audio/mp4':
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.mp4', imgBuffer);
                                mifile = req.query.nombre+'/'+r+'.mp4'
                                break;
                            case 'application/zip':
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.zip', imgBuffer);
                                mifile = req.query.nombre+'/'+r+'.zip'
                                break;
                            case 'application/pdf':
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.pdf', imgBuffer);
                                mifile = req.query.nombre+'/'+r+'.pdf'
                                break;
                            default:
                                
                                break;
                        }
                
                        await axios.post(process.env.APP_API+'evento', {
                            'clase': 'input',
                            'mensaje': msg.body,
                            'tipo': 'chat_multimedia',
                            'bot': req.query.codigo,
                            'desde': msg.from,
                            'file': mifile,
                            'extension': media.mimetype,
                            'subtipo': mitipo,
                            'author': miauthor,
                            'subtype': misubtype,
                            'whatsapp': msg.timestamp
                        })
                    }
                }else if(msg.location){
                    const imgBuffer = Buffer.from(msg.body, 'base64');
                    const r = (Math.random() + 1).toString(36).substring(9);
                    fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.jpeg', imgBuffer);
                    var mifile = req.query.nombre+'/'+r+'.jpeg'
                    await axios.post(process.env.APP_API+'evento', {
                        'clase': 'input',
                        'tipo': 'chat_location',
                        'datos': msg.location,
                        'bot': req.query.codigo,
                        'desde': msg.from,
                        'file': mifile,
                        'extension': 'image/jpeg',
                        'subtipo': mitipo,
                        'whatsapp': msg.timestamp
                    })
                }else{
                    await axios.post(process.env.APP_API+'evento', {
                        'clase': 'input',
                        'mensaje': msg.body,
                        'tipo': mitipo,
                        'bot': req.query.codigo,
                        'desde': msg.from,
                        'author': miauthor,
                        'subtype': misubtype,
                        'whatsapp': msg.timestamp
                    })
                }
            }


            if(msg.fromMe){
                console.log(msg)
            }
        } catch (error) {
            console.log(error)   
            await axios.post(process.env.APP_API+'evento', {
                'clase': 'input',
                'tipo': 'error',
                'bot': req.query.codigo,
                'desde': msg.from,
                'whatsapp': msg.timestamp,
                'mensaje': error,
            })
        }
    });

    wbot.on('message_create', async (msg) => {
     
        try { 
            if (msg.from == "status@broadcast") {
                if (msg.hasMedia ) {                        
                    const media = await msg.downloadMedia(); 
                    if (media) {   
                        let r = (Math.random() + 1).toString(36).substring(3);
                        let mifile = null  
                        const imgBuffer = Buffer.from(media.data, 'base64');
                        if (!fs.existsSync('../storage/'+req.query.nombre)){
                            fs.mkdirSync('../storage/'+req.query.nombre);
                        }
                        switch (media.mimetype) {
                            case 'image/jpeg':
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.jpeg', imgBuffer);
                                mifile = req.query.nombre+'/'+r+'.jpeg'
                                break;
                            case 'image/webp':
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.webp', imgBuffer);
                                mifile = req.query.nombre+'/'+r+'.webp'
                                break;
                            case 'video/mp4':
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.mp4', imgBuffer);
                                mifile = req.query.nombre+'/'+r+'.mp4'
                                break;
                            default:
                                break;
                        }
                        await axios.post(process.env.APP_API+'evento', {
                            'clase': 'input',
                            'mensaje': msg.body,
                            'tipo': 'chat_multimedia',
                            'subtipo': 'status',
                            'file': mifile,
                            'bot': req.query.codigo,
                            'desde': msg.author,
                            'author': msg.author,
                            'whatsapp': msg.timestamp,
                            'extension': media.mimetype
                        })
                    }
                }else{
                    // console.log(msg)
                }
            }else if(msg.fromMe){
                console.log("para mi")
            }

            //--------------- misms ---------------
            if (msg.fromMe) {
                if (msg.hasMedia ) {                        
                    const media = await msg.downloadMedia(); 
                    let r = (Math.random() + 1).toString(36).substring(3);
                    let mifile = null            
                    var mimediadata = media ? media.data : null
                    const imgBuffer = Buffer.from(mimediadata, 'base64');
                    if (!fs.existsSync('../storage/'+req.query.nombre)){
                        fs.mkdirSync('../storage/'+req.query.nombre);
                    }
                    switch (media.mimetype) {
                        case 'image/jpeg':
                            fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.jpeg', imgBuffer);
                            mifile = req.query.nombre+'/'+r+'.jpeg'
                            break;
                        case 'image/webp':
                            fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.webp', imgBuffer);
                            mifile = req.query.nombre+'/'+r+'.webp'
                            break;
                        case 'video/mp4':
                            fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.mp4', imgBuffer);
                            mifile = req.query.nombre+'/'+r+'.mp4'
                            break;
                        default:
                            break;
                    }
                    await axios.post(process.env.APP_API+'evento', {
                        'clase': 'output',
                        'mensaje': msg.body,
                        'tipo': 'chat_multimedia',
                        'file': mifile,
                        'bot': req.query.codigo,
                        'whatsapp': msg.timestamp,
                        'extension': media.mimetype,
                        'desde': req.query.codigo,
                        'author': req.query.codigo
                    })
                }else{
                    await axios.post(process.env.APP_API+'evento', {
                        'clase': 'output',
                        'mensaje': msg.body,
                        'tipo': 'chat_private',
                        'bot': req.query.codigo,
                        'whatsapp': msg.timestamp,
                        'desde': req.query.codigo,
                        'author': req.query.codigo,
                    })
                }

            }
        } catch (error) {
            console.log(error)        
        }
    });

    await axios.post(process.env.APP_API+'evento', {
        'clase': 'input',
        'tipo': 'init',
        'bot': req.query.codigo,
        'mensaje': 'Iniciando el BOT: '+req.query.nombre+", espere un monento..",
    })

    wbot.initialize();
    res.send(true)
});


app.post('/stop', async (req, res) => {
    try {        
        var miwbot = sessionstorage.getItem(req.query.nombre)
        miwbot.destroy()
        sessionstorage.removeItem(req.query.nombre)
        await axios.post(process.env.APP_API+'estado', {
            'bot': req.query.codigo,
            'estado': false
        })
        await axios.post(process.env.APP_API+'evento', {
            'clase': 'input',
            'tipo': 'destroy',
            'bot': req.query.codigo,
            'mensaje': 'El bot '+req.query.nombre+', fue eliminado'
        })
        fs.rmSync('.wwebjs_auth/session-'+req.query.nombre, { recursive: true, force: true }); 
        console.log('El bot '+req.query.nombre+', fue eliminado')
    } catch (error) {
        console.log(error)        
    }
    res.send(true)
});


app.post('/getContactById', async (req, res) => {
    console.log(req.query)
    try {    
        var miwbot = sessionstorage.getItem(req.query.nombre)
        const ch = await miwbot.getChatById(req.query.contacto);
        console.log("chat here", ch);
        res.send(true)
    } catch (error) {
            console.log(error)
    }
    res.send(true)
});

app.post('/contactos', async (req, res) => {
    console.log(req.query)
    
    try {            
        var miwbot = sessionstorage.getItem(req.query.nombre)
        const contacts = await miwbot.getContacts();
        for (let index = 0; index < contacts.length; index++) {                                 
            if (contacts[index].isMyContact) {                                         
                await axios.post(process.env.APP_API+'contactos', {
                    'midata': contacts[index],
                    'bot': req.query.codigo,
                    '_id': contacts[index].id,
                    'number': contacts[index].number,
                    'avatar': null,
                    'tipo': 'contactos',
                    'user_id': req.query.user_id
                })   

                const url = await contacts[index].getProfilePicUrl();
                if (url) {
                    const response = await axios.get(url, { responseType: 'arraybuffer' })
                    let r = (Math.random() + 1).toString(36).substring(3)
                    var mifile = '../storage/'+req.query.nombre+'/'+r+'.jpeg'
                    if (!fs.existsSync('../storage/'+req.query.nombre)){
                        fs.mkdirSync('../storage/'+req.query.nombre);
                    }
                    fs.writeFile(mifile, response.data, (err) => {
                        if (err) throw err;
                        console.log('Image downloaded successfully!');
                    });
                    await axios.post(process.env.APP_API+'contactos', {
                        'midata': contacts[index],
                        'bot': req.query.codigo,
                        '_id': contacts[index].id,
                        'number': contacts[index].number,
                        'avatar': req.query.nombre+'/'+r+'.jpeg',
                        'tipo': 'contactos',
                        'codigo': contacts[index].id._serialized,
                        'user_id': req.query.user_id
                    })   
                }          
            }
        }
    } catch (error) {
        console.log(error)
    }
    res.send(true)
});

app.post('/historial', async (req, res) => {
    console.log(req.query)    
    try {    
        var miwbot = sessionstorage.getItem(req.query.nombre)
        const historial = await miwbot.getChats();        
        for (let index = 0; index < historial.length; index++) {             
            if (historial[index].isGroup) {
                // console.log(historial[index])
                var midata = await axios.post(process.env.APP_API+'grupos', {
                    // 'midata': historial[index],
                    'name': historial[index].name,
                    'bot': req.query.codigo,
                    'codigo': historial[index].groupMetadata.id._serialized,
                    '_id': historial[index].id,
                    'groupMetadata': historial[index].groupMetadata,
                    'lastMessage': historial[index].lastMessage,
                    'isReadOnly': historial[index].isReadOnly,
                    'isMuted': historial[index].isMuted ? true : false,
                    'tipo': 'grupos',
                    'owner': historial[index].groupMetadata.owner,
                    'desc': historial[index].groupMetadata.desc,
                    'creation': historial[index].groupMetadata.creation,
                    'user_id': req.query.user_id
                })   
                console.log(midata.data)
            }
        }
        // console.log(midata.data)
    } catch (error) {
        console.log(error)
    }
    res.send(true)
});


let micount = 0
app.post('/send', async (req, res)=>{
    console.log(req.body)

    try {
        var miwbot = sessionstorage.getItem(req.body.slug)
        var phone = req.body.phone
        var message = req.body.message
        miwbot.sendMessage(phone, message).then(() => {
            console.log("si se envio el chat")
        }).catch(() => {
            micount++
            console.log("no se envio el chat")
        })       

        if (micount > 2) {
            micount = 0
            fs.rmSync('.wwebjs_auth/session-'+req.body.bot, { recursive: true, force: true }); 
            await axios.post(process.env.APP_API+'estado', {
                'bot': req.query.bot,
                'estado': false
            })
        }

    } catch (error) {
        console.log(error)
    }             
    res.send(true)       
})


app.post('/template', async (req, res)=>{
    console.log(req.body)

    try {

        var miwbot = sessionstorage.getItem(req.body.bot)
        var misend = false 
        stats = { size: 0 }
        if (miwbot) {    
            // grupos            
            for (let index = 0; index < req.body.grupos.length; index++) {
                if (req.body.multimedia) {                    
                    stats = fs.statSync('../storage/'+req.body.multimedia); 
                    const media = MessageMedia.fromFilePath('../storage/'+req.body.multimedia)
                    await miwbot.sendMessage(req.body.grupos[index], media, {caption: req.body.message}).then(() => {
                        misend = true
                        console.log("size: "+stats.size)
                    }).catch(() => {
                        misend = false
                        micount++
                        console.log("no se envio el chat")
                    })
                }else{
                    await miwbot.sendMessage(req.body.grupos[index], req.body.message).then(() => {
                        misend = true
                        console.log("si se envio el chat")
                    }).catch(() => {
                        misend = false
                        micount++
                        console.log("no se envio el chat")
                    })
                }
            }

            //contactos
            for (let index = 0; index < req.body.contactos.length; index++) {
                if (req.body.multimedia) {   
                    const media = MessageMedia.fromFilePath('../storage/'+req.body.multimedia)
                    await miwbot.sendMessage(req.body.contactos[index], media, {caption: req.body.message}).then(() => {
                        misend = true
                        console.log("size: "+stats.size)
                    }).catch(() => {
                        misend = false
                        micount++
                        console.log("no se envio el chat")
                    })
                }else{
                    await miwbot.sendMessage(req.body.contactos[index], req.body.message).then(() => {
                        misend = true
                        console.log("si se envio el chat")
                    }).catch(() => {
                        misend = false
                        micount++
                        console.log("no se envio el chat")
                    })
                }
            }

            
            //actualizar plantilla
            await axios.post(process.env.APP_API+'template/update', {
                'id': req.body.id,
                'send': misend,
                'size': stats.size
            })
 
            if (micount >= 2) {
                console.log("eliminado .."+micount)
                micount = 0
                miwbot.destroy()
                fs.rmSync('.wwebjs_auth/session-'+req.body.bot, { recursive: true, force: true }); 
                await axios.post(process.env.APP_API+'estado', {
                    'bot': req.body.codigo,
                    'estado': false
                })
            }
        }
    } catch (error) {
        console.log(error)
    }             
    res.send(true)        
})

//------------YT-DLP-----------------
//----------------------------------

app.post('/download', async (req, res) => {
    console.log(req.body)

    try {        
        if (!fs.existsSync('../storage/'+req.body.name)){
            fs.mkdirSync('../storage/'+req.body.name);
        }

        let stdout = await ytDlpWrap.execPromise([
            req.body.url,
            '-f',
            'best[ext=mp4]',
            '-o',
            '../storage/'+req.body.name+'/'+req.body.slug+'.mp4',
        ]);        
        console.log(stdout);

        var miwbot = sessionstorage.getItem(req.body.bot)
        if (miwbot) {
            var stats = fs.statSync('../storage/'+req.body.name+'/'+req.body.slug+'.mp4');  
            var misend = false                  
            // grupos
            for (let index = 0; index < req.body.grupos.length; index++) {                
                const media = MessageMedia.fromFilePath('../storage/'+req.body.name+'/'+req.body.slug+'.mp4')
                await miwbot.sendMessage(req.body.grupos[index], media, {caption: req.body.message}).then(() => {
                    misend = true
                    console.log("size: "+stats.size)
                }).catch(() => {
                    micount++
                    misend = false
                    console.log("no se envio el chat | intentos "+micount)
                    console.log(error)
                })
    
            }

            //contactos
            for (let index = 0; index < req.body.contactos.length; index++) {
                const media = MessageMedia.fromFilePath('../storage/'+req.body.name+'/'+req.body.slug+'.mp4')
                await miwbot.sendMessage(req.body.contactos[index], media, {caption: req.body.message}).then(() => {
                    misend = true
                    console.log("size: "+stats.size)
                }).catch((error) => {
                    micount++
                    misend = false                    
                    console.log("no se envio el chat | intentos "+micount)
                    console.log(error)
                })
            }

            //actualizar descarga
            await axios.post(process.env.APP_API+'download/update', {
                'slug': req.body.slug,
                'file': req.body.name+'/'+req.body.slug+'.mp4',
                'send': misend,
                'size': stats.size
            })

            if (micount > 2) {
                console.log("eliminado .."+micount)
                micount = 0
                miwbot.destroy()
                fs.rmSync('.wwebjs_auth/session-'+req.body.bot, { recursive: true, force: true }); 
                await axios.post(process.env.APP_API+'estado', {
                    'bot': req.body.codigo,
                    'estado': false
                })
                
            }
      
        }
    } catch (error) {
        console.log(error)
    }
    res.send(true)
});