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
    database: "demo_rob"
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

/** PROJECTS **/
app.get('/api/getProjects', function(req, res){
    con.query('select * from `projects`', function(err, rows){
        if (err) throw err;
        res.send(rows);
    });
});

app.post('/api/updateProject', function(req, res){
    var query, query_vars;
    query = ((req.body.id) ? 'update ' : 'insert ') +
        ' `projects` SET `project` = ?, `status` = ?, `manager` = ?, `progress` = ?'+
        ((req.body.id) ? ' where `id` = ?;' : ';');
    query_vars = [req.body.project, req.body.status, req.body.manager, req.body.progress];
    if (req.body.id) {
        query_vars.push(req.body.id);
    }
    con.query(query, query_vars, function (err, q_res) {
        if (err) throw err;
        res.send(q_res);
    });
});

app.post('/api/deleteProject', function(req, res) {
    con.query("delete from `projects` where `id` = ?;", [req.body.id], function(err, q_res){
        if (err) throw err;
        res.send(q_res);
    });
});

/** ORDERS **/
app.get('/api/getOrders', function(req, res){
    con.query('select * from `orders`', function(err, rows){
        if (err) throw err;
        res.send(rows);
    });
});

app.post('/api/updateOrder', function(req, res){
    var query, query_vars;
    query = ((req.body.id) ? 'update ' : 'insert ') +
        ' `orders` SET `order` = ?, `price` = ?, `created_by` = ?, `date_created` = ?'+
        ((req.body.id) ? ' where `id` = ?;' : ';');
    query_vars = [req.body.order, req.body.price, req.body.created_by, req.body.date_created];
    if (req.body.id) {
        query_vars.push(req.body.id);
    }
    con.query(query, query_vars, function (err, q_res) {
        if (err) throw err;
        res.send(q_res);
    });
});

app.post('/api/deleteOrder', function(req, res) {
    con.query("delete from `orders` where `id` = ?;", [req.body.id], function(err, q_res){
        if (err) throw err;
        res.send(q_res);
    });
});

/** PRODUCTS **/
app.get('/api/getProducts', function(req, res){
    con.query('select * from `products`', function(err, rows){
        if (err) throw err;
        res.send(rows);
    });
});

app.post('/api/updateProduct', function(req, res){
    var query, query_vars;
    query = ((req.body.id) ? 'update ' : 'insert ') +
        ' `products` SET `product_name` = ?, `price` = ?, `quantity` = ?, `vendor` = ?'+
        ((req.body.id) ? ' where `id` = ?;' : ';');
    query_vars = [req.body.product_name, req.body.price, req.body.quantity, req.body.vendor];
    if (req.body.id) {
        query_vars.push(req.body.id);
    }
    con.query(query, query_vars, function (err, q_res) {
        if (err) throw err;
        res.send(q_res);
    });
});

app.post('/api/deleteProduct', function(req, res) {
    con.query("delete from `products` where `id` = ?;", [req.body.id], function(err, q_res){
        if (err) throw err;
        res.send(q_res);
    });
});




var port = 8080;
server.listen(port);
console.log('http://localhost:'+ port +' app started !');
