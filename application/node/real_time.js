function RealTime(io, socket) {
    let app = require('./app');
    let myHeartbeat = app.myHeartbeat;
    let utils = require('./utils');
    let redisClient = app.redisClient;
    let request = socket.request, types = [], isBind = false;

    function init() {
        app.rtNum(app.rtNum() + 1);
        utils.log('实时数据客户端已连接,total:' + app.rtNum());
        utils.log('real_time types', types);
        socket.on('init', initHandler);
        socket.on('disconnect', disconnectHandler);
    }

    this.getSocketId = function () {
        return socket ? socket.id : null;
    };

    function disconnectHandler() {
        app.rtNum(app.rtNum() - 1);
        utils.log('实时数据客户端断开', types);
        if (isBind) {
            myHeartbeat.removeListener('peng', pengHandler);
            isBind = false;
        }
    }

    function initHandler(_types) {
        if (isBind) {
            myHeartbeat.removeListener('peng', pengHandler);
            isBind = false;
        }
        types = _types ? _types : [];
        pengHandler(function () {
            if (!isBind) {
                myHeartbeat.addListener('peng', pengHandler);
                isBind = true;
            }
        }, true);
    }

    function pengHandler(callback, isInit) {
        utils.loop(types, function (item, next) {
            let key = request.actToken + ':rt:' + item;
            let time = utils.getTime() - 3;//3秒延时，考虑到网络环境
            if (isInit) {
                let arr = [];
                //10分钟
                for (let i = 0; i < (10*60); i++) {
                    arr.push(['zscore', key, time - i]);
                }
                arr.reverse();
                redisClient.batch(arr).exec(function (bErr, resArr) {
                    if (bErr) {
                        return console.log('bErr:', bErr);
                    }
                    let tmp = [];
                    for (let j = 0; j < resArr.length; j++) {
                        tmp.push({value: resArr[j] ? resArr[j] : 0, time: arr[j][2]});
                    }
                    socket.emit('init_data', tmp);
                    next();
                });
            } else {
                redisClient.zscore(key, time, function (zErr, num) {
                    if (zErr) {
                        return console.log('zErr', zErr);
                    }
                    num = num ? num : 0;
                    socket.emit('data', {value: num, time: time});
                    next();
                })
            }
        }, function (err) {
            if (err) {
                return console.log('pengHandler err:', err);
            }
            if (typeof callback == 'function') {
                callback();
            }
        });
    }

    init();
}
module.exports = RealTime;