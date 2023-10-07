<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

$config = [
    // 模板引擎类型 支持 php think 支持扩展
    'type' => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule' => 1,
    // 模板路径
    'view_path' => '',
    // 模板后缀
    'view_suffix' => 'tpl',
    // 模板文件名分隔符
    'view_depr' => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin' => '{',
    // 模板引擎普通标签结束标记
    'tpl_end' => '}',
    // 标签库标签开始标记
    'taglib_begin' => '<',
    // 标签库标签结束标记
    'taglib_end' => '>',
    'tpl_replace_string' => array(
        '__STATIC__' => '/static',
        '__NEWSTATIC__' => '/bx_static',
        '__VENDOR__' => '/static/vendor',
        '__AGENT__' => '/static/agent',
        '__ADMIN__' => '/static/admin',
        '__HOME__' => '/static/home',
        '__H5__' => '/static/h5',
        '__WEB__' => '/static/web',
        '__RV__' => '<?php echo config(\'upload.resource_version\'); ?>',
        '__JS__' => '<?php echo (\'/static/\'.strtolower(\think\facade\Request::module()).\'/js\'); ?>',
        '__CSS__' => '<?php echo (\'/static/\'.strtolower(\think\facade\Request::module()).\'/css\'); ?>',
        '__IMAGES__' => '<?php echo (\'/static/\'.strtolower(\think\facade\Request::module()).\'/images\'); ?>',
        '__JUMP__' => '<?php echo (\'redirect=\'.urlencode(\think\facade\Request::url())); ?>',
        '__BOUNCE__' => '<?php echo \'<input type="hidden" name="redirect" value="\'.input(\'redirect\').\'" />\'; ?>',
    ),
    'tpl_cache' => false,
    'taglib_build_in' => 'cx,expand'
];
use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/template.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;
