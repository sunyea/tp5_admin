<?php
// +----------------------------------------------------------------------
// | Upfile.php
// +----------------------------------------------------------------------
// | Copyright (c) 2004~2024 http://www.sunyea.cn All rights reserved.
// +----------------------------------------------------------------------
// | Create Time: 2017-04-15 08:18:15
// +----------------------------------------------------------------------
// | Author: sunyea <7192506@qq.com>
// +----------------------------------------------------------------------
namespace app\common;
use think\Image;
use think\Request;
use think\Config;

/**
* 文件上传类
*/
class Upfile
{
	protected $config = [
        'basepath'  =>  'upload',
        'image'     =>  [
            'name'      =>  'image/{yyyy}-{mm}/{filename}.{ext}',   //保存路径及名称
            'type'      =>  '', //MIME类型
            'ext'       =>  'png,jpg,jpeg,gif,bmp', //文件扩展名
            'size'      =>  '2048000', //文件大小，单位byte
            'autoresize'=>  false,
            'resizew'   =>  '800',
            'resizeh'   =>  '600',
            'resizemode'=>  1,  //1:等比例缩放;2:缩放后填充;3:居中裁剪;4:左上角裁剪;5:左下角裁剪;6:固定尺寸缩放;
            'autowater' =>  false,
            'watertype' =>  'text', //text:文字水印；image:图片水印；
            'watertext' =>  '商易科技',
            'watersize' =>  14, //单位像素
            'waterfont' =>  'static/water/msyh.ttf',   //字体文件路径
            'watercolor'=>  '#ffffff',  //字体颜色
            'waterimg'  =>  'static/water/logo.png',
            'waterpos'  =>  9,  //1:左上角;2:上居中;3:右上角;4:左居中;5:居中;6:右居中;7:左下角;8:下居中;9:右下角;
            'wateralpha'=>  50, //0-100透明度
        ],
        'file'      =>  [
            'name'      =>  'file/{yyyy}-{mm}/{filename}.{ext}',   //保存路径及名称
            'type'      =>  '', //MIME类型
            'ext'       =>  'png,jpg,jpeg,gif,bmp,flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4,webm,mp3,wav,mid,rar,zip,tar,gz,7z,bz2,cab,iso,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,md,xml', //文件扩展名
            'size'      =>  '51200000', //文件大小，单位byte
        ],
        'video'     =>  [
            'name'      =>  'video/{yyyy}-{mm}/{filename}.{ext}',   //保存路径及名称
            'type'      =>  '', //MIME类型
            'ext'       =>  'flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4,webm', //文件扩展名
            'size'      =>  '102400000', //文件大小，单位byte
        ],
        'audio'     =>  [
            'name'      =>  'audio/{yyyy}-{mm}/{filename}.{ext}',   //保存路径及名称
            'type'      =>  '', //MIME类型
            'ext'       =>  'mp3,wav', //文件扩展名
            'size'      =>  '2048000', //文件大小，单位byte
        ],       
    ];
  protected $fname;	//表单项名称
  protected $type;	//文件类型
  protected $error;	//错误信息
  protected $fullname;	//文件全路径名称
  protected $request;

  protected $bool_progress;
  protected $progress_key;

	function __construct($_fname, $_type, $_configimg=[])
	{
		if ($upfile = Config::get('upfile')) {
			$this->config = array_merge($this->config, $upfile);
			$this->config['image'] = array_merge($this->config['image'], $_configimg);
		}
		$this->fname = $_fname;
		$this->type = $_type;
		$this->request = Request::instance();

		if (version_compare(phpversion(), '5.4.0', '<')){
			$this->bool_progress = false;
		}else{
			if (!intval(ini_get('session.upload_progress.enabled'))){
				$this->bool_progress = false;
			}else{
				$progress_prefix = ini_get('session.upload_progress.prefix');
				$progress_name = ini_get('session.upload_progress.name');
				$this->progress_key = $progress_prefix.input('post.'.$progress_name);
				$this->bool_progress = true;
			}
		}
	}

	//保存文件
	//return bool
	public function save(){
		$file = $this->request->file($this->fname);
		$validate = $this->getValidate();
		$filepath = $this->getFilePath($file->getInfo('name'));
		$path = str_replace('/', DS, $filepath['path']);

		$info = $file->validate($validate)->move(ROOT_PATH.'public'.DS.$path, iconv('utf-8','gb2312',$filepath['basename']));
		
		if ($info) {
			$this->fullname = $filepath['path'].'/'.$filepath['basename'];
			if ($this->type == 'image') {
				return $this->operateImage($this->fullname);
			}
			return true;
		}else{
			$this->error = $file->getError();
			return false;
		}
		
	}

	//获取错误信息
	public function getError(){
		return $this->error;
	}

	//获取文件信息
	public function getFile(){
		return '/'.$this->fullname;
	}

	//获取上传进度
	//return int
	public function getProgress(){
		$progress = 0;
		if ($this->bool_progress) {
			if(empty($_SESSION[$this->progress_key])){
				$progress = 100;
			}else{
				$_up = $_SESSION[$this->progress_key]['bytes_processed'];
				$_all = $_SESSION[$this->progress_key]['content_length'];
				$progress = ceil(100*$_up/$_all);
			}
		}
		
		return $progress;
	}

	//取消上传
	public function stopUpload(){
		if ($this->bool_progress) {
			if(!empty($_SESSION[$this->progress_key])){
				$_SESSION[$this->progress_key]['cancel_upload'] = true;
			}
		}
	}


	//获取验证规则
	//return array
	protected function getValidate(){
		$_validate = [];
		$_ext = $this->config[$this->type]['ext'];
		$_type = $this->config[$this->type]['type'];
		$_size = $this->config[$this->type]['size'];
		if (!empty($_ext)) {
			$_validate['ext'] = $_ext;
		}
		if (!empty($_type)) {
			$_validate['type'] = $_type;
		}
		if (!empty($_size)) {
			$_validate['size'] = $_size;
		}

		return $_validate;
	}

	//获取文件路径名称
	//return array 文件路径和文件名
	protected function getFilePath($filename){
		$_filename = $this->path_info($filename)['filename'];	//pathinfo($filename, PATHINFO_FILENAME);
		$_fileext = $this->path_info($filename)['extension'];	//pathinfo($filename, PATHINFO_EXTENSION);
		$_fullname = $this->config[$this->type]['name'];
		$_basepath = $this->config['basepath'];
		if (substr($_basepath, -1) == '/') {
			$_basepath = substr($_basepath, 0, -1);
		}
		$_fullname = str_replace('{yyyy}', date('Y'), $_fullname);
		$_fullname = str_replace('{mm}', date('m'), $_fullname);
		$_fullname = str_replace('{dd}', date('d'), $_fullname);
		$_fullname = str_replace('{uid}', session('manager.uid'), $_fullname);
		$_fullname = str_replace('{filename}', $_filename, $_fullname);
		$_fullname = str_replace('{ext}', $_fileext, $_fullname);
		$_fullname = str_replace('{md5}', md5($_filename), $_fullname);

		$_file_path = $_basepath.'/'.$this->path_info($_fullname)['dirname'];	//pathinfo($_fullname, PATHINFO_DIRNAME);
		$_file_name = $this->path_info($_fullname)['basename'];	//pathinfo($_fullname, PATHINFO_BASENAME);

		if (!is_dir($_file_path)) {
			$res = mkdir($_file_path, 0777, true);
			if (!$res) {
				array_push($this->message, '目录不存在，创建目录失败');
				return;
			}
		}
		return ['path'=>$_file_path, 'name'=>$_filename, 'basename'=>$_file_name];
	}

	//处理图片文件
	//return bool
	protected function operateImage($filename){
		$b_resize = $this->config['image']['autoresize'];
		$b_water = $this->config['image']['autowater'];
		$filename = iconv('utf-8', 'gb2312', $filename);
		if ($b_resize) {
			$_width = $this->config['image']['resizew'];
			$_height = $this->config['image']['resizeh'];
			$_mode = $this->config['image']['resizemode'];
			$img = Image::open($filename);
			if($img){
				$img->thumb($_width, $_height, $_mode)->save(ROOT_PATH.'public'.DS.$filename);
			}else{
				$this->error = '文件尺寸改变失败';
				return fasle;
			}
		}
		if ($b_water) {
			$_watertype = $this->config['image']['watertype'];
			$_watertext = $this->config['image']['watertext'];
			$_waterfont = $this->config['image']['waterfont'];
			$_watersize = $this->config['image']['watersize'];
			$_watercolor = $this->config['image']['watercolor'];
			$_waterimg = $this->config['image']['waterimg'];
			$_waterpos = $this->config['image']['waterpos'];
			$_wateralpha = $this->config['image']['wateralpha'];
			$img = Image::open($filename);
			if ($img) {
				if ($_watertype == 'text') {
					$img->text($_watertext, $_waterfont, $_watersize, $_watercolor, $_waterpos)->save(ROOT_PATH.'public'.DS.$filename);
				}elseif($_watertype == 'image'){
					$img->water($_waterimg, $_waterpos, $_wateralpha)->save(ROOT_PATH.'public'.DS.$filename);
				}
			}else{
				$this->error = '文件加水印失败';
				return false;
			}
		}
		return true;
	}

	//获取文件路径信息
	//return array()
	protected function path_info($filepath){   
		$path_parts = array();   
		$path_parts ['dirname'] = rtrim(substr($filepath, 0, strrpos($filepath, '/')),"/")."/";   
		$path_parts ['basename'] = ltrim(substr($filepath, strrpos($filepath, '/')),"/");   
		$path_parts ['extension'] = substr(strrchr($filepath, '.'), 1);   
		$path_parts ['filename'] = ltrim(substr($path_parts ['basename'], 0, strrpos($path_parts ['basename'], '.')),"/");   
		return $path_parts;
	}

}