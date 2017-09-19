<?php
// +----------------------------------------------------------------------
// | Rule.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-18 16:01:56
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\admin\controller\BaseAdmin;
use app\admin\model\AuthRule;

/**
* 权限管理
*/
class Rule extends BaseAdmin
{
	
	public function index(){
		$rule = new AuthRule();
		$keyword = input('param.keyword');
		$where_str = '';
		$where_val = '';
		$paginate = [];

		if ($this->request->isPost()) {
			$paginate['page'] = 1;
		}
		if (!(empty($keyword) or $keyword == '')) {
			$paginate['query'] = ['keyword'=>$keyword];
			$where_str = 'title like :title or name like :name';
			$where_val = ['title'=>'%'.$keyword.'%', 'name'=>'%'.$keyword.'%'];
		}
		if ($where_str == '') {
			$list = $rule->order('order')->paginate($paginate);
		}else{
			$list = $rule->order('order')->where($where_str)->bind($where_val)->paginate($paginate);
		}
		$this->assign('keyword', $keyword);
		$this->assign('list', $list);
		return $this->fetch();
	}
	//添加规则
	public function add(){
		if (request()->isPost()) {
			$result = $this->validate(input('post.'), 'AuthRule.add');
			if(true !== $result){
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>$result]);
				exit();
			}
			$rule = new AuthRule();
			if($rule->allowField(true)->save(input('post.'))){
				return json(['code'=>'1', 'msg'=>'添加成功']);
			}else{
				return json(['code'=>'0', 'errorcode'=>'-2', 'msg'=>$rule.getError()]);
			}
		}else{
			$this->assign('action', 'add');
			$this->assign('id', '');
			$this->assign('actiontxt', '添加');
			$this->assign('rule',['title'=>'','name'=>'', 'condition'=>'', 'order'=>10, 'status'=>'有效']);
			return $this->fetch('form');
		}
	}

	//修改规则
	public function update(){
		if (request()->isPost()) {
			$result = $this->validate(input('post.'), 'AuthRule.update');
			if (true !== $result) {
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>$result]);
				exit();
			}
			$rule = new AuthRule();
			if($rule->allowField(true)->isUpdate(true)->save(input('post.'))){
				return json(['code'=>'1', 'msg'=>'修改成功']);
			}else{
				return json(['code'=>'0', 'errorcode'=>'-2', 'msg'=>$rule.getError()]);
			}
		}else{
			$rule = new AuthRule();
			$rule_data = $rule->where('id', input('param.id'))->find();
			$this->assign('action', 'update');
			$this->assign('id', input('param.id'));
			$this->assign('actiontxt', '修改');
			$this->assign('rule', $rule_data);
			return $this->fetch('form');
		}
	}

	//删除规则
	public function delete(){
		if (request()->isPost()) {
			$rule = new AuthRule();
			if($rule){
				$rule->where('id in ('.input('post.id').')')->delete();
				return json(['code'=>'1', 'msg'=>'删除成功']);
			}else{
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>'删除失败']);
			}
		}else{
			return json(['code'=>'0', 'errorcode'=>'-3', 'msg'=>'请确认操作']);
		}
	}
}