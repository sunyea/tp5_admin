<?php
// +----------------------------------------------------------------------
// | Index.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-07 12:30:27
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\admin\controller\BaseAdmin;
use app\common\Auth;



/**
* 管理界面
*/
class Index extends BaseAdmin
{
	public function index(){
		return $this->fetch();
	}

}