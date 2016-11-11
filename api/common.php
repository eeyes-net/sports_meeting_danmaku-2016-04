<?php
function C($key) {
	static $config = null;
	if (is_null($config))
		$config = include 'config.php';
	return $config[$key];
}
function M() {
	$mysqli = new mysqli(C('DB_SERVER'), C('DB_USER'), C('DB_PWD'), C('DB_NAME'));
	if ($mysqli->connect_error) {
		return false;
	}
	return $mysqli;
}
function get_ip() {
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}
function get_referer() {
	$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
	if ($referer) {
		if (strlen($referer) > 255)
			$referer = substr($referer, 0, 255);
	} else
		$referer = false;
	return $referer;
}
function get_user_agent() {
	$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
	if ($user_agent) {
		if (strlen($user_agent) > 255)
			$user_agent = substr($user_agent, 0, 255);
	} else
		$user_agent = false;
	return $user_agent;
}
function session_valid_id($session_id) {
    return preg_match('/^[-,a-zA-Z0-9]{1,40}$/', $session_id) > 0;
}
session_name(C('SESSION_NAME'));
session_start();
