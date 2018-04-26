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
	'app_namespace' => 'app',
	// 应用调试模式
	'app_debug' => true,
	// 应用Trace
	'app_trace' => false,
	// 应用模式状态
	'app_status' => '',
	// 是否支持多模块
	'app_multi_module' => true,
	// 入口自动绑定模块
	'auto_bind_module' => false,
	// 注册的根命名空间
	'root_namespace' => [],
	// 扩展函数文件
	'extra_file_list' => [THINK_PATH . 'helper' . EXT],
	// 默认输出类型
	'default_return_type' => 'html',
	// 默认AJAX 数据返回格式,可选json xml ...
	'default_ajax_return' => 'json',
	// 默认JSONP格式返回的处理方法
	'default_jsonp_handler' => 'jsonpReturn',
	// 默认JSONP处理方法
	'var_jsonp_handler' => 'callback',
	// 默认时区
	'default_timezone' => 'PRC',
	// 是否开启多语言
	'lang_switch_on' => false,
	// 默认全局过滤方法 用逗号分隔多个
	'default_filter' => '',
	// 默认语言
	'default_lang' => 'zh-cn',
	// 应用类库后缀
	'class_suffix' => false,
	// 控制器类后缀
	'controller_suffix' => false,

	// +----------------------------------------------------------------------
	// | 模块设置
	// +----------------------------------------------------------------------

	// 默认模块名
	'default_module' => 'index',
	// 禁止访问模块
	'deny_module_list' => ['common'],
	// 默认控制器名
	'default_controller' => 'index',
	// 默认操作名
	'default_action' => 'index',
	// 默认验证器
	'default_validate' => '',
	// 默认的空控制器名
	'empty_controller' => 'Error',
	// 操作方法后缀
	'action_suffix' => '',
	// 自动搜索控制器
	'controller_auto_search' => false,

	// +----------------------------------------------------------------------
	// | URL设置
	// +----------------------------------------------------------------------

	// PATHINFO变量名 用于兼容模式
	'var_pathinfo' => 's',
	// 兼容PATH_INFO获取
	'pathinfo_fetch' => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
	// pathinfo分隔符
	'pathinfo_depr' => '/',
	// URL伪静态后缀
	'url_html_suffix' => '',
	// URL普通方式参数 用于自动生成
	'url_common_param' => false,
	// URL参数方式 0 按名称成对解析 1 按顺序解析
	'url_param_type' => 0,
	// 是否开启路由
	'url_route_on' => true,
	// 路由使用完整匹配
	'route_complete_match' => false,
	// 路由配置文件（支持配置多个）
	'route_config_file' => ['route'],
	// 是否强制使用路由
	'url_route_must' => false,
	// 域名部署
	'url_domain_deploy' => false,
	// 域名根，如thinkphp.cn
	'url_domain_root' => '',
	// 是否自动转换URL中的控制器和操作名
	'url_convert' => true,
	// 默认的访问控制器层
	'url_controller_layer' => 'controller',
	// 表单请求类型伪装变量
	'var_method' => '_method',
	// 表单ajax伪装变量
	'var_ajax' => '_ajax',
	// 表单pjax伪装变量
	'var_pjax' => '_pjax',
	// 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
	'request_cache' => false,
	// 请求缓存有效期
	'request_cache_expire' => null,

	// +----------------------------------------------------------------------
	// | 模板设置
	// +----------------------------------------------------------------------

	'template' => [
		// 模板引擎类型 支持 php think 支持扩展
		'type' => 'Think',
		// 模板路径
		'view_path' => '',
		// 模板后缀
		'view_suffix' => 'html',
		// 模板文件名分隔符
		'view_depr' => DS,
		// 模板引擎普通标签开始标记
		'tpl_begin' => '{',
		// 模板引擎普通标签结束标记
		'tpl_end' => '}',
		// 标签库标签开始标记
		'taglib_begin' => '{',
		// 标签库标签结束标记
		'taglib_end' => '}',
	],

	// 视图输出字符串内容替换
	'view_replace_str' => [],
	// 默认跳转页面对应的模板文件
	'dispatch_success_tmpl' => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
	'dispatch_error_tmpl' => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

	// +----------------------------------------------------------------------
	// | 异常及错误设置
	// +----------------------------------------------------------------------

	// 异常页面的模板文件
	'exception_tmpl' => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

	// 错误显示信息,非调试模式有效
	'error_message' => '页面错误！请稍后再试～',
	// 显示错误信息
	'show_error_msg' => false,
	// 异常处理handle类 留空使用 \think\exception\Handle
	'exception_handle' => '',

	// +----------------------------------------------------------------------
	// | 日志设置
	// +----------------------------------------------------------------------

	'log' => [
		// 日志记录方式，内置 file socket 支持扩展
		'type' => 'File',
		// 日志保存目录
		'path' => LOG_PATH,
		// 日志记录级别
		'level' => [],
	],

	// +----------------------------------------------------------------------
	// | Trace设置 开启 app_trace 后 有效
	// +----------------------------------------------------------------------
	'trace' => [
		// 内置Html Console 支持扩展
		'type' => 'Html',
	],

	// +----------------------------------------------------------------------
	// | 缓存设置
	// +----------------------------------------------------------------------

	'cache' => [
		// 驱动方式
		'type' => 'File',
		// 缓存保存目录
		'path' => CACHE_PATH,
		// 缓存前缀
		'prefix' => '',
		// 缓存有效期 0表示永久缓存
		'expire' => 0,
	],

	// +----------------------------------------------------------------------
	// | 会话设置
	// +----------------------------------------------------------------------

	'session' => [
		'id' => '',
		// SESSION_ID的提交变量,解决flash上传跨域
		'var_session_id' => '',
		// SESSION 前缀
		'prefix' => 'think',
		// 驱动方式 支持redis memcache memcached
		'type' => '',
		// 是否自动开启 SESSION
		'auto_start' => true,
	],

	// +----------------------------------------------------------------------
	// | Cookie设置
	// +----------------------------------------------------------------------
	'cookie' => [
		// cookie 名称前缀
		'prefix' => '',
		// cookie 保存时间
		'expire' => 86400,
		// cookie 保存路径
		'path' => '/',
		// cookie 有效域名
		'domain' => '',
		//  cookie 启用安全传输
		'secure' => false,
		// httponly设置
		'httponly' => '',
		// 是否使用 setcookie
		'setcookie' => true,
	],

	//分页配置
	'paginate' => [
		'type' => 'bootstrap',
		'var_page' => 'page',
		'list_rows' => 15,
	],
	//微信模板消息配置,原子旅游
	'yuanzi_temp' => [
		'gaijia' => 'thCySFvOudTlpOer9U7cdrNJh1dnpPNP3MLk2m8aMxA',
		'tuikuan' => '2wlzODhCUxLUvU8mVP6j7lYsNBPcC_n0kNh1IlErc5A',
		'baoming' => 'lP48omIlnjhdZdh4YUTvL3To-xbrjR2gTM_kIWncAcw',
	],
	//微信模板消息配置,bunale
	'bunale_temp' => [
		'gaijia' => 'DxNZA4u6Lc__xyJF70pVfrsWg6PruuIUv8tBzjTvenQ',
		'tuikuan' => 'adu4pYsvWJ_LlgnWOKrfqQ-j0OsGMivz-4qJrAxgSdA',
		'baoming' => 'Bmp3TADjVJmyTvssJB60PqC5J9WaGz0KOcxqC-Gj8G4',
	],
	//微信模板消息配置,liangzi
    'liangzi_temp' => [
        'gaijia' => 'DxNZA4u6Lc__xyJF70pVfrsWg6PruuIUv8tBzjTvenQ',
        'tuikuan' => 'adu4pYsvWJ_LlgnWOKrfqQ-j0OsGMivz-4qJrAxgSdA',
        'baoming' => 'CPOL7LnB68bNyvEuNDyFEslhizvFC-sdentDQPnzWUQ',
    ],    
	'view_replace_str' => [
		'__ROOT__' => 'http://' . $_SERVER['SERVER_NAME'], //根路径
		'__PLUS__' => 'http://' . $_SERVER['SERVER_NAME'] . '/static/Plus', //插件目录（所有插件放在此文件夹）
		'__Ueditor__' => 'http://' . $_SERVER['SERVER_NAME'] . '/static/Plus/Ueditor', //百度编辑器
		'__MOBISCROLL__' => 'http://' . $_SERVER['SERVER_NAME'] . '/static/Plus/Mobiscroll', //时间解决方案
		'__CSS__' => 'http://' . $_SERVER['SERVER_NAME'] . '/static/nifty/css', //前台css
		'__JS__' => 'http://' . $_SERVER['SERVER_NAME'] . '/static/nifty/js', //前台js
		'__IMAGES__' => 'http://' . $_SERVER['SERVER_NAME'] . '/static/nifty/img', //前台图片
		'__PLUGINS__' => 'http://' . $_SERVER['SERVER_NAME'] . '/static/nifty/plugins', //前台图片
		'__IMG__' => 'http://' . $_SERVER['SERVER_NAME'] . '/static/img',
		'__NIMG__' => 'http://chat.xiaomange.com/static/nifty/img', //前台图片
	],
	//阿里云配置项
	'aliyun_oss' => [
		'KeyId' => 'LTAIuXMCWbMQFix0', //您的Access Key ID
		'KeySecret' => 'hMNZtF4pTUwneHmZgF8kqVUOR4YFra', //您的Access Key Secret
		'Endpoint' => 'oss-cn-shanghai.aliyuncs.com', //阿里云oss 外网地址endpoint
		'Bucket' => 'aman', //Bucket名称
	],

];
