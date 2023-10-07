let nodemailer = require("nodemailer");
let crypto = require('crypto');

//发送邮件
exports.sendEmail = function (email, title, content) {
    let config = require('./config.js');
    let transport = nodemailer.createTransport(config.email);
    transport.sendMail({
        from: config.email.auth.user,
        to: email,
        subject: title,
        generateTextFromHTML: true,
        html: content
    }, function (error, response) {
        if (error) {
            console.log(error);
        } else {
            console.log("email message[title:" + title + "] sent: " + response.message);
        }
        transport.close();
    });
};

exports.getClientIp = function (req) {
    return req.headers['x-forwarded-for'] ||
        req.connection.remoteAddress ||
        req.socket.remoteAddress ||
        req.connection.socket.remoteAddress;
};

exports.empty = function (val) {
    return typeof val == 'undefined' || !val;
};

exports.isset = function (val) {
    return typeof val != 'undefined';
};

exports.base64_decode = function (data) {
    let buf = new Buffer(data, 'base64');
    return buf.toString();
};

exports.dateFormat = function (fmt, date) {
    if (typeof date == 'undefined') {
        date = new Date();
    }
    let o = {
        "m+": date.getMonth() + 1, //月份
        "d+": date.getDate(), //日
        "h+": date.getHours(), //小时
        "i+": date.getMinutes(), //分
        "s+": date.getSeconds(), //秒
        "q+": Math.floor((date.getMonth() + 3) / 3), //季度
        "c": date.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (let k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
};

exports.loop = function (list, handler, callback) {
    let index = 0;

    function next() {
        if (index < list.length) {
            handler(list[index], function (err) {
                if (err) {
                    return (typeof callback == 'function' ? callback(err) : null);
                }
                index++;
                next();
            });
        } else {
            return (typeof callback == 'function' ? callback() : null);
        }
    }

    next();
};

exports.while = function (condition, handler, callback) {
    function next() {
        if (condition()) {
            handler(function (err) {
                if (err) {
                    return (typeof callback == 'function' ? callback(err) : null);
                }
                next();
            });
        } else {
            return (typeof callback == 'function' ? callback() : null);
        }
    }

    next();
};

exports.getUcode = function (count, type) {
    count = typeof count == 'undefined' ? 6 : count;
    type = typeof count == 'undefined' ? '1' : type;
    let code = '', tmp = '';
    let abc = 'abcdefghijklmnopqrstuvwxyz';
    let ABC = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    let num = '0123456789';
    if (type.indexOf('a') > -1) {
        code += abc;
    }
    if (type.indexOf('A') > -1) {
        code += ABC;
    }
    if (type.indexOf('1') > -1) {
        code += num;
    }
    for (let i = 0; i < count; i++) {
        let index = Math.round(Math.random() * code.length);
        tmp += code[index];
    }
    return tmp;
};

exports.sha1 = function (data) {
    let sha1 = crypto.createHash('sha1');
    sha1.update(data, 'utf8');
    return sha1.digest('hex');
};

exports.md5 = function (data) {
    let md5 = crypto.createHash('md5');
    md5.update(data, 'utf8');
    return md5.digest('hex');
};

//秒戳
exports.getTime = function () {
    return Math.floor(new Date().getTime() / 1000);
};

/**
 *
 * @param msg
 * @param data
 * @param level
 * fatal 致命错误 fat
 * warning 警告 war
 * error 一般性错误 err
 * info 重要信息 inf
 */
exports.log = function (msg, data, level) {
    let config = require('./config.js');
    if (config.debug || level == 'fat' || level == 'fatal') {
        console.log(msg);
        if (typeof data != 'undefined') {
            console.log(data);
        }
    }
};

//扩展对象
exports.extend = function () {
    let tmp = {};
    for (let i = 0; i < arguments.length; i++) {
        if (typeof arguments[i] == 'object') {
            tmp = exports.mergeObj(tmp, arguments[i]);
        }
    }
    return tmp;
};

//合并对象
exports.mergeObj = function (obj1, obj2) {
    obj1 = typeof obj1 == 'object' ? obj1 : {};
    for (var key in obj2) {
        if (typeof obj1[key] == 'object' && typeof obj2[key] == 'object') {
            if (typeof obj1[key]['length'] == 'undefined' && typeof obj2[key]['length'] == 'undefined') {
                obj1[key] = exports.mergeObj(obj1[key], obj2[key]);
            } else if (typeof obj1[key]['length'] != 'undefined' && typeof obj2[key]['length'] != 'undefined') {
                obj1[key] = obj1[key].concat(obj2[key]);
            } else {
                obj1[key] = obj2[key];
            }
        } else {
            obj1[key] = obj2[key];
        }
    }
    return obj1;
};

//循环创建目录
exports.mkdirs = function (dirpath, mode, callback) {
    path.exists(dirpath, function (exists) {
        if (exists) {
            callback(dirpath);
        } else {
            //尝试创建父目录，然后再创建当前目录
            mkdirs(path.dirname(dirpath), mode, function () {
                fs.mkdir(dirpath, mode, callback);
            });
        }
    });
};