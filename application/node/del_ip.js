var config=require('./config.js');
var redis = require('redis');
var redis_scanner = require('redis-scanner');
var client = redis.createClient(config.redis);
var total=0;

//连接redis
console.log('当前运行环境:'+config.env_name);
client.on('connect',function(){
    var scanner = new redis_scanner.Scanner(client, 'SCAN', null, {
        pattern: 'gip:*',
        onData: function(result,done){
            if(/^gip\:[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/.test(result)){
                client.del(result,function(err2,res2){
                    if(!err2){
                        total++;
                        if(total%100==0){
                            console.log('current del ip:'+total);
                        }
                        done();
                    }
                });
            }
        },
        onEnd: function(err){
            if(total>0){
                client.hincrby(['sys:tj','ip_num',-(total)]);
            }
            console.log('end! del ip:'+total);
            process.exit();
        }
    });
    scanner.start();
});