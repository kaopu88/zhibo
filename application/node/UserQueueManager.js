var myRedis=require('./redis');
var config=require('./config');
var fork=require('child_process').fork;
var utils=require('./utils');
var redisClient=require('./app').redisClient;
var workerJs='worker/UserWorker.js';
var blockRedisClient;//监听队列，队列为空则一直阻塞
var timer;//任务定时器
var workerNum,maxWorkerNum,listening,taskRound,task_time,test_time;
var queryLength,doLength,isConnect=false;
var processList=[],processIndex,processMax;
var gNextDate;
var processDataList=[];
var exitNum=0,isStopListen=false;
var doStartTime=0;
var overTime=7200000;//收集任务超出2个小时将会提示

//启动
exports.start=function(callback)
{
    var os=require('os');
    gNextDate=new Date();
    workerNum=0;
    processIndex=0;
    processMax=os.cpus().length-1;//主进程占一个CPU
    processMax=processMax<1?1:processMax;
    maxWorkerNum=config.guser.max_cpu_num*processMax;
    taskRound=1;
    task_time=config.guser.task_time;
    listening=false;
    test_time=typeof config.guser['test_time']!='undefined'?config.guser['test_time']:null;
    queryLength=config.guser.query_len;
    doLength=config.guser.do_len;
    redisClient.lrange(['guq:queue:working',0,-1],function(err,res){
        if(err){
            return utils.log('恢复guq:queue:working失败');
        }
        if(res.length>0){
            res.reverse();
            for(var i=0;i<res.length;i++){
                doWork(res[i]);
            }
            next();
        }else{
            next();
        }
    })
    function next() {
        createListener();
        doStartTime=new Date().getTime();
        overTime=7200000;
        doTask(complete);
        if(typeof callback=='function'){
            callback();
        }
    }
}

function complete(err)
{
    if(err){
        console.error(err);
    }
    utils.log('任务完成');
    taskRound=1;
    setTimer(complete);
}

function setTimer(callback)
{
    var taskDate=getTaskDate();
    var nowDate=new Date();
    var time;
    if(nowDate.getTime()>=taskDate.getTime()){
        var diff=nowDate.getTime()-taskDate.getTime();
        time=(3600*24*1000)-diff;
    }else{
        time=taskDate.getTime()-nowDate.getTime();
    }
    if(test_time){
        time=test_time;
    }
    timer=setTimeout(function () {
        doStartTime=new Date().getTime();
        overTime=7200000;
        doTask(callback);
    },time);
    var nextDate=new Date();
    nextDate.setTime(nextDate.getTime()+time);
    gNextDate=nextDate;
    var str=nextDate.getFullYear()+'年'+(nextDate.getMonth()+1)+'月'+nextDate.getDate()+'日'+nextDate.getHours()+'时'+nextDate.getMinutes()+'分'+nextDate.getSeconds()+'秒';
    utils.log('下一次任务时间',str);
    utils.log('---------------------------------------------');
}

function getTaskDate()
{
    var taskDate=new Date();
    taskDate.setHours(task_time[0]);
    taskDate.setMinutes(task_time[1]);
    taskDate.setSeconds(task_time[2],0);
    return taskDate;
}

//执行任务
function doTask(callback)
{
    utils.log('***开始执行任务',taskRound+'轮');
    redisClient.zrangebyscore(['guq:list','-inf','+inf','LIMIT',0,doLength],function (err,list) {
        if(err){
            return callback(err);
        }
        var end=list.length>5?4:list.length-1;
        utils.log('本次APP列表',list.length,list.splice(0,end));
        if(list.length>0) {
            utils.loop(list,function(item,next)
            {
                utils.log('===APP  '+item);
                createTask(item,function(createTaskErr)
                {
                    return next(createTaskErr);
                });
            },function(err3){
                if(err3){
                    return callback(err3);
                }
                taskRound++;
                doTask(callback);
            })
        } else {
            utils.log('任务完成,执行回调');
            callback();
        }
    });
    if((new Date().getTime()-doStartTime)>=overTime){
        console.log('用户收集与创建任务已超出'+(overTime/(3600*1000))+'小时了');
        overTime+=3600000;
    }
}

//创建任务
function createTask(appKey,callback)
{
    var key='guq:users:'+appKey;
    var key2='guq:pro_users:'+appKey;
    redisClient.zrangebyscore([key,'-inf','+inf','LIMIT',0,queryLength],function(err,users)
    {
        if(err){
            return callback(err);
        }
        utils.log('本次用户数量',users.length);
        var taskObj={appKey:appKey,time:utils.getTime(),num:0,users:users.join(',')};
        var taskName=utils.sha1(new Date().getTime()+utils.getUcode(8,'1aA'));
        redisClient.hmset('guq:task:'+taskName,taskObj,function(err2,res2) {
            if(err2){
                return callback(err2);
            }
            utils.log('任务创建成功',taskName);
            addWaitQueue(taskName,function(err3,res3)
            {
                if(err3){
                    return callback(err3);
                }
                var tmp=[['zrem',key,users],['sadd',key2,users],['zcount',key,'-inf','+inf']];
                redisClient.multi(tmp).exec(function(err4,res4){
                    if(err4){
                        return callback(err4);
                    }
                    clear(appKey,key,res4[2],function(err5,res5)
                    {
                        if(err5){
                            return callback(res5);
                        }
                        callback();
                    });
                });
            });
        });
    });
}

function clear(appKey,key,num,callback)
{
    utils.log('清理用户集',appKey,key,num);
    if(num<=0){
        var arr=[['zrem','guq:list',appKey],['del',key]];
        redisClient.batch(arr).exec(function(err,res) {
            if(err){
                return callback(err);
            }
            utils.log('清理并删除了APP和用户集',appKey);
            callback();
        });
    }else{
        var time=utils.getTime();
        redisClient.zincrby(['guq:list',time,appKey],function(err,res) {
            if(err){
                return callback(err);
            }
            utils.log('清理并更新了APP时间',appKey,time);
            callback();
        });
    }
}

//加入等待队列
function addWaitQueue(taskId,callback)
{
    redisClient.lpush('guq:queue:wait',taskId,function(err,res)
    {
        if(err){
            return callback(err);
        }
        callback();
    });
}

//创建侦听器
function createListener()
{
    blockRedisClient=myRedis.createClient();
    blockRedisClient.on("error",function(err)
    {
        isConnect=false;
        utils.log('UserQueueManager blockRedisClient error',err,'fat');
    });
    blockRedisClient.on("connect", function()
    {
        isConnect=true;
        utils.log('UserQueueManager blockRedisClient success');
        checkListen();
    });
}

//检查侦听队列状态，如果没有侦听且没有超出最大进程数则添加阻塞侦听
function checkListen()
{
    if(workerNum<maxWorkerNum&&!listening&&isConnect&&!isStopListen) {
        listening=true;
        blockRedisClient.brpoplpush(['guq:queue:wait','guq:queue:working',0],function(err,res) {
            listening=false;
            if(err){
                console.log('guq brpoplpush error',err);
                return err;
            }
            doWork(res);
            checkListen();
        });
    }
}

function doWork(res)
{
    workerNum++;
    var current=processIndex%processMax;
    if(!processList[current]){
        var worker=fork(workerJs,[],{silent:true});
        worker.stderr.setEncoding('utf8');
        worker.stdout.setEncoding('utf8');
        //工作进程发生错误
        worker.stderr.on('data',function(err){
            console.log(err);
        });
        //工作进程输出
        worker.stdout.on('data',function(data){
            console.log(data);
        });
        //工作进程消息
        worker.on('message',function(data){
            data['current']=current;
            if(data['type']==='complete'){
                workerComplete(data,function(){
                    workerNum--;
                    checkListen();
                });
            }else if(data['type']==='error'){
                workerError(data,function () {
                    workerNum--;
                    checkListen();
                });
            }else if(data['type']==='init'){
                processList[current]['isInit']=true;
                doWork2(current,res);
            }
        });
        //工作进程结束
        worker.on('exit',function (code) {
            exitNum++;
            processList[current]=null;
            var arr=processDataList[current];
            for(var i=0;i<arr.length;i++){
                workerError({current:current,task:arr[i],msg:'工作进程非正常退出',code:500},function () {
                    workerNum--;
                    checkListen();
                });
            }
        });
        processList[current]=worker;
        processDataList[current]=[];
    }else if(typeof processList[current]['isInit']=='undefined'||!processList[current]['isInit']){
        processList[current].on('message',function(data){
            if(data['type']==='init'){
                processList[current]['isInit']=true;
                doWork2(current,res);
            }
        });
    }else{
        doWork2(current,res);
    }
    processIndex++;
}

function doWork2(current,task)
{
    processDataList[current].push(task);
    processList[current].send({type:'doWork',task:task});
}

//工作完成处理
function workerComplete(event,callback)
{
    utils.log('###工作完成',event.task);
    var arr=[['lrem','guq:queue:working',0,event.task],['del','guq:task:'+event.task]];
    redisClient.batch(arr).exec(function(err,res)
    {
        if(err){
            console.log('清理任务失败',err);
        }
        removeData(event.current,event.task);
        callback();
    });
}

//工作失败处理
function workerError(event,callback)
{
    utils.log('###工作失败',event.task,event.msg);
    var key='guq:task:'+event.task;
    if(event['code']=='2'||event['code']=='3'){
        lremDel(event.code,event.task,function(){
            workerNext(callback,event);
        })
    }else{
        redisClient.hgetall(key,function (err,res) {
            if(err){
                return console.log(err);
            }
            var tmp=[['hincrby',key,'num',1]];
            if(typeof res=='undefined'||!res||typeof res!='object'){
                console.log('2016年07月31日发现未知BUG打印1',event,key,res);
                return lremDel('3',event.task,function(){
                    workerNext(callback,event);
                });
            }else if(typeof res['num']=='undefined'){
                console.log('2016年07月31日发现未知BUG打印2',event,key,res);
                res['num']=0;
            }
            tmp.push(['hset',key,'err_'+res['num'],event.msg]);
            redisClient.batch(tmp).exec(function (batchErr,batchRes) {
                if(batchErr){
                    return console.log(batchErr);
                }
                if(res['num']<2){
                    workerNext(callback,event,'guq:queue:working','guq:queue:wait');
                }else{
                    workerNext(callback,event,'guq:queue:working','guq:queue:failed');
                }
            });
        });
    }
}

//删除错误任务
function lremDel(code,task,callback)
{
    var key='guq:task:'+task;
    var tmp=[['lrem','guq:queue:working',0,task]];
    if(code=='3'){
        tmp.push(['del',key]);
    }
    redisClient.batch(tmp).exec(function(batchErr,batchRes)
    {
        if(batchErr){
            console.log(batchErr);
        }
        callback();
    });
}

function workerNext(callback,event,from,to)
{
    if(typeof from!='undefined'){
        var rlArr=[['lrem',from,0,event.task],['lpush',to,event.task]];
        redisClient.multi(rlArr).exec(function(rpErr,rpRes){
            if(rpErr){
                return console.log(rpErr);
            }
            removeData(event.current,event.task);
            callback();
        });
    }else{
        removeData(event.current,event.task);
        callback();
    }
}

function removeData(current,data)
{
    var index=processDataList[current].indexOf(data);
    if(index>-1){
        processDataList[current].splice(index,1);
    }
}

function count(list)
{
    var total=0;
    for(var i=0;i<list.length;i++){
        if(list[i]){
            total++;
        }
    }
    return total;
}

exports.getProcessNum=function()
{
    return count(processList);
}

exports.getNextDate=function()
{
    return gNextDate;
}

exports.getWorkerNum=function()
{
    return {workerNum:workerNum,maxWorkerNum:maxWorkerNum};
}

exports.getExitNum=function () {
    return exitNum;
}

exports.retryListen=function(){
    checkListen();
}

exports.stopListen=function(){
    isStopListen=true;
}

exports.startListen=function(){
    isStopListen=false;
    checkListen();
}