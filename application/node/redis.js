let config = require('./config.js');
let redis = require('redis');
let util = require('util');
let utils = require('./utils');

//redis挂掉后重试策略
function retryStrategy(options) {
    console.log('Redis: retry');
    if (options.error.code === 'ECONNREFUSED') {
        return new Error('The server refused the connection');
    }
    if (options.total_retry_time > 1000 * 60 * 60) {
        return new Error('Retry time exhausted');
    }
    if (options.times_connected > 10) {
        return undefined;
    }
    return Math.max(options.attempt * 100, 3000);
}

exports.createClient = function (options) {
    options = utils.extend(config.redis, options);
    return redis.createClient(options);
};