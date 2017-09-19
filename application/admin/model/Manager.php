<?php 
namespace app\admin\model;
use think\Model;

class Manager extends Model
{
	//获取状态
	protected function getStatusAttr($value){
		$status = ['0'=>'无效', '1'=>'有效'];
		return $status[$value];
	}
	//角色名
	protected function getGroupTitleAttr($value){
		$groups = $this->groups;
		if (!empty($groups)) {
			$title = '';
			foreach ($groups as $group) {
				$title .= ','.$group['title'];
			}
			return trim($title, ',');
		}else{
			return '未分配';
		}		
	}
	//角色ID
	protected function getGroupIdAttr($value){
		$groups = $this->groups;
		if (!empty($groups)) {
			$id = '';
			foreach ($groups as $group) {
				$id .= ','.$group['id'];
			}
			return trim($id, ',');
		}else{
			return '';
		}
	}

	//存储密码
	protected function setUserPwdAttr($value){
		return password_hash($value, PASSWORD_DEFAULT);
	}

	//关联Group
	public function groups(){
		return $this->belongsToMany('AuthGroup', 'zc_auth_group_access', 'group_id', 'uid');
	}

	//分配用户角色
	public function setGroup($ids){
		$this->groups()->attach($ids);
		$groups = $this->groups()->column('id');
		if (!empty($groups)) {
			foreach ($groups as $id) {
				if (!in_array($id, $ids)) {
					$this->groups()->detach($id);
				}
			}
		}
	}
	//清除所有角色
	public function clearGroup(){
		$groups = $this->groups()->column('id');
		if (!empty($groups)) {
			foreach ($groups as $id) {
				$this->groups()->detach($id);
			}
		}
	}

}