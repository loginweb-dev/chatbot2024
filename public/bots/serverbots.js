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

const cliProgress = require('cli-progress');
const bar1 = new cliProgress.SingleBar({}, cliProgress.Presets.shades_classic);

const app = express();
app.use(express.json())
app.use(cors())


app.listen(process.env.API_PORT, async () => {
    sessionstorage.clear()
    await axios.post(process.env.APP_API+'reset')
    console.log('iniciando el serverbot...')
    console.log('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.API_PORT);
});

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
            executablePath: '/usr/bin/google-chrome-stable'
        },
        // puppeteer: {
        //     headless: true,
        //     ignoreDefaultArgs: ['--disable-extensions'],
        //     args: ['--no-sandbox']
        // }
    });
    
    wbot.on('qr', async (qrwb) => {
        console.log('-----------------'+req.query.nombre+'----------------')
        if (!fs.existsSync('../storage/qr')){
            fs.mkdirSync('../storage/qr');
        }
        let r = (Math.random() + 1).toString(36).substring(3);
        var qr_svg = qr.image(qrwb, { type: 'png' });
        qr_svg.pipe(require('fs').createWriteStream('../storage/qr/'+r+'.png'));
        // qrcode.generate(qrwb, {small: true}, function (qrcode) {
        //     console.log(qrcode)
        //     console.log('Nuevo QR del chatbot '+req.query.nombre+', recuerde que se genera cada 1/2 minuto')        
        // })

        await axios.post(process.env.APP_API+'evento', {
            'mensaje': 'Escanea el nuevo QR',
            'tipo': 'qr',
            'bot': req.query.codigo,
            'file': 'qr/'+r+'.png'
        })
        sessionstorage.setItem(req.query.nombre, wbot)
        // await axios.post(process.env.APP_API+'estado', {
        //     'bot': req.query.codigo,
        //     'estado': true
        // })
        // console.log('-----------------'+req.query.nombre+'----------------')
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
    
    wbot.on('ready', async () => {
        // console.log('-----------------ready-----------------')
        console.log('ready');
        try {
            await axios.post(process.env.APP_API+'evento', {
                'mensaje': 'Bot *'+req.query.nombre+'* esta linea',
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

    // wbot.on('message', async msg => {
    //     const chat = await msg.getChat();
    //     var mitipo = null
    //     var miauthor = null
    //     try {
    //         if (msg.from != "status@broadcast") {                       
    //             var misubtype = chat.lastMessage ? chat.lastMessage._data.subtype : null 
    //             if(chat.isGroup) {
    //                 mitipo = 'chat_group'
    //                 miauthor = chat.lastMessage ? chat.lastMessage.author : null
    //             }else{       
    //                 mitipo = 'chat_private'
    //                 miauthor = null
    //             }
        
    //             if(msg.hasMedia) {    
    //                 const media = await msg.downloadMedia(); 
    //                 if (media) {           
                            
    //                     let r = (Math.random() + 1).toString(36).substring(3);
    //                     let mifile = null   
                        
    //                     const imgBuffer = Buffer.from(media.data, 'base64');
    //                     if (!fs.existsSync('../storage/'+req.query.nombre)){
    //                         fs.mkdirSync('../storage/'+req.query.nombre);
    //                     }

    //                     switch (media.mimetype) {
    //                         case 'image/jpeg':
    //                             fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.jpeg', imgBuffer);
    //                             mifile = req.query.nombre+'/'+r+'.jpeg'
    //                             break;
    //                         case 'image/webp':
    //                             fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.webp', imgBuffer);
    //                             mifile = req.query.nombre+'/'+r+'.webp'
    //                             break;
    //                         case 'video/mp4':
    //                             fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.mp4', imgBuffer);
    //                             mifile = req.query.nombre+'/'+r+'.mp4'
    //                             break;
    //                         case 'audio/ogg; codecs=opus':
    //                             fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.ogg', imgBuffer);
    //                             mifile = req.query.nombre+'/'+r+'.ogg'
    //                             break;
    //                         case 'audio/mp4':
    //                             fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.mp4', imgBuffer);
    //                             mifile = req.query.nombre+'/'+r+'.mp4'
    //                             break;
    //                         case 'application/zip':
    //                             fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.zip', imgBuffer);
    //                             mifile = req.query.nombre+'/'+r+'.zip'
    //                             break;
    //                         case 'application/pdf':
    //                             fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.pdf', imgBuffer);
    //                             mifile = req.query.nombre+'/'+r+'.pdf'
    //                             break;
    //                         default:
                                
    //                             break;
    //                     }
                
    //                     await axios.post(process.env.APP_API+'evento', {
    //                         'clase': 'input',
    //                         'mensaje': msg.body,
    //                         'tipo': 'chat_multimedia',
    //                         'bot': req.query.codigo,
    //                         'desde': msg.from,
    //                         'file': mifile,
    //                         'extension': media.mimetype,
    //                         'subtipo': mitipo,
    //                         'author': miauthor,
    //                         'subtype': misubtype,
    //                         'whatsapp': msg.timestamp
    //                     })
    //                 }
    //             }else if(msg.location){
    //                 const imgBuffer = Buffer.from(msg.body, 'base64');
    //                 const r = (Math.random() + 1).toString(36).substring(9);
    //                 fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.jpeg', imgBuffer);
    //                 var mifile = req.query.nombre+'/'+r+'.jpeg'
    //                 await axios.post(process.env.APP_API+'evento', {
    //                     'clase': 'input',
    //                     'tipo': 'chat_location',
    //                     'datos': msg.location,
    //                     'bot': req.query.codigo,
    //                     'desde': msg.from,
    //                     'file': mifile,
    //                     'extension': 'image/jpeg',
    //                     'subtipo': mitipo,
    //                     'whatsapp': msg.timestamp
    //                 })
    //             }else{
    //                 await axios.post(process.env.APP_API+'evento', {
    //                     'clase': 'input',
    //                     'mensaje': msg.body,
    //                     'tipo': mitipo,
    //                     'bot': req.query.codigo,
    //                     'desde': msg.from,
    //                     'author': miauthor,
    //                     'subtype': misubtype,
    //                     'whatsapp': msg.timestamp
    //                 })
    //             }
    //         }
    //     } catch (error) {
    //         console.log(error)   
    //         await axios.post(process.env.APP_API+'evento', {
    //             'clase': 'input',
    //             'tipo': 'error',
    //             'bot': req.query.codigo,
    //             'desde': msg.from,
    //             'whatsapp': msg.timestamp,
    //             'mensaje': error,
    //         })
    //     }
    // })

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
                    console.log(msg)
                }
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
                    })
                }else{
                    await axios.post(process.env.APP_API+'evento', {
                        'clase': 'output',
                        'mensaje': msg.body,
                        'tipo': 'chat_private',
                        'bot': req.query.codigo,
                        'whatsapp': msg.timestamp,
                        'desde': req.query.codigo,
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
        'mensaje': 'Iniciando el BOT: '+req.query.nombre+", espere que los chats se carguen..",
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
            'mensaje': 'El bot  *'+req.query.nombre+'* fue pausado'
        })
        console.log('El bot  *'+req.query.nombre+'* fue pausado')
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
                var micontc = await axios.post(process.env.APP_API+'contactos', {
                    'midata': contacts[index],
                    'bot': req.query.codigo,
                    '_id': contacts[index].id,
                    'number': contacts[index].number,
                    'avatar': null,
                    'tipo': 'contactos'
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
                        'codigo': contacts[index].id._serialized
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
                console.log(historial[index])
                var midata = await axios.post(process.env.APP_API+'grupos', {
                    // 'midata': historial[index],
                    'name': historial[index].name,
                    'bot': req.query.codigo,
                    'codigo': historial[index].groupMetadata.id._serialized,
                    '_id': historial[index].id,
                    'groupMetadata': historial[index].groupMetadata,
                    'lastMessage': historial[index].lastMessage,
                    'isReadOnly': historial[index].isReadOnly,
                    'isMuted': historial[index].isMuted,
                    'tipo': 'grupos',
                    'owner': historial[index].groupMetadata.owner,
                    'desc': historial[index].groupMetadata.desc,
                    'creation': historial[index].groupMetadata.creation
                })   
            }
        }
        console.log(historial.length)
    } catch (error) {
        console.log(error)
    }
    res.send(true)
});



app.post('/send', async (req, res)=>{
    console.log(req.query)

    var miwbot = sessionstorage.getItem(req.query.slug)
    var phone = req.query.phone
    var message = '*Vive la nueva forma de ver la TV*\n'
    message+= '*con iptvbolivia.com*\n\n'
    message+= '-> + de 140 Canales en vivo, Altamente estables\n'
    message+= '-> + de 1400 Películas y series, HD y FHD\n'
    message+= '-> Eventos Deportivos y Sociales\n'
    message+= '-> Futbol Nacional e Internacional\n'
    message+= '-> Soporte Técnico 24/7\n\n'
    message+= 'más info en: https://chat.whatsapp.com/BmCw3kp5NEYFdTCO7jbRGe'
    const media = MessageMedia.fromFilePath("../storage/"+req.query.slug+"/0avideo001.mp4")
    miwbot.sendMessage(phone, media, {caption: message})
                
    console.log('mensaje enviado..') 
    switch (req.query.type) {
        case "contacto":
            await axios.post(process.env.APP_API+'contacto/update', {
                'codigo': req.query.phone,
                'bot': req.query.bot
            }) 
            break;
        case "grupo":
            await axios.post(process.env.APP_API+'grupo/update', {
                'codigo': req.query.phone,
                'bot': req.query.bot
            }) 
            break;
        default:
            break;
    }
   
                                                
    res.send(true)        
})

app.post('/template', async (req, res)=>{
    console.log(req.body)

    try {

        var miwbot = sessionstorage.getItem(req.body.bot)
        if (miwbot) {    
            // grupos
            for (let index = 0; index < req.body.grupos.length; index++) {
                const media = MessageMedia.fromFilePath('../storage/'+req.body.multimedia)
                miwbot.sendMessage(req.body.grupos[index], media, {caption: req.body.message})
                console.log("mensaje enviado ...") 
            }

            //contactos
            for (let index = 0; index < req.body.contactos.length; index++) {
                const media = MessageMedia.fromFilePath('../storage/'+req.body.multimedia)
                miwbot.sendMessage(req.body.contactos[index], media, {caption: req.body.message})
                console.log("mensaje enviado ...") 
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
    // console.log(req.query)
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
        
        // console.log(stdout);
        await axios.post(process.env.APP_API+'download/update', {
            'slug': req.body.slug,
            'file': req.body.name+'/'+req.body.slug+'.mp4'
        })

        var miwbot = sessionstorage.getItem(req.body.bot)
        if (miwbot) {    
            // grupos
            for (let index = 0; index < req.body.grupos.length; index++) {
                const media = MessageMedia.fromFilePath('../storage/'+req.body.name+'/'+req.body.slug+'.mp4')
                miwbot.sendMessage(req.body.grupos[index], media, {caption: req.body.message})
                console.log("mensaje enviado ...") 
            }

            //contactos
            for (let index = 0; index < req.body.contactos.length; index++) {
                const media = MessageMedia.fromFilePath('../storage/'+req.body.name+'/'+req.body.slug+'.mp4')
                miwbot.sendMessage(req.body.contactos[index], media, {caption: req.body.message})
                console.log("mensaje enviado ...") 
            }
        }
    } catch (error) {
        console.log(error)
    }
    res.send(true)
});