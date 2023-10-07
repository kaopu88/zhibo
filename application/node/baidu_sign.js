let utils = require('./utils');
let config = require('./config');
let url = require('url');
let ak = config.baidu_map.ak;
let sk = config.baidu_map.sk;
exports.createSign = function (uri, param) {
    return caculateAKSN(ak, sk, uri, param);
};

exports.getUrl = function (api, param) {
    param = param ? param : {};
    let info = url.parse(api);
    param['ak'] = ak;
    param['sn'] = caculateAKSN(ak, sk, info['path'], param);
    let queryStr = '';
    for (let key in param) {
        queryStr += key + '=' + encodeURIComponent(param[key]) + '&';
    }
    queryStr = queryStr.replace(/\&$/, '');
    return api.indexOf('?') > -1 ? api + '&' + queryStr : api + '?' + queryStr;
};

exports.caculateAKSN = caculateAKSN;

function caculateAKSN(ak, sk, url, param, method) {
    method = typeof method == 'undefined' ? 'GET' : method;
    let keys = [];
    for (let key in param) {
        keys.push(key);
    }
    if (method === 'POST') {
        keys.sort();
    }
    let queryStr = '';
    for (let i = 0; i < keys.length; i++) {
        queryStr += keys[i] + '=' + param[keys[i]] + '&';
    }
    queryStr = queryStr.replace(/\&$/, '');
    return utils.md5(encodeURIComponent(url + '?' + queryStr + sk));
}