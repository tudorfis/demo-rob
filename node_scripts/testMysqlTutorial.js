
var mysql = require('mysql');

var con = mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "test"
});

/** mysql - connect */
con.connect(function(err){
    if(err){
        console.log('Error connecting to Mysql Db');
        return;
    }
    console.log('Mysql connection established');
});

/** mysql - insert */
var user = {username: 'Winnie', first_name: 'Australia' };
con.query('insert into `users` SET ?', user, function(err, res){
    if(err) throw err;
    console.log('Last insert ID:', res.insertId);
});

/** mysql - update */
con.query('update `users` SET first_name = ? Where id = ?', ["South Africa", 1],
    function (err, result) {
        if (err) throw err;
        console.log('Changed ' + result.changedRows + ' rows');
    }
);

/** mysql - select */
con.query('select * from `users`',function(err, rows){
    if (err) throw err;

    console.log('Data received from Db:\n');
    console.log(rows);
});

/** mysql - delete */
con.query('delete from `users` WHERE id = ?', [1], function (err, result) {
        if (err) throw err;
        console.log('Deleted ' + result.affectedRows + ' rows');
    }
);

/** mysql - disconnect */
con.end(function(err) {
    // The connection is terminated gracefully
    // Ensures all previously enqueued queries are still
    // before sending a COM_QUIT packet to the MySQL server.
});