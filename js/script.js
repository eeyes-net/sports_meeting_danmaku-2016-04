/////////////////////////////////////////////////////////////
//
//  弹幕动态脚本
//  Version: 2.0.0
//
//  Author: Ganlv<ganlvtech at qq dot com>
//  Organisation: eeYes.net<https://github.com/eeyes-net>
//
//  LICENSE: Apache 2.0
//
/////////////////////////////////////////////////////////////
/**
 * 弹幕构造器
 * @param div {HTMLDivElement} 弹幕的div元素
 * @param [position=0] {number} 弹幕位置
 * @param [duration=5] {number} 弹幕持续时间（秒）
 * @constructor
 */
function Danmaku(div, position, duration) {
    div.style.opacity = '0';
    div.style.position = 'absolute';
    div.style.textShadow = '#000 0 0 2px';
    div.style.fontWeight = 'bold';
    div.style.fontFamily = '黑体, sans-serif';
    div.style.whiteSpace = 'nowrap';
    this.div = div;
    this.position = position || 0;
    this.duration = duration || 5;
    this.width = NaN;
    this.height = NaN;
    this.time = NaN;
}
/**
 * 弹幕引擎
 * @param wrapper {HTMLDivElement} 容器元素
 * @constructor
 */
function DanmakuPainter(wrapper) {
    this.wrapper = wrapper;
    this.wrapper.style.position = 'relative';
    this.container = document.createElement('div');
    this.container.style.position = 'absolute';
    this.container.style.left = '0';
    this.container.style.top = '0';
    this.container.style.width = '100%';
    this.container.style.height = '100%';
    this.container.style.fontSize = '0.8em';
    this.container.style.overflow = 'hidden';
    this.container.style.pointerEvents = 'none';
    this.resize();
    this.wrapper.appendChild(this.container);
    this.offsets = [0, 0, 0];
    this.danmakus = [];
    setInterval(this.paint.bind(this), 15);
}
/**
 * 调整大小
 */
DanmakuPainter.prototype.resize = function () {
    this.width = this.container.clientWidth;
    this.height = this.container.clientHeight;
}
/**
 * 获取当前时间戳
 * @returns {number}
 */
DanmakuPainter.prototype.now = function () {
    return Date.now() / 1000;
};
/**
 * 发射弹幕
 * @param danmaku {Danmaku} 弹幕对象
 * @param [delay=0] {number} 延时发射（秒）
 */
DanmakuPainter.prototype.launch = function (danmaku, delay) {
    danmaku.div.style.left = this.width + 'px';
    this.container.appendChild(danmaku.div);
    danmaku.width = danmaku.div.clientWidth;
    danmaku.height = danmaku.div.clientHeight;
    if (this.offsets[danmaku.position] + danmaku.height > this.height) {
        this.offsets[danmaku.position] = 0;
    }
    switch (danmaku.position) {
        case 0:
            danmaku.div.style.left = this.width + 'px';
            break;
        case 1:
        case 2:
            danmaku.div.style.left = ((this.width - danmaku.width) / 2) + 'px';
            break;
    }
    switch (danmaku.position) {
        case 0:
        case 1:
            danmaku.div.style.top = this.offsets[danmaku.position] + 'px';
            this.offsets[danmaku.position] += danmaku.height;
            break;
        case 2:
            this.offsets[danmaku.position] += danmaku.height;
            danmaku.div.style.top = (this.height - this.offsets[danmaku.position]) + 'px';
            break;
    }
    delay = delay || 0;
    danmaku.time = this.now() + delay;
    this.danmakus.push(danmaku);
};
/**
 * 重绘（即重新定位普通弹幕）
 */
DanmakuPainter.prototype.paint = function () {
    var t = this.now();
    for (var i in this.danmakus) {
        var danmaku = this.danmakus[i];
        var dt = t - danmaku.time;
        if (dt > danmaku.duration) {
            switch (danmaku.position) {
                case 0:
                case 1:
                    if (danmaku.div.offsetTop < this.offsets[danmaku.position]) {
                        this.offsets[danmaku.position] = danmaku.div.offsetTop;
                    }
                    break;
                case 2:
                    var offset = this.height - (danmaku.div.offsetTop + danmaku.div.offsetHeight);
                    if (offset < this.offsets[danmaku.position]) {
                        this.offsets[danmaku.position] = offset;
                    }
                    break;
            }
            this.container.removeChild(danmaku.div);
            this.danmakus.splice(i, 1);
        } else if (dt >= 0) {
            danmaku.div.style.opacity = '';
            switch (danmaku.position) {
                case 0:
                    danmaku.div.style.left = (this.width - dt / danmaku.duration * (this.width + danmaku.width)) + 'px';
                    break;
                case 1:
                case 2:
                    danmaku.div.style.left = ((this.width - danmaku.width) / 2) + 'px';
                    break;
            }
        }
    }
};

/**
 * 弹幕管理
 * 主要用于发送弹幕和从服务器获取弹幕
 * painter {DanmakuPainter} 弹幕引擎
 * @constructor
 */
function DanmakuManager(painter) {
    this.lastId = -1;
    this.painter = painter;
    var that = this;
    $.post('api/danmaku.php', {last_id: -1}, function (data) {
        if (data.retcode >= 0) {
            that.lastId = data.last_id - 50;
            if (that.lastId < 0) {
                that.lastId = 0;
            }
        }
    });
    setInterval(this.poll.bind(this), 3000);
}
/**
 * 构造弹幕div
 * @param text {string} 弹幕文本
 * @param color {string} 弹幕颜色
 * @param fontSize {string} 弹幕字号大小
 * @param [isSelf=false] {boolean} 是否是自己的弹幕（自己的弹幕有边框）
 * @returns {HTMLDivElement}
 */
DanmakuManager.prototype.buildDiv = function (text, color, fontSize, isSelf) {
    var div = document.createElement('div');
    div.textContent = text;
    div.style.color = color;
    div.style.fontSize = fontSize;
    isSelf = isSelf || false;
    if (isSelf) {
        div.style.border = '2px solid #fff';
    }
    return div;
};
/**
 * 十进制颜色值转换为颜色文本
 * @param n {number} 十进制颜色值
 * @returns {string}
 */
DanmakuManager.prototype.intToColor = function (n) {
    n = parseInt(n);
    if (n < 0 || n > 0xffffff) {
        n = 0xffffff;
    }
    n = n.toString(16);
    return '#' + '0'.repeat(6 - n.length) + n;
};
/**
 * 显示弹幕
 * @param text {string} 弹幕文本
 * @param color {string} 弹幕颜色
 * @param size {number} 0: 小字（1em） 1:大字（1.3em）
 * @param [isSelf=false] {boolean} 是否是自己的弹幕（自己的弹幕有边框）
 * @param [position=0] {number} 弹幕位置
 * @param [delay=0] {number} 延时几毫秒后发射
 */
DanmakuManager.prototype.show = function (text, color, size, isSelf, position, delay) {
    var fontSize = (parseInt(size) == 1) ? '1.3em' : '1em';
    this.painter.launch(new Danmaku(this.buildDiv(text, color, fontSize, isSelf), parseInt(position)), parseInt(delay));
};
/**
 * 轮询新弹幕
 */
DanmakuManager.prototype.poll = function () {
    var that = this;
    $.post("api/danmaku.php", {last_id: this.lastId}, function (data) {
        if (data.retcode >= 0) {
            that.lastId = data.last_id;
            var danmakuCount = data.danmaku.length;
            for (var i in data.danmaku) {
                var danmaku = data.danmaku[i];
                var delay = danmaku.timestamp - data.danmaku[0].timestamp + Math.random() * 3;
                that.show(danmaku.text, that.intToColor(danmaku.color), danmaku.size, false, danmaku.position, delay);
            }
        }
    });
};
/**
 * 弹幕常用颜色表
 * @type {{b: number, g: number, r: number, c: number, m: number, y: number, k: number, w: number}}
 */
DanmakuManager.prototype.colorTable = {
    b: 0x0000ff,
    g: 0x00ff00,
    r: 0xff0000,
    c: 0x00ffff,
    m: 0xff00ff,
    y: 0xffff00,
    k: 0x000000,
    w: 0xffffff
};
/**
 * 先解析文本，再发射弹幕
 * @param text 待解析文本
 */
DanmakuManager.prototype.launch = function (text) {
    var that = this;
    var position = 0, color = 0xffffff, size = 0;
    var matches = text.match(/^\$\(([012]?),([bgrcmykw]?|[0-9a-f]{6}),([01]?)\)(.+)$/i);
    if (matches) {
        if (matches[1]) {
            position = matches[1];
        }
        if (matches[2]) {
            switch (matches[2].length) {
                case 1:
                    color = this.colorTable[matches[2].toLowerCase()];
                    break;
                case 6:
                    color = parseInt(matches[2], 16);
                    break;
            }
        }
        if (matches[3]) {
            size = matches[3];
        }
        text = matches[4];
    }
    $.post('api/danmaku.php', {
        text: text,
        position: position,
        color: color,
        size: size
    }, function (data) {
        if (data.retcode >= 0) {
            that.lastId = data.id;
            that.show(text, that.intToColor(color), size, true, position, 0);
            $("#danmaku-text").val($("#danmaku-text").val().slice(0, -text.length));
        }
    });
};

/**
 * 投票Object构造器
 * @constructor
 */
function Vote() {
    this.targetCount = [];
    setInterval(this.increase.bind(this), 1000);
    setInterval(this.refresh.bind(this), 8000);
    this.refresh();
}
/**
 * 票数span的jQuery对象
 * @type {Array}
 */
Vote.prototype.countSpan = [];
for (var i = 0; i < 8; ++i) {
    Vote.prototype.countSpan[i] = $('#vote-map button[data-id="' + i + '"] span');
}
/**
 * 投票
 * @param id 书院id
 */
Vote.prototype.vote = function (id) {
    var that = this;
    $.post('api/vote.php', {id: id}, function (data) {
        if (data.retcode >= 0) {
            that.refresh();
            $('#vote-ok').addClass('vote-ok-indicate');
            setTimeout(function () {
                $('#vote-ok').removeClass('vote-ok-indicate');
            }, 1000);
        } else if (data.wait) {
            danmakuManager.show('您的投票速度过快，请稍等' + data.wait + '秒', 'yellow', 0, false, 2, 0);
        }
    });
};
/**
 * 票数过渡式增长
 */
Vote.prototype.increase = function () {
    for (var i in this.targetCount) {
        var currentCount = parseInt(this.countSpan[i].text());
        if (currentCount == 0) {
            this.countSpan[i].text(this.targetCount[i]);
        } else if (this.targetCount[i] > currentCount) {
            this.countSpan[i].text(currentCount + 1 + parseInt((this.targetCount[i] - currentCount) / 5));
        }
    }
};
/**
 * 刷新票数信息
 */
Vote.prototype.refresh = function () {
    var that = this;
    $.post('api/vote.php', {}, function (data) {
        for (var i in data.result) {
            that.targetCount[i] = parseInt(data.result[i]);
        }
    });
};
/**
 * 微信分享
 * @param id 书院id
 */
Vote.prototype.share = function (id) {
    var collegeName = $('#vote-map button[data-id="' + id + '"]').contents()[0];
    var collegeVoteCount = this.targetCount[id] + 1;
    var rank = 1;
    for (var i in this.targetCount) {
        if (this.targetCount[i] > collegeVoteCount) {
            ++rank;
        }
    }
    document.title = '我是第' + collegeVoteCount + '个给' + collegeName + '书院加油的小伙伴，现在' + collegeName + '书院排名第' + rank + '哟';
};

var danmakuPainter = new DanmakuPainter($('#danmaku-wrapper')[0]);
var danmakuManager = new DanmakuManager(danmakuPainter);
var vote = new Vote(danmaku);

(window.onresize = function () {
    $('html').css('font-size', $('body').height() * 20 / 568 + 'px'); // 适应高度
    danmakuPainter.resize.bind(danmakuPainter)();
})();
$('#vote-map button').on('click', function () {
    vote.vote($(this).data('id'));
});
$('#danmaku-send button').click(function () {
    danmakuManager.launch($("#danmaku-text").val());
});
$("#danmaku-text").on('keyup', function (event) {
    if (event.keyCode == 13) {
        $('#danmaku-send button').click();
    }
});

danmakuManager.show('点击书院进行投票吧', 'red', 0, false, 1, 0.0);
danmakuManager.show('每5分钟可以投票一次', 'lime', 0, false, 2, 1.5);
danmakuManager.show('在下方输入文字', 'white', 0, false, 0, 2.7);
danmakuManager.show('点击发送即可发射弹幕', 'white', 0, false, 0, 3.9);
danmakuManager.show('邀请别人一起来吧', 'yellow', 1, false, 2, 5.2);
danmakuManager.show('同学们请勿恶意刷票', 'red', 1, false, 0, 6.5);
danmakuManager.show('e瞳出品', 'blue', 1, false, 0, 7.8);
danmakuPainter.launch(new Danmaku($('<div><img src="pic/logo.png"></div>')[0], 2, 3), 7.8)