<?php
// +----------------------------------------------------------------------
// | AuthRule.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-19 11:21:23
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;
use think\Validate;

/**
* 规则表验证
*/
class AuthGroup extends Validate
{
	
	protected $rule = [
		'id'			=>	'number',
		'title'		=>	'require|unique:auth_group|length:2,20',
		'status'	=>	'require',
	];

	protected $message = [
		'id.number'				=>	'ID必须为正整数',
		'title.require'		=>	'角色名称必填',
		'title.unique'		=>	'角色名称已经存在，角色不能同名',
		'title.length'		=>	'角色名称必须是2-20个字符',
		'status.require'	=>	'角色状态必填',
	];

	protected $scene = [
		'add'		=>	['title','status'],
		'update'=>	['id','title','status'],
		'delete'=>	['id'],
	];
}