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
use app\admin\model\AuthRule;
use think\Model;

/**
* 管理规则
*/
class AuthGroup extends Model
{
	//获取状态
	protected function getStatusAttr($value){
		$status = ['0'=>'无效', '1'=>'有效'];
		return $status[$value];
	}

	//获取规则名称
	public function getRulesTitleAttr($value, $data){
		$_rules = $data['rules'];
		if(empty($_rules)){
			return '无';
		}else{
			$rule = new AuthRule();
			$list = $rule->where('id in ('.$data['rules'].')')->where('status', '1')->column('title');
			if (is_array($list)) {
				$_rules_text = implode(",", $list);
				return $_rules_text;
			}else{
				return $list;
			}
		}
	}
}