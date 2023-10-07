let config = require('./config.js');
let express = require('express');
let utils = require('./utils.js');
let sign = require('./sign.js');
let bodyParser = require('body-parser');
let cookie = require('cookie-parser');
let SocketIo = require("socket.io");
let QueueManager = require('./queue_manager');
let RealTime = require('./real_time');
let Timer = require('./timer');
let app = express();
app.use(express.static(__dirname + '/public'));
app.use(bodyParser.json()); // for parsing application/json
app.use(bodyParser.urlencoded({extended: true})); // for parsing application/x-www-form-urlencoded
let redisClient = require('./redis.js').createClient();
let startTime = new Date().getTime();
let realTimeNum = 0;
let permanentNum = 1;//常驻进程数,默认1为主进程数
exports.redisClient = redisClient;
exports.app = app;
let myHeartbeat = new require('./heartbeat').createHeartbeat();
exports.myHeartbeat = myHeartbeat;
let checkPush = require('./check_push').createCheckPush();
let pushQueue = new QueueManager('push');

console.log('当前运行环境:' + config.env_name);

//连接redis成功
redisClient.on('connect', function () {
    console.log('app redis connect at ' + config.redis.host + ':' + config.redis.port);
    pushQueue.start();
    myHeartbeat.start();
    checkPush.start();
    new Timer();
    // ready();
});


let server, io, realTimeArr = [];

function ready() {

    server = app.listen(config.port, config.site_domain);

    io = SocketIo.listen(server);

    //http服务启动
    server.listen(config.port, function () {
        console.log('app run ' + config.site_domain + ':' + config.port);
    });

    //socket连接授权
    io.use(function (socket, next) {
        let cookies = socket.request.headers.cookie;
        cookies = cookies ? cookie.parse(cookies) : null;
        let auth = cookies ? cookies[config.connection.name] : null;
        socket.request['join_time'] = utils.getTime();
        if (utils.empty(auth)) {
            utils.log("读取COOKIE连接凭证失败01");
            return next(new Error('读取COOKIE连接凭证失败'));
        }
        let obj = JSON.parse(utils.base64_decode(auth));
        if (!obj || !obj['uid'] || obj['uid'] == '' || !sign.isSign(obj['sign'], obj, config.connection.token)) {
            utils.log("读取COOKIE连接凭证失败02");
            return next(new Error('读取COOKIE连接凭证失败'));
        }
        obj['time'] = typeof obj['time'] == 'undefined' ? 0 : parseInt(obj['time']);
        if (obj['time'] + 300 < utils.getTime()) {
            utils.log("COOKIE连接凭证已过期");
            return next(new Error('COOKIE连接凭证已过期'));
        }
        socket.request['uid'] = obj['uid'];
        socket.request['app_key'] = obj['app_key'];
        socket.request['actToken'] = obj['actToken'];
        next();
    });

    io.on('connection', function (socket) {
        let tmp = new RealTime(io, socket);
        realTimeArr.push(tmp);
        let socketId = socket.id;
        socket.on('disconnect', function () {
            for (let i = 0; i < realTimeArr.length; i++) {
                let item = realTimeArr[i];
                if (item.getSocketId() == socketId) {
                    realTimeArr.splice(i, 1);
                    break;
                }
            }
        });
    });
}


/*服务异常*/
process.on('uncaughtException', function (err) {
    console.error(err.stack);
    if (!config.debug) {
        /*utils.sendEmail(config.master_email,'[重要]ERP NODE.JS服务器警报！','站长：<br/>您好！您的服务器在'+utils.dateFormat('yyyy-mm-dd hh:ii:ss')+'时发生严重错误！请立即登录服务器检查异常情况，异常信息：<br/>'+err.stack);*/
    }
});

//读取和设置realTimeNum
exports.rtNum = function (num) {
    if (typeof num == 'undefined') {
        return realTimeNum;
    }
    realTimeNum = num;
};

//获取运行时长
exports.getRunLen = function () {
    let nowTime = new Date().getTime();
    return nowTime - startTime;
};

//读取和设置常驻进程数
exports.permanentNum = function (val) {
    if (typeof val == 'undefined') {
        return permanentNum;
    }
    permanentNum = val;
};




