<?php
error_reporting(0);
header("Content-Type:application/json;charset=utf-8");
function f_curl($url,$hdr,$data,$hosts,$ist){
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    if($hdr!="") curl_setopt($ch,CURLOPT_HTTPHEADER,$hdr);
    if($data!="") curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    if($hosts!="") curl_setopt($ch,CURLOPT_RESOLVE,$hosts);
    if($ist==1) curl_setopt($ch,CURLOPT_HEADER,1);
    $cj_tempz=curl_exec($ch);
    if($ist==1) $cj_tempz=curl_getinfo($ch);
    curl_close($ch);
    return $cj_tempz;
}
function f_ranm(){
    $macParts=[];
    for($i=0;$i<6;$i++){
        $macParts[]=strtoupper(dechex(rand(0,255)));
    }
    return implode(':', $macParts);
}
function f_rip(){
    return rand(1,254).".".rand(0,255).".".rand(0,255).".".rand(0,255);
}
$id = $_GET["id"] ?? "";
if($id === "") die("404");
$api_id = rtrim($id, '.m3u8'); 
$gip = f_rip();
$hdr = ["X-Forwarded-For: {$gip}"];
$login_data = "mac=" . f_ranm() . "&username=0000";
$pkey_response = f_curl("http://xvod.iptv8.com:8090/api/v1/login", $hdr, $login_data, "", 0);
$pkey_data = json_decode($pkey_response, true);
$pkey = $pkey_data['token'] ?? "";
if($pkey === "") die("404");
$play_url = "http://xvod.iptv8.com:8090/storage/channel/vod/vodheb.php?playseek=&key={$pkey}&cid={$api_id}";
$purl_info = f_curl($play_url, $hdr, "", "", 1);
$purl = $purl_info["redirect_url"] ?? "";
if($purl === "") die("404");
if (str_contains($purl, '&_taskId=')) {
    [$url_part, $task_id_part] = explode('&_taskId=', $purl, 2);
    [$task_id, $other_params] = explode('&', $task_id_part, 2); 
    $new_task_id = 'he' . substr(md5("dabendan" . mt_rand(100000, 999999)), -30);
    $purl = "{$url_part}&_taskId={$new_task_id}" . ($other_params ? "&{$other_params}" : "");
}
$purl = str_replace(['&starttime=', '&endtime='], ['', ''], $purl);
header("location: {$purl}");
die();
?>
