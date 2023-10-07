let querystring = require('querystring');
let http = require('http');
let fs = require('fs');

function Poster(apiName, apiConfig) {
    let utils = require('../utils');
    this.send = function (params, callback) {
        let postData = querystring.stringify(params);
        let options = {
            hostname: apiConfig.hostname,
            port: apiConfig.port,
            path: apiConfig.path,
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Content-Length': postData.length
            }
        };

        let request = http.request(options, function (res) {
            if (res.statusCode != 200) {
                return callback(getError('http status not 200', 4));
            }
            let responseText = [];
            let size = 0;
            res.on('data', function (data) {
                responseText.push(data);
                size += data.length;
            });
            res.on('end', function () {
                responseText = Buffer.concat(responseText, size);
                let path = 'response/' + apiName, obj = {};
                if (apiConfig.debug) {
                    if (!fs.existsSync(path)) {
                        fs.mkdirSync(path, 777);
                    }
                    path += '/' + utils.dateFormat('yyyy-m-d');
                    if (!fs.existsSync(path)) {
                        fs.mkdirSync(path, 777);
                    }
                    fs.writeFileSync(path + '/' + encodeURIComponent(params['task']) + '.html', responseText.toString());
                }
                try {
                    obj = JSON.parse(responseText.toString());
                    if (!obj || typeof obj != 'object' || obj === null) {
                        return callback(getError('data format error', 4));
                    }
                } catch (e) {
                    return callback(getError('data format error', 4));
                }
                obj['status'] = typeof obj['status'] == 'undefined' ? 4 : obj['status'];
                if (obj['status'] != 0) {
                    let info = typeof obj['info'] != 'undefined' ? (' message：' + obj['info']) : '';
                    return callback(getError('failed' + info, obj['status']));
                }
                return callback(null, params);
            })
        });
        request.on('error', function (err) {
            let code = 4, info = 'http request error';
            if (err['code'] == 'ECONNRESET') {
                info = 'timeout';
                if (typeof apiConfig['timeout_code'] != 'undefined') {
                    code = parseInt(apiConfig['timeout_code']);
                }
            }
            return callback(getError(info, code));
        });
        //默认10s超时
        let timeout = utils.empty(apiConfig['timeout']) ? (10 * 1000) : parseInt(apiConfig['timeout']);
        request.setTimeout(timeout, function () {
            request.abort();
        });
        request.write(postData);
        request.end();
    };

    //获取一个封装好的错误对象
    function getError(msg, code, err) {
        return {
            msg: msg,
            code: typeof code == 'undefined' ? 1 : code,
            originalErr: err
        };
    }

}

exports.createPoster = function (apiName, apiConfig) {
    return new Poster(apiName, apiConfig);
};