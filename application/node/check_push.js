function CheckPush() {
    let utils = require('./utils'), redisClient = require('./app').redisClient, that = this;
    this.start = function () {
        check();
    };

    function check() {
        //console.log('check push start');
        let minTime = 0, key = 'messages:push:timer', now = utils.getTime(), offset = 0, length = 100;
        redisClient.zrangebyscore([key, minTime, now, 'WITHSCORES', 'LIMIT', offset, length], function (err2, taskList) {
            if (err2) {
                return false;
            }
            //console.log(taskList);
            if (taskList.length > 0) {
                let arr = [], params = ['messages:push:timer'];
                for (let i = 0; i < taskList.length; i += 2) {
                    arr.push({
                        key: taskList[i],
                        time: taskList[i + 1]
                    });
                    params.push(taskList[i]);
                }
                redisClient.zrem(params, function (err3, result3) {
                    if (err3) {
                        return false;
                    }
                    handler(arr, function () {
                        //小于100说明后面没有了
                        if (arr.length < length) {
                            setTimeout(function () {
                                check();
                            }, 5000);
                        } else {
                            setTimeout(function () {
                                check();
                            }, 100);
                        }
                    });
                });
            } else {
                // 5s后再次检查
                setTimeout(function () {
                    check();
                }, 5000);
            }
        });
    }

    function handler(arr, callback) {
        let params = ['queue:push:wait'];
        let params2 = ['messages:push:dup'];
        for (let i = 0; i < arr.length; i++) {
            params.push(arr[i]['key']);
            params2.push(arr[i]['key']);
        }
        redisClient.sadd(params2, function (err, result) {
            if (err) {
                return false;
            }
            redisClient.lpush(params, function (err2, result2) {
                if (err2) {
                    return false;
                }
                if (typeof callback == 'function') {
                    callback();
                }
            });
        });
    }
}

exports.CheckPush = CheckPush;
exports.createCheckPush = function () {
    return new CheckPush();
};