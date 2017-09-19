<?php
// +----------------------------------------------------------------------
// | Rule.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-18 16:33:59
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;
use think\Model;

/**
* 管理规则
*/
class AuthRule extends Model
{
	protected $insert = ['type'];
	//获取状态
	protected function getStatusAttr($value){
		$status = ['0'=>'无效', '1'=>'有效'];
		return $status[$value];
	}
	//自动设置type=1
	protected function setTypeAttr(){
		return 1;
	}
}