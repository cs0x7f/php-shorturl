<?php

$BASE_URL = 'HTTPS://HOSTNAME/';
$DB_HOST = 'localhost';
$DB_USER = 'mysql';
$DB_PASSWORD = '';
$DB_NAME = 'mysql';
$DB_TABLE = 'shorturls';
$MD5_PASSWORD = 'e10adc3949ba59abbe56e057f20f883e'; // 123456

function azzert($value, $code) {
	if (!$value) {
		http_response_code($code);
		die(0);
	}
}

$db = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
azzert(!$db->connect_errno, 503);

$path = trim($_GET['path']);

if (empty($path)) {
} else if ($path == 'set') {
?>
<!DOCTYPE html>
<html>
<head>
	<title>Short URL</title>
	<script src="jquery.min.js"></script>
	<script>
		$(function() {
			$('#short').submit(function() {
				var obj = {'url': $('#url').val()};
				if ($('#surl').val() && $('#passwd').val()) {
					obj['surl'] = $('#surl').val();
					obj['passwd'] = $('#passwd').val();
				}
				$.post('/short', obj, function(ret) {
					$('#result').attr('href', ret).html(ret);
				});
				return false;
			});
		});
	</script>
</head>
<body>
	<form method="post" action="short" id="short">
		<label for="url">Raw URL</label><input type="text" name="url" id="url"><br>
		<label for="surl">Shorted URL</label><input type="text" name="surl" id="surl"><br>
		<label for="passwd">Password</label><input type="password" name="passwd" id="passwd"><br>
		<input type="submit" value="submit"><br>
		<a href="#" id="result">ResultLink</a>
	</form>
</body>
</html>
<?php
} else if ($path == 'list') {
} else if ($path == 'short') {
	$url = trim($_REQUEST['url']);
	$url = get_magic_quotes_gpc() ? stripslashes($url) : $url;
	azzert(filter_var($url, FILTER_VALIDATE_URL), 400);
	$url = $db->real_escape_string($url);
	$sql = "INSERT IGNORE INTO `$DB_TABLE` (`url`) VALUES ('$url')";
	if (isset($_REQUEST['surl']) && !empty($_REQUEST['surl'])) {
		$surl = $_REQUEST['surl'];
		azzert(preg_match("/^[0-9A-Z]*$/", $surl), 400);
		azzert(isset($_REQUEST['passwd']) && md5($_REQUEST['passwd']) == $MD5_PASSWORD, 401);
		$uid = $db->real_escape_string(base_convert($surl, 36, 10));
		$sql = "INSERT IGNORE INTO `$DB_TABLE` (`uid`, `url`) VALUES ('$uid', '$url')";
	}
	$ret = $db->query($sql);
	azzert($ret, 503);
	$sql = "SELECT `uid` FROM $DB_TABLE WHERE `url` = '$url' LIMIT 1";
	$ret = $db->query($sql);
	azzert($ret, 503);
	$ret = $ret->fetch_assoc();
	azzert($ret, 503);
	$ret = strtoupper(base_convert($ret['uid'], 10, 36));
	echo "$BASE_URL$ret";
} else if (preg_match("/^[0-9A-Z]*$/", $path)) {
	$uid = $db->real_escape_string(base_convert($path, 36, 10));
	$sql = "SELECT `url` FROM $DB_TABLE WHERE `uid` = '$uid' LIMIT 1";
	$ret = $db->query($sql);
	azzert($ret, 503);
	$ret = $ret->fetch_assoc();
	azzert($ret, 404);
	$url = $ret['url'];
	header("Location: $url");
} else {
	azzert(false, 404);
}
?>