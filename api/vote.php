<?php
require 'common.php';
// $_SESSION['last_vote'] 上次投票时间戳
if (!isset($_SESSION['last_vote']))
	$_SESSION['last_vote'] = 0;
$ret = array('retcode' => -1, 'retstr' => '');
function ajaxReturn($retArr) {
	if (C('APP_DEBUG')) {
		$retArr['retstr'] = ': ' . $retArr['retstr'];
		switch ($retArr['retcode']) {
			case 20:
				$retArr['retstr'] = '获取投票结果成功';
				break;
			case 10:
				$retArr['retstr'] = '投票成功';
				break;
			// case 0:
				// $retArr['retstr'] = '成功';
				// break;
			case -10:
				$retArr['retstr'] = '您投票速度过快';
				break;
			case -11:
				$retArr['retstr'] = '投票参数格式错误';
				break;
			case -12:
				$retArr['retstr'] = '此书院不存在';
				break;
			case -13:
				$retArr['retstr'] = '投票连接数据库错误' . $retArr['retstr'];
				break;
			case -14:
				$retArr['retstr'] = '检查ip错误' . $retArr['retstr'];
				break;
			case -15:
				$retArr['retstr'] = '您的ip投票速度过快';
				break;
			case -16:
				$retArr['retstr'] = '记录投票错误' . $retArr['retstr'];
				break;
			case -17:
				$retArr['retstr'] = '投票已记录，更新票数信息错误' . $retArr['retstr'];
				break;
			case -20:
				$retArr['retstr'] = '获取投票结果连接数据库错误' . $retArr['retstr'];
				break;
			case -21:
				$retArr['retstr'] = '获取投票结果错误' . $retArr['retstr'];
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
if (isset($_POST['votefor'])) {
	if (time() - $_SESSION['last_vote'] < C('VOTE_INTERVAL')) {
		$ret['retcode'] = -10;
		$ret['wait'] = C('VOTE_INTERVAL') - (time() - $_SESSION['last_vote']);
		ajaxReturn($ret);
	}
	if (!is_numeric($_POST['votefor'])) {
		$ret['retcode'] = -11;
		ajaxReturn($ret);
	}
	$votefor = (int)$_POST['votefor'];
	if ($votefor < C('COLLEGE_ID_MIN') || $votefor > C('COLLEGE_ID_MAX')) {
		$ret['retcode'] = -12;
		ajaxReturn($ret);
	}
	$mysqli = M();
	if (!$mysqli) {
		$ret['retcode'] = -13;
		$ret['retstr']  = $mysqli->error;
		ajaxReturn($ret);
	}
	if (C('IS_CHECK_IP')) {
		$sql = sprintf("SELECT UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - UNIX_TIMESTAMP(`timestamp`) FROM `%s` WHERE `ip` = '%s' AND UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - UNIX_TIMESTAMP(`timestamp`) < %d LIMIT 1", C('VOTE_DETAIL_TABLE_NAME'), get_ip(), C('VOTE_INTERVAL'));
		$result = $mysqli->query($sql);
		if (!$result) {
			$ret['retcode'] = -14;
			$ret['retstr']  = $mysqli->error;
			ajaxReturn($ret);
		}
		if ($result->num_rows > 0) {
			$ret['retcode'] = -15;
			$ret['wait'] = C('VOTE_INTERVAL') - (int)$result->fetch_row()[0];
			ajaxReturn($ret);
		}
	}
	$session_id = session_id();
	if (!session_valid_id($session_id))
		die();
	$session_id = $mysqli->real_escape_string($session_id);
	$sql = sprintf("INSERT INTO `%s` (`votefor`, `cookie`, `ip`) VALUES ('%d', '%s', '%s');", C('VOTE_DETAIL_TABLE_NAME'), $votefor, $session_id, get_ip());
    // echo $sql;
	$result = $mysqli->query($sql);
	if (!$result) {
		$ret['retcode'] = -16;
		$ret['retstr']  = $mysqli->error;
		ajaxReturn($ret);
	}
	$_SESSION['last_vote'] = time();
	$sql = sprintf("UPDATE `%s` SET `count` = `count` + 1 WHERE `id` = %d;", C('VOTE_COUNT_TABLE_NAME'), $votefor);
	$result = $mysqli->query($sql);
	if (!$result) {
		$ret['retcode'] = -17;
		$ret['retstr']  = $mysqli->error;
		ajaxReturn($ret);
	}
	$ret['retcode'] = 10;
	ajaxReturn($ret);
} else if (isset($_POST['result'])) {
	$mysqli = M();
	if (!$mysqli) {
		$ret['retcode'] = -20;
		$ret['retstr']  = $mysqli->error;
		ajaxReturn($ret);
	}
	$sql = sprintf("SELECT `count` FROM `%s` WHERE 1", C('VOTE_COUNT_TABLE_NAME'));
	$result = $mysqli->query($sql);
	if (!$result) {
		$ret['retcode'] = -21;
		$ret['retstr']  = $mysqli->error;
		ajaxReturn($ret);
	}
	$ret['result'] = $result->fetch_all(MYSQLI_ASSOC);
	for ($i = 0; $i < count($ret['result']); ++$i) {
		$ret['result'][$i] = (int)$ret['result'][$i]['count'];
	}
	$result->close();
	$ret['retcode'] = 20;
	ajaxReturn($ret);
}
ajaxReturn($ret);
