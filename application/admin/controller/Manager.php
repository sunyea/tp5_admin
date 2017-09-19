<?php
// +----------------------------------------------------------------------
// | Manager.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-22 11:54:41
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;
use app\admin\model\Manager as AuthManager;
use app\admin\model\AuthGroup;
use think\Controller;

/**
* 管理员操作
*/
class Manager extends Controller
{
	public function index(){
		$manager = new AuthManager();
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
			$list = $manager->paginate($paginate);
		}else{
			$list = $manager->where($where_str)->bind($where_val)->paginate($paginate);
		}
		$this->assign('keyword', $keyword);
		$this->assign('list', $list);
		return $this->fetch();
	}
	//添加
	public function add(){
		if (request()->isPost()) {
			$result = $this->validate(input('post.'), 'Manager.add');
			if(true !== $result){
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>$result]);
				exit();
			}
			$manager = new AuthManager();
			if($manager->allowField(true)->save(input('post.'))){
				return json(['code'=>'1', 'msg'=>'添加成功']);
			}else{
				return json(['code'=>'0', 'errorcode'=>'-2', 'msg'=>$manager.getError()]);
			}
		}else{
			$this->assign('action', 'add');
			$this->assign('id', '');
			$this->assign('actiontxt', '添加');
			$this->assign('manager',['user_guid'=>getGUID(), 'user_id'=>'','user_pwd'=>'', 'name'=>'', 'email'=>'', 'status'=>'有效']);
			return $this->fetch('form');
		}
	}

	//修改
	public function update(){
		if (request()->isPost()) {
			$result = $this->validate(input('post.'), 'Manager.update');
			if (true !== $result) {
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>$result]);
				exit();
			}
			$_pwd = AuthManager::where('id', input('post.id'))->value('user_pwd');
			$_allow_field = ['name', 'email', 'status'];
			if (input('post.user_pwd') != $_pwd) {
				array_push($_allow_field, 'user_pwd');
			}
			$manager = new AuthManager();
			if($manager->allowField($_allow_field)->isUpdate(true)->save(input('post.'),['id'=>input('post.id')])){
				return json(['code'=>'1', 'msg'=>'修改成功']);
			}else{
				return json(['code'=>'0', 'errorcode'=>'-2', 'msg'=>$manager2.getError()]);
			}
		}else{
			$manager = new AuthManager();
			$manager_data = $manager->where('id', input('param.id'))->find();
			$this->assign('action', 'update');
			$this->assign('id', input('param.id'));
			$this->assign('actiontxt', '修改');
			$this->assign('manager', $manager_data);
			return $this->fetch('form');
		}
	}

	//删除
	public function delete(){
		if (request()->isPost()) {
			$managers = AuthManager::all(explode(',',input('post.id')));
			if($managers){
				foreach ($managers as $manager) {
					$manager->clearGroup();
					$manager->delete();
				}
				return json(['code'=>'1', 'msg'=>'删除成功']);
			}else{
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>'删除失败']);
			}
		}else{
			return json(['code'=>'0', 'errorcode'=>'-3', 'msg'=>'请确认操作']);
		}
	}
	//角色分配
	public function group(){
		if (request()->isPost()) {
			$id = input('post.id');
			$manager = AuthManager::getById($id);
			$ids = input('post.group/a');
			$manager->setGroup($ids);
			return json(['code'=>'1', 'msg'=>'分配成功']);
		}else{
			$id = input('param.id');
			$manager = AuthManager::getById($id);
			$group_id = $manager->group_id;
			$group = new AuthGroup();
			$list = $group->where('status', '1')->select();
			$this->assign('group', $list);
			$this->assign('group_id', $group_id);
			$this->assign('id', $id);
			return $this->fetch('form_group');
		}
	}

}