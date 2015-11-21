var express = require('express'),
    app = express(),
    mysql = require('mysql'),
    bodyParser = require('body-parser'),
    crypto = require('crypto'),
    server = require('http').createServer(app);

var con = mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "test"
});

con.connect(function(err){
    if(err){
        console.log('Error connecting to Mysql Db');
        return;
    }
    console.log('Mysql connection established');
});

app.use(express.static('./'));
app.use(bodyParser.json({limit: '50mb'}));
app.use(bodyParser.urlencoded({limit: '50mb', extended: true}));

app.get('/', function(req, res){
    res.sendFile(__dirname + '/index.html');
});

app.get('/api/getUser', function(req, res){
    var email = req.query.email,
        query = 'select * from `users` where `email` = "'+ email +'"';
    if (req.query.password) {
        md5password = crypto.createHash('md5').update(req.query.password).digest("hex");
        query += ' and `password` = "'+ md5password +'"';
    }
    con.query(query, function(err, rows){
        if (err) throw err;
        res.send({
            user: rows[0]
        });
    });
});

app.post('/api/saveUser', function(req, res){
    con.query('select * from `users` where `email` = "'+ req.body.email +'"', function(err, rows){
        if (err) throw err;
        if (rows.length == 0) {
            req.body.password = md5password = crypto.createHash('md5').update(req.body.password).digest("hex");
            con.query('insert into `users` SET ?', req.body, function(err, row){
                if(err) throw err;
                res.send({
                    id: row.insertId
                });
            });
        } else {
            res.send({
                is_error: 1,
                error_message: "* Email already taken, please select different email"
            })
        }
    });
});


var port = 8080;
server.listen(port);
console.log('http://localhost:'+ port +' app started !');