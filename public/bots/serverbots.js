const qrcode = require('qrcode-terminal');
const axios = require('axios');
var qr = require('qr-image');
var path = require('path');
const { Client, LocalAuth, MessageMedia} = require("whatsapp-web.js");
require('dotenv').config()
const fs = require("fs");
const cors = require('cors')
const express = require('express');
var sessionstorage = require('sessionstorage');


const { get } = require('request');
const { log } = require('console');

const app = express();
app.use(express.json())
app.use(cors())
// app.use(express.urlencoded({ extended: true }))

app.listen(process.env.PORT, async () => {
    sessionstorage.clear()
    await axios.post(process.env.DOMINIO+'reset')
    console.log('iniciando el serverbot...')
    console.log('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.PORT);
});

app.get('/', async (req, res) => {
    res.send('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.PORT)
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
        puppeteer: {
            headless: true,
            ignoreDefaultArgs: ['--disable-extensions'],
            args: ['--no-sandbox']
        }
    });
    

    wbot.on('qr', async (qrwb) => {
        console.log('-----------------'+req.query.nombre+'----------------')
        if (!fs.existsSync('../storage/qr')){
            fs.mkdirSync('../storage/qr');
        }
        var qr_svg = qr.image(qrwb, { type: 'png' });
        qr_svg.pipe(require('fs').createWriteStream('../storage/qr/'+req.query.nombre+'.png'));
        qrcode.generate(qrwb, {small: true}, function (qrcode) {
            console.log(qrcode)
            console.log('Nuevo QR del chatbot '+req.query.nombre+', recuerde que se genera cada 1/2 minuto')        
        })

        await axios.post(process.env.DOMINIO+'evento', {
            'mensaje': 'Escanea el nuevo QR',
            'tipo': 'qr',
            'bot': req.query.codigo,
            'file': 'qr/'+req.query.nombre+'.png'
        })
        console.log('-----------------'+req.query.nombre+'----------------')
    });

    wbot.on("authenticated", async session => {
        console.log('AUTHENTICATED');
        console.log('-----------------authenticated-----------------')
        try {
            await axios.post(process.env.DOMINIO+'estado', {
                'whatsapp': req.query.codigo,
                'estado': true
            })
        } catch (error) {
            console.log(error)
        }
        console.log('-----------------authenticated-----------------')
    });
    
    wbot.on('ready', async () => {
        console.log('-----------------ready-----------------')
        try {
            await axios.post(process.env.DOMINIO+'evento', {
                'mensaje': 'Bot *'+req.query.nombre+'* esta linea',
                'tipo': 'ready',
                'bot': req.query.codigo
            })
            await axios.post(process.env.DOMINIO+'estado', {
                'bot': req.query.codigo,
                'estado': true
            })
            sessionstorage.setItem(req.query.nombre, wbot)
        } catch (error) {
            console.log(error)
        }
        console.log('-----------------ready-----------------')
    });

    wbot.on('message', async msg => {
        const chat = await msg.getChat();
        var mitipo = null
        var miauthor = null
        try {
            // console.log(msg.from)
            if (msg.from != "status@broadcast") {       
                // console.log('message', chat)                   
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
                        // console.log('-----------------MEDIA-----------------')
                            
                        let r = (Math.random() + 1).toString(36).substring(7);
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
                                fs.writeFileSync('../storage/'+req.query.nombre+'/'+r, imgBuffer);
                                mifile = req.query.nombre+'/'+r
                                break;
                        }
                
                        await axios.post(process.env.DOMINIO+'evento', {
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
                        // console.log('-----------------MEDIA-----------------')
                    }
                }else if(msg.location){
                    const imgBuffer = Buffer.from(msg.body, 'base64');
                    const r = (Math.random() + 1).toString(36).substring(9);
                    fs.writeFileSync('../storage/'+req.query.nombre+'/'+r+'.jpeg', imgBuffer);
                    var mifile = req.query.nombre+'/'+r+'.jpeg'
                    await axios.post(process.env.DOMINIO+'evento', {
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
                    await axios.post(process.env.DOMINIO+'evento', {
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
        } catch (error) {
            console.log(error)   
            await axios.post(process.env.DOMINIO+'evento', {
                'clase': 'input',
                'tipo': 'error',
                'bot': req.query.codigo,
                'desde': msg.from,
                'whatsapp': msg.timestamp,
                'mensaje': error,
            })
        }
    })

    wbot.on('message_create', async (msg) => {
        // Fired on all message creations, including your own
        // console.log(req.query)
        
        if (msg.from == "status@broadcast") {
            console.log(msg)
            if (msg.hasMedia ) {
                
        
                const media = await msg.downloadMedia(); 
                console.log(media)
                let r = (Math.random() + 1).toString(36).substring(7);
                let mifile = null            
                var mimediadata = media.data ? media.data : null
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
                        fs.writeFileSync('../storage/'+req.query.nombre+'/'+r, imgBuffer);
                        mifile = req.query.nombre+'/'+r
                        break;
                }
                await axios.post(process.env.DOMINIO+'evento', {
                    'clase': 'input',
                    'mensaje': msg.body,
                    'tipo': 'chat_multimedia',
                    'subtipo': 'status',
                    'file': mifile,
                    'bot': req.query.codigo,
                    'desde': msg.author,
                    'author': msg.author,
                    'whatsapp': msg.timestamp
                })
            }else{
                console-log(msg)
            }
   
        }



        //////////////////mi sms ---------------
        if (msg.fromMe) {
            // do stuff here
            console.log('message_create', msg)
        }
    });
    wbot.initialize();

    res.send('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.PORT)
});



app.post('/stop', async (req, res) => {
    try {        
        var miwbot = sessionstorage.getItem(req.query.nombre)
        miwbot.destroy()
        sessionstorage.removeItem(req.query.nombre)
        await axios.post(process.env.DOMINIO+'estado', {
            'bot': req.query.codigo,
            'estado': false
        })
        console.log('El bot  *'+req.query.nombre+'* fue pausado')
        res.send('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.PORT)
    } catch (error) {
        console.log(error)
        res.send('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.PORT)
    }
});


app.post('/getContactById', async (req, res) => {
    try {    
        var miwbot = sessionstorage.getItem(req.query.nombre)
        const ch = await miwbot.getChatById(req.query.contacto);
        console.log("chat here", ch);
        res.send(true)
    } catch (error) {
            console.log(error)
    }
});



app.post('/contactos', async (req, res) => {
    console.log(req.query)
    try {    
        var miwbot = sessionstorage.getItem(req.query.nombre)
        const contacts = await miwbot.getContacts();
        // console.log(contacts)
        // var midata = await axios.post(process.env.DOMINIO+'contactos', {
        //     'datos': contacts,
        //     'bot': req.query.bot,
        // })
        for (let index = 0; index < 4000; index++) {
            // const element = array[index];
            
            
        }
        var midata = await axios.post(process.env.DOMINIO+'contactos', )
        console.log(midata.data)
        res.send(midata.data)
    } catch (error) {
        console.log(error)
    }
});

