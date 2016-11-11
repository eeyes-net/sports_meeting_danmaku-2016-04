$(document).ready(function () {
	+function () {
		//================= 调节height =================
		$("html").css("font-size", $("body").width() / 10);
		var height = $(".header").height() + $("#wrapper").height();
		$("body").height(height);
		$("#bg").height(height);
	}();
	+function () {
		//==================== 投票 ====================
		var m_targetCount = [];
		var m_collegeSpans = [];
		for (var i = 0; i < 8; ++i)
			m_collegeSpans[i] = $("#college" + i + " span");
		//API封装
		function voteFor(id, callback) {
			$.post("api/vote.php", {votefor: id}, function (data) {callback(JSON.parse(data));});
		}
		function voteResult(callback) {
			$.post("api/vote.php", {result: ''}, function (data) {callback(JSON.parse(data));});
		}
		//界面响应，投票成功则提示+1
		function voteOk() {
			var indicator = $('#voteOk');
			indicator.css("opacity", "1").stop().animate({
				top: '-0.6rem',
				opacity: '0'
			}, function () {
				indicator.css("top", "0.7rem");
			});
		}
		//动态过渡票数增加
		function voteAdd() {
			for (var i in m_targetCount) {
				var currentCount = parseInt(m_collegeSpans[i].html());
				if (currentCount == 0)
					m_collegeSpans[i].html(m_targetCount[i]);
				else if (m_targetCount[i] > currentCount) {
					m_collegeSpans[i].html(currentCount + 1 + parseInt((m_targetCount[i] - currentCount) / 5));
				}
			}
		}
		setInterval(voteAdd, 1000);
		//调用voteResult API的回调函数，解析返回数据
		function voteParseResult(jsonObj) {
			var results = jsonObj.result;
			for (var i in results)
				m_targetCount[i] = parseInt(results[i]);
		}
		//微信分享
		function forWeixinShare(jsonObj, collegeId) {
			var collegeName = $("#college" + collegeId).html().substr(0, 2);
			var results = jsonObj.result;
			var collegeVoteCount = results[collegeId];
			results.sort(function (a, b) {return (a < b) ? 1 : 0;});
			for (var i in results) {
				if (collegeVoteCount == results[i])
					document.title = "我是第" + collegeVoteCount + "个给" + collegeName + "书院加油的小伙伴，现在" + collegeName + "书院排名第" + (parseInt(i) + 1) + "哟";
			}
		}
		//点击投票
		$('#map a').click(function () {
			var collegeId = $(this).attr("id").substring(7);
			voteFor(collegeId, function (jsonObj) {
				if (jsonObj.retcode >= 0) {
					voteOk();
					voteResult(function (jsonObj) {
						voteParseResult(jsonObj);
						forWeixinShare(jsonObj, collegeId);
					});
				} else if (jsonObj.wait) {
					//有等待时间
					danmakuShow("您的投票速度过快，请稍等" + jsonObj.wait + "秒", 2, "yellow", 0, 0, false);
				}
			})
		});
		//轮询票数
		function pollVote() {
			voteResult(voteParseResult);
		}
		setInterval(pollVote, 8000);
		pollVote();
		//==================== 弹幕 ====================
		var m_lastid = -1;
		//API封装
		function danmakuLaunch(text, position, color, size, callback) {
			$.post("api/danmaku.php", {text: text, position: position, color: color, size: size}, function (data) {callback(JSON.parse(data));});
		}
		function danmakuGet(lastid, callback) {
			$.post("api/danmaku.php", {lastid: lastid}, function (data) {callback(JSON.parse(data));});
		}
		/********************************
		**                             **
		**     弹幕动态脚本 v 1.0.0    **
		**                             **
		**               Ganlv         **
		**           2016.04.13        **
		**      XJTU - eeYes.net       **
		**                             **
		********************************/
		function getTime() {
			return new Date().getTime() / 1000;
		}
		function intToColor(x) {
			x = parseInt(x);
			if (x < 0 || x > 16777215)
				x = 16777215;
			return "rgb(" + ((x >> 16) & 0xff) + "," + ((x >> 8) & 0xff) + "," + (x & 0xff) + ")";
		}
		var DANMAKU_DURATION = 5;
		var MAX_W = $("#dmSection").width(), MAX_H = $("#dmSection").height();
		var danmakuSets = [];
		var topBaseLine = 0, bottomBaseLine = MAX_H;
		//添加新弹幕
		function danmakuShow(text, position, color, size, time, isnew) {
			size = (size == 1 ? 20 : 16);
			var divDanmaku = $("<div/>")
				.addClass("danmaku")
				.html(text)
				.css("color", color)
				.css("font-size", size)
				.css("left", MAX_W)
				.css("top", parseInt((MAX_H - size) * Math.random()))
				.css("border", isnew ? "solid 2px white" : "");
			$("#dmWra").append(divDanmaku);
			danmakuSets.push({
				position : position,
				time     : getTime() + time,
				div      : divDanmaku,
				width    : divDanmaku.width()
			});
			if (position != 0)
				divDanmaku.css("left", (MAX_W - divDanmaku.width()) / 2);
			if (position == 1) {
				topBaseLine += size;
				if (topBaseLine > MAX_H / 2)
					topBaseLine = size;
				divDanmaku.css("top", topBaseLine - size);
			} else if (position == 2) {
				bottomBaseLine -= size;
				if (bottomBaseLine < MAX_H / 2)
					bottomBaseLine = MAX_H - size;
				divDanmaku.css("top", bottomBaseLine)
			}
		}
		//定时移动弹幕
		function danmakuTimer() {
			var t = getTime();
			for (var i in danmakuSets) {
				var danmaku = danmakuSets[i];
				var dt = t - danmaku.time;
				if (dt > DANMAKU_DURATION) {
					danmaku.div.remove();
					danmakuSets.splice(i, 1);
				} else if (dt >= 0) {
					danmaku.div.css("display", "block")
					if (danmaku.position == 0)
						danmaku.div.css("left", MAX_W - dt / DANMAKU_DURATION * (MAX_W + danmaku.width));
				} else {
					danmaku.div.css("display", "none")
				}
			}
		}
		setInterval(danmakuTimer, 15);
		/********************************
		**        弹幕引擎结束         **
		********************************/
		//初始化提示弹幕
		danmakuShow("点击书院进行投票吧"  , 1, "red"   , 0, 0.0, false);
		danmakuShow("每5分钟可以投票一次" , 2, "lime"  , 0, 1.5, false);
		danmakuShow("在下方输入文字"      , 0, "white" , 0, 2.7, false);
		danmakuShow("点击发送即可发射弹幕", 0, "white" , 0, 3.9, false);
		danmakuShow("邀请别人一起来吧"    , 2, "yellow", 1, 5.2, false);
		danmakuShow("同学们请勿恶意刷票"  , 0, "red"   , 1, 6.5, false);
		danmakuShow("e瞳出品"             , 0, "blue"  , 1, 7.8, false);
		//点击发送
		$('#dmSend button').click(function() {
			//弹幕不能为空
			var position = 0, color = 16777215, size = 0;
			var text = $('#dmText').val();
			var regArr = text.match(/^!\(([012]?)\|([bgrcmykw]?|[0-9A-Fa-f]{6})\|([01]?)\)(.+)/);
			if (regArr) {
				if (regArr[1]) position = regArr[1];
				if (regArr[3]) size     = regArr[3];
				if (regArr[2].length == 1 || isNaN("0x" + regArr[2])) {
					switch (regArr[2][0]) {
						case 'b':
							color = 0x0000ff;
							break;
						case 'g':
							color = 0x00ff00;
							break;
						case 'r':
							color = 0xff0000;
							break;
						case 'c':
							color = 0x00ffff;
							break;
						case 'm':
							color = 0xff00ff;
							break;
						case 'y':
							color = 0xffff00;
							break;
						case 'k':
							color = 0x000000;
							break;
						case 'w':
							color = 0xffffff;
							break;
					}
				} else if (!isNaN("0x" + regArr[2]))
					color = parseInt("0x" + regArr[2]);
				text = regArr[4];
			}
			if (text) {
				//本地显示弹幕
				danmakuShow(text, position, intToColor(color), size, 0, true);
				//向服务器发送弹幕
				danmakuLaunch(text, position, color, size, function (jsonObj) {
					if (jsonObj.retcode >= 0) {
						m_lastid = jsonObj.id;
						$("#dmText").val("");
					}
				});
			}
		});
		//回车发送
		$("#dmText").keyup(function (event) {
			if (event.keyCode == 13)
				$('#dmSend button').click();
		});
		//lastid == -1 返回最新id
		danmakuGet(-1, function (jsonObj) {
			if (jsonObj.retcode >= 0) {
				m_lastid = jsonObj.lastid - 50;
				if (m_lastid < 0)
					m_lastid = 0;
			}
		});
		//轮询新弹幕
		function pollDanmaku() {
			danmakuGet(m_lastid, function (jsonObj) {
				if (jsonObj.retcode >= 0) {
					m_lastid = jsonObj.lastid;
					var danmakus = jsonObj.danmaku;
					for (i in danmakus) {
						//加上一定的随机时间
						var time = danmakus[i].timestamp - danmakus[0].timestamp + Math.random() * 3;
						danmakuShow(danmakus[i].text, danmakus[i].position, intToColor(danmakus[i].color), danmakus[i].size, time, false);
					}
				}
			});
		}
		setInterval(pollDanmaku, 3000);
	}();
});
