<?php
// +----------------------------------------------------------------------
// | BaseAdmin.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-07 11:40:57
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\common\Auth;

/**
* 管理模块共用控制器
*/
class BaseAdmin extends Controller
{
	public function _initialize(){
		if(session('?manager')){
			if (session('manager.lock')) {
				$this->error('您的屏幕已经锁定，请重新登录', '/admin/login', '', 5);
			}
			$author = new Auth();
			$request = Request::instance();
			$rule_name = '/'.$request->module().'/'.$request->controller().'/'.$request->action();
			$result = $author->check($rule_name, session('manager.id'));
			if(!$result){
				$this->success('您没有权限进行该操作，请联系系统管理员！');
			}
		}else{
			$this->error('您未登录，或者登录过期，请重新登录', '/admin/login', '', 5);
		}
	}
	
}