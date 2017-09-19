<?php
// +----------------------------------------------------------------------
// | Manager.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-08 17:07:07
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;
use think\Validate;
/**
* 管理员验证类
*/
class Manager extends Validate
{
	
	protected $rule = [
			'user_id'		=>	'require|length:4,20|unique:manager',
			'user_pwd'	=>	'require|length:6,20',
			'name'			=>	'require|max:20',
			'email'			=>	'email',
			'status'		=>	'require',
			'repassword'=>	'confirm:user_pwd',
	];

	protected $message = [
			'user_id.require'		=>	'用户名必填',
			'user_id.length'		=>	'用户名长度在4到20字符之间',
			'user_id.unique'		=>	'用户名已经被使用，请修改用户名后提交',
			'user_pwd.require'	=>	'密码必填',
			'user_pwd.lenght'		=>	'密码长度在6到20字符之间',
			'name.require'			=>	'名称必填',
			'name.max'					=>	'名称最大不能超过20个字符',
			'email'							=>	'邮箱格式有错',
			'status'						=>	'没有设置状态',
			'repassword'				=>	'两次输入的密码不一致',
	];

	protected $scene = [
			'add'			=>	['user_id','user_pwd','name','email','status'],
			'update'	=>	['name','email'],
			'changepwd'=>	['user_pwd','repassword'],
	];
}