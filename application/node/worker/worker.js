let config = require('../config');
let apiName = typeof process.argv[3] == 'undefined' ? 'data' : process.argv[3], apiConfig = config['queues'][apiName];
apiConfig['debug'] = typeof apiConfig['debug'] == 'undefined' ? config.debug_api : apiConfig['debug'];
let poster = require('./poster').createPoster(apiName, apiConfig);

//工作
process.on('message', function (data) {
    if (data['type'] == 'doWork') {
        worker(data);
    }
});
process.send({type: 'init'});
//收到kill信息，进程退出
process.on('SIGHUP', function () {
    process.exit();
});

function worker(workData) {
    let task = workData['task'];
    let startMs = workData['startMs'] ? workData['startMs'] : 0;
    poster.send({task: task}, function (error, result) {
        if (error) {
            error['type'] = 'error';
            error['startMs'] = startMs;
            error['task'] = task;
            return process.send(error);
        }
        return process.send({
            'type': 'complete',
            'task': task,
            'startMs': startMs
        });
    });
}