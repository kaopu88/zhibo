function Timer() {
    let app = require('./app');
    let utils = require('./utils');
    let http = require('http');
    let url = require('url');
    let qs = require('querystring');
    let token = 'BUGUTIMER201805221342@XLY';
    let sign = require('./sign');
    let myHeartbeat = app.myHeartbeat;
    let redisClient = app.redisClient;
    myHeartbeat.addListener('peng', pengHandler);

    function pengHandler() {
        myHeartbeat.removeListener('peng', pengHandler);
        check(function (error) {
            if (error) {
                console.log('timer error', error);
            }
            myHeartbeat.addListener('peng', pengHandler);
        });
    }

    function check(callback) {
        let minTime = 0, key = 'timer:line', now = utils.getTime(), offset = 0, length = 500;
        redisClient.zrangebyscore([key, minTime, now, 'WITHSCORES', 'LIMIT', offset, length], function (err2, taskList) {
            if (err2) {
                return callback({status: 1, message: 'error2'});
            }
            if (taskList.length > 0) {
                let arr = [];
                for (let i = 0; i < taskList.length; i += 2) {
                    arr.push({
                        key: taskList[i],
                        time: taskList[i + 1]
                    });
                }
                return handler(arr, function () {
                    callback(null);
                });
            } else {
                callback(null);
            }
        });
    }

    //处理
    function handler(arr, callback) {
        let index = 0;

        function next() {
            let params = ['timer:task:' + arr[index]['key'], 'key', 'method', 'url', 'data', 'first_trigger_time', 'trigger_time', 'trigger_num', 'cycle', 'interval'];
            redisClient.hmget(params, function (err, data) {
                if (!err) {
                    let obj = {};
                    for (let t = 1; t < params.length; t++) {
                        obj[params[t]] = data[t - 1];
                    }
                    if (obj['trigger_time'] === null || obj['url'] === null || obj['url'] === '') {
                        remove(arr[index]['key'], function () {
                            goon();
                        });
                    } else {
                        obj['cycle'] = parseInt(obj['cycle']);
                        obj['trigger_time'] = parseInt(obj['trigger_time']);
                        obj['trigger_num'] = parseInt(obj['trigger_num']);
                        obj['method'] = String(obj['method']).toLowerCase();
                        try {
                            obj['data'] = JSON.parse(obj['data']);
                        } catch (e) {
                            obj['data'] = {};
                        }
                        if (typeof obj['data'] != 'object' || obj['data'] === null) {
                            obj['data'] = {};
                        }
                        obj['data']['sign_time'] = utils.getTime();
                        obj['data']['sign_str'] = utils.getUcode(6, '1aA');
                        obj['data']['sign'] = sign.generateSign(obj['data'], token);
                        once(obj, goon);
                    }
                }

                function goon() {
                    index++;
                    if (arr.length - 1 >= index) {
                        next();
                    } else {
                        callback();
                    }
                }
            });
        }

        if (arr.length - 1 >= index) {
            next();
        } else {
            callback();
        }
    }

    function once(data, callback) {
        let now = utils.getTime();
        let tmp = {trigger_num: parseInt(data['trigger_num'])};
        let isCycle = !(!data['cycle'] || data['cycle'] == 0 || (data['cycle'] > 0 && tmp['trigger_num'] >= data['cycle']));
        let isRequest = true, interval = parseInt(data['interval']);
        let first_trigger_time = parseInt(data['first_trigger_time']);
        let next_trigger_time = now + interval;
        //检查有没有失效，误差重置
        if (isCycle) {
            let maxMistake = 500;
            let mistake = now - data['trigger_time'];
            if (mistake > maxMistake) {
                isRequest = false;
                next_trigger_time = first_trigger_time + (Math.ceil((now - first_trigger_time) / interval) * interval);
            }
        }
        if (isRequest) {
            let result = data['method'] == 'get' ? get(data['url'], data['data']) : post(data['url'], data['data']);
            tmp['trigger_num']++;
        }

        if (!isCycle) {
            remove(data['key'], callback);
        } else {
            tmp['trigger_time'] = next_trigger_time;
            let multi = redisClient.multi();
            multi.hmset('timer:task:' + data['key'], tmp);
            multi.zadd("timer:line", parseInt(tmp['trigger_time']), data['key']);
            multi.exec(function (error) {
                callback();
            });
        }
    }

    function remove(key, callback) {
        let multi = redisClient.multi();
        multi.zrem('timer:line', key);
        multi.del('timer:task:' + key);
        multi.exec(function (error) {
            callback();
        });
    }

    function post(requestUrl, data) {
        let contents = qs.stringify(data);
        let urlOpts = url.parse(requestUrl);
        if (urlOpts && urlOpts['host']) {
            let options = {
                host: urlOpts['host'],
                path: urlOpts['path'],
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Content-Length': contents.length
                }
            };
            let req = http.request(options, function (res) {
                res.setEncoding('utf8');
                res.on('data', function (data) {
                    //console.log("data:",data);
                });
            });
            req.write(contents);
            req.setTimeout(1500, function () {
                req.abort();
            });
            req.end();
        }
    }

    function get(requestUrl, data) {
        let queryStr = qs.stringify(data);
        let urlOpts = url.parse(requestUrl);
        if (urlOpts && urlOpts['host']) {
            let options = {
                hostname: urlOpts['host'],
                port: 80,
                path: urlOpts['path'].indexOf('?') > -1 ? urlOpts['path'] + '&' + queryStr : urlOpts['path'] + '?' + queryStr,
                method: 'GET'
            };
            let req = http.request(options, function (res) {
                res.setEncoding('utf8');
                res.on('data', function (chunk) {
                    //console.log('BODY: ' + chunk);
                });
            });
            req.on('error', function (e) {
                //console.log('problem with request: ' + e.message);
            });
            req.setTimeout(1500, function () {
                req.abort();
            });
            req.end();
        }
    }
}

module.exports = Timer;