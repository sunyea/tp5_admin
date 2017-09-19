<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用命名空间
    'app_namespace'          => 'app',
    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态
    'app_status'             => '',
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 扩展函数文件
    'extra_file_list'        => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'PRC',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => 'htmlspecialchars',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'index',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由
    'url_route_on'           => true,
    // 路由使用完整匹配
    'route_complete_match'   => false,
    // 路由配置文件（支持配置多个）
    'route_config_file'      => ['route'],
    // 是否强制使用路由
    'url_route_must'         => false,
    // 域名部署
    'url_domain_deploy'      => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => true,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
        //打开布局
        'layout_on'    => false,
        'layout_name'  => 'layout',
    ],

    // 视图输出字符串内容替换
    'view_replace_str'       => [],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => APP_PATH . 'tpl' . DS . 'dispatch_nojump.tpl',
    'dispatch_error_tmpl'    => APP_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => [],
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace'                  => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                  => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'sunyea',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
        //过期时间
        'expire'         => 7200,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    //分页配置
    'paginate'               => [
        'type'      => 'laypage',
        'var_page'  => 'page',
        'list_rows' => 7,
    ],

    // +----------------------------------------------------------------------
    // | 验证码设置
    // +----------------------------------------------------------------------
    'captcha'               =>  [
        'codeSet'   =>  '3456789abcdefghijklmnopqrstuvwxyz',
        'length'    =>  '4',
        'useCurve'  =>  false,
        //'useZh'     =>  true,
        //'imageW'    =>  '100',
    ],

    // +----------------------------------------------------------------------
    // | 文件上传设置
    // | {yyyy}=年,{mm}=月,{dd}=日,{uid}=用户ID账号,{filename}=原始名称,{ext}=原始扩展名,{md5}=md5名称
    // +----------------------------------------------------------------------
    'upfile'                =>  [
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
            'autowater' =>  true,
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
            'size'      =>  '51200000', //文件大小，单位byte 最大限制为：64M
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
    ],
    
    // +----------------------------------------------------------------------
    // | 网站基本信息设置
    // +----------------------------------------------------------------------
    'webinfo'               =>  [
        'name'      =>  '德阳商易网络科技有限责任公司',
        'sname'     =>  '商易科技',
        'keyword'   =>  '软件开发,APP开发,网站设计,电子商务,电子政务,微信公众号,德阳科技公司',
        'tel'       =>  '13700903288',
        'qq'        =>  '7192506',
        'email'     =>  '7192506@qq.com',
        'address'   =>  '四川省德阳市河东区龙泉山北路',
        'beian'     =>  '蜀ICP备15000529号-1',
    ],
];
