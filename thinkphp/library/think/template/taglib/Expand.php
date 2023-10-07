<?php

namespace think\template\taglib;

use think\template\TagLib;

class Expand extends TagLib
{
    protected $tags = array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        "widget" => array('attr' => 'name'),
        "auth" => array('attr' => 'rules'),
        "authelse" => array('attr' => '', 'close' => 0),
    );

    public function tagWidget($tag, $content)
    {
        $widget = config('widgets.' . $tag['name']);
        $class = $widget['class'];
        $action = $widget['action'] ? $widget['action'] : "index";
        $arr = array();
        preg_match('/(\w+)$/U', $class, $arr);
        $class_name = $arr[1];
        $content = addcslashes($content, '\'');
        $parseStr = "<?php call_user_func_array(array(new " . $class . "('" . $class_name . "','" . $action . "','" . $content . "'," . var_export($tag, true) . ",\$vars),'" . $action . "'),
        array()); ?>";
        return $parseStr;
    }

    public function tagAuth($tag, $content)
    {
        $rules = $tag['rules'];
        $html = "<?php if(check_auth('{$rules}'," . (isset($tag['uid']) ? "'{$tag['uid']}'" : 'AUTH_UID') . ")): ?>{$content}<?php endif; ?>";
        return $html;
    }

    public function tagAuthelse($tag,$content)
    {
        $parseStr = '<?php else: ?>';
        return $parseStr;
    }

}