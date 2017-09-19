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
use app\admin\model\AuthGroup;

/**
* 权限管理
*/
class Group extends BaseAdmin
{
	
	public function index(){
		$rule = new AuthGroup();
		$keyword = input('param.keyword');
		$where_str = '';
		$where_val = '';
		$paginate = [];
		if ($this->request->isPost()) {
			$paginate['page'] = 1;
		}
		if (!(empty($keyword) or $keyword == '')) {
			$paginate['query'] = ['keyword'=>$keyword];
			$where_str = 'title like :title';
			$where_val = ['title'=>'%'.$keyword.'%'];
		}
		if ($where_str == '') {
			$list = $rule->paginate($paginate);
		}else{
			$list = $rule->where($where_str)->bind($where_val)->paginate($paginate);
		}
		$this->assign('keyword', $keyword);
		$this->assign('list', $list);
		return $this->fetch();
	}
	//添加规则
	public function add(){
		if (request()->isPost()) {
			$result = $this->validate(input('post.'), 'AuthGroup.add');
			if(true !== $result){
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>$result]);
				exit();
			}
			$group = new AuthGroup();
			if($group->allowField(true)->save(input('post.'))){
				return json(['code'=>'1', 'msg'=>'添加成功']);
			}else{
				return json(['code'=>'0', 'errorcode'=>'-2', 'msg'=>$rule.getError()]);
			}
		}else{
			$this->assign('action', 'add');
			$this->assign('id', '');
			$this->assign('actiontxt', '添加');
			$this->assign('group',['title'=>'', 'status'=>'有效']);
			return $this->fetch('form');
		}
	}

	//修改规则
	public function update(){
		if (request()->isPost()) {
			$result = $this->validate(input('post.'), 'AuthGroup.update');
			if (true !== $result) {
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>$result]);
				exit();
			}
			$group = new AuthGroup();
			if($group->allowField(true)->isUpdate(true)->save(input('post.'))){
				return json(['code'=>'1', 'msg'=>'修改成功']);
			}else{
				return json(['code'=>'0', 'errorcode'=>'-2', 'msg'=>$rule.getError()]);
			}
		}else{
			$group = new AuthGroup();
			$group_data = $group->where('id', input('param.id'))->find();
			$this->assign('action', 'update');
			$this->assign('id', input('param.id'));
			$this->assign('actiontxt', '修改');
			$this->assign('group', $group_data);
			return $this->fetch('form');
		}
	}

	//删除规则
	public function delete(){
		if (request()->isPost()) {
			$group = AuthGroup::all(explode(',', input('post.id')));
			if($group){
				foreach ($group as $g) {
					$g->delete();
				}
				return json(['code'=>'1', 'msg'=>'删除成功']);
			}else{
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>'删除失败']);
			}
		}else{
			return json(['code'=>'0', 'errorcode'=>'-3', 'msg'=>'请确认操作']);
		}
	}
}