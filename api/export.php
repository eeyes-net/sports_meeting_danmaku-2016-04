<?php
require 'common.php';
if (!isset($_POST['password']) || $_POST['password'] !== config('EXPORT_PASSWORD')) {
    exit('<form method="post"><input type="password" name="password"><input type="submit"></form>');
}
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=danmakus.csv");
if ($mysqli = connect_db()) {
    if ($result = $mysqli->query('SELECT `id`, `timestamp`, `text`, `position`, `color`, `size`, `ip` FROM `' . config('TABLE_DANMAKU') . '`')) {
        ob_start();
        $f = fopen('php://output', 'rw');
        fputcsv($f, array('序号', '时间', '弹幕内容', '弹幕位置', '弹幕颜色', '字体大小', '来源ip'));
        while ($row = $result->fetch_row()) {
            fputcsv($f, $row);
        }
        fclose($f);
        echo iconv('utf-8', 'GBK//IGNORE', ob_get_clean());
        $result->close();
    }
    $mysqli->close();
}
