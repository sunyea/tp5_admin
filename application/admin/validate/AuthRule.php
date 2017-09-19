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
class AuthRule extends Validate
{
	
	protected $rule = [
		'id'			=>	'number',
		'title'		=>	'require|length:2,20',
		'name'		=>	'require|max:200|unique:auth_rule',
		'status'	=>	'require',
		'order'		=>	'require|number',
	];

	protected $message = [
		'id.number'				=>	'ID必须为正整数',
		'title.require'		=>	'规则名称必填',
		'title.length'		=>	'规则名称必须是2-20个字符',
		'name.require'		=>	'规则路径必填',
		'name.max'				=>	'规则路径最大可填200个字符',
		'name.unique'			=>	'您填写了重复的规则路径，路径必须唯一',
		'status.require'	=>	'规则状态必填',
		'order.require'		=>	'排序权值必填',
		'order.number'		=>	'规则权值必须是正整数',
	];

	protected $scene = [
		'add'		=>	['title','name','status','order'],
		'update'=>	['id','title','name','status','order'],
		'delete'=>	['id'],
	];
}