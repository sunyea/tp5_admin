<?php
// +----------------------------------------------------------------------
// | Laypage.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-18 17:20:45
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------
namespace think\paginator\driver;

use think\Paginator;

/**
* layui 分页显示驱动
*/
class Laypage extends Paginator
{
	
	//获取layui的JS部分
	protected function getJs($url){
		$_js = <<<EOD
<div id="page"></div>
<script>
layui.use(['laypage'], function(){
	var laypage = layui.laypage;
	laypage({
		cont:'page',
		pages:$this->lastPage,
		groups:3,
		skip:true,
		curr:$this->currentPage,
		skin:'#1E9FFF',
		jump:function(obj, first){
			var url = '$url';
			url = url.replace('page=1', 'page='+obj.curr);
			if(!first){
				location.href=url;
			}
		}
	});
});
</script>

EOD;
		return $_js;
	}

	//渲染
	public function render(){
		if ($this->hasPages()) {
			return $this->getJs($this->url(1));
		}
	}
}