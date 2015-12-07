var express = require('express'),
    app = express(),
    server = require('http').createServer(app);

app.use(express.static('./dist'));

app.get('/', function(req, res){
    res.sendFile(__dirname + '/dist/index.html');
});

var port = 8090;
server.listen(port);
console.log('http://localhost:'+ port +' chat app started !');
