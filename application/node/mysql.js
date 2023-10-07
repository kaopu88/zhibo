let mysql = require("mysql");
let config = require('./config.js');
let dbConfig = config.mysql;
let pool = mysql.createPool(dbConfig);

exports.query = function (sql, data, callback) {
    sql = sql.replace('PREFIX_', dbConfig.prefix);
    if (typeof data == 'function') {
        callback = data;
        data = [];
    }
    pool.getConnection(function (err, conn) {
        if (err) {
            callback(err, null, null);
        } else {
            conn.query(sql, data, function (qerr, vals, fields) {
                //释放连接
                conn.release();
                //事件驱动回调
                callback(qerr, vals, fields);
            });
        }
    });
};