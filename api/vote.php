<?php
require 'common.php';
if (isset($_POST['id'])) {
    if (!is_numeric($_POST['id'])) {
        ajaxReturn(-11, '投票参数格式错误');
    }
    $id = (int)$_POST['id'];
    if ($id < config('COLLEGE_ID_MIN') || $id > config('COLLEGE_ID_MAX')) {
        ajaxReturn(-12, '此书院不存在');
    }
    if (!$mysqli = connect_db()) {
        ajaxReturn(-13, '投票连接数据库错误' . $mysqli->error);
    }
    if (config('IS_CHECK_IP')) {
        if (!$ip = get_client_ip()) {
            ajaxReturn(-14, '读取ip错误');
        }
        $stmt = $mysqli->prepare('SELECT UNIX_TIMESTAMP(`timestamp`) AS `timestamp` FROM `' . config('TABLE_VOTE_DETAIL') . '` WHERE `ip` = ? ORDER BY `id` DESC LIMIT 1');
        $stmt->bind_param('s', $ip);
        $stmt->bind_result($timestamp);
        if (!$stmt->execute()) {
            $stmt->close();
            $mysqli->close();
            ajaxReturn(-14, '检查ip错误' . $mysqli->error);
        }
        if ($stmt->fetch()) {
            $wait = config('VOTE_INTERVAL') - (time() - $timestamp);
            if ($wait > 0) {
                $stmt->close();
                $mysqli->close();
                ajaxReturn(-15, '您的投票速度过快', array(
                    'wait' => $wait,
                ));
            }
        }
        $stmt->close();
    }
    $stmt = $mysqli->prepare('INSERT INTO `' . config('TABLE_VOTE_DETAIL') . '` (`college_id`, `ip`) VALUES (?, ?)');
    $stmt->bind_param('ds', $id, $ip);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        ajaxReturn(-16, '记录投票错误' . $mysqli->error);
    }
    $stmt->close();
    $stmt = $mysqli->prepare('UPDATE `' . config('TABLE_VOTE_COUNT') . '` SET `count` = `count` + 1 WHERE `id` = ?');
    $stmt->bind_param('d', $id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        ajaxReturn(-17, '投票已记录，更新票数信息错误' . $mysqli->error);
    }
    $stmt->close();
    $mysqli->close();
    ajaxReturn(10, '投票成功');
} else {
    if (!$mysqli = connect_db()) {
        ajaxReturn(-21, '获取投票结果连接数据库错误' . $mysqli->error);
    }
    $stmt = $mysqli->prepare('SELECT `count` FROM `' . config('TABLE_VOTE_COUNT') . '` WHERE 1');
    $stmt->bind_result($count);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        ajaxReturn(-22, '获取投票结果错误');
    }
    $ret = array(
        'result' => array(),
    );
    while ($stmt->fetch()) {
        $ret['result'][] = $count;
    }
    $stmt->close();
    $mysqli->close();
    ajaxReturn(20, '获取投票结果成功', $ret);
}
