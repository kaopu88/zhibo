let utils = require('./utils');

//默认配置
let config = {
    master_email: '840855344@qq.com',//站长邮箱
    site_domain: '127.0.0.1',
    debug: false,
    debug_api: false,
    port: 3000,//服务端口
    queues: {
        events: {
            port: 80,
            name: 'events',
            workerParams: ['events'],
            isHash: true,
            errEchoNum: 3,
            workerJs: 'worker/Worker.js',
            maxCpuNum: 3,
            timeout: 5000
        },
        ips: {
            port: 80,
            name: 'ips',
            workerParams: ['ips'],
            isHash: true,
            errEchoNum: 3,
            workerJs: 'worker/IpWorker.js',
            maxCpuNum: 3,
            timeout: 5000
        }
    },
    connection: {
        name: 'connection_auth',
        token: 'erp@tongji#1046!'
    },
    sign_token: 'erp@tongji#1046!',
    email: {},
    guser: {
        test_time: 60 * 20 * 1000,//测试阶段20分钟一次
        task_time: [2, 0, 0],//每日定时时间
        max_worker_num: 2,//最大工作进程数
        max_cpu_num: 3,//单个核心最大任务数
        do_len: 200,//每次执行APP收集条数
        query_len: 100,//每次查询条数
        timeout: 7000//7s
    },
    baidu_map: {
        ak: '75btOpfaFpbhVeseBFiCUT7gD3711bpG',
        sk: 'TXCQmukuWbRM9oxidkDiQ2nRIX0IhwUh'
    },
    env_name: '',
    redis:{},
    mysql:{},
};

if (process.argv[2] == '' || process.argv[2] == 'undefined')
{
    console.error('未设置运行环境');
    return false;
}

if (process.argv[3] == '' || process.argv[3] == 'undefined')
{
    console.error('未设置redis配置');
    return false;
}

let env_name = process.argv[2];

let env_config = JSON.parse(process.argv[3]);

try {

    config = utils.extend(env_config, config);

    config['env_name'] = env_name;

} catch (e) {
    console.error('error:'+env_name+' 配置不存在');
}

module.exports = config;