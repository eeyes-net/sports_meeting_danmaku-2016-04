<?php
require 'common.php';
if (isset($_POST['text'])) {
    $text = (string)$_POST['text'];
    $position = null;
    $color = null;
    $size = null;
    if (empty($text)) {
        ajaxReturn(-10, '弹幕文本不能为空');
    }
    if (isset($_POST['position'])) {
        $position = (int)$_POST['position'];
        if ($position < 0 || $position > 2) {
            ajaxReturn(-11, '弹幕位置错误');
        }
    }
    if (isset($_POST['color'])) {
        $color = (int)$_POST['color'];
        if ($color < 0 || $color > 16777215) {
            ajaxReturn(-12, '弹幕颜色错误');
        }
    }
    if (isset($_POST['size'])) {
        $size = (int)$_POST['size'];
        if ($size < 0 || $size > 1) {
            ajaxReturn(-13, '弹幕大小错误');
        }
    }
    if (!$ip = get_client_ip()) {
        ajaxReturn(-14, '读取ip错误');
    }
    if (!$mysqli = connect_db()) {
        ajaxReturn(-15, '发射弹幕连接数据库错误' . $mysqli->error);
    }
    $stmt = $mysqli->prepare('INSERT INTO `' . config('TABLE_DANMAKU') . '` (`text`, `position`, `color`, `size`, `ip`) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sddds', $text, $position, $color, $size, $ip);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        ajaxReturn(-16, '发射弹幕错误' . $mysqli->error);
    }
    $id = $stmt->insert_id;
    $stmt->close();
    $mysqli->close();
    ajaxReturn(10, '发射弹幕成功', array(
        'id' => $id,
    ));
} elseif (isset($_POST['last_id'])) {
    $last_id = (int)$_POST['last_id'];
    if (!$mysqli = connect_db()) {
        ajaxReturn(-20, '获取弹幕连接数据库错误' . $mysqli->error);
    }
    if ($last_id < 0) {
        $sql = 'SELECT `id` FROM `' . config('TABLE_DANMAKU') . '` ORDER BY `id` DESC LIMIT 1';
        if (!$result = $mysqli->query($sql)) {
            $mysqli->close();
            ajaxReturn(-21, '获取最新弹幕id错误' . $mysqli->error);
        }
        $last_id = (int)$result->fetch_row()[0];
        $result->close();
        $mysqli->close();
        ajaxReturn(20, '获取弹幕成功', array(
            'last_id' => $last_id,
        ));
    }
    $stmt = $mysqli->prepare('SELECT `id`, UNIX_TIMESTAMP(`timestamp`) AS `timestamp`, `text`, `position`, `color`, `size` FROM `' . config('TABLE_DANMAKU') . '` WHERE `id` > ? LIMIT ' . config('DANMAKU_LIMIT'));
    $stmt->bind_param('d', $last_id);
    $stmt->bind_result($id, $timestamp, $text, $position, $color, $size);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        ajaxReturn(-22, '获取弹幕错误' . $mysqli->error);
    }
    $ret = array();
    $ret['last_id'] = $last_id;
    $ret['danmaku'] = array();
    while ($stmt->fetch()) {
        $ret['danmaku'][] = array(
            'timestamp' => $timestamp,
            'text' => $text,
            'position' => $position,
            'color' => $color,
            'size' => $size,
        );
        if ($id > $ret['last_id']) {
            $ret['last_id'] = $id;
        }
    }
    $stmt->close();
    $mysqli->close();
    ajaxReturn(20, '获取弹幕成功', $ret);
}
ajaxReturn(-1, '未知错误');
