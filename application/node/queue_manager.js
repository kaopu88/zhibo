function QueueManager(myConf) {
    let myRedis = require('./redis');
    let config = require('./config');
    let fork = require('child_process').fork;
    let utils = require('./utils');
    let redisClient = require('./app').redisClient;
    myConf = typeof myConf == 'object' ? myConf : (typeof myConf == 'string' ? config.queues[myConf] : {});
    myConf = utils.extend({
        name: 'data',
        errEchoNum: 3,
        workerJs: 'worker/worker.js',
        workerParams: [],
        isHash: false,
        maxCpuNum: 3,
        retryMaxNum: 2
    }, myConf);

    let blockRedisClient;//监听队列，队列为空则一直阻塞
    let workerNum, maxWorkerNum, listening, isConnect = false;
    let errEchoTotal = 0, exitNum = 0, isStopListen = false;
    let minTime = 0, maxTime = 0, aveTime = 0, aveTotal = 0;//最短和最长处理时间
    let processDataList = [], processList = [], processIndex, processMax, that = this;

    this.start = function (callback) {
        let os = require('os');
        workerNum = 0;
        processIndex = 0;
        processMax = os.cpus().length - 1;//主进程占一个CPU
        processMax = processMax < 1 ? 1 : processMax;
        maxWorkerNum = myConf.maxCpuNum * processMax;
        listening = false;
        redisClient.lrange(['queue:' + myConf.name + ':working', 0, -1], function (err, res) {
            if (err) {
                return utils.log('恢复queue:' + myConf.name + ':working' + '失败');
            }
            if (res.length > 0) {
                res.reverse();
                for (let i = 0; i < res.length; i++) {
                    doWork(res[i]);
                }
                next();
            } else {
                next();
            }
        });

        function next() {
            createListener();
            if (typeof callback == 'function') {
                callback();
            }
        }
    };

    //创建侦听器
    function createListener() {
        blockRedisClient = myRedis.createClient();
        blockRedisClient.on("error", function (err) {
            isConnect = false;
            utils.log('QueueManager queue:' + myConf.name + ' blockRedisClient error', err, 'fat');
        });
        blockRedisClient.on("connect", function () {
            isConnect = true;
            utils.log('QueueManager queue:' + myConf.name + ' blockRedisClient success');
            checkListen();
        });
    }

    //检查侦听队列状态，如果没有侦听且没有超出最大进程数则添加阻塞侦听
    function checkListen() {
        if (workerNum < maxWorkerNum && !listening && isConnect && !isStopListen) {
            listening = true;
            blockRedisClient.brpoplpush(['queue:' + myConf.name + ':wait', 'queue:' + myConf.name + ':working', 0], function (err, task) {
                listening = false;
                if (err) {
                    console.log('queue:' + myConf.name + ' brpoplpush error', err);
                    return err;
                }
                doWork(task);
                checkListen();
            });
        }
    }

    //开始工作
    function doWork(task) {
        workerNum++;
        let current = processIndex % processMax;
        if (!processList[current]) {
            let myParams = [config.env_name, myConf.name];
            for (let i = 0; i < myConf.workerParams.length; i++) {
                myParams.push(myConf.workerParams[i]);
            }
            let worker = fork(myConf.workerJs, myParams, {silent: true});
            worker.stderr.setEncoding('utf8');
            worker.stdout.setEncoding('utf8');
            //工作进程发生错误
            worker.stderr.on('data', function (stderr) {
                console.log(stderr);
            });
            //工作进程输出
            worker.stdout.on('data', function (data) {
                console.log(data);
            });
            //工作进程消息
            worker.on('message', function (data) {
                data['current'] = current;
                if (data['type'] === 'complete') {
                    workerComplete(data, function () {
                        workerNum--;
                        checkListen();
                        let endMs = new Date().getTime();
                        let runLen = endMs - data['startMs'];
                        aveTotal++;
                        aveTime += runLen;
                        minTime = minTime > 0 ? Math.min(minTime, runLen) : runLen;
                        maxTime = Math.max(maxTime, runLen);
                        if (config.debug) {
                            console.log('queue:' + myConf.name + ' Worker 工作时长:' + (runLen) + 'ms');
                        }
                    });
                } else if (data['type'] === 'error') {
                    workerError(data, function () {
                        workerNum--;
                        checkListen();
                    });
                } else if (data['type'] === 'init') {
                    processList[current]['isInit'] = true;
                    doWork2(current, task);
                }
            });
            //工作进程结束
            worker.on('exit', function (code) {
                exitNum++;
                processList[current] = null;
                let arr = processDataList[current];
                for (let i = 0; i < arr.length; i++) {
                    workerError({current: current, task: arr[i], msg: '工作进程非正常退出', code: 500}, function () {
                        workerNum--;
                        checkListen();
                    });
                }
            });
            processList[current] = worker;
            processDataList[current] = [];
        } else if (typeof processList[current]['isInit'] == 'undefined' || !processList[current]['isInit']) {
            processList[current].on('message', function (data) {
                if (data['type'] === 'init') {
                    processList[current]['isInit'] = true;
                    doWork2(current, task);
                }
            });
        } else {
            doWork2(current, task);
        }
        processIndex++;
    }

    function doWork2(current, task) {
        //var startMs=config.debug?(new Date().getTime()):0;
        let startMs = new Date().getTime();
        processDataList[current].push(task);
        processList[current].send({type: 'doWork', task: task, startMs: startMs});
    }

    //工作完成
    function workerComplete(event, callback) {
        utils.log('###queue:' + myConf.name + ' 工作完成', event.task);
        let arr = [['lrem', 'queue:' + myConf.name + ':working', 0, event.task], ['del', 'task:' + myConf.name + ':' + event.task]];
        redisClient.batch(arr).exec(function (err, res) {
            if (err) {
                console.log('queue:' + myConf.name + '清理任务失败', err);
            }
            removeData(event.current, event.task);
            callback();
        });
    }

    //工作失败
    function workerError(event, callback) {
        //9不是失败，是继续执行
        if (event['code'] != '9' && event['code'] != '10') {
            if ((errEchoTotal % myConf.errEchoNum) == 0) {
                console.log('###工作失败', event.task + ':' + event.msg + ';total:' + errEchoTotal);
            } else {
                utils.log('###工作失败', event.task + ':' + event.msg + ';total:' + errEchoTotal);
            }
            errEchoTotal++;
        }
        let key = 'task:' + myConf.name + ':' + event.task;
        if (event['code'] == '2') {
            redisClient.lrem(['queue:' + myConf.name + ':working', 0, event.task], function (lremErr, lremRes) {
                if (lremErr) {
                    return console.log(lremErr);
                }
                workerNext(callback, event);
            });
        } else if (event['code'] == '3') {
            let tmp = [['lrem', 'queue:' + myConf.name + ':working', 0, event.task]];
            tmp.push(['del', key]);
            redisClient.batch(tmp).exec(function (batchErr, batchRes) {
                if (batchErr) {
                    return console.log(batchErr);
                }
                workerNext(callback, event);
            });
        } else if (event['code'] == '9') {
            //继续执行
            return workerNext(callback, event, 'queue:' + myConf.name + ':working', 'queue:' + myConf.name + ':wait');
        } else if (event['code'] == '10') {
            //不做任何处理
            utils.log('###工作完成（不处理）', event.task + ':' + event.msg);
            callback();
        } else {
            if (myConf.isHash) {
                return workerNext(callback, event, 'queue:' + myConf.name + ':working', 'queue:' + myConf.name + ':failed');
            } else {
                redisClient.hgetall(key, function (err, res) {
                    if (err) {
                        return console.log(err);
                    }
                    let tmp = [['hincrby', key, 'num', 1]];
                    tmp.push(['hset', key, 'err_' + res['num'], event.msg]);
                    redisClient.batch(tmp).exec(function (batchErr, batchRes) {
                        if (batchErr) {
                            return console.log(batchErr);
                        }
                        if (res['num'] < myConf.retryMaxNum) {
                            workerNext(callback, event, 'queue:' + myConf.name + ':working', 'queue:' + myConf.name + ':wait');
                        } else {
                            workerNext(callback, event, 'queue:' + myConf.name + ':working', 'queue:' + myConf.name + ':failed');
                        }
                    });
                });
            }
        }
    }

    function workerNext(callback, event, from, to) {
        if (typeof from != 'undefined') {
            let rlArr = [['lrem', from, 0, event.task], ['lpush', to, event.task]];
            redisClient.multi(rlArr).exec(function (rpErr, rpRes) {
                if (rpErr) {
                    return console.log(rpErr);
                }
                removeData(event.current, event.task);
                callback();
            });
        } else {
            removeData(event.current, event.task);
            callback();
        }
    }

    function removeData(current, data) {
        let index = processDataList[current].indexOf(data);
        if (index > -1) {
            processDataList[current].splice(index, 1);
        }
    }

    function count(list) {
        let total = 0;
        for (let i = 0; i < list.length; i++) {
            if (list[i]) {
                total++;
            }
        }
        return total;
    }

    //获取当前进程数量
    this.getProcessNum = function () {
        return count(processList);
    };

    //获取性能参数
    this.getPerformance = function () {
        return {
            minTime: minTime,
            maxTime: maxTime,
            aveTime: Math.round(aveTime / aveTotal),
            exitNum: exitNum,
            processNum: that.getProcessNum()
        };
    };

    this.retryListen = function () {
        checkListen();
    };

    this.stopListen = function () {
        isStopListen = true;
    };

    this.startListen = function () {
        isStopListen = false;
        checkListen();
    }
}

module.exports = QueueManager;