var config=require('./config.js');
var redis = require('redis');
var redis_scanner = require('redis-scanner');
var client = redis.createClient(config.redis);
var total=0;

//连接redis
console.log('当前运行环境:'+config.env_name);
client.on('connect',function(){
    var scanner = new redis_scanner.Scanner(client, 'SCAN', null, {
        pattern: 'guser:*',
        onData: function(result,done){
            if(/^guser\:[0-9a-zA-Z_\-]{40}\:[0-9a-zA-Z_\-]{32}$/.test(result)){
                client.hgetall(result,function(err,res){
                    if(err){
                        return console.log(err);
                    }
                    if(res&&res['ill']=='1'){
                        var tmpArr=result.split(':');
                        var key='guser_inv:'+tmpArr[1];
                        client.sadd(key,tmpArr[2],function(err2,res2){
                            if(!err2){
                                client.del(result,function(err3,res3){
                                    if(!err3){
                                        total++;
                                        if(total%100==0){
                                            console.log('current:'+total);
                                        }
                                        done();
                                    }else{
                                        console.log(err3);
                                    }
                                });
                            }else{
                                console.log(err2);
                            }
                        });
                    }else{
                        done();
                    }
                })
            }
        },
        onEnd: function(err){
            console.log('end! del_inv_user:'+total);
            if(total>0){
                client.hmset('sys:tj',{thruser_inv_num:total});
            }
            process.exit();
        }
    });
    scanner.start();
});