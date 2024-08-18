console.log("Script is Link!!!");

var socket = "";
var connFlag = "";
var windowChat = [];

let globalPublicKeyPem = "";
let globalPrivateKeyPem = "";
let globalKeypair = "";

function connect(){
    password = document.getElementById('password').value;
    
    let content = document.getElementById("content");
    content.style.display = "block"; 

    content = document.getElementById("loginWindow");
    content.style.display = "none";

    getServerKey()
    .then(text => {
        var crypt = new JSEncrypt();

        console.log(text);
        let parser = new DOMParser();
        let xmlDoc = parser.parseFromString(text, "application/xml");
        let session = xmlDoc.getElementsByTagName("session")[0]?.textContent;


        let encMatch = text.match(/<enc>([\s\S]*?)<\/enc>/);

        let encContent = "";
        if (!encMatch) {
            console.error("Tag <enc> not found.");
        } else if (!encMatch[1]) {
            console.error("Tag <enc> is empty.");
        } else {
            encContent = encMatch[1];
            try {
                //const privateKey = forge.pki.privateKeyFromPem(globalPrivateKeyPem);
                var encryptedData = forge.util.decode64(encContent);
                var decryptedMessageAesKey = globalKeypair.privateKey.decrypt(encryptedData, 'RSA-OAEP');

                console.log("<enc>:", decryptedMessageAesKey);
            } catch (error) {
                console.error('Błąd odszyfrowania:', error);
                throw error;
            }
            console.log("<enc>:", decryptedMessageAesKey);
            
        }

        function encryptAES(password, keyHex) {
            const key = CryptoJS.enc.Hex.parse(keyHex);
            const iv = CryptoJS.lib.WordArray.random(16);
            const encrypted = CryptoJS.AES.encrypt(password, key, {
                iv: iv,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.Pkcs7
            });

            const ivCiphertext = iv.concat(encrypted.ciphertext).toString(CryptoJS.enc.Base64);
            
            return ivCiphertext;
        }

        const passwordEncrypted = encryptAES(password, decryptedMessageAesKey).toString();
        ws = "ws://enlightenment.xaa.pl:8081?room=" + encodeURIComponent(passwordEncrypted) + "&session=" + encodeURIComponent(session);
        //ws = "wss://enlightenment.xaa.pl:443?room=" + encodeURIComponent(passwordEncrypted) + "&session=" + encodeURIComponent(session);
        console.log(ws) 

        socket = new WebSocket(ws);

        socket.addEventListener("open", function(event) {        
            socket.send("<tb>backend219</tb>");
            connFlag = 1;
        });

        socket.addEventListener("message", function(event) {
            let response = event.data; 
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(response, "text/xml" );

            let instance = xmlDoc.getElementsByTagName("instance")[0].childNodes[0].nodeValue;
            console.log(instance);

            let clients = "";
            switch (instance){
                case ("you"):
                    let myId = xmlDoc.getElementsByTagName("id")[0].childNodes[0].nodeValue;
                    document.getElementById("my").innerHTML = myId;
                    break;

                case ("clients"):
                    let ids = xmlDoc.getElementsByTagName("id");
                    for (let i = 0; i < ids.length; i++) {
                        console.log(ids[i].childNodes[0].nodeValue);
                        clients += "<button class='cli btn-clients' onclick=\"getInner('" + ids[i].childNodes[0].nodeValue + "')\">";
                        clients += ids[i].childNodes[0].nodeValue + "</br>";
                        clients += "</button>";
                    }
                    document.getElementById("clients").innerHTML = clients;
                    break;

                case ("send"):
                    console.log("(send): " +response);   
                    break;

                case ("msg"):
                    let id = xmlDoc.getElementsByTagName("id")[0].childNodes[0].nodeValue;
                    let msg = xmlDoc.getElementsByTagName("msg")[0].childNodes[0].nodeValue;
                    console.log("(msg): " + response);
                    privKey = document.getElementById("myPrivKey").innerHTML;
                    crypt.setPrivateKey(privKey);
                    decryptedText = crypt.decrypt(msg);                    

                    var client = document.getElementById(id);
                    if(client){
                        //client.innerHTML += "<div>" + id + ": " + decryptedText + "</div>";
                        client.innerHTML += "<div class='cloud-msg'><span class='cloud-msg-header recive'>" + id + "</span><span class='cloud-msg-content'>" + decryptedText + "</span></div>";
                        windowChat.push(id);
                        scrollToBottom();
                    }else{
                        var messagesDiv = document.getElementById("messages");
                        if (messagesDiv) {
                            messagesDiv.innerHTML += "<div class='window-msg' id=" + id + "><div class='cloud-msg'><span class='cloud-msg-header'>" + id + "</span><span class='cloud-msg-content'>" + decryptedText + "</span></div></div>";
                        } else {
                            console.error('Element o id "messages" nie został znaleziony.');
                        }
                    }
                    break;

                case ("pls"):
                    clientId = xmlDoc.getElementsByTagName("mid")[0].childNodes[0].nodeValue;;
                    myPubKey = document.getElementById("myPubKey").innerHTML;
                    console.log("pls: " + clientId);
                    tb = "<tb>";
                    tb += "<instance>key</instance>";
                    tb += "<id>" + clientId + "</id>";
                    tb += "<msg>" + myPubKey + "</msg>";
                    tb += "<mid>" + clientId + "</mid>";
                    tb += "</tb>";
                    socket.send(tb);
                    break;

                case ("key"):
                    console.log(response);
                    let reciverPubKey = xmlDoc.getElementsByTagName("msg")[0].childNodes[0].nodeValue;
                    document.getElementById("reciverPubKey").innerHTML = reciverPubKey;
                    break;   

                default:
                    console.log(response);
                    break;
            }
        });

        socket.addEventListener("close", function(event) {
            console.log("conn closed");
            let content = document.getElementById("content");
            content.style.display = "none"; 

            content = document.getElementById("loginWindow");
            content.style.display = "block";
        });

        socket.addEventListener("error", function(event) {
            console.log("error");
        });
    })
    .catch(error => {
        console.error('Error getServerKey:', error);
    });    
}

function send(){
    clientId = document.getElementById("client").value;
    msg = document.getElementById("msg").value;
    my = document.getElementById("my").innerHTML;

    var crypt = new JSEncrypt();
    pubKey = document.getElementById("reciverPubKey").innerHTML
    crypt.setPublicKey(pubKey);
    var encryptedText = crypt.encrypt(msg);

    tb  = "<tb>";
    tb += "<instance>send</instance>";
    tb += "<id>" + clientId + "</id>";
    tb += "<msg>" + encryptedText + "</msg>";
    tb += "<mid>" + my + "</mid>";
    tb += "</tb>";
    socket.send(tb);

    var client = document.getElementById(clientId);
    if(client){
        client.innerHTML += "<div class='cloud-msg'><span class='cloud-msg-header'>" + my + "</span><span class='cloud-msg-content'>" + msg + "</span></div>";
        windowChat.push(clientId);
        scrollToBottom();
    }else{
        var messagesDiv = document.getElementById("messages");
        if (messagesDiv) {
            messagesDiv.innerHTML += "<div class='window-msg' id=" + clientId + "><div class='cloud-msg'><span class='cloud-msg-header'>" + clientId + "</span><span class='cloud-msg-content'>" + msg + "</span></div></div>";
        } else {
            console.error('Element o id "messages" nie został znaleziony.');
        }
    }
}

function plsKey(){
    myId  = document.getElementById("my").innerHTML;
    clientId = document.getElementById("client").value;
    tb  = "<tb>";
    tb += "<instance>pls_key</instance>";
    tb += "<id>" + clientId + "</id>";
    tb += "<msg>" + "" + "</msg>";
    tb += "<mid>" + myId + "</mid>";
    tb += "</tb>";
    socket.send(tb);
}

async function getServerKey() {
    try {
        // Inicjalizacja zmiennych lokalnych
        let publicKeyPem = "";
        let privateKeyPem = "";

        // Generowanie pary kluczy RSA
        var rsa = forge.pki.rsa;

        const generateKeyPair = () => {
            return new Promise((resolve, reject) => {
                rsa.generateKeyPair({ bits: 2048, e: 0x10001 }, (err, keypair) => {
                    if (err) {
                        reject(err);
                    } else {                        
                        publicKeyPem = forge.pki.publicKeyToPem(keypair.publicKey);
                        privateKeyPem = forge.pki.privateKeyToPem(keypair.privateKey);

                        console.log("Public Key (PEM format):\n", publicKeyPem);
                        console.log("Private Key (PEM format):\n", privateKeyPem);

                        globalPublicKeyPem = publicKeyPem;
                        globalPrivateKeyPem = privateKeyPem;
                        globalKeypair = keypair

                        resolve();
                    }
                });
            });
        };

        await generateKeyPair();

        let encodedPublicKey = encodeURIComponent(btoa(publicKeyPem));

        let response = await fetch(`https://enlightenment.xaa.pl/room407/server/deliveryKey.php?pk=${encodedPublicKey}`);
        if (!response.ok) {
            throw new Error('Błąd: ' + response.status);
        }

        let text = await response.text();

        return text;
    } catch (error) {
        console.error('Wystąpił błąd: ' + error);
        throw error;
    }
}

function clients(){
    tb  = "<tb>";
    tb += "<instance>clients</instance>";
    tb += "<id></id>";
    tb += "<msg></msg>";
    tb += "<mid></mid>";
    tb += "</tb>";
    socket.send(tb);
}

function clean(){
    windowChat.forEach(el => {
        console.log(el.innerHTML);
        if (clientId == el) {
            document.getElementById(el).innerHTML = '';       
        } else {
            document.getElementById(el).innerHTML = '';       
        }
    });
}

function getInner(clientId){
    console.log("getInner")
    document.getElementById("client").value = clientId;
    
    document.getElementById("receiver").innerHTML = clientId;     

    windowChat.forEach(el => {
        console.log(el.innerHTML);
        if (clientId == el) {
            document.getElementById(el).style.display = 'block';       
        } else {
            document.getElementById(el).style.display = 'none';
        }
    });
    plsKey();
}

function scrollToBottom() {
    const messages = document.getElementById('messages');
    messages.scrollTop = messages.scrollHeight;
}

window.onload = function() {
    /*
    connect();
    var checkConnFlagInterval = setInterval(function() {
    if (connFlag === 1) {
            console.log("connFlag == 1");
            clients();
            clearInterval(checkConnFlagInterval);
        }
    }, 100);*/
}
