<?php
require 'common.php';
$ret = array('retcode' => 0, 'retstr' => '');
function ajaxReturn($retArr) {
	if (C('APP_DEBUG')) {
		$retArr['retstr'] = ': ' . $retArr['retstr'];
		switch ($retArr['retcode']) {
			case 20:
				$retArr['retstr'] = '获取弹幕成功';
				break;
			case 10:
				$retArr['retstr'] = '发射弹幕成功';
				break;
			// case 0:
				// $retArr['retstr'] = '成功';
				// break;
			case -10:
				$retArr['retstr'] = '弹幕文本不能为空';
				break;
			case -11:
				$retArr['retstr'] = '发射弹幕连接数据库错误' . $retArr['retstr'];
				break;
			case -12:
				$retArr['retstr'] = '弹幕位置错误';
				break;
			case -13:
				$retArr['retstr'] = '弹幕颜色错误';
				break;
			case -14:
				$retArr['retstr'] = '弹幕大小错误';
				break;
			case -15:
				$retArr['retstr'] = '发射弹幕错误' . $retArr['retstr'];
				break;
			case -20:
				$retArr['retstr'] = '获取弹幕连接数据库错误' . $retArr['retstr'];
				break;
			case -21:
				$retArr['retstr'] = '获取最新弹幕id错误' . $retArr['retstr'];
				break;
			case -22:
				$retArr['retstr'] = '获取弹幕错误' . $retArr['retstr'];
				break;
			default:
				$retArr['retstr'] = '未知错误';
				break;
		}
	} else
		unset($retArr['retstr']);
	if (isset($mysqli))
		$mysqli->close();
	exit(json_encode($retArr));
}
if (isset($_POST['text'])) {
	$danmakuText = (string)$_POST['text'];
	if (empty($danmakuText)) {
		$ret['retcode'] = -10;
		ajaxReturn($ret);
	}
	$mysqli = M();
	if (!$mysqli) {
		$ret['retcode'] = -11;
		$ret['retstr']  = $mysqli->error;
		ajaxReturn($ret);
	}
	$danmakuText = $mysqli->real_escape_string(htmlspecialchars($danmakuText));
	if (isset($_POST['position'])) {
		$danmakuPosition = (int)$_POST['position'];
		if ($danmakuPosition < 0 || $danmakuPosition > 2) {
			$ret['retcode'] = -12;
			ajaxReturn($ret);
		}
		$danmakuPosition = "'" . $danmakuPosition . "'";
	} else {
		$danmakuPosition = 'DEFAULT';
	}
	if (isset($_POST['color'])) {
		$danmakuColor = (int)$_POST['color'];
		if ($danmakuColor < 0 || $danmakuColor > 16777215) {
			$ret['retcode'] = -13;
			ajaxReturn($ret);
		}
		$danmakuColor = "'" . $danmakuColor . "'";
	} else {
		$danmakuColor = 'DEFAULT';
	}
	if (isset($_POST['size'])) {
		$danmakuSize = (int)$_POST['size'];
		if ($danmakuSize < 0 || $danmakuSize > 1) {
			$ret['retcode'] = -14;
			ajaxReturn($ret);
		}
		$danmakuSize = "'" . $danmakuSize . "'";
	} else {
		$danmakuSize = 'DEFAULT';
	}
	$session_id = session_id();
	if (!session_valid_id($session_id))
		die();
	$session_id = $mysqli->real_escape_string($session_id);
	$sql = sprintf("INSERT INTO `%s` (`text`, `position`, `color`, `size`, `cookie`, `ip`) VALUES ('%s', %s, %s, %s, '%s', '%s');", C('DANMAKU_TABLE_NAME'), $danmakuText, $danmakuPosition, $danmakuColor, $danmakuSize, $session_id, get_ip());
	$result = $mysqli->query($sql);
	if (!$result) {
		$ret['retcode'] = -15;
		$ret['retstr']  = $mysqli->error;
		ajaxReturn($ret);
	}
	$ret['id']      = $mysqli->insert_id;
	$ret['retcode'] = 10;
	ajaxReturn($ret);
} else if (isset($_POST['lastid'])) {
	$mysqli = M();
	if (!$mysqli) {
		$ret['retcode'] = -20;
		$ret['retstr']  = $mysqli->error;
		ajaxReturn($ret);
	}
	$lastid = (int)$_POST['lastid'];
	if ($lastid < 0) {
		$sql = sprintf("SELECT MAX(`id`) FROM `%s`", C('DANMAKU_TABLE_NAME'));
		$result = $mysqli->query($sql);
		if (!$result) {
			$ret['retcode'] = -21;
			$ret['retstr']  = $mysqli->error;
			ajaxReturn($ret);
		}
		$ret['lastid']  = (int)$result->fetch_row()[0];
		$result->close();
		$ret['danmaku'] = array();
		$ret['retcode'] = 10;
		ajaxReturn($ret);
	}
	$sql = sprintf("SELECT `id`, UNIX_TIMESTAMP(`timestamp`), `text`, `position`, `color`, `size` FROM `%s` WHERE `id` > '%d' LIMIT 0, %d", C('DANMAKU_TABLE_NAME'), (int)$_POST['lastid'], C('DANMAKU_LIMIT'));
	$result = $mysqli->query($sql);
	if (!$result) {
		$ret['retcode'] = -22;
		$ret['retstr']  = $mysqli->error;
		ajaxReturn($ret);
	}
	$ret['lastid'] = $lastid;
	$ret['danmaku'] = $result->fetch_all(MYSQLI_ASSOC);
	for ($i = 0; $i < count($ret['danmaku']); ++$i) {
		$ret['danmaku'][$i]['timestamp'] = (int)$ret['danmaku'][$i]['UNIX_TIMESTAMP(`timestamp`)'];
		$ret['danmaku'][$i]['position']  = (int)$ret['danmaku'][$i]['position'];
		$ret['danmaku'][$i]['color']     = (int)$ret['danmaku'][$i]['color'];
		$ret['danmaku'][$i]['size']      = (int)$ret['danmaku'][$i]['size'];
		if ($ret['danmaku'][$i]['id'] > $ret['lastid'])
			$ret['lastid'] = (int)$ret['danmaku'][$i]['id'];
		unset($ret['danmaku'][$i]['id']);
		unset($ret['danmaku'][$i]['UNIX_TIMESTAMP(`timestamp`)']);
	}
	$result->close();
	$ret['retcode'] = 20;
	ajaxReturn($ret);
}
ajaxReturn($ret);
