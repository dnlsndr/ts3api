var settings = require('./config.json')
var env = process.argv[2]
var express = require('express');
var net = require('net');
var app = express();
var socket;
switch(env){
	case "dev":
		settings = settings.local
	break;
	case "prod":
		settings = settings.server
	break;
	default:
		console.log("Missing argument")
}
var loginstr = "login " + settings.loginname + " " + settings.loginpw + "\n";
var resString;
var finalArray = {};


//Initial connection to the ts3 socket
connect();

//Preventing bot to be thrown out of the server after 10 minutes. 
//If there is a nother command that is even lower in perfomance, then replase "version" with the new command !
setInterval(function(){
	socket.write("version\n");
}, 300000)

//Error handler
socket.on('error', function(e){
	console.log("socket connection failed, please make sure the settings are correct and teamspeak is running\nERROR:")
	console.log(e)
})

//Ending handler
socket.on('end', function(e){
	console.log("socket connection ended\nMESSAGE:")
	console.log(e)
})

//Closing handler, if socket is colsed it automatically reconnects
socket.on('close', function(e){
	console.log("socket connection closed")
	connect();
})

//Here we create a Webserver on the defined port in the config. 
// It listens to everything after /api/, make sure your command is raw url encoded, 
// otherwise it will throw errors if there are spaces in the url.
//It then uses the command in the url and writes it into the socket connection to the teamspeak, 
// where it then gets back all the wanted data and palces it in as an JSON object in to the HTML body. 
// The result is concerted to a string where all extra unneeded objects are deleted. i.e. /n /r slashes
app.get('/hubbot/api/:_command', function (req, res) {
	var request = req.params._command;
	console.log(request)
	socket.write(request + "\n")
	socket.once("data", function (data) {
		resString = data.toString();
		// console.log(resString);
		resString = resString.replace("error id=0 msg=ok" , "")
		resString = conformer(resString)
		var date = new Date()
		finalArray = {
			request: request,
			data: resString,
			time: date

		}
			res.status(200).end(JSON.stringify(finalArray, null, 4))
	})
})
//setting the api port and the address to listen to. 127.0.0.1 allsows the api olnly to be accessed trough local code, 
//so no outside person can have acces to the api.
if(settings.apiaccess == "local"){
	app.listen(settings.apiport, "127.0.0.1");
} else {
	app.listen(settings.apiport);
}


//This is the standard connection function that gets called every time this scripts starts. 
//It connects to the ts3 socket, loggs itself in, with the credentials specified in the config, 
//then defines which virtual ts3 serves should be used and cahnges its nickname to the name specified in the config.

function connect(){
	socket = net.connect(settings.socport, settings.sochost, function(){
		console.log('connected to socket')
		socket.write(loginstr);
		socket.write("use sid=" + settings.sid + "\n");
		socket.write("clientupdate client_nickname=" + settings.nickname + "\n");
	})
}

// This function transforms the string, ready to be parsed by json and used in other Languages like PHP, 
//it also removes unwanted returns and empty lines.
function conformer(str) {
    str = str.replace(/\\'/g, '\'');
    str = str.replace(/\\"/g, '"');
    str = str.replace(/\\0/g, '\0');
    str = str.replace(/\\\\/g, '\\');
	str = str.replace(/\\s/g, '\\s');
	str = str.replace(/\n/g, '');
	str = str.replace(/\r/g, '');
    return str;
}
