var config=require('../config');
var utils=require('../utils');
var fs=require('fs');
var http=require('http');
var querystring=require('querystring');
var util=require('util');
var sign=require('../sign');
var baiduSign=require('../baiduSign');
var redisClient=require('../redis').createClient();
var apiName=typeof process.argv[3]=='undefined'?'default':process.argv[3];
var apiConfig=config['queues'][apiName];

//工作
process.on('message',function(data){
    if(data['type']=='doWork'){
        worker(data);
    }
});

//连接redis成功
redisClient.on('connect',function(){
    process.send({type:'init'});
});

//连接redis失败
redisClient.on('error',function(err){
    console.log('IpWorker redis connect error');
    process.exit();
});

//收到kill信息，进程退出
process.on('SIGHUP', function() {
    process.exit();
});

function worker(workData)
{
    var ip=workData['ip'];
    var startMs=workData['startMs']?workData['startMs']:0;
    var taskKey='giq:task:'+ip;
    var url=baiduSign.getUrl('http://api.map.baidu.com/location/ip',{'ip':ip});
    getTask(url,ip,function(err3,data) {
        if(err3){
            return process.send(getError(err3['msg'],1));
        }
        handler(data);
    });

    function getTask(url,ip,callback)
    {
        var request = http.get(url, function (res) {
            if (res.statusCode != 200) {
                return callback({'msg': '请求状态码不是200'});
            }
            var responseText = [];
            var size = 0;
            res.on('data', function (data) {
                responseText.push(data);
                size += data.length;
            })
            res.on('end', function () {
                responseText = Buffer.concat(responseText, size);
                var obj = {};
                try {
                    obj = JSON.parse(responseText.toString());
                } catch (e) {
                    return callback({'msg': '数据格式错误'});
                }
                if (!obj || typeof obj['status'] == 'undefined' || obj['status'] != 0 || typeof obj['content'] == 'undefined' || !obj['content']) {
                    var message = '';
                    if (obj && typeof obj['message'] != 'undefined') {
                        message = ',返回消息：[status '+obj['status']+']'+ obj['message'];
                    }
                    return callback({msg: '获取数据失败' + message});
                }
                callback(null, obj);
            })
        });
        request.on('error', function (err) {
            var info = err['code'] == 'ECONNRESET' ? '请求超时' : '请求时发生错误';
            callback({msg: info});
        });
        //10s超时
        request.setTimeout(apiConfig.timeout, function () {
            request.abort();
        });
    }

    //处理数据
    function handler(data)
    {
        //交给PHP服务端处理
        var postData = querystring.stringify({ipData:JSON.stringify(data),ip:ip});
        var options = {
            hostname: config.post_ip.hostname,
            port: config.post_ip.port,
            path:config.post_ip.path,
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Content-Length': postData.length
            }
        };
        var request=http.request(options,function(res){
            if(res.statusCode!=200) {
                return process.send(getError('请求PHP服务端状态码不是200',4));
            }
            var responseText=[];
            var size = 0;
            res.on('data',function(data) {
                responseText.push(data);
                size+=data.length;
            })
            res.on('end',function() {
                responseText = Buffer.concat(responseText,size);
                if(config.debug_api){
                    var path='response/post_ip/'+utils.dateFormat('yyyy-m-d');
                    if(!fs.existsSync(path)){
                        fs.mkdirSync(path,777);
                    }
                    fs.writeFileSync(path+'/'+ip+'.html', responseText.toString());
                }
                var obj={};
                try {
                    obj=JSON.parse(responseText.toString());
                }catch (e) {
                    return process.send(getError('请求PHP服务端返回数据格式错误',4));
                }
                if(!obj||typeof obj['status']=='undefined'||obj['status']!=0){
                    var info='';
                    if(obj&&typeof obj['info']!='undefined'){
                        info=',返回消息：'+obj['info'];
                    }
                    return process.send(getError('提交数据到PHP服务端失败'+info,4));
                }
                //集中清理
                var bt=[['srem','giq:has_ips',ip],['del','giq:sub:'+ip]];
                redisClient.batch(bt).exec(function(btErr,btRes){
                    if(btErr){
                        console.log('清理giq:has_ips和sub失败',btErr);//不退出
                    }
                    return process.send({type:'complete',ip:ip,startMs:startMs});
                });
            })
        })
        request.on('error', function(err) {
            var info=err['code']=='ECONNRESET'?'请求PHP服务端超时':'请求PHP服务端时发生错误';
            return process.send(getError(info,4));
        });
        //10s超时
        request.setTimeout(timeout,function()
        {
            request.abort();
        });
        request.write(postData);
        request.end();
    }

    function getError(msg,code,err)
    {
        return {
            type:'error',
            msg:msg,
            code:typeof code=='undefined'?1:code,
            ip:ip,
            startMs:startMs,
            originalErr:err
        };
    }
}