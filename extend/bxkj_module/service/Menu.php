<?php

namespace bxkj_module\service;

use think\Db;
use think\facade\Request;

class Menu extends Tree
{
    protected $authOn = false;
    protected $currentName = 'current';

    public function __construct()
    {
        parent::__construct('menu', 'pid', 'id');
        $this->authOn = config('app.auth_on');
    }

    public function setCurrentName($currentName)
    {
        $this->currentName = $currentName;
        return $this;
    }

    //获取菜单树
    public function getMenuTree($mark, $url = null, $uid = null, $param = null, $menu_id = null)
    {
        $MODULE_NAME = Request::module();
        $CONTROLLER_NAME = parse_name(Request::controller(), 0, false);
        $ACTION_NAME = Request::action();
        //查找URL对应的menu_id
        if (!isset($menu_id)) {
            $where['url'] = isset($url) ? $url : "$MODULE_NAME/{$CONTROLLER_NAME}/{$ACTION_NAME}";
            $where['status'] = '1';
            $where['copy'] = '0';
            $order['level'] = 'desc';
            $order['sort'] = 'desc';
            $order['create_time'] = 'asc';
            if (isset($param)) {
                $where['param'] = is_array($param) ? http_build_query($param) : $param;
                $current = Db::name('menu')->where($where)->order($order)->field('id')->find();
                $menu_id = $current['id'];
            } else {
                $current = Db::name('menu')->where($where)->order($order)->field('id,param')->select();
                $menu_id = $this->contrastParam($current);
            }
        }
        //菜单树
        $tree = $this->setOrderOptions('sort desc,create_time asc')->setWhereOptions(['status' => '1', 'display' => '1'])->getChildrenByMark($mark);
        $path = $this->getParentPath((int)$menu_id, (int)$tree['id'], true);//当前继承路径
        $currentTree = array();
        $tree['children'] = $this->displayTree($tree['children'], $path, 1, $uid, $currentTree);
        if (!$this->authOn || (!empty($tree['rules']) && check_auth($tree['rules'], $uid)) || (empty($tree['rules']) && !empty($tree['children']))) {
            $tree['current'] = $this->currentName;
            $tree['menu_url'] = menu_url($tree['url'], $tree['param']);
            array_unshift($currentTree, $tree);
        }
        return $currentTree;
    }

    //获取快捷方式
    public function getShortcuts($name, $uid = null, $length = 10)
    {
        $arr = array();
        $shortcuts = $this->where(array(
            'shortcut' => array('like', "%{$name}%"),
            'status' => '1'
        ))->limit($length)->select();
        foreach ($shortcuts as $shortcut) {
            if (!$this->authOn || (!empty($shortcut['rules']) && check_auth($shortcut['rules'], $uid))) {
                $shortcut['current'] = $this->currentName;
                $shortcut['menu_url'] = menu_url($shortcut['url'], $shortcut['param']);
                array_unshift($arr, $shortcut);
            }
        }
        return $arr;
    }

    //按照当前所在位置和auth规则显示
    protected function displayTree($tree, $path, $index, $uid = null, &$currentTree = array())
    {
        $list = array();
        for ($i = 0; $i < count($tree); $i++) {
            //子级菜单
            if (!empty($tree[$i]['children'])) {
                $tree[$i]['children'] = $this->displayTree($tree[$i]['children'], $path, $index + 1, $uid, $currentTree);
            }
            //检查当前菜单项权限
            $rules = $tree[$i]['rules'];
            if (!$this->authOn || (!empty($rules) && check_auth($rules, $uid)) || (empty($rules) && !empty($tree[$i]['children']))) {
                //当前级别选中项
                if (isset($path[$index]) && $tree[$i]['id'] == $path[$index]) {
                    $tree[$i]['current'] = $this->currentName;
                    array_unshift($currentTree, $tree[$i]);
                } else {
                    $tree[$i]['current'] = '';
                }
                $tree[$i]['menu_url'] = menu_url($tree[$i]['url'], $tree[$i]['param']);
                array_push($list, $tree[$i]);
            }
        }
        return $list;
    }

    //比对参数,返回参数相符的一条记录ID
    protected function contrastParam($arr, $get = null)
    {
        $get = isset($get) ? $get : input();
        $menu_id = isset($arr[0]) ? $arr[0]['id'] : 0;//默认第一个
        for ($i = 0; $i < count($arr); $i++) {
            if (!empty($arr[$i]['param'])) {
                $tmp = explode("&", $arr[$i]['param']);
                $accord = true;//参数符合
                for ($j = 0; $j < count($tmp); $j++) {
                    list($name, $value) = explode("=", $tmp[$j]);
                    if ($get[$name] != $value) {
                        $accord = false;
                        break;
                    }
                }
                if ($accord) return $arr[$i]["id"];
            }
        }
        return $menu_id;
    }

}