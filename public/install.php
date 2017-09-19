<?php
// +----------------------------------------------------------------------
// | install.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-06 11:03:05
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------

error_reporting(0);
@set_time_limit(0);
@set_magic_quotes_runtime(0);
ob_start();
define('ZC_ROOT', str_replace("\\",'/', dirname(__FILE__)));
if($_GET['res']) {
	$res = $_GET['res'];
	$reses = tpl_resources();
	if(array_key_exists($res, $reses)) {
		if($res == 'css') {
			header('content-type:text/css');
		} else {
			header('content-type:image/png');
		}
		echo base64_decode($reses[$res]);
		exit();
	}
}
$actions = array('license', 'env', 'db', 'finish');
$action = $_COOKIE['action'];
$action = in_array($action, $actions) ? $action : 'license';
$ispost = strtolower($_SERVER['REQUEST_METHOD']) == 'post';

if(file_exists(ZC_ROOT . '/data/install.lock') && $action != 'finish') {
	header('location: ./index.php');
	exit;
}
header('content-type: text/html; charset=utf-8');
if($action == 'license') {
	if($ispost) {
		setcookie('action', 'env');
		header('location: ?refresh');
		exit;
	}
	tpl_install_license();
}
if($action == 'env') {
	if($ispost) {
		setcookie('action', $_POST['do'] == 'continue' ? 'db' : 'license');
		header('location: ?refresh');
		exit;
	}
	$ret = array();
	$ret['server']['os']['value'] = php_uname();
	if(PHP_SHLIB_SUFFIX == 'dll') {
		$ret['server']['os']['remark'] = '建议使用 Linux 系统以提升程序性能';
		$ret['server']['os']['class'] = 'warning';
	}
	$ret['server']['sapi']['value'] = $_SERVER['SERVER_SOFTWARE'];
	if(PHP_SAPI == 'isapi') {
		$ret['server']['sapi']['remark'] = '建议使用 Apache 或 Nginx 以提升程序性能';
		$ret['server']['sapi']['class'] = 'warning';
	}
	$ret['server']['php']['value'] = PHP_VERSION;
	$ret['server']['dir']['value'] = ZC_ROOT;
	if(function_exists('disk_free_space')) {
		$ret['server']['disk']['value'] = floor(disk_free_space(ZC_ROOT) / (1024*1024)).'M';
	} else {
		$ret['server']['disk']['value'] = 'unknow';
	}
	$ret['server']['upload']['value'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';

	$ret['php']['version']['value'] = PHP_VERSION;
	$ret['php']['version']['class'] = 'success';
	if(version_compare(PHP_VERSION, '5.3.0') == -1) {
		$ret['php']['version']['class'] = 'danger';
		$ret['php']['version']['failed'] = true;
		$ret['php']['version']['remark'] = 'PHP版本必须为 5.3.0 以上.';
	}

	$ret['php']['mysql']['ok'] = function_exists('mysql_connect');
	if($ret['php']['mysql']['ok']) {
		$ret['php']['mysql']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
	} else {
		$ret['php']['pdo']['failed'] = true;
		$ret['php']['mysql']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
	}

	$ret['php']['pdo']['ok'] = extension_loaded('pdo') && extension_loaded('pdo_mysql');
	if($ret['php']['pdo']['ok']) {
		$ret['php']['pdo']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['pdo']['class'] = 'success';
		if(!$ret['php']['mysql']['ok']) {
			$ret['php']['pdo']['remark'] = '您的PHP环境不支持 mysql_connect，请开启此扩展. ';
		}
	} else {
		$ret['php']['pdo']['failed'] = true;
		if($ret['php']['mysql']['ok']) {
			$ret['php']['pdo']['value'] = '<span class="glyphicon glyphicon-remove text-warning"></span>';
			$ret['php']['pdo']['class'] = 'warning';
			$ret['php']['pdo']['remark'] = '您的PHP环境不支持PDO, 请开启此扩展.';
		} else {
			$ret['php']['pdo']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
			$ret['php']['pdo']['class'] = 'danger';
			$ret['php']['pdo']['remark'] = '您的PHP环境不支持PDO, 也不支持 mysql_connect, 系统无法正常运行.';
		}
	}

	$ret['php']['fopen']['ok'] = @ini_get('allow_url_fopen') && function_exists('fsockopen');
	if($ret['php']['fopen']['ok']) {
		$ret['php']['fopen']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
	} else {
		$ret['php']['fopen']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
	}

	$ret['php']['curl']['ok'] = extension_loaded('curl') && function_exists('curl_init');
	if($ret['php']['curl']['ok']) {
		$ret['php']['curl']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['curl']['class'] = 'success';
		if(!$ret['php']['fopen']['ok']) {
			$ret['php']['curl']['remark'] = '您的PHP环境虽然不支持 allow_url_fopen, 但已经支持了cURL, 这样系统是可以正常高效运行的, 不需要额外处理.';
		}
	} else {
		if($ret['php']['fopen']['ok']) {
			$ret['php']['curl']['value'] = '<span class="glyphicon glyphicon-remove text-warning"></span>';
			$ret['php']['curl']['class'] = 'warning';
			$ret['php']['curl']['remark'] = '您的PHP环境不支持cURL, 但支持 allow_url_fopen, 这样系统虽然可以运行, 但还是建议你开启cURL以提升程序性能和系统稳定性.';
		} else {
			$ret['php']['curl']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
			$ret['php']['curl']['class'] = 'danger';
			$ret['php']['curl']['remark'] = '您的PHP环境不支持cURL, 也不支持 allow_url_fopen, 系统无法正常运行. ';
			$ret['php']['curl']['failed'] = true;
		}
	}

	$ret['php']['ssl']['ok'] = extension_loaded('openssl');
	if($ret['php']['ssl']['ok']) {
		$ret['php']['ssl']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['ssl']['class'] = 'success';
	} else {
		$ret['php']['ssl']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['ssl']['class'] = 'danger';
		$ret['php']['ssl']['failed'] = true;
		$ret['php']['ssl']['remark'] = '没有启用OpenSSL, 将无法访问公众平台的接口, 系统无法正常运行. ';
	}

	$ret['php']['gd']['ok'] = extension_loaded('gd');
	if($ret['php']['gd']['ok']) {
		$ret['php']['gd']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['gd']['class'] = 'success';
	} else {
		$ret['php']['gd']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['gd']['class'] = 'danger';
		$ret['php']['gd']['failed'] = true;
		$ret['php']['gd']['remark'] = '没有启用GD, 将无法正常上传和压缩图片, 系统无法正常运行. ';
	}

	$ret['php']['dom']['ok'] = class_exists('DOMDocument');
	if($ret['php']['dom']['ok']) {
		$ret['php']['dom']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['dom']['class'] = 'success';
	} else {
		$ret['php']['dom']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['dom']['class'] = 'danger';
		$ret['php']['dom']['failed'] = true;
		$ret['php']['dom']['remark'] = '没有启用DOMDocument, 将无法正常安装使用模块, 系统无法正常运行. ';
	}

	$ret['php']['session']['ok'] = ini_get('session.auto_start');
	if($ret['php']['session']['ok'] == 0 || strtolower($ret['php']['session']['ok']) == 'off') {
		$ret['php']['session']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['session']['class'] = 'success';
	} else {
		$ret['php']['session']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['session']['class'] = 'danger';
		$ret['php']['session']['failed'] = true;
		$ret['php']['session']['remark'] = '系统session.auto_start开启, 将无法正常注册会员, 系统无法正常运行. ';
	}

	$ret['php']['asp_tags']['ok'] = ini_get('asp_tags');
	if(empty($ret['php']['asp_tags']['ok']) || strtolower($ret['php']['asp_tags']['ok']) == 'off') {
		$ret['php']['asp_tags']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['asp_tags']['class'] = 'success';
	} else {
		$ret['php']['asp_tags']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['asp_tags']['class'] = 'danger';
		$ret['php']['asp_tags']['failed'] = true;
		$ret['php']['asp_tags']['remark'] = '请禁用可以使用ASP 风格的标志，配置php.ini中asp_tags = Off';
	}

	$ret['write']['root']['ok'] = local_writeable(ZC_ROOT . '/');
	if($ret['write']['root']['ok']) {
		$ret['write']['root']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['write']['root']['class'] = 'success';
	} else {
		$ret['write']['root']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['write']['root']['class'] = 'danger';
		$ret['write']['root']['failed'] = true;
		$ret['write']['root']['remark'] = '本地目录无法写入, 将无法使用自动更新功能, 系统无法正常运行.';
	}
	$ret['write']['data']['ok'] = local_writeable(ZC_ROOT . '/../application');
	if($ret['write']['data']['ok']) {
		$ret['write']['data']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['write']['data']['class'] = 'success';
	} else {
		$ret['write']['data']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['write']['data']['class'] = 'danger';
		$ret['write']['data']['failed'] = true;
		$ret['write']['data']['remark'] = 'application目录无法写入, 将无法写入配置文件, 系统无法正常安装. ';
	}

	$ret['continue'] = true;
	foreach($ret['php'] as $opt) {
		if($opt['failed']) {
			$ret['continue'] = false;
			break;
		}
	}
	if($ret['write']['failed']) {
		$ret['continue'] = false;
	}
	tpl_install_env($ret);
}
if($action == 'db') {
	if($ispost) {
		if($_POST['do'] != 'continue') {
			setcookie('action', 'env');
			header('location: ?refresh');
			exit();
		}
		$db = $_POST['db'];
		$user = $_POST['user'];
		$link = mysql_connect($db['server'], $db['username'], $db['password']);
		if(empty($link)) {
			$error = mysql_error();
			if (strpos($error, 'Access denied for user') !== false) {
				$error = '您的数据库访问用户名或是密码错误. <br />';
			} else {
				$error = iconv('gbk', 'utf8', $error);
			}
		} else {
			mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
			mysql_query("SET sql_mode=''");
			if(mysql_errno()) {
				$error = mysql_error();
			} else {
				$query = mysql_query("SHOW DATABASES LIKE  '{$db['name']}';");
				if (!mysql_fetch_assoc($query)) {
					if(mysql_get_server_info() > '4.1') {
						mysql_query("CREATE DATABASE IF NOT EXISTS `{$db['name']}` DEFAULT CHARACTER SET utf8", $link);
					} else {
						mysql_query("CREATE DATABASE IF NOT EXISTS `{$db['name']}`", $link);
					}
				}
				$query = mysql_query("SHOW DATABASES LIKE  '{$db['name']}';");
				if (!mysql_fetch_assoc($query)) {
					$error .= "数据库不存在且创建数据库失败. <br />";
				}
				if(mysql_errno()) {
					$error .= mysql_error();
				}
			}
		}
		if(empty($error)) {
			mysql_select_db($db['name']);
			$query = mysql_query("SHOW TABLES LIKE '{$db['prefix']}%';");
			if (mysql_fetch_assoc($query)) {
				$error = '您的数据库不为空，请重新建立数据库或是清空该数据库或更改表前缀！';
			}
		}
		if(empty($error)) {
			$pieces = explode(':', $db['server']);
			$db['port'] = !empty($pieces[1]) ? $pieces[1] : '3306';
			$config = local_config();
			$config = str_replace(array('{db-server}', '{db-username}', '{db-password}', '{db-port}', '{db-name}', '{db-tablepre}'), 
				array($db['server'], $db['username'], $db['password'], $db['port'], $db['name'], $db['prefix']), $config);
			$dbfile = ZC_ROOT . '/../application/database.php';
			$dbtable = ZC_ROOT . '/data/table.sql';
			$dbsql = file_get_contents($dbtable);
			if ($dbsql) {
				local_run($dbsql);
				$password = password_hash($user['password'], PASSWORD_DEFAULT);
				$guid = strtolower(md5(uniqid(mt_rand(), true)));
				mysql_query("INSERT INTO {$db['prefix']}manager (`user_guid`, `user_id`, `user_pwd`, `name`, `email`, `status`, `login_ip`, `login_time`, `login_count`) VALUES('{$guid}', '{$user['username']}', '{$password}', '超级管理员', '', 1, '".$_SERVER["REMOTE_ADDR"]."', '".date('Y-m-d H:i:s')."', 0)");
				mysql_query("INSERT INTO {$db['prefix']}auth_group_access (`uid`, `group_id`) VALUES(1,1)");
			}
			
			file_put_contents($dbfile, $config);
			touch(ZC_ROOT . '/data/install.lock');
			setcookie('action', 'finish');
			header('location: ?refresh');
			exit();
		}
	}
	tpl_install_db($error);

}
if($action == 'finish') {
	setcookie('action', '', -10);
	tpl_install_finish();
}

function local_writeable($dir) {
	$writeable = 0;
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = fopen("$dir/test.txt", 'w')) {
			fclose($fp);
			unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

function local_config() {
	$cfg = <<<EOF
<?php
// +----------------------------------------------------------------------
// | database.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-06 22:30:43
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------

return [
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => '{db-server}',
    // 数据库名
    'database'        => '{db-name}',
    // 用户名
    'username'        => '{db-username}',
    // 密码
    'password'        => '{db-password}',
    // 端口
    'hostport'        => '{db-port}',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => '{db-tablepre}',
    // 数据库调试模式
    'debug'           => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 是否严格检查字段是否存在
    'fields_strict'   => true,
    // 数据集返回类型
    'resultset_type'  => 'array',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,
    // Builder类
    'builder'         => '',
    // Query类
    'query'           => '\\think\\db\\Query',
];
EOF;
	return trim($cfg);
}

function local_mkdirs($path) {
	if(!is_dir($path)) {
		local_mkdirs(dirname($path));
		mkdir($path);
	}
	return is_dir($path);
}

function local_run($sql) {
	global $link, $db;

	if(!isset($sql) || empty($sql)) return;
	$sql = str_replace("/^\/\*.*\*\/$/", "", $sql);
	$sql = str_replace("\r", "\n", str_replace(' sy_', ' '.$db['prefix'], $sql));
	$sql = str_replace("\r", "\n", str_replace(' `sy_', ' `'.$db['prefix'], $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);
	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			if(!mysql_query($query, $link)) {
				echo mysql_errno() . ": " . mysql_error() . "<br />";
				exit($query);
			}
		}
	}
}

function tpl_frame() {
	global $action, $actions;
	$action = $_COOKIE['action'];
	$step = array_search($action, $actions);
	$steps = array();
	for($i = 0; $i <= $step; $i++) {
		if($i == $step) {
			$steps[$i] = ' list-group-item-info';
		} else {
			$steps[$i] = ' list-group-item-success';
		}
	}
	$progress = $step * 25 + 25;
	$content = ob_get_contents();
	ob_clean();
	$tpl = <<<EOF
<!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>安装系统 - 商易科技 - 网站管理系统</title>
		<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.2.0/css/bootstrap.min.css">
		<style>
			html,body{font-size:13px;font-family:"Microsoft YaHei UI", "微软雅黑", "宋体";}
			.pager li.previous a{margin-right:10px;}
			.header a{color:#FFF;}
			.header a:hover{color:#428bca;}
			.footer{padding:10px;}
			.footer a,.footer{color:#eee;font-size:14px;line-height:25px;}
		</style>
		<!--[if lt IE 9]>
		  <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body style="background-color:#28b0e4;">
		<div class="container">
			<div class="header" style="margin:15px auto;">
				<ul class="nav nav-pills pull-right" role="tablist">
					<li role="presentation" class="active"><a href="javascript:;">安装网站管理系统</a></li>
					<li role="presentation"><a href="http://www.sunyea.cn">商易官网</a></li>
				</ul>
				<img src="?res=logo" />
			</div>
			<div class="row well" style="margin:auto 0;">
				<div class="col-xs-3">
					<div class="progress" title="安装进度">
						<div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="{$progress}" aria-valuemin="0" aria-valuemax="100" style="width: {$progress}%;">
							{$progress}%
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							安装步骤
						</div>
						<ul class="list-group">
							<a href="javascript:;" class="list-group-item{$steps[0]}"><span class="glyphicon glyphicon-copyright-mark"></span> &nbsp; 许可协议</a>
							<a href="javascript:;" class="list-group-item{$steps[1]}"><span class="glyphicon glyphicon-eye-open"></span> &nbsp; 环境监测</a>
							<a href="javascript:;" class="list-group-item{$steps[2]}"><span class="glyphicon glyphicon-cog"></span> &nbsp; 参数配置</a>
							<a href="javascript:;" class="list-group-item{$steps[3]}"><span class="glyphicon glyphicon-ok"></span> &nbsp; 成功</a>
						</ul>
					</div>
				</div>
				<div class="col-xs-9">
					{$content}
				</div>
			</div>
			<div class="footer" style="margin:15px auto;">
				<div class="text-center">
					<a href="http://www.sunyea.cn">关于商易</a> &nbsp; &nbsp; <a href="http://www.sunyea.cn">购买授权</a>
				</div>
				<div class="text-center">
					Powered by <a href="http://www.sunyea.cn"><b>商易</b></a> v1.0 &copy; 2017 <a href="http://www.sunyea.cn">www.sunyea.cn</a>
				</div>
			</div>
		</div>
		<script src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>
		<script src="http://cdn.bootcss.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
	</body>
</html>
EOF;
	echo trim($tpl);
}

function tpl_install_license() {
	echo <<<EOF
		<div class="panel panel-default">
			<div class="panel-heading">阅读许可协议</div>
			<div class="panel-body" style="overflow-y:scroll;max-height:400px;line-height:20px;">
				<h3>版权所有 (c)2017，德阳商易网络科技有限责任公司保留所有权利。 </h3>
				<p>
					感谢您选择商易 - 网站管理系统（以下简称系统），系统基于 PHP + MySQL的技术开发，全部源码开放。 <br />
					为了使你正确并合法的使用本软件，请你在使用前务必阅读清楚下面的协议条款：
				</p>
				<p>
					<strong>一、本授权协议适用且仅适用于商易网站管理系统任何版本，商易官方对本授权协议的最终解释权。</strong>
				</p>
				<p>
					<strong>二、协议许可的权利 </strong>
					<ol>
						<li>您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。</li>
						<li>您可以在协议规定的约束和限制范围内修改系统源代码或界面风格以适应您的网站要求。</li>
						<li>您拥有使用本软件构建的网站全部内容所有权，并独立承担与这些内容的相关法律义务。</li>
						<li>获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持内容，自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见将被作为首要考虑，但没有一定被采纳的承诺或保证。</li>
					</ol>
				</p>
				<p>
					<strong>三、协议规定的约束和限制 </strong>
					<ol>
						<li>未获商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目的或实现盈利的网站）。</li>
						<li>未经官方许可，不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。</li>
						<li>未经官方许可，禁止在系统的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。</li>
						<li>如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。</li>
					</ol>
				</p>
				<p>
					<strong>四、有限担保和免责声明 </strong>
					<ol>
						<li>本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。</li>
						<li>用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺对免费用户提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。</li>
						<li>电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始确认本协议并安装系统，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。</li>
						<li>如果本软件带有其它软件的整合API示范例子包，这些文件版权不属于本软件官方，并且这些文件是没经过授权发布的，请参考相关软件的使用许可合法的使用。</li>
					</ol>
				</p>
			</div>
		</div>
		<form class="form-inline" role="form" method="post">
			<ul class="pager">
				<li class="pull-left" style="display:block;padding:5px 10px 5px 0;">
					<div class="checkbox">
						<label>
							<input type="checkbox"> 我已经阅读并同意此协议
						</label>
					</div>
				</li>
				<li class="previous"><a href="javascript:;" onclick="if(jQuery(':checkbox:checked').length == 1){jQuery('form')[0].submit();}else{alert('您必须同意软件许可协议才能安装！')};">继续 <span class="glyphicon glyphicon-chevron-right"></span></a></li>
			</ul>
		</form>
EOF;
	tpl_frame();
}

function tpl_install_env($ret = array()) {
	if(empty($ret['continue'])) {
		$continue = '<li class="previous disabled"><a href="javascript:;">请先解决环境问题后继续</a></li>';
	} else {
		$continue = '<li class="previous"><a href="javascript:;" onclick="$(\'#do\').val(\'continue\');$(\'form\')[0].submit();">继续 <span class="glyphicon glyphicon-chevron-right"></span></a></li>';
	}
	echo <<<EOF
		<div class="panel panel-default">
			<div class="panel-heading">服务器信息</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">参数</th>
					<th>值</th>
					<th></th>
				</tr>
				<tr class="{$ret['server']['os']['class']}">
					<td>服务器操作系统</td>
					<td>{$ret['server']['os']['value']}</td>
					<td>{$ret['server']['os']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['sapi']['class']}">
					<td>Web服务器环境</td>
					<td>{$ret['server']['sapi']['value']}</td>
					<td>{$ret['server']['sapi']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['php']['class']}">
					<td>PHP版本</td>
					<td>{$ret['server']['php']['value']}</td>
					<td>{$ret['server']['php']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['dir']['class']}">
					<td>程序安装目录</td>
					<td>{$ret['server']['dir']['value']}</td>
					<td>{$ret['server']['dir']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['disk']['class']}">
					<td>磁盘空间</td>
					<td>{$ret['server']['disk']['value']}</td>
					<td>{$ret['server']['disk']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['upload']['class']}">
					<td>上传限制</td>
					<td>{$ret['server']['upload']['value']}</td>
					<td>{$ret['server']['upload']['remark']}</td>
				</tr>
			</table>
		</div>

		<div class="alert alert-info">PHP环境要求必须满足下列所有条件，否则系统或系统部份功能将无法使用。</div>
		<div class="panel panel-default">
			<div class="panel-heading">PHP环境要求</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">选项</th>
					<th style="width:180px;">要求</th>
					<th style="width:50px;">状态</th>
					<th>说明及帮助</th>
				</tr>
				<tr class="{$ret['php']['version']['class']}">
					<td>PHP版本</td>
					<td>5.3或者5.3以上</td>
					<td>{$ret['php']['version']['value']}</td>
					<td>{$ret['php']['version']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['pdo']['class']}">
					<td>MySQL</td>
					<td>支持(建议支持PDO)</td>
					<td>{$ret['php']['mysql']['value']}</td>
					<td rowspan="2">{$ret['php']['pdo']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['pdo']['class']}">
					<td>PDO_MYSQL</td>
					<td>支持(强烈建议支持)</td>
					<td>{$ret['php']['pdo']['value']}</td>
				</tr>
				<tr class="{$ret['php']['curl']['class']}">
					<td>allow_url_fopen</td>
					<td>支持(建议支持cURL)</td>
					<td>{$ret['php']['fopen']['value']}</td>
					<td rowspan="2">{$ret['php']['curl']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['curl']['class']}">
					<td>cURL</td>
					<td>支持(强烈建议支持)</td>
					<td>{$ret['php']['curl']['value']}</td>
				</tr>
				<tr class="{$ret['php']['ssl']['class']}">
					<td>openSSL</td>
					<td>支持</td>
					<td>{$ret['php']['ssl']['value']}</td>
					<td>{$ret['php']['ssl']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['gd']['class']}">
					<td>GD2</td>
					<td>支持</td>
					<td>{$ret['php']['gd']['value']}</td>
					<td>{$ret['php']['gd']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['dom']['class']}">
					<td>DOM</td>
					<td>支持</td>
					<td>{$ret['php']['dom']['value']}</td>
					<td>{$ret['php']['dom']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['session']['class']}">
					<td>session.auto_start</td>
					<td>关闭</td>
					<td>{$ret['php']['session']['value']}</td>
					<td>{$ret['php']['session']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['asp_tags']['class']}">
					<td>asp_tags</td>
					<td>关闭</td>
					<td>{$ret['php']['asp_tags']['value']}</td>
					<td>{$ret['php']['asp_tags']['remark']}</td>
				</tr>
			</table>
		</div>

		<div class="alert alert-info">系统要求整个安装目录必须可写, 才能使用系统所有功能。</div>
		<div class="panel panel-default">
			<div class="panel-heading">目录权限监测</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">目录</th>
					<th style="width:180px;">要求</th>
					<th style="width:50px;">状态</th>
					<th>说明及帮助</th>
				</tr>
				<tr class="{$ret['write']['root']['class']}">
					<td>/</td>
					<td>整目录可写</td>
					<td>{$ret['write']['root']['value']}</td>
					<td>{$ret['write']['root']['remark']}</td>
				</tr>
				<tr class="{$ret['write']['data']['class']}">
					<td>../application</td>
					<td>application目录可写</td>
					<td>{$ret['write']['data']['value']}</td>
					<td>{$ret['write']['data']['remark']}</td>
				</tr>
			</table>
		</div>
		<form class="form-inline" role="form" method="post">
			<input type="hidden" name="do" id="do" />
			<ul class="pager">
				<li class="previous"><a href="javascript:;" onclick="$('#do').val('back');$('form')[0].submit();"><span class="glyphicon glyphicon-chevron-left"></span> 返回</a></li>
				{$continue}
			</ul>
		</form>
EOF;
	tpl_frame();
}

function tpl_install_db($error = '') {
	if(!empty($error)) {
		$message = '<div class="alert alert-danger">发生错误: ' . $error . '</div>';
	}
	echo <<<EOF
	{$message}
	<form class="form-horizontal" method="post" role="form">
		<div class="panel panel-default">
			<div class="panel-heading">数据库选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库主机</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[server]" value="localhost">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库用户</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[username]" value="root">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库密码</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[password]">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">表前缀</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[prefix]" value="sy_">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库名称</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[name]" value="sunyea">
					</div>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">管理选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">管理员账号</label>
					<div class="col-sm-4">
						<input class="form-control" type="username" name="user[username]">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">管理员密码</label>
					<div class="col-sm-4">
						<input class="form-control" type="password" name="user[password]">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">确认密码</label>
					<div class="col-sm-4">
						<input class="form-control" type="password"">
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="do" id="do" />
		<ul class="pager">
			<li class="previous"><a href="javascript:;" onclick="$('#do').val('back');$('form')[0].submit();"><span class="glyphicon glyphicon-chevron-left"></span> 返回</a></li>
			<li class="previous"><a href="javascript:;" onclick="if(check(this)){jQuery('#do').val('continue');$('form')[0].submit();}">继续 <span class="glyphicon glyphicon-chevron-right"></span></a></li>
		</ul>
	</form>
	<script>
		var lock = false;
		function check(obj) {
			if(lock) {
				return;
			}
			$('.form-control').parent().parent().removeClass('has-error');
			var error = false;
			$('.form-control').each(function(){
				if($(this).val() == '') {
					$(this).parent().parent().addClass('has-error');
					this.focus();
					error = true;
				}
			});
			if(error) {
				alert('请检查未填项');
				return false;
			}
			if($(':password').eq(0).val() != $(':password').eq(1).val()) {
				$(':password').parent().parent().addClass('has-error');
				alert('确认密码不正确.');
				return false;
			}
			lock = true;
			$(obj).parent().addClass('disabled');
			$(obj).html('正在执行安装');
			return true;
		}
	</script>
EOF;
	tpl_frame();
}

function tpl_install_finish() {
	echo <<<EOF
	<div class="page-header"><h3>安装完成</h3></div>
	<div class="alert alert-success">
		恭喜您!已成功安装“商易 - 网站管理系统”，您现在可以: <a target="_blank" class="btn btn-success" href="./index">访问网站首页</a>
		<a target="_blank" class="btn btn-success" href="./admin">登录管理</a>
	</div>
EOF;
	tpl_frame();
}

function tpl_resources() {
	static $res = array(
		'logo' => 'iVBORw0KGgoAAAANSUhEUgAAAaQAAABfCAYAAACnbrNbAAAACXBIWXMAAAsTAAALEwEAmpwYAAAKTWlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVN3WJP3Fj7f92UPVkLY8LGXbIEAIiOsCMgQWaIQkgBhhBASQMWFiApWFBURnEhVxILVCkidiOKgKLhnQYqIWotVXDjuH9yntX167+3t+9f7vOec5/zOec8PgBESJpHmomoAOVKFPDrYH49PSMTJvYACFUjgBCAQ5svCZwXFAADwA3l4fnSwP/wBr28AAgBw1S4kEsfh/4O6UCZXACCRAOAiEucLAZBSAMguVMgUAMgYALBTs2QKAJQAAGx5fEIiAKoNAOz0ST4FANipk9wXANiiHKkIAI0BAJkoRyQCQLsAYFWBUiwCwMIAoKxAIi4EwK4BgFm2MkcCgL0FAHaOWJAPQGAAgJlCLMwAIDgCAEMeE80DIEwDoDDSv+CpX3CFuEgBAMDLlc2XS9IzFLiV0Bp38vDg4iHiwmyxQmEXKRBmCeQinJebIxNI5wNMzgwAABr50cH+OD+Q5+bk4eZm52zv9MWi/mvwbyI+IfHf/ryMAgQAEE7P79pf5eXWA3DHAbB1v2upWwDaVgBo3/ldM9sJoFoK0Hr5i3k4/EAenqFQyDwdHAoLC+0lYqG9MOOLPv8z4W/gi372/EAe/tt68ABxmkCZrcCjg/1xYW52rlKO58sEQjFu9+cj/seFf/2OKdHiNLFcLBWK8ViJuFAiTcd5uVKRRCHJleIS6X8y8R+W/QmTdw0ArIZPwE62B7XLbMB+7gECiw5Y0nYAQH7zLYwaC5EAEGc0Mnn3AACTv/mPQCsBAM2XpOMAALzoGFyolBdMxggAAESggSqwQQcMwRSswA6cwR28wBcCYQZEQAwkwDwQQgbkgBwKoRiWQRlUwDrYBLWwAxqgEZrhELTBMTgN5+ASXIHrcBcGYBiewhi8hgkEQcgIE2EhOogRYo7YIs4IF5mOBCJhSDSSgKQg6YgUUSLFyHKkAqlCapFdSCPyLXIUOY1cQPqQ28ggMor8irxHMZSBslED1AJ1QLmoHxqKxqBz0XQ0D12AlqJr0Rq0Hj2AtqKn0UvodXQAfYqOY4DRMQ5mjNlhXIyHRWCJWBomxxZj5Vg1Vo81Yx1YN3YVG8CeYe8IJAKLgBPsCF6EEMJsgpCQR1hMWEOoJewjtBK6CFcJg4Qxwicik6hPtCV6EvnEeGI6sZBYRqwm7iEeIZ4lXicOE1+TSCQOyZLkTgohJZAySQtJa0jbSC2kU6Q+0hBpnEwm65Btyd7kCLKArCCXkbeQD5BPkvvJw+S3FDrFiOJMCaIkUqSUEko1ZT/lBKWfMkKZoKpRzame1AiqiDqfWkltoHZQL1OHqRM0dZolzZsWQ8ukLaPV0JppZ2n3aC/pdLoJ3YMeRZfQl9Jr6Afp5+mD9HcMDYYNg8dIYigZaxl7GacYtxkvmUymBdOXmchUMNcyG5lnmA+Yb1VYKvYqfBWRyhKVOpVWlX6V56pUVXNVP9V5qgtUq1UPq15WfaZGVbNQ46kJ1Bar1akdVbupNq7OUndSj1DPUV+jvl/9gvpjDbKGhUaghkijVGO3xhmNIRbGMmXxWELWclYD6yxrmE1iW7L57Ex2Bfsbdi97TFNDc6pmrGaRZp3mcc0BDsax4PA52ZxKziHODc57LQMtPy2x1mqtZq1+rTfaetq+2mLtcu0W7eva73VwnUCdLJ31Om0693UJuja6UbqFutt1z+o+02PreekJ9cr1Dund0Uf1bfSj9Rfq79bv0R83MDQINpAZbDE4Y/DMkGPoa5hpuNHwhOGoEctoupHEaKPRSaMnuCbuh2fjNXgXPmasbxxirDTeZdxrPGFiaTLbpMSkxeS+Kc2Ua5pmutG003TMzMgs3KzYrMnsjjnVnGueYb7ZvNv8jYWlRZzFSos2i8eW2pZ8ywWWTZb3rJhWPlZ5VvVW16xJ1lzrLOtt1ldsUBtXmwybOpvLtqitm63Edptt3xTiFI8p0in1U27aMez87ArsmuwG7Tn2YfYl9m32zx3MHBId1jt0O3xydHXMdmxwvOuk4TTDqcSpw+lXZxtnoXOd8zUXpkuQyxKXdpcXU22niqdun3rLleUa7rrStdP1o5u7m9yt2W3U3cw9xX2r+00umxvJXcM970H08PdY4nHM452nm6fC85DnL152Xlle+70eT7OcJp7WMG3I28Rb4L3Le2A6Pj1l+s7pAz7GPgKfep+Hvqa+It89viN+1n6Zfgf8nvs7+sv9j/i/4XnyFvFOBWABwQHlAb2BGoGzA2sDHwSZBKUHNQWNBbsGLww+FUIMCQ1ZH3KTb8AX8hv5YzPcZyya0RXKCJ0VWhv6MMwmTB7WEY6GzwjfEH5vpvlM6cy2CIjgR2yIuB9pGZkX+X0UKSoyqi7qUbRTdHF09yzWrORZ+2e9jvGPqYy5O9tqtnJ2Z6xqbFJsY+ybuIC4qriBeIf4RfGXEnQTJAntieTE2MQ9ieNzAudsmjOc5JpUlnRjruXcorkX5unOy553PFk1WZB8OIWYEpeyP+WDIEJQLxhP5aduTR0T8oSbhU9FvqKNolGxt7hKPJLmnVaV9jjdO31D+miGT0Z1xjMJT1IreZEZkrkj801WRNberM/ZcdktOZSclJyjUg1plrQr1zC3KLdPZisrkw3keeZtyhuTh8r35CP5c/PbFWyFTNGjtFKuUA4WTC+oK3hbGFt4uEi9SFrUM99m/ur5IwuCFny9kLBQuLCz2Lh4WfHgIr9FuxYji1MXdy4xXVK6ZHhp8NJ9y2jLspb9UOJYUlXyannc8o5Sg9KlpUMrglc0lamUycturvRauWMVYZVkVe9ql9VbVn8qF5VfrHCsqK74sEa45uJXTl/VfPV5bdra3kq3yu3rSOuk626s91m/r0q9akHV0IbwDa0b8Y3lG19tSt50oXpq9Y7NtM3KzQM1YTXtW8y2rNvyoTaj9nqdf13LVv2tq7e+2Sba1r/dd3vzDoMdFTve75TsvLUreFdrvUV99W7S7oLdjxpiG7q/5n7duEd3T8Wej3ulewf2Re/ranRvbNyvv7+yCW1SNo0eSDpw5ZuAb9qb7Zp3tXBaKg7CQeXBJ9+mfHvjUOihzsPcw83fmX+39QjrSHkr0jq/dawto22gPaG97+iMo50dXh1Hvrf/fu8x42N1xzWPV56gnSg98fnkgpPjp2Snnp1OPz3Umdx590z8mWtdUV29Z0PPnj8XdO5Mt1/3yfPe549d8Lxw9CL3Ytslt0utPa49R35w/eFIr1tv62X3y+1XPK509E3rO9Hv03/6asDVc9f41y5dn3m978bsG7duJt0cuCW69fh29u0XdwruTNxdeo94r/y+2v3qB/oP6n+0/rFlwG3g+GDAYM/DWQ/vDgmHnv6U/9OH4dJHzEfVI0YjjY+dHx8bDRq98mTOk+GnsqcTz8p+Vv9563Or59/94vtLz1j82PAL+YvPv655qfNy76uprzrHI8cfvM55PfGm/K3O233vuO+638e9H5ko/ED+UPPR+mPHp9BP9z7nfP78L/eE8/sl0p8zAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAEWaSURBVHja7L15mB3ldSb+nvNV1V379u1udau1txYEYjFiMQYcjAj2GDuODUkYP4lnYuGZJzN5JhPjmcl45jeLxWQSj5NJwEuc1UH22MaxicF24jUGYQwGY0BsAqGttbXUrV5uL3et+s75/VF1uy9N72pJLVzv81zd1r23qr76quq833u+851DqooYMWLEWGwoBKQc/k0+SJUQkIIYIIUaBwQBwAACKBwQAIGClaAEQBVE4acQAphmPh44/G30Hn7mQAGQAqD4uixlUExIMWLEOFMIyUUAMtBKgW1vd05Ko2nO5EroWD3GXkvADFgIDBxAdJx0FAAkgIKhfg1MCkXFISEBhAFAKBkYhwnwFAZQcEQ8ISmJhtQEEkAZAVk4MPGFWaJw4i6IESPGmaAiAGAwlAxILVAaM9WBF96RGPnB5yt+9iROXftRu/IXHmxu7fTJpABHQvawCgQFR0olIlK2PYfzQfnQ2yxJwghVQQSQJhDQqDXqOekNPzLpXNk0t5U11eKT4wLsgiNBpZBQaQEwIWstSCnVB++hYosRE1KMGDHOLyhQt99EBBFBdeBo0hs7st4feemrNPiTQ+XW6z/qpDc95GcyPihwjbp+re/ZdxqYWmX4x3c3cbnDVCtOebQnUaXQsWcJIDLqWVCNApiWy47aYNPHE23L+k3HVd+m/KqSZLPKcEAMKDWoogXySUxEZx6xyy5GjBhnko/G7b/UyrAHn1lefuWPDyVH9qRULZgZ1dQqFbetRKmOvlDSiCOlkx1OMJRwR3tAhiESgMiAwSCtwTJA4kBJoEwwgcCyIEhvUDRvOsay5eO8Zdt9TtuGgmZyMBBACQQad+fNVx3FhBQTUowYMc5nMlKBEofBClpDpX/AyLNf+6CO3v+55NjJ8HckEAKMBrDkgJUhJDAafgchkAFUCKQMywGMMhD9DThgCxAHsMRgBaqZlajQZY+m1r3l02bT++5PZNNK7ABEcwpuaCQgVYW1IXkyc3xhzyDMjh074l6IESPG4o92IUBERoBAlYFERl0rPWP9fW9J+PvXgRRMBCNAQC4YBIKAgChCjyHMcC1gyULYhEQFwEBBSICoBhAgZMBKIFhQMIZU9dV1fm3k3Xxi6CQlcq9qeplvSCHEaBQ72uBWRF1JNfyAiFAqlcgYAyKKldI8lWW9D2OFFCNGjHOnjgAAAUL3GEPVQpUwMjbK5tUHf9W+uPPLaT7uaMnCkEAVUBOAg3DeJ4AFkQOjFgEIBg4sAhhSWJgwjlsAVgURg1ggFhAYGNLwuGnCWK1TqOvSw6Od/+bK9q7Nw4l0QpUMCAEgJmQjmrLx4/B9H47jgIhi9908yKjOL3NVlrFCihEjxhlQR4AKQMRQDQ0+KYFYkHA99bllnxnDkcrIyVs87XcIDMsKoyFBCAEGLgQ+CAYMgipBSUHKMABIDVgUbDgkCSiEGUQKKINIYH0LT4fJH7FZHut+51A1802vua1oOAEyErrwxslFwpZPwTXGmPGRfkxGsxNRYz/Np89iQooRI8aiQ2DBUeAAkUCVQExQZQgYJpGRU5m1L2eqQW9Pxekc4dUnSlUny17OUNUjh0tEGq6FdeAiUIBJQFp3/xCEAjAbWCsAO6BoWWwY2adQZSiFpJgICsaMja6ALdxkpFTS0eoR47KK8YTJHV+nBHq9QKob2FqtFpPSHMio3kcL6avYZRcjRowzQkmq4VyNiIBrw2zLRWNIGcqiqaagQp4WBoc8//DzbeVDRy9QglQqB1fk9eh/6Bh75S3GO0V1F5mD8B3kQFGDkgNAwWKBKKRbVaFMYAnnmlhDrWYhcEAIogwQ5cQGv9J8wfPZtdd/Qlfe+KCzbJXvuQakBHBEnlMY0XK5DMdxxl13MaYnpNcRzRz7K16HFCNGjDMDAoLagIue46ny4HPvlN6hBNyX/xcN5x/nTMdjurxttNmiCgfo7+p6nOEjZdbuH+k+2NukR37Y1J90iMowSrCwUABMPqAOWGtQNhCNFBiFaYqMAlYJShZQieagFNb4oGi+KEX73OEe7iKS/14J2n+ca2o5aZyUOuxG6oimNaqxOprlkkcDiIWQUUxIMWLEOGMjZSn0OJVDP3h/5vhDnw5OHc56pZKBKgJGlyP6AX+/wk83BbqsSZPBqufTTdxKhfzDbS2Fm/wxxwqJY5oYOixwmw10JIC1Cm4BqOCgliO4w4IAFpxNAKMBJEcYHW2VsVR+hKAsyj4RAWqNkltjFcM1GD/bdnx4sPN/Zlc4HBQDh5Lkh/4+nhx2N4447HvupDTdPTEbOcUuuxgxYiw6pHDcrR546Fb3mb+7Nxjan2EqA2oBhOl7wlSqDNVwYauqIsh0hKHiYtSUe4lVwPBg2YeCAbUgOCAVUBS+HQYzMFRTGCVPi+m24cFy1ydp2cpnS9TGkmwpKgJGunXYS3nF7MCx/HCl2mJTTWXTtrInv2rN8eUrOoabMy1i3Gh8roDi9cazvhYpVkhnkMxiQooRI8aCVBAasjDAgmDCnHGVYa7s+davmme+9jcoPJdjGwoPSwDUhoEGGi5gJYrmdoigJHACgjVhyLaqwlGCZUzMB5FC4YPggkURUBNKkgwKwbpHhlZf+JeBdgZYs2Gf07J6KJ1MlFOZdIU52loZxmUbBGJUfUq46SCZcoKmdJPAc8CEeljE+PkhOkeFACIgdjCRTfz1v5tLX4W59cL5tfrnqmEn1TObR8HSU+xTIqVhFj38fN77azipxUqkHrvsYsSIsRANFBkgBhTgunUVH/6hPS2JV//+87XCMymGAUZrsM0uUAjg5DxgqAbbaiDDPjjnwh0FVAU2x9BRgTQTaKQKk0tAhqvQlgS0YCF5hjtkoc0eUKhhKL9chvx1jxVXXfRXw4lNA6n2NUea2lf3pZtTxTYPvjfa025PHXdBJ96G/oFlMjbwJs62PY/21lHY1Q95nctKWs2P2UxGwiW58trz03Bhb0gMDEHozWMKCWQ8kkwb3Hg0teGmhu8IHGagqP/dEDmo0Wek9U0nk8QESc1EHjp+fSaDx8/vtf/HpPB3nvX6o65QiWYlpbmSXayQYsSIsTB11FDmQVVBSvCP7s4PPfOX32n62fev9VAZV08MA5CBQEEQqBCYbGSoXEgeQMEiDMJTMAKIJMEkUKovrhWIhmuSRnLL5Ihz5e+XWi5/QdpX9DSt3XygrX3ZcI6rYg53t+vwV5/wjh1dZot9SQXA1RGCMBgKLoygtnIFKulskGj+hS9gzY3/n6zePJjs6PIdo+E6psggjxtZDdsahpdPfDE5zPm1qmoantJxKw0wNagkHU9tpDR5uwlltJBrNU5u0R6jUlOvnyqbi9wDEIne8b4JdzQziYnIrHNwMSHFiBFjQQqpro5CFxxQGxug2uNf+Lfec3/5WTNcRJVdlCkj0pS0pmoCJCpJLYombJHTtoiAGAQPDH9ccaia0D8ndWPNgEQLLSFQAoZNe3Ake/X/KK677mdm7WX7V3R29OWbmypNIz3Z6ssP/Ro//+efMyPFaM7JQAlgDfPlhcbThbE2jNKDj5F8p9oN1/+4bcsHbrcbru41CReGOCJPHVeCNd/CsMCwiZTTa0lnvJjgOFGH273GxjeQOMaJRxrcedxQlHBqVhN9fXKJmdxwr1E/GrYzXJ8VLfRCdA0pJEcBpqwYpa9rV0REGp5juGg5bCBFi5Xrx65vO5tSil12MWLEWABCV1OjkXRsyZORwmbyVlaHk9VgCG0vjKaWfwOtHS8Xi80dTZmhgi4zNe3vuSLjD/7iMtt3dbLSm3K4ClIDiSrCEhRghcKAIGAmiCiEFDWkcDxz5R8U1133M2/dxa+uXbvhZGve850DL3YEP/n9Q073cylWAeBEwRIWxgLV1mY4EAgMWAQ1MnCHCyB1kCv0kT7yvRuOnXj1pY7i9n9VvfjWb2ZSaSVmEDRalwRUa2VyjaPkueBxl1uDK2xcJSlUIvcahWHp431G4buMUxVN9CUAEQuChDkjaCJ3nojABjb6v4KNO6Nxr38XBEFo6OtrpwhQquskhVWBtQoiC9d1AWUYmnpfqoogCMaP6ThO9P8oRRATDIXuSLFhQto6IbmuO6ubMSakGDFiLNBnFwUxIJyvYGZAWWT9BbsASR4eCx4qZTYPoaXtVDq/cqAtkym6Cce3ltiO9P90bKz45eDk3s7Evof+6/Lq07ckh0tAkwsargE5B1RgaF5gR2pAswMatqC8i57Em344kr7oqFlz8YE1a7tO5luyNTn0TFvwg/92mAdeTchwAJNLwA77kK4WOLkNRazPOV7CE5aAlcAEJzAQ1qoP3nOStf+Aq7kKVu99oW146M+/1Heo+s6VN7/niUy+1Rp2x7M3GAJqtRolPEfr5dYnu7lUFbXAB4kFsRMZ4knuPwgI0XomjciKGH4gKJfK5DgOEq6j5NC4agkCge8H5DCp53mzXp4JAhGIKrFj1EQF4kN3HUWLloFKqcygAE4uJ0QT6ndyCiAA44TkJrzQ48gOiuUxSiaT6rCJ5soYHOZ+QqVSoWQyOWc3XOyyixEjxvz5CGgo4xCWGUepSiPDJ5yRgeHMQGASnuf4Tc25YlMiETgiRLZIKgwkE6Kc0mKl6GSfv/9f+D+97zPJvv6kp0USmog4C6PwwuzeNVKMtXUEx9K3/G7f1e98fOOmdftWdqwt4cgTK82P/uhlev7JXKAWjjI0n4B2XTFCm5sSRh2xFDCRgQoHULYOrBsQiSFxVH3WCgL76KMpLgRgZgwv31wYuOZ3r1tx/dv3plIp5QZSoskuy6k6BjYsCPg6BSPRxE1YtVYwXq092rdM4baTSaq08XOe+frg9WU0GttTn0N67W/s6+apptrHeHuj8wnD7xsmvuY4FxUrpBgxYiwO6vnf4ISj7WRCM+4q32Tah3MKcmtj5JX7M9WDJ/IYevUdGD1+E5FTg1gPueWPNbe31OzYyV8EjBhSsqowlkMpogRLNszsTQrhDLqzV/xBec1FB1ataD/S1rKs7JVPutXvf/5z9tWf5Fx1QDAgstDL3lx0O7KuCMSysCNeILBsSBxrAicIw8lZhIUoGSAZOObSa0fw2GM5C0VT3/585Ud/9fcD+eabl73p+pOpRJgnD8V+x46NEQyD2VVhI3CTQskEyCSgUfg6ECoFHR1gqZWMJQ7Via0Zy57PTW3CXgIMgT9wwtHCiWbSIKHklZzc8jFtXREQh+5KlErkl4aJNTDjWRCIILkO33XdibRJk2y/HTzuIPCJxTIx2FcIEk01N9usYhIAKbjmU1A4nAyGT7Q7agJLAGUyVZPuGEZueVBfczW5FEedZ4JCj6vF/pxfLDoOYFiqWR/uGGfaB0z7uio7XqicOc7UECNGjEVSQuPuucnz7PXkpeNeJ4YhBxmvTLYwZCrHH721dmjvLebVvddSuXedFI5niDWMsEu0/EsCi1FhJxh0Vcth0T3WKGJPQWzC2ZQiUF6TlYJ0VZJrN+9vaV025iY8HfvJ3//Lpu7H3xmIgWUAKvDzGTgrmmHJMlkOiAIOyDKVqywvn4LCMla21WR5DiDrWAqYFdAVaZeb2pVH+kgcQXPx4Jb9rzzyFnRe+P2VncvLjuei8uwPrk7s+c4X4OWOaCrhk5sekMTKr4297QPfzKSMGsd5Tb8FP/7aL7v9r/x71sCzJK4Rp8gkXt9b7vilZuMKv/jVv3RGBzeGQRuJgiVxWYOMb3J77CXXfxZb3vm0Uzrl8bf/9B9JrEeigNE0+/D9zVf/aeWK99+fSmXUcV67fgoA6Hsff1RHRnIggvgaOAZOZet7/lVpyzt+mrODXN79zdvS+x7fwQQkosg3tlwmA1etoJZe9R29+r1/yF1bR7wEg5ihYJAoKvt+eLH7/Hc+JSND7V4VvuMwBNZVVSTVpgDyxady7ZK3fJauvH0nNzWJw8nXhL7HhBQjRoz5iyBIg0smipqKQrBBDIWNAhFCH5AUep3KvhfbcOThP+T9+6+mUwe2uNVeV0pVeGrgs4URA4yNuRItRCUIWAVEBAsCiCEqYCKQKJTT6PU2PZxeu+aZtrZl/S3ZfOAO9rrenm981h8KE7Cy1sCahN2wrkoaOKjnV7WEgAH+0cseDfcTKgI5CFffdFWRL2oDWw4AYaMs5VWGvD4FqSCJYep45Sd/PNJ5+avFlne80mxIRQoX6+DxlUr9rYaTJXG9SpALjp08fuyRVavXDmeyaa270UgtgqHeLeg/uJWDwLAaKzY8x8EXH33TskM/+Admhq1Za1hcFeNzFALuKl3gHH/xfdWXnvlM4Z/9zv9uKtqiVzh4lRJbBNWkNY7Pw/2fONJ+/T+tW9NVSGbT6kS5LwhA7diejHdo72VifVZVwDFCAfDCaHbs4ufv/9Xg8e9+Ih0MdaqQhK5IYaVkiVkNCVmLmuvxkd/Rgz/57dKb3/nxylv/zR9l0xkxLmPsO3/y2+ln/uFPKLyKEBJWNT6RdckK1DhCEGYRuD86+JnC7m/vkF/51ObmlasrnuNiuvIeMSHFiBFjDhIpLMkA0nBuo05SyrAU1iQiAqRWhT2xN2df/tF76Zldv5c4uecyMzxAfq0GTXsAGAFptB4pzLQQrhithx6bCW0hCrBAovDhEhutOvkfB9mm0UxzpkKJhFb27np/Ys+RRBjFrFByQVSD29lWVSChDnFQliol2eUqfB0dSAU1ASiAUyJUju7NJIqrfQU8AAhAkuwPFBoKPlVFeuDApp6eZzenBt/cnU5ly6hKzQyOJOAaT9hkiQnDRf/Ggf6hz7V3dI5mkbYT7jMD23vw3amh/pxYCLHvAo5VIlzwwpd/xOJYqwoyjlgbMJFJMBsJAp/VATvqBIlX/vF/dKfXfye9atWzwcln3+VYS5YAo+yhOJwZOfDKBaXWlmczac8HO+E6IyXUnvnKp5ziYApCQmBVEjqY3/Rt7Tu0JvPMzi8hAbIIQ7TD8GxSQ6MtKsYKAQ6LURGQWMr88Mt/8HKx5dEVN9/2RPLw99+T/eEXP2OTDJCRsC3Ch3MX/mAkkX2WhCoX9b38Xx1TTih8wwK0niquKn/nT75w8pf/82+u7FxTMUbGg2FiQooRI8b8+Kgxu0CDx0UpohBV2MLRROXV/W04/v17aNfjN7pjBzu06MNXGxboG6uCKQmRWqixjAexNTA7EASAEJQojNYjA0UNAoJnXQgsNK9QNpVMdtlAyktYxyhqh/beZEcGwVERP09cBCQQClz4CACFIQbK6lsWQ299S9F5em/aHjlBQoTkq0OwOuI6SvDZh6MGCgtrDFgDqDLSpVHKH33l3+qJvT+ttLZVWP0mDSoGvqOk1gGRoimXC0QjC9tA2ARoKj2kR2vEZF0LhWrFITLKEDqRXrVv/6q3f6KnY8OpLFN1yyv/b8favoPXOfBNAAKUHQBo3n3f/3v2lrtuuW7kvv8JsXDYQ5haSJHd8/QVlcuueFGUfG4IPDDP7Ho/xmpERgxZAyVBcdnGb246/OWvqa0alABSSwzCgY4LH3pp1S/9rc3lBpfbsdqyA9/+8MYTL7+HyEDZwvg1vuCpv/7hvq7LN3b95NHbVQKiYngVDREOtm751lPr3ruzvb39YFMuNXhw/wuPX/jkp3cRGCI1ELmU3PPIr53c/O67ctmWl3P5jFDssosRI8aCXHZaIwSkYggMA+HIzSYWwWiBpGfPitrh3b+IfS/cnnzhqXdq4XjCQENXHhHABkYAtQGIwzU3LD4AA1EK1+hQuOZflVBfl8PEsCwgAdwyKahtKNGUGTXGqNbK4H2v3MDRSJsUsBwACgSHX0k5bdcVNBGkUGEfiSDFgVOlzqxr33NVhU+MCU6MQft70tR7ioJiEKo9aNhWpYggHbAKkkdGLjnQN7Qhu7nclz5ul1trwQgI5CokILXVlIiYeuLYRmLyCgObxJZNGN6tcCAEYlS9dOXJDb/yiczazc+9adWKI8lcusxXXv1O/dwHTmlhKM0UEg4QYHnh8KY91VLab7/kGad3zxWwVQITWBntw0989ETh9geXLa+V0okUFILg8Iu5xNBAmiBQn2DhI0gmrfqayh0eTGtiIhPEyczyYz/ddPufrVm1+sUVK1adcJJuEGz9hafk/23v5UIhSVYg7KlXHvP8R/7+Pem9D/2GpCL3LBHUEo4mLv/x8uXL923YfOH+lqZs1d90UY//zF9ap1oyRAQWn4RJ9cj+zSMXXHIw05Qss4kVUowYMRaCwinHHxlISWUky81r+ggAamOJ6okTzVp46Rbet+d9iVf2vs0MHm8ORvuJM0BATujKU4G1PkBOlJggCAMVhKHMULVgDXPFheHEGi5kBUOFoFUfknRR8ZJVBJwIKxUZaN/+PPUMNBMpYC3gOMBoDZRx4b3oK11eSQQVtwoA1teAoOCaCpHjc1sTY2U2IdUVo/DU1X2ngCdeSJnRKgK1UEQl1MtVSMpB2j/ZagonW4vFspuMhJCWLEgtkRHilDpaN9D1jAsNue247JOqUSaFNarkF+no+k2Pc8eKg+u7Vh9cs2r1qJP0IIGP2ob3/nfz+F/eTcoqSmCypASkD7+w8eiVt39w/f3//QViS6SsqhbtpaNd+w8f3FBetXIgmfQCUkZt956bUmM+lIRArhoIjnasfyTtv/gBN8GQUjVaKmT05Jquv+1Y1n5w/fp1Rzo7VlTZIfi+rfjrf+V/JR7/7MfDAlRGRSxdfOKfPv2DX/+LrUaBcrmYUyUmBEil08Nda9d2d7S2VFKpFERgq60b97jdz10mAoiyAgpv4Jm3lUfe9UhtOcquiRVSjBgxFoDayYPt1e98749M5dWLbK7luNQ6q+z1NNFYYaV7/NQqf+hoC4YH2ULBxMCYgtmGU0PRNLtFAFYOFZJYgMP1K0wUrYWRhtxqEylORQEq12CJhA2qNccVkKtSHE0YkJFiAKMRGTBDi4LADBL/6KUk3XSFDwQuyFgWx1cKAApcKFupmjIrgFogZl2Hxdp3VHXXU83mpaNhTlNRGHKAksB1x9KZkz2rK5WKK7m+NabkE4QUbCAiICRGRQNSpYZgD4RuNaGAyGiAMJiBbUAwrh71tn6vvW3Z8fb2tmIilQxP1HVhLr7iq/oY/SnUEjFFGcaNGigqqy86GSQ7BhO1vlaLgAwRfJA2vfLTraNvuvK55nxr4DgEfelr/ytMW06wasEAjrdce981L37pzzFaAzJGVS2xAozlJ1ac2t+cz0neHzkaMMRRMoGWXno7Iq0qqmAyakaG3aBSzqzu6tqbb20ZIxVIf1+mef/ea+nR3TfJysRIZe8Lt2NkeCUf2btFLYeEyqoQApEzPFobTZDvExKexoQUI0aMecMfqcLU9l2UfvwnVwVcvKrU3CKJUWJXAlR1CF7FgyYBwwyxBkwWEmXJZnFBqIXqqAJoMswKbssEkwoDIoKawnPCPHPWBxw3dOOFS2NNPZ6CLSy77ApRDcFYGaY6kiaKcqhFioo4AKwCLx4j9A3l7DveXDStGQPAVzIyYQWFFQplYymAiAHM9VcNy8HePIrhIl+NSFJGBEAA37eOGWvfZ8tWjTCUAjAZrQ3WUg5YqcHEhkopUkmjNXKMUcAC1lXiAE46OZjLNo26rhtVmVAYZQS5pKVSACJSiFBIG4raWG1Zs2sCf+ON3+Wn7vsNl0gDsnAEtKzwzO/0Fwa/3tHRWeZTR53EnucvU1iQGHXZpwEvUzyxfP1J94kxJwDBjFkIkYICXPzcfX/h7E2pfrMaqi4TpknyNAklaKj6AkAdEKDNoyebWlquGms+/t1/zp+/90/cck87g2DLPgzCSEwOdTCilLrqoEYCo9rnJUnBosGM91tMSDFixJgWuvGyXnn5LZ8byx+81Ovbn0iWB9hAYYXAhkHqg0t1Ax7W8TEqEJIwYC6aIzIIIGUDQg2OOqCiRcAKQwSp2sgYOSARQC0YDNIAvmOAVJi1wTgasDFwsinYRNOoV7Ipi4ks26xRHSUKQN1F0F/vytgVOdBVGxxd3hw4y5qtDwGDQKSGLPlKlhmeL8kgKdde7Dvf3u2KhgZVwOCMj+FUe9BOFlKP8OAwK4GqhRAHwhDhqBEUlo5QKITgqwmj2VRDwgxIwUTWTXg1Y8w4jYkGUdA2h4mFSJVYwTY6NxitvPfXfyv59Jd+A+KEapNZVxw6vuXYwNH2SqWrH889+K/TYsk3rGQsjBCOLH/rZ1aVehxVggNLQiacMRMDYqNaqhKYVJWhoZaD4TJECY61xAxVBCBlyKnBNS33/rtnvaefuhhJo1RRgucoOAzdt6QgATGpkjIMUzhQAGDaqhWBQGahnJiQYsSIMS0yrW129Jbb/uZYouml5l3372zueXk9VYowHICCqBxCVB5CEBKPsguoH+2B4SghAIcpRZURsMBoWOiOrUbZuBkBByAhwBiQWIANWCyURMLkpSZ0AqZz5Qo6q64wrGPhKMGoBQhhoQtlWA7dhDhZgP32MwlHKeEnGWZjp+Itm2qay5fF813UnIrxqknU2Hcu3ejTQ915qowAAjikGJKsZWYxxrWEAFwSAocp6BgGnDZOeHYNtYSIQ6UGAKMK3/VhQOQL4MGB1cAFGkpWKIHJwdjIGDIVG/ox2QDiA8wgMlYYmmxfV/Zb1pziE4c7oAyBD6MC75nHbihe+Ob92X/8xu9rTWFUCKwQVXRfeO2LFyZqQ7bqQ9WAKAiDAFmglqBGo9R6NRAnYMSCNLySSgqBT2BHjRBd9eR9f+aWBhIEhpbDhQDwA+rzOgoDuXVPB/mWn0EdbOx54nebSoMpQRjmLwjIDiU9SFiOMSakGDFiLFAiMdLL1vir3v1rPx5c33XDq4/+8L0rn370jzO9+zLJcgViaiGpGIErHnwAREG4/lGDMGw8ZVCzDtT1UE1mRCQ03E3FMTbVIkijarEazlmAQgMfcJj52rh+WgdH1qE4mhRbBRLNJXdzyxPOz3QNI6xEq4gK5mkVgIGjCLNOg8K/oZBSADNwlOTpYwn61avgXLyurJ64WmNfPTBVxMeGtMpTBQJZUMpDNbm6hPbWw57nBSAXGkwUEbKowbIt24C8sEifRX09FTGjdtxZkYCFU2UCLBxiWK2CyfPrZF0PiBAokplsIH49i3gAYkB9gqo1pExugjV4+29vt3/7e992JKy8a1WwfP9zv9d34MV/6jxyoA3qgkgg1qK38+JD1Jzvc1PBoFOzJGQBcaMyHoK9nW97otSWfcQIg0khSlD4TOwFUJ9DXyuDiRKtg/t/aVXvqxfUBwqgAD4l8OTVH/rP/Rdc8GJzftnxbDozyq7j06ee/F34oSIUtYAkoPCZlWdNbRcTUowYMabnI2Nh1EGmqUXcN13Xk122/vN9a7peqj309b9J9RzZ2Dx4gkEMIwLRKgw5ELJgMfCTaQgsCok2KaXbxoptq54rN7c84bW1HPIHB9e5Q6PXt48cujwzcDSXHh1FQAGYE2AJYBlwRABykCoNE4b7t5j+Y62y6ZJek2sNuOnCZ0pJ53bX98PS4obBgQP7oWvLCtcP55ZIVJXxD8/mpH8EzIxAFY5V0FNHPLlgVZXg+CyB0SqLAqi6mZohm1AwAnEx0tKxp7ZsY18q6fmgoMJlGxZrV4EyYWVf98V7BWw1oMlJUz065kAdoGJhIQAkVJS2lqj373j1VyWUiiUnVYkKAVI4n6NkAQqr1yoxzHU3fV/uTcKWq9AoeKS1r7vr1BO73umUCBZVcBTW3Zvf8NXW1mVHmy66YL+WGEoazvBE0Yy9m7Z8vXLZVT9qbWs/4bKxqpbUkIVYo8TKwkRs1bfg9Le+eJU99PIFRsPzEGK8uuGaRwc3b3luw6YNuzs7lg8lUxlryGqmpzclqjAmLJuhXIpC8zG+uDompBgxYswbDBeiAsOEZCqlvG51KZn6Zz/pyXfc1v/C428rH3j+d7zC8XXJ/mqGTBnWT8CmkyKwCHKtI/3Zrhekrfnx4soNB+zyZYcTre096ebOAZdIy2MDf1vo7e7oeeap9646uuffNJ/Yl+NKBZbDsnhEhBoApzaKlYVjN/cP9reXyoV9qeyyms13PuWsWV/F/lcTDEW4ANSCyfO5Pe+LARyFEajojZvLdP8TKSMMq2E9oGBVPjDCvrDCsGMVYkjVeN1DCVUGlFBINkvv5kvvb21p7kulUtYODF0tUQJXIQKphQ6UEZSrTeoH45UTOMpqwT3H2k1JILDRPJpCQ4Uxpd+KScGWQBQev0Y+PBhowF5dT3ktrbb/0vc8mn/yKzc46iFQH85omd70T1/4tIXCEcCaMPPFK5e+5fnLljX35XK5oLyis5o+2ZOwyqH6UkbLwNE2rHhP95o1a/pTriNSL60+MuyY4/vaEQpPgKmmh358jZCASUPaIIG05B5vX77swNo1KwbyzR2WWRCcPOwKAYYYvg1duiJjIGUQqUYTcXGUXYwYMRZISszR6J7gOQk4HWv9VW/t3FPatOXQ4L49jw4+/8w2C2EuDK1kS80BY9i0Nh8eS+UDu7zjcCK/4uSyfMepZEfLcCqd9ZMJ1zJ5ajToHS5fdih36ZtfOPmtvztCI6c+2dbTw2hl8KBFsMzAOVWFLHfg9fZk/GP7t1D/qd0231rjX3jbLnvwlW96h47eTn0jsG0uzEANwSvHUroi77MFlMgSweDC1VX90M1+be/xFIhFE4nAfUuXNWAIxASq1lGyUhxJSk8/eDAAWhLoXXvlw8nNWx5vzbeNJNIZ8dvaH6Oq/wEAUCEIuxCpofMn37xl7PKLftZi1TcOAFWUdv7pf0meKpGCoyq4FiIEMTSuEiaX/gmgCGrBeAVeF4C1CnVQE4ZaBqwxSL33XbfLj75ykqUGMgCrDysaFjLUsEz8wc4rBnKt7YebmprKadfVU2uv+2n6yP03aLQoWVWx/OhLv90L3J3wHEk0NasJZSVKn/9P30z83bduIWJI0QdnwjRPqoiS7PogdUGDw9enkslPJ5ysMAOqjNp3H/yPiUoAQVhB1oCg8OAWRq8PVD9DBhorpBgxYizMZTdeLTRc20IEsOsgyUa9NRtLpnX5y6MXXXXQLY54w+VqSiiAyyyqSi2pXC2T9iqpZJPvJRPW8Vz13KQ6xoy7jdqzOb+ay4/kLrziAJ5/WbT1JNOgACpwTyn8dhemzyKbLSP/s6f+cPTStzyaXLNhpKl5hS3/wlv/ffnpZ25NnHrIpYEK0JYC/WSfa67uSlIm6wsErIQAatGZt4nOVl+hRkGWiCBWoA5ZRwBlNfTl5zI0FLrMBtuWydD6i75nOjtPZHNNVcd44Ddf+XX76epnGe54IIcHwqU/+Pv/OuKkfqHv9g+9ry0xnLJPPPHPE3/2px+nNCEo+SD1o8g8A9IAtXKpFcDr6g65cAyX7bg2tRQASjBj1TwLyAjDGIV509t6NdEOHToJEMDCUWonH8JhyfUTV1/5f3PNbX3NTfmKOgz60L+7Bd/9uyITQKiBlNH+6su5zF//n+f7f/OOrau23nTC9u5PVe//4l9n7vuHWzTKfmHYgxQDBK3L4Q71QxFACCAt4uKnH7ph+MEV9516zzvfn1i3ud//zjf/m/eZP/wYjAOIwo2CWECKakvT44qocF9MSDFixFgIqF6Fj6Lhc5Sd24Q1gdDqNdtMJlMKZFkp59OwUgBVg7CiAcOwq65HYAZIHcD4gCVoUIWoTxgaMmbwSJs9/MKvuf0HHYwkQRiFEIVl//otRBVUHEWu/3D+8J6fvrXlwssO+sm1Jb70bX3ya4PvK5588du5Y33QAR+OMvhzT2bsh64d4WxGoGS53mqqn4oaC0RZtklorMz2vidztK8vJIBsM3rXXPPw2GXX/PiS5Wt6s9mkNSTQzW/tLV94vZ95+WeuGCcMhRYLMgb579x3A77zpUHAgYlC4HnMgRgTLQjWMPKQDYw4qK9bqtc3ojBAwYaVXAkKHxAPRAKflHV8sskg4RKK737fl8wX/+YDIIVPCiBMWGs0QCGXxuD6C1+6qDU3kEonhMigpWtjafBd//yJ3Pe/cm2Y/0lAcJF5cldH05MP9QRq4JR9UFqhSEBJQVahZPHqxmt6Trz5qnt+8St/8UdSj16EC+UAzd/98g1t3/5ij60IvJQDZQUSAMphsAkhjCb0BkdurJJ+2sblJ2LEiHEaDrvQDr5mMjr6LCpG53kMDx4oRVp3QykBLBQaNgWC4hDZoWNNcqIvG5w4tYUAjwrHLpShsavt/n03efteXJk4cgKCMpTCdUdhwYsgSrgKtBzrxdCTT/2fU6vXv0z5X30sl8vb4s3v+p5zcM/XRr7y5dubRnugAOzRQejvP5zzb1hbpTevCqizrRKFOruw7CurMQLIgaMpfqrPCZ4+QAQHQi5ctXj+6ht/2H3Ntp0bVq/Zn89nq66bCpPJEkD/95OZsTt+vZY6+mqYrBwmrN0URd4J1UCi2HvZ9T3a0nTgwke/cwOIoBIWqmPUoBQGKITBAYCJzLADMijXQAgLAlqqgNWAFEZpokIsGUb5l//FbyX/+lMfCMOqKQqqL0PgoG/DpX3Z5tyJfGvLmJNIAhAwA87v/cFbi0N9R5t2/dNKiAKwUI6yZcBCicBFAlFtvELu8UsuGNn77ls/2tK1/tnh7lfubHroByuFLOr5ChmCoO6FKylG03kcvv6f3XPpt75yZxiqHy0wBnxVS6RxUEOMGDHOqIqaqqJoEOawg8DvO+ZUnnj8Rv9nT/1v99D+q8ypU65PiiSK4FO9cKoKCUqQUQGaODSOdRehMhQKHSVoNkB+z8t57th132jXBVentlxxMp1pkuHf/PBvJCmoFb/89Q9kRg6DxhQmU4bsejXB39qbsFnNBF15sHpgWKBqIQeHgTQgJYAzIXGOmVaceNtbnzhw3U07V158xY9WrFo5mGlKy3iZcSPw2tf4/X/8Fy3+fV/8J+9737oqVe4FsQurCgPFaLId3W//xU8cuHrboxt+9oMbGc4NogGMhouJFQ6ISOqBBQYcpgjScB1XWGo9nEcyaibmmYSI6vnylNG+bkNp5Mobq5lnH08QAJX62iXB8Wuu+6N8S3tvLpP2TRRaLgpkM64M7fjkulcfevDa1P33fWvtS8/nRSwYobJRDUDkQSA40rFFDr377f9lZONVz61c0b5vzbqu49Xf/9TaY2v/72+t+toDn20q9UPYRPkIPaj42Hf5W3teveWXP9qSMX30rfvvZFEI+wAZdOx5+YaDvroBKgRkpp1HoskTazFixIhx+hCo+CRHX20qf/0rn9Xv7vpAanAfaMwCWg5H9SMarVUJx9BELlQtoCZMAA6BEYaPAMxOGE8NxdiK1fDf9Z5HK+951/tbt771hOclMdDX62a+97V/fXLn1z6z/uTLrCNj41mtDTlhzSRViLFgS2FctjBsk6JkOjC4fI2MXbPlU3uvfMeu9Rs3Pb22a83J5lxLkHBM9FsN1YQIgpqif7DXO3Git73Yc2it6d5/qV+qtBXaV/agbfmx5mymv72jrSff0jY2NDCY6+sbXDVWGWkyxliVqmnKtfVt2nBBd0dne8VzCFAHSoLA99Hbeyp54MCB9SMjI+0sytao5HNtfRs2bOjuXN5ScdxUlBEiwNgHbqu5u55y3eYayqNAIssYaOvEo//hrndtfdPlj61dt37UMwbKE6mMrC8YHi2Y/v6+zKne/k59/snrbbHWZjVwici6aW+wd9Wmo+lkZripKTPY1tbW39GxbCSfa7FCjLGxAe7t6W8e2P/SRXLk4MVBqdZKCDCwaesLnpcca+9oObJ8+fJ+69fMiRO9K4aHCp3CkKSXKK3o7Di6sWtjfzrXFBNSjBgxzh60XCS/+9mOsS8++I/NP/jSVbUeC0+GQ7cTh2680GnFkQoI1+iwWFgK8z4oebBq4RLG3UJEITmc2rAFzrZtjwbv/9B78xu3FBwHGB4tmOK+ZzoGHvz6hzsffeKjycEhZMf6QAFARsBCEDZAGoBJoJTtwGjCQ+HiS57Ye/07/iyzqmvPuq4V+ztaO4vZbNa6CQcINRUYiAhOACVY66NSLlOpWjPlctmp1WquVUXScW06nfSz2XzgeY6OVcrkF8tOIJZUw/q4rutKNp20TiqtRJEjjgBrLWq1GsbGxhy/poZMVRUOPHYk05QNXC8Jx4S+NH9smPyLNkkaw4A6sOQDRHjp3b/66Kn3/9bvXHb5pXs6WtsDsESx2y5UAjAzRICgZlEsjppKpeIMV4Y9Ix4EFqRCnucFnpcM0slU4GZSkna8qJCfQmBRLdeoXC5zaazo1fySY4WJDGs2ma5lsik/mcoIRDFUHHW0aqP5rwDpdC7IZFLiONM75mJCihEjxuKSkSqC3iNO9b6/2Zn8qz//gA6PwHCUhQEOSMNsBKFXLqw/NK5CKEA4kxC+O0oABQgQJm4N1IBYUdMMSmvXQW5+2w/z733Pb8ol15xwTFqrfoVKvUdTJ470rMZT37vZOXDk152hsQvdvuH2tB2iUS/rS2vnSK0tubtv46XfSySTBeeiC59Mtq/pXd7eUWhubqklk56yYxAtcQUiIgqzXzM4WkkTrqixYZFCEagFXGOghqOsERYYj6QL9xXmhXPGM4LX+wtkorgRDfsCAq1nNqgfhwwsAIKi/MBX3uf97h0PqloEaEICFVQpi6//wZ988LLLL/3uBRdceCqdTut4UcX6OShDafzMACuwKmHm8uicmBnGmOgc6kwhUbh6tA8AkHA7EQCOwDBAcF/jwtVw01ClRdtRXMI8RowYZ5OQMHi0Ldi/7yZ/DKjmVwBJA2NcVItlpEdLIGW4MgY7YuHkGDIcQPMMZ8hAcgIdJZgsUBuzCPI5VLPp0IUHg8zwSaSoiGT3yxj+7vDN5cH+Z723D/xW9Zrrvp1ubQ5Say8sJZat3l+66OLu/sGBr5ZO9bYlTu7v7K9UW/xEuqqZ1uEgt2wk39RUaGpKjWYyzdVkNuU3Z7JiHAVgxudqQHXiCUP0mKK6ThRFypEBCHAotKZaN/5AlHvPhmXbIyIgdcJ3RFkLVMOErHWjX/+7Xjp+XDqEBGmiBK56/xe/xlZRM81wtQwWi97Nm7WlpeVgS3PrmOc5GgZdYKJRNJHFPExeC6gBHDAgDkI1xSAJaaw+NKi3NyRMjso9hdkx1ChcmHGCpgnuDZuNAJYdmCjH32ypg2KFFCNGjDOgkI46/ve++jvFfcd/5Zgt/1hVuR4CbIYKV5vB4S2ZvqEVSVslt1wClUagcMeTlFoVmKyLoexqrbZnTvgtLQf81szj7mDp+o4XX7qh7YUXo/BjwejqTgyuWC/Zt17+cPqqG/7EXn3tD9NNOV9Y4JerbJM5qVRKjrVKqhaGGG7Cswk3KcyA4yXUcULCeI3JVISljxoyitej4KGAJYky10XWVwWWABP9PzLvr1FIU3dYZMwl2jcBFuG+FRYEd5wghRQyOkRYuVI4UlhBtArp+X93xyfKv/wv/vrCLRd1t7a0W4rYoT5/VFcr4PGKU1A4QEOUIIBxF2V9z/WcexQR7fhPo75QteGaKo22jlRh+LUAEqXFi/ogLmEeI0aMM0I8NI3/RVs7A3n7r/zZqUv6/y6phDIxJ2AFMAjGhrNeedgbGz3VcqSvcGG+NOQFg/3rQAmB+gxWgTXc37aykE4lBnTdphdNW2dfU1PTiHv8lS+mioWf2cPHEhgeAeAgc6wfmWP9XNu/5+bgqadvxsZ1fUOtnS+kW5oG0dLyjHvxpV9wtrz5hEkkVY0DDw7IEBQ1EHmR0US0ADiqXBsZTo6KajDCBad1BaN14qkbZQBE9aLqobHncXdfFDY/7j+LCErrBf2i9yjXG0HC6DtVkLqRWAoVE4NQvv8LH3VhxwsaMhSVlhwOXHHzTy5ubRtsSjVbnoL0iADLMk6YUB4vIT9ew0ktTJ1coijJca9hXQFpVJ6e6i44ik7NQusuyvF7gyMCjFaDvaYPTp+QugDcCuDy6O9zjY8A2D3ps+0APghgK4B8w+e7ANx0ltv3QNRfk/EggNum2eZjALY1/L8bwOej9p8tbAVw9wL6fK6/P1OYqR2fjPp9rue5lHDTGb7WH254nj8PYOdcNpyOjIgIjuMA7av9Nbn2k3Xiqv/eWksSWPJtwM212s/K1YrrV2su2YC16rtGBZLOVNe5HHieF3hu1rquWDNS8NyqXU7KHGYLMHDIR4AwPDxVGIN97GnwY093uPnczVBGcPGG28vXHn17Nb389vYLLhpKsAPL4bif4EUt5gkXE4USZXzuZnxMT2j4aMKm0qT/j2PcZ/W6345/TxO/o/F3RIpEQBTVdhInipSL6ioRYfTDv/3E8f7+IkHAljJ+W8sjbcs6D7Xks0XP8xqOxa85tmlUaVMcf3zOa/L2rzlnM+mzusvRNPyGXtcXhJnJaD6EtG0KQ7kUkJ/is3VLqJ35eX5+N4A7pzG0uwDcERHU2Wj3tnm0/cHIqG2d4ruHI4N6pklpO4B7p/lu5xRkNNt5/jxgqj7bFl3LmwAUFrpjIoLrujDGaCMZRcpKI2KyqupDtBJGoYXphNhA7VjJjPWebMru2X2dGT5+mRCqwRPP7dCDezvMCwfhjowAxgDCcCAQUgQS+tKEDNzCCBQWI8WV6GlK/H26Uk3m/Ao5nqtm1pmMc4+6ohLicEFulBqIyIXzG7/9f8p9/ffQ0KlspeSniUjVWFmXWzbSnM/7kzISnVdwTvNBj7H4BmKmQcGzESk9uMTaXYgM2MNTkFL+LJDSbGR0R3xrvQ63ztBnWyN1f1rKjIhgjJlWWTW+uxFJqVqoCLT/hWWV+776teB7377BOzEMMJAdHgSJRm4hBtkahAxYndCVFk3C1JrTqDY3oe/iKwZOvvnNH+cLNz/f1NY2aonPI39og1uQBBACsQsggOMwli1rreTyTVUoDzApIArHM+p56fP6pnRO46aNcfYUVeP3D0QGducSa/u5IqWZ3G4xGU1/H832XG+L1Po9CyWjGSEa5nhDQ2QxKMwm7QdUHimaxNDAxU7vEGhkGK4yHHUQsICEoCwQDnO31ZhAzQmUshkMdXZJefPqx/o2Xvyt4QsueW59S/ZQds3mE635pnJTIqXCYVqiJS+SaLyIOqAU9lU0r0MsSCaSSCaSGoZjI6y0ywSc5zFqsxHS3fGzuyRxL4Abl6CxPduktDXaZz4mo3nhw3MY/AChm34nTsN197qBfz17ONNr52NogpwCx+hY25pC5h2//K9Krcv+Y++pkzDDxY2JvsHOQABmA5CVTHnMKXtNCnJQam86ceqaK/90uG11b279hc+25HP969qaR1oz+WoikdBEIqFRhDbOA48doABT47qfMNgCyuMBDmF/hjQeLRGaCN1+AxLSdiyNwIUYM1+f2xbTYJwlUroCpz8XFpPRwtAFYMc8lNSHAdy1WEQ0nfENw4c1DL12DFpXry1Vmlv/oXDJlQ9hdChTGhvKDQwX21AZzrRKtVaolNtk1F8mBMHarleodXVfPpsdWZ3LFJvS+WoyY4KEl4PnioIccENyUpwnCilc9xOqwShXBCwJwnDvcB0UgcbXFik3hl+88Qjpxlm23YWzG/k1Fbp/zo3Ltsgo34GzF8m2GKRUn59YKJEuFhl1z8M4v1HwsXn+/k6EEYqnNeh53bzRJONb/y4sy0BQNwUvn7SrsrlRqbUXa0J9EuhBVR9+UOWmsNSROmxU1YIdo5lUOnDYRSJplMiElVej0OQQEoVwL/1Z/zCcOsruoIQww7aBUY7CxBsXrNazr+O8VkezEdJM6ugjWKBvOcaio26cz0Yk22KRUmObCws838VQRt2LMfo/z9TR9hn6outMqqS5KgMgLLHgGsB1U0AqJZk6oyx4lzzx73lgscfbTPUVPJPDsacOJ6fz/AblBRBSd0xGSw55hBF425dYuwozEOVMxHI2yOjnER+b5TrdM4NKysfdd868INuja3cv5jeNshVTr4Nc7AHxtrNBSNPhwfgeWbK4F0svEGWxSCkmozOnju5pUIuFGVTSucIQQi/WQl4PLPCYD5zGMad6zfZcbo/u74ejwWV9u4ej53p7w/PU+LzPNAi9ewHXrd6GueJ9mL8beFo4s7gzuqYxMDGWLu6MrtsdS+ha1Ulpoe67mIzOrDr6ZMPf92DqebU7sQhzSQvEXQCaF7jtc6fhddiNcHpiMQaKW2f5TXfD37uift7WMJgrTLPNvQ3PwXmPOJfdGxO3RqR0WqvtlwgpxWR05tVRY59/ElO76Ooq6VzMud1zjvrtQSxO4Fb3HH6za9Kx6plxds1C1NsiUtqNpTWHHBPSGxi3RTfo1nlssxXAISytYIeZSClWRudWHWGJqKQHcObnqxZL+SwV2/Aszlxg062Y2e3XhYnlHDMR8pye1ZiQzg8UEK7dmc1nPJXb4eHo4du5hM5lMintnkIdxWR09tXRUlBJj2Dh7rm54vA8+m7bIhwvfwZJvNBASvcuMa9I4/m/oRTSthlGOgX8/OAOhD7xu+d5M9yLMOnsUglxbiQlnEMyys9TdZ6rvjqdUe981NFSUEn3zKKevjHL4KqrYRD24Gm2ZTsWL3J111lQfHdHz/pti7jvB2fpx7prcVEy0p8vhDSdHLwJ535x7tnGPdENOF/Xxg5MBDssJdU3GWdTGW3F/CKKzgV2ncbDvhB1tBRU0kyYbb7k3qiNi2EXdiIsx3G6uPss2YUbEbrYPjaP67MdU+c11Em246xc79hld35iV2TMH5jnCH979PulKOvPNhn9PGAh6mgpqKTp8BFMlMKZSgXcGn2/Y5Ha1r1IxDaXtmybYjAx1ee7Z9jfHdEztAOh63OuCqh7CvL8yKR+OCuICen8RXdELPdifovftiL0N9+GpRWV0xWT0aL35/YZDPtcjORSU0n1e36qIoz56FnongPZLkU8PMfP61ly6m7J3ZOI77bou7vneI0Lk0i30DDoPevgn6MHdOsb8JzqN+COBRr/W5fQuXRhehdkTEaLp466MfcAl7pKwjQqKX+G7oOZFpg+Gx178udDUXu6MPNC2qFzYDPm0k83TXp1TyLh+mtnwz6nGsDtjn734CLbzun6c0ek4qb7fvvPu0IaPo2b4nzFXQiDHe6dx3nmsXRrK8U4c+povqrmbKukruh9B6Z2O9Xv26m+fxgzz/vcOM/B261YvApDsxHErkl90DXp78nPaD2ybqpCiruj17ZFVKbzHfRujfpv3c87Ie2e5WbvPsuGYbabD4t4w3djbqvCG7FUayvFOLfqaLJK2jGNSjpTc0mPTPOc3NpAlIVpznExnq87Jj2/H4xIfjqiPB27NBkfnmQrPhY934VJ53lHREjzCWJYqCfmcPQ+V+W1LbpW88qU4cwiId9o2HYW1UAXzn49qbpcf2Ceo6PtWJq1lc40duH8T5B8JtXRuVJJM+GDkTE+0/dpd8PgtSsyrrvOwrnmJ12/uyLlN1U/P9gwWPgGzsyccFfUpo9hIp3SXAb1Ny5k8P1GnEOaqQPedxbbcesso78zOZqZKXPzTGQ91wwKMX4+1NFklYRpVFL+LBHtA9FzNRMpbI3u5aleC7m385hI0HrHWbIb+QYlsit63TnNALce2HDvGWjLnQizvdRrruXneJyuiCR3znvgoKrnw2s6bJvm98/OsE3XWWhvXlUPzdCG/CKd52yv7ao6pPPDkKreeQbaMttr2wzHRPya06trhj7cvgj39HT30sfOwH2wLXp9rOF5HprlPOZ6r8/nnJ/VxcOQqm6d5ZiHVPXh6Lx1Up/cO802W6e5Dg9Hr/n0/8NRG+r264EGm1lv052z7OOBhdraN2rY964ZRkP3ngV35MdmcNftPotusZ3R8eZTdyiPpVfCIsa5U0eTVdKOaUbSZ2IuqZ4FYDcm0l/NdIyWOXoB5oKtmJiPvWeK4+aj855OPda/2znJlfq+GVxrddf5XXhtMMCuaD/bEQZs7JrCpuw4TdddvW3bJrn/d01yH94aXZdd0xzn1ui1EwuZr3+DKqSts4xU7j2Dbd0+y7HvXMTzPBcjvVgh/fypo7OpkhoVUr5hlD20QEXSNcW+Z2vDxxq23zbL8zSV4rl1mn6fqZ/qfXtoUhsmf//sLOrqgXkqpDsb+nZoDsfoavhNfgq7W99HfiHX/3yYQ9q2gG12zzKXtB1nJqvw3bP4WAs4NyHWBYSZHXYiRqyOTl8lna25pELDKDuPiTRKc3ntxMS6pPmohEOR0tgFYP0UdiTfMNd6xxQqoQsT6Y3m0+8fjvZ9xwx98ZFZVMdNmDqL+dbINk3l+WiOzvGOSGHO5sHpjo6xddL+6vNLeZxGJpjzwWW30ECEu2Yhs1uj7+9ZBHfD9lncdHXcg3MbxXYHwoCKexHjjYS6O2i652AxcS4j7nZhflFb2xdga7qjc9g5jTu7kYx2TvH9A7MQy3TuwR1zOL+dmD2xbFfk7tsW7TePcL1S9zTXZiHXayfCKLrtkT27axaSfsMQ0p3Ra6E3785Zbsp8g+/1wagjH8FrQz6n2mZr9LoxuvBzGRl2Y2mkNNmJ+c8rxYjV0WSVtGOa5/VcVZVdDNw2i6emMdpu5zyV02zXrzAHEqsPsG9sILJ8w3vjda8rnQLOTJqwukq6ExMFQU97kf1MhHSusyBvmyPpzKXTts5hX7fizKXSqd8US+VB3Y2FJWd9o2Erln4Ax27MXExuJnXUhcXLNDAXnA2VdLaRjwjjzsjQT2XcG8loNhUz3TX+BmZ2x22bwibvahggPIfX56V7uGH/Z8KmfT46564Fnve8CCm/xI3Vg3PstPlUKD1TZLSUqrY2jqJuigzydvx8Io/FS6+y1NTRufRqnM8qaTK53hn9fU9EtIUpBjUPREb5HiysEu1cCLw+MNmNmT04Z6tv6kmddzcMip7DaZabd2YxpEsZc3V/1QlhvtkLFsvoL7Ws2pP7ZiFF/2IsDXQtwcHEmVRJW+fxDH9wgcfYFm17a3QuO6Nz6Z5mMLCj4TnaeYaf1XuWwPW9ExNZG3ZEfVNXiHcDuBynsYD4fF2HtAPzm9ysk1JjZ55pTDeiWoq4BxN58Gbrm27EiNXR2VdJBczfrV6Y5/26DRNurpmIaFv0rHRFg825zBnV2/3IeXqvbcdE4Nau6Jy7G/r5iqhPtkcDh9sWYivOx9RBHzmN0dc9CEM5d5whw1qIbuT1mHvNmaWCBzF7uGYBMSHF6mjuKmkx0YIw7+BcX+uje3rrpHbNhN2RbVg/yeA2GuWHMREQ9JHIENfJ6O6IjLdO0R/1wIXd59E9lsdE+qB6ZO7k8hiNuAMT8/bPLmTwf74QUqOhv2cR9nVXtK8rFqC2prqJ74lGBC3T3MjnC3ZH/bJzhsFAjFgdzVUl5c9xG25F6Kqv1+Z5YBZCKEyjiroajPLWBtK6ZwrldHdkjLWBvA5F2y1Vj8ndDW3dFrXxY1G7724gm/VzsJX3NJD0joZ9zOlemMll93mco6qBDXjkDI8qdkevuxpuvK6o8y6fZpvDmMg2vNjt2jHN52eT4ArRzVdPE9Icff4NnNnRXTfmX3PlfDzmfHF4hmdjqQ988gswwLtxmmtZGq5tCyaWZVyOsFbazgU+E93RtjO5Iq/Aa1PwbGtoy11YGnNAU+E5TMzP1dv6vsj+fx7zL/a3O1JR2zBRtuMbc+ETUlXEiBEjRow3BKGfiX2cNcSEFCNGjBgxlgQ47oIYMWLEiBETUowYMWLEiBETUowYMWLEiAkpRowYMWLEiAkpRowYMWLEhBQjRowYMWLEhBQjRowYMWJCihEjRowYMWJCihEjRowYMSHFiBEjRowYMSHFiBEjRozzCf//AGxPcZqdU6V7AAAAAElFTkSuQmCC',
	);
	return $res;
}

function showerror($errno, $message = '') {
	return array(
		'errno' => $errno,
		'error' => $message,
	);
}
