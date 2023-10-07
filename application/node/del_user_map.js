var config=require('./config.js');
var redis = require('redis');
var utils=require('./utils');
var redis_scanner = require('redis-scanner');
var client = redis.createClient(config.redis);
var mysql=require('./mysql');
var total=0;

//连接redis
console.log('当前运行环境:'+config.env_name);
client.on('connect',function(){
    mysql.query('SELECT `app_key` FROM PREFIX_site where 1',function(err,list){
        utils.loop(list,function(item,next){
            console.log('app:'+item['app_key']);
            client.zrange(['app_als:'+item['app_key'],0,-1],function(err2,res2){
                actList(res2,next);
            });
        },function(){
            console.log('end! del total:'+total);
            process.exit();
        });
    })
});

function actList(list,callback)
{
    utils.loop(list,function(item,next){
        console.log('act:'+item);
        var arr=[['del',item+':map:user:p'],['del',item+':map:user:c']];
        client.batch(arr).exec(function(err,res){
            var key='';
            if(res[0]>0){
                total++;
                key=item+':map:user:p';
            }
            if(res[1]>0){
                total++;
                key=item+':map:user:c';
            }
            if(total%10==0){
                console.log('current del total:'+total+';key:'+key);
            }
            next();
        });
    },function(){
        callback();
    });
}