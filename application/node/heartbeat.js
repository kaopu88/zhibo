let events = require('events');
let util = require('util');

function Heartbeat() {
    events.EventEmitter.call(this);
    let timer, total = 0, that = this;
    this.start = function () {
        timer = setInterval(function () {
            total++;
            that.emit('peng', {total: total});
        }, 1000);
    };

    this.stop = function () {
        if (timer) {
            clearInterval(timer);
            timer = null;
            total = 0;
        }
    }
}

util.inherits(Heartbeat, events.EventEmitter);
exports.Heartbeat = Heartbeat;
exports.createHeartbeat = function () {
    return new Heartbeat();
};