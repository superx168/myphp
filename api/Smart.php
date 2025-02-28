<?php
error_reporting(0);
header('Content-Type: text/json;charset=UTF-8');
date_default_timezone_set("Asia/Shanghai");

$name = $_GET["id"] ?? "";
$port = 'http://198.16.100.186:8278/';
$ts = $_GET["ts"] ?? "";

$ip = '127.0.0.1';
$header = array(
    "CLIENT-IP:" . $ip,
    "X-FORWARDED-FOR:" . $ip,
);

if ($ts) {
    $host = $port. $name . "/";
    $url = $host . $ts;
    $data = curl_get($url, $header);
    echo $data;
} else {
    $url = $port . $name . "/playlist.m3u8";
    $seed = "tvata nginx auth module";
    $path = parse_url($url, PHP_URL_PATH);
    $tid = "mc42afe745533";
    $t = strval(intval(time() / 150));
    $str = $seed . $path . $tid . $t;
    $tsum = md5($str);
    $link = http_build_query(["ct" => $t, "tsum" => $tsum]);
    $url .= "?tid=$tid&$link";

    $parseUrl = "https://";
    $parseUrl .= $_SERVER['HTTP_HOST'];
    $parseUrl .= $_SERVER['PHP_SELF'];

    $result = curl_get($url, $header);
    if (strpos($result, "EXTM3U")) {
        $m3u8s = explode("\n", $result);
        $result = '';
        foreach ($m3u8s as $v) {
            if (strpos($v, ".ts") > 0) {
                $result .= $parseUrl . "?id=" . $name . "&ts=" . $v . "\n";
            } else {
                if ($v != '') {
                    $result .= $v . "\n";
                }
            }
        }
    }
    echo $result;
}
exit();

function curl_get($url, $header = array())
{

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    $data = curl_exec($curl);
    if (curl_error($curl)) {
        return "Error: " . curl_error($curl);
    } else {
        curl_close($curl);
        return $data;
    }
}

?>