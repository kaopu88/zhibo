let config = require('./config.js');
let crypto = require('crypto');

exports.isSign = function (sign, data, token) {
    return sign == generateSign(data, token);
};

function generateSign(data, token) {
    let tmp = {};
    token = typeof token == 'undefined' ? config.sign_token : token;
    for (let key1 in data) {
        tmp[key1] = data[key1];
    }
    tmp['token'] = token;
    let keys = [];
    for (let key in tmp) {
        if (key != 'sign') {
            keys.push(key);
        }
    }
    keys.sort();
    let str = '';
    for (let i = 0; i < keys.length; i++) {
        str += keys[i] + tmp[keys[i]];
    }
    return sha1(str);
}

exports.generateSign = generateSign;

function sha1(data) {
    let sha1 = crypto.createHash('sha1');
    sha1.update(data, 'utf8');
    return sha1.digest('hex');
}