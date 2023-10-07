var app=require('./app');

exports.tongji=function(req,res)
{
    var dataQ=require('./DataQueueManager');
    var userQ=require('./UserQueueManager');
    var ipQ=require('./IpQueueManager');
    var data={rtNum:app.rtNum()};
    var minTime=dataQ.getMinTime();//ms
    var maxTime=dataQ.getMaxTime();//ms
    data['data']={processNum:dataQ.getProcessNum(),minTime:minTime,maxTime:maxTime,aveTime:dataQ.getAveTime(),exitNum:dataQ.getExitNum()};
    var nextDate=userQ.getNextDate();
    var str=nextDate.getFullYear()+'年'+(nextDate.getMonth()+1)+'月'+nextDate.getDate()+'日'+nextDate.getHours()+'时'+nextDate.getMinutes()+'分'+nextDate.getSeconds()+'秒';
    data['user']={processNum:userQ.getProcessNum(),nextStart:str,exitNum:userQ.getExitNum()};
    data['user']['workerNum']=userQ.getWorkerNum();
    data['ip']={processNum:ipQ.getProcessNum(),exitNum:ipQ.getExitNum()};
    data['runLen']=Math.round(app.getRunLen()/1000);//转为s
    res.json({status:0,data:data});
}

exports.retryListen=function(req,res){
    controllerQueue(req._get['queue'],'retryListen');
    res.json({status:0,info:''});
}

exports.stopListen=function (req,res) {
    controllerQueue(req._get['queue'],'stopListen');
    res.json({status:0,info:''});
}

exports.startListen=function (req,res) {
    controllerQueue(req._get['queue'],'startListen');
    res.json({status:0,info:''});
}

function controllerQueue(arr,funName,data)
{
    var list=['Data','User','Ip'];
    if(typeof arr=='undefined'||arr==''){
        arr=list;
    }else if(typeof arr=='string'){
        arr=arr.split(',');
    }
    for(var i=0;i<arr.length;i++){
        var name=String(arr[i]).replace(/(\w)/,function(v){return v.toUpperCase()});
        if(list.indexOf(name)>-1){
            var tmp=require('./'+name+'QueueManager');
            if(typeof tmp[funName]=='function'){
                tmp[funName](data);
            }
        }
    }
}