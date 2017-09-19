<?php
// +----------------------------------------------------------------------
// | Login.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-08 18:02:40
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;
use app\common\Auth;
use app\common\Upfile;
use app\admin\model\Manager;
use think\Controller;
use think\Request;
use think\Cache;

/**
* 登录
*/
class Login extends Controller
{
	
	public function index(){
		return $this->fetch('index');
	}
	//登录验证
	public function login(){
		if (!captcha_check(input('post.captcha'))) {
			return json(['code'=>'0', 'errorcode'=>'-1','msg'=>'验证码错误']);
			exit();
		}
		$data = db('manager')->where('user_id', input('post.user_id'))->where('status', '1')->find();
		if (!empty($data)) {
			if (!password_verify(input('post.user_pwd'), $data['user_pwd'])) {
				return json(['code'=>'0', 'errorcode'=>'-3', 'msg'=>'密码不正确']);
			}elseif (!$data['status']) {
				return json(['code'=>'0', 'errorcode'=>'-5', 'msg'=>'账号被禁用']);
			}else{
				$request = Request::instance();
				db('manager')->where('id', $data['id'])->update([
						'login_count'	=>['exp', 'login_count+1'], 
						'login_time'	=>['exp', 'now()'],
						'login_ip'		=>$request->ip(),
						'login_local'	=>taobaoIP($request->ip()),
						]);
				session('manager', [
					'id'		=>$data['id'], 
					'uid'		=>$data['user_id'], 
					'heard'	=>$data['heard'],
					'name'	=>$data['name'],
					'count'	=>$data['login_count']+1,
					'time'	=>$data['login_time'],
					'ip'		=>$data['login_ip'],
					'local'	=>$data['login_local'],
					'lock'	=>false,
					]);
				return json(['code'=>'1', 'errorcode'=>'0', 'msg'=>'登录成功']);
			}
		}else{
			return json(['code'=>'0', 'errorcode'=>'-2', 'msg'=>'账号不存在或已被禁用']);
		}
	}
	//退出登录
	public function logout(){
		session('manager', null);
		$this->redirect('/admin/login');
	}

	//获取菜单
	public function getmenu(){
		$auth = new Auth();
		$menu = $auth->getMenu(session('manager.id'));
		$rt = [];
		foreach ($menu as $item) {
			array_push($rt, $item);
		}
		return json($rt);
	}

	public function welcome(){
		$this->islogin();
		return $this->fetch();
	}

	public function changePassword(){
		$this->islogin();
		if(request()->isPost()){
			$result = $this->validate(input('post.'), 'Manager.changepwd');
			if (true !== $result) {
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>$result]);
				exit();
			}
			$manager = new Manager();
			$pwd = $manager->where('user_id', input('post.user_id'))->value('user_pwd');
			if (empty($pwd)) {
				return json(['code'=>'0', 'errorcode'=>'-2', 'msg'=>'账号不存在']);
				exit();
			}else{
				if(!password_verify(input('post.oldpwd'), $pwd)){
					return json(['code'=>'0', 'errorcode'=>'-3', 'msg'=>'旧密码不正确']);
				}else{
					$manager->where('user_id', input('post.user_id'))->update([
						'user_pwd'	=> password_hash(input('post.user_pwd'), PASSWORD_DEFAULT)
						]);
					return json(['code'=>'1', 'msg'=>'密码修改成功']);
				}
			}
		}else{
			return $this->fetch();
		}
	}

	public function changeInfo(){
		$this->islogin();
		$manager = new Manager();
		if(request()->isPost()){
			$result = $this->validate(input('post.'), 'Manager.update');
			if (true !== $result) {
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>$result]);
				exit();
			}
			$manager->where('user_id', session('manager.uid'))->update([
				'name'		=>	input('post.name'),
				'email'		=>	input('post.email'),
				'heard'		=>	input('post.heard')
				]);
			session('manager.heard', input('post.heard'));
			return json(['code'=>'1', 'msg'=>'个人资料修改成功']);
		}else{
			$data = $manager->where('user_id', session('manager.uid'))->find();
			$this->assign('manager', $data);
			return $this->fetch();
		}
	}

	public function upheard(){
		$upfile = new Upfile('heardfile', 'image', ['name'=>'user/{uid}/{md5}.{ext}', 'autoresize'=>true, 'resizew'=>40, 'resizeh'=>40, 'resizemode'=>6, 'autowater'=>false]);
		if($upfile->save()){
			return json(['code'=>'1', 'msg'=>'上传成功', 'file'=>$upfile->getFile()]);
		}else{
			return json(['code'=>'0', 'msg'=>$upfile->getError(), 'file'=>'']);
		}
	}

	//锁屏
	public function lock(){
		$this->islogin();
		$islock = input('post.islock');
		if ($islock == '1') {
			session('manager.lock', true);
		}else{
			$manager = new Manager();
			$pwd = $manager->where('user_id', session('manager.uid'))->value('user_pwd');
			if (empty($pwd)) {
				return json(['code'=>'0', 'errorcode'=>'-1', 'msg'=>'登录过期']);
				exit();
			}else{
				if(!password_verify(input('post.pwd'), $pwd)){
					return json(['code'=>'0', 'errorcode'=>'-2', 'msg'=>'密码不正确']);
				}else{
					session('manager.lock', false);
					return json(['code'=>'1', 'msg'=>'解锁成功']);
				}
			}
		}
	}
	//清除缓存
	public function clearcache(){
		if (session('?manager')) {
			Cache::clear();
			return json(['code'=>'1', 'msg'=>'清除成功']);
		}else{
			return json(['code'=>'0', 'msg'=>'清除失败']);
		}
	}
	//判断是否登录
	private function islogin(){
		if (!session('?manager')) {
			$this->error('未登录或登录过期，请重新登录', '/admin/login');
		}
	}
}