<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

// 生成唯一编码GUID
function getGUID(){
	return strtolower(md5(uniqid(mt_rand(), true)));
}

//通过IP获取地理位置，淘宝接口
function taobaoIP($clientIP){
	$taobaoIP = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$clientIP;
	$IPinfo = json_decode(file_get_contents($taobaoIP));
	$country = $IPinfo->data->country;
	$province = $IPinfo->data->region;
	$city = $IPinfo->data->city;
	$county = $IPinfo->data->county;
	$data = $country.$province.$city.$county;
	return $data;
}