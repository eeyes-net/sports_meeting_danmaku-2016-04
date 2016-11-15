<?php
function config($key) {
	static $config = null;
	if (is_null($config))
		$config = include 'config.php';
	return $config[$key];
}
function connect_db() {
	$mysqli = new mysqli(config('DB_SERVER'), config('DB_USER'), config('DB_PWD'), config('DB_NAME'));
	if ($mysqli->connect_error) {
		return false;
	}
    $mysqli->query("SET NAMES 'utf8'");
	return $mysqli;
}
/**
 * 获取请求来源的ip地址
 *
 * @param bool $advance 是否使用高级方式获取ip，PHP主机暴露可能被伪造
 *                      false 返回 REMOTE_ADDR
 *                      true 返回 HTTP_X_REAL_IP -> HTTP_X_FORWARDED_FOR首个ip -> HTTP_CLIENT_IP -> REMOTE_ADDR
 *
 * @return bool|string ip不合法返回false
 */
function get_client_ip($advance = false)
{
    $ip = false;
    if ($advance) {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return ip2long($ip) ? $ip : false;
}
function ajaxReturn($retcode, $err_msg, $ret_arr = array())
{
    header('Content-Type: application/json');
    $ret_arr['retcode'] = $retcode;
    $ret_arr['err_msg'] = $err_msg;
    echo json_encode($ret_arr);
    exit();
}
