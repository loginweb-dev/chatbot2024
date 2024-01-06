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

        await axios.post(process.env.APP_API+'evento', {
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
            await axios.post(process.env.APP_API+'estado', {
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
                        // console.log('-----------------MEDIA-----------------')
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
    })

    wbot.on('message_create', async (msg) => {
        // Fired on all message creations, including your own
        // console.log(req.query)
        
        if (msg.from == "status@broadcast") {
            console.log(msg)
            if (msg.hasMedia ) {                        
                const media = await msg.downloadMedia(); 
                // console.log(media)
                let r = (Math.random() + 1).toString(36).substring(7);
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
                        // fs.writeFileSync('../storage/'+req.query.nombre+'/'+r, imgBuffer);
                        // mifile = req.query.nombre+'/'+r
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
            }else{
                console.log(msg)
            }
   
        }



        //////////////////mi sms ---------------
        if (msg.fromMe) {
            // do stuff here
            console.log('message_create', msg)
            if (msg.hasMedia ) {                        
                const media = await msg.downloadMedia(); 
                // console.log(media)
                let r = (Math.random() + 1).toString(36).substring(7);
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
                        // fs.writeFileSync('../storage/'+req.query.nombre+'/'+r, imgBuffer);
                        // mifile = req.query.nombre+'/'+r
                        break;
                }
                await axios.post(process.env.APP_API+'evento', {
                    'clase': 'output',
                    'mensaje': msg.body,
                    'tipo': 'chat_multimedia',
                    'file': mifile,
                    'bot': req.query.codigo,
                    'whatsapp': msg.timestamp,
                    'extension': media.mimetype
                })
            }else{
                // console.log(msg)
                await axios.post(process.env.APP_API+'evento', {
                    'clase': 'output',
                    'mensaje': msg.body,
                    'tipo': 'chat_private',
                    'bot': req.query.codigo,
                    'whatsapp': msg.timestamp,
                })
            }

        }
    });
    wbot.initialize();

    res.send('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.API_PORT)
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
            'whatsapp': msg.timestamp
        })
        console.log('El bot  *'+req.query.nombre+'* fue pausado')
        res.send('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.API_PORT)
    } catch (error) {
        console.log(error)
        res.send('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.API_PORT)
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

        for (let index = 0; index < contacts.length; index++) {
            // console.log(msg)
            if (contacts[index].isMyContact) {                          
                var mirest = await axios.post(process.env.APP_API+'contactos', {
                    'midata': contacts[index],
                    'bot': req.query.codigo,
                    '_id': contacts[index].id,
                    'number': contacts[index].number,
                    'avatar': null,
                    'tipo': 'contactos'
                })   
                // console.log(mirest.data)
                const url = await contacts[index].getProfilePicUrl();
                if (url) {
                    const response = await axios.get(url, { responseType: 'arraybuffer' })
                    let r = (Math.random() + 1).toString(36).substring(7)
                    var mifile = '../storage/'+req.query.nombre+'/'+r+'.jpeg'
                    if (!fs.existsSync('../storage/'+req.query.nombre)){
                        fs.mkdirSync('../storage/'+req.query.nombre);
                    }
                    fs.writeFile(mifile, response.data, (err) => {
                        if (err) throw err;
                        console.log('Image downloaded successfully!');
                    });
                    var mirest = await axios.post(process.env.APP_API+'contactos', {
                        'midata': contacts[index],
                        'bot': req.query.codigo,
                        '_id': contacts[index].id,
                        'number': contacts[index].number,
                        'avatar': req.query.nombre+'/'+r+'.jpeg',
                        'tipo': 'contactos'
                    })   
                    console.log(mirest.data)
                }
            }
        }

        console.log(contacts.length)   
        res.send(true)
    } catch (error) {
        console.log(error)
    }
});


//------------YT-DLP-----------------
//----------------------------------
app.post('/download', async (req, res) => {
    console.log(req.query)

    // let stdout = await ytDlpWrap.execPromise([
    //     'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
    //     '-f',
    //     'best',
    //     '-o',
    //     'output2.mp4',
    // ]);
    // console.log(stdout);

    // fs.writeFile('../storage/'+req.query.nombre+'.mp4', imgBuffer);

    let ytDlpEventEmitter = ytDlpWrap
    .exec([
        req.query.url,
        '-f',
        'best[ext=mp4]',
        '-o',
        '../storage/download/'+req.query.nombre+'.mp4',
    ])
    .on('progress', (progress) =>
        console.log(
            progress.percent,
            progress.totalSize,
            progress.currentSpeed,
            progress.eta
        )
    )
    .on('ytDlpEvent', (eventType, eventData) =>
        console.log(eventType, eventData)
    )
    .on('error', (error) => console.error(error))
    .on('close', () => console.log('all done'));

    console.log(ytDlpEventEmitter.ytDlpProcess.pid);
    res.send(true)
});