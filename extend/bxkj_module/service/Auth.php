<?php

namespace bxkj_module\service;

use bxkj_module\exception\Exception;
use think\Db;

class Auth
{
    protected $currentRules = array();
    public static $userAuth = array();
    protected $config;
    protected $groupTab;
    protected $groupRelationTab;
    protected $ruleTab;

    public function __construct()
    {
        $this->config = config('app.auth_config');
        $this->groupTab = $this->config['auth_group'];
        $this->groupRelationTab = $this->config['auth_group_access'];
        $this->ruleTab = $this->config['auth_rule'];
    }

    public function check($uid, $condition, $type = 1)
    {   
        if (empty($uid)) return false;
        if (is_string($condition) && preg_match('/\,/', $condition)) {
            $condition = explode(',', $condition);
        }
        if (is_array($condition)) {
            $condition = implode(' OR ', $condition);
        }
        $this->currentRules = $this->getRulesByUid($uid, $type);
        $res = $this->parseGroupCondition($condition);
        return $res;
    }

    //根据用户ID获取其拥有的所有权限（含缓存机制）
    protected function getRulesByUid($uid, $type = 1)
    {
        //程序运行时缓存
        if (isset(self::$userAuth[(string)$uid])) {
            return self::$userAuth[(string)$uid];
        }
        $rules = array();
        $cacheKey = self::getAdminGroupsKey($uid);
        $appDebug = false;
        //$appDebug =config('app.app_debug');
        $cache = $appDebug ? '' : cache($cacheKey);
        $groupIds = empty($cache) ? array() : json_decode($cache, true);
        if (empty($groupIds)) {
            $relations = Db::name($this->groupRelationTab)->field('id,gid')->where(array('uid' => $uid))->select();
            foreach ($relations as $relation) {
                $groupIds[] = $relation['gid'];
            }
            cache($cacheKey, json_encode($groupIds));
        }
        $tmpIds = array();
        foreach ($groupIds as $groupId) {
            $gk = self::getAdminGroupRulesKey($groupId);
            $gCache = $appDebug ? '' : cache($gk);
            if (!empty($gCache)) {
                $arr = json_decode($gCache, true);
                $rules = array_merge($rules, is_array($arr) ? $arr : array());
            } else {
                $tmpIds[] = $groupId;
            }
        }
        if (!empty($tmpIds)) {
            $db = Db::name($this->groupTab);
            $db->whereIn('id', $tmpIds)->where(['status' => '1']);
            $groups = $db->field('id,rules')->select();
            foreach ($groups as $group) {
                $gk = self::getAdminGroupRulesKey($group['id']);
                if (!empty($group['rules'])) {
                    $ruleIds = explode(',', $group['rules']);
                    $ruleRes = Db::name($this->ruleTab)->whereIn('id', $ruleIds)->where('status', '1')->field('id,name,type')->select();
                    $rules = array_merge($rules, is_array($ruleRes) ? $ruleRes : array());
                    cache($gk, json_encode($ruleRes));
                }
            }
        }
        $ruleNames = array();
        foreach ($rules as $rule) {
            if ($rule['type'] == $type) $ruleNames[] = $rule['name'];
        }
        self::$userAuth[(string)$uid] = $ruleNames;
        return $ruleNames;
    }

    protected function parseGroupCondition($condition)
    {
        $res = null;
        if (preg_match('/\(.+\)/', $condition)) {
            $condition = $this->logicSuffix($condition);
            $matches = array();
            preg_match_all('/((\((\s*[a-zA-Z0-9_\:\(\)\s]+\s*)\)\s*)|([a-zA-Z0-9_\:\s]+))(AND|OR)/U', $condition, $matches);
            if (!empty($matches) && !empty($matches[3])) {
                foreach ($matches[3] as $k => $m) {
                    $m = empty($m) ? $matches[4][$k] : $m;
                    $res = $this->parseGroupCondition($m);
                    $logic = $matches[5][$k];
                    if ($logic == 'AND' && !$res) break;
                    if ($logic == 'OR' && $res) break;
                }
            }
        } else {
            $res = $this->parseCondition($condition);
        }
        return $res;
    }

    protected function parseCondition($condition)
    {
        $matches = array();
        preg_match_all('/([a-z0-9A-Z_\:]+\s*(AND|OR))/U', $this->logicSuffix($condition), $matches);
        $res = null;
        if (!empty($matches) && !empty($matches[1])) {
            foreach ($matches[1] as $k => $m) {
                $tmp = preg_replace('/\s*(OR|AND)$/', '', $m);
                $res = $this->verification($tmp);
                $logic = $matches[2][$k];
                if ($logic == 'AND' && !$res) break;
                if ($logic == 'OR' && $res) break;
            }
        }
        return $res;
    }

    protected function logicSuffix($condition)
    {
        return $condition . (preg_match('/(AND|OR)$/', $condition) ? '' : ' OR');
    }

    protected function verification($name)
    {
        return in_array(trim($name), $this->currentRules);
    }

    //分组下面的所有规则
    public static function getAdminGroupRulesKey($groupId)
    {
        if (empty($groupId)) {
            throw new Exception('auth group_id empty');
        }
        $config = config('app.auth_config');
        $cachePrefix = $config['cache_prefix'] ? $config['cache_prefix'] : 'admin';
        return "auth:{$cachePrefix}:group_rules:" . $groupId;
    }

    //用户下面的所有分组
    public static function getAdminGroupsKey($uid)
    {
        if (empty($uid)) {
            throw new Exception('auth uid empty');
        }
        $config = config('app.auth_config');
        $cachePrefix = $config['cache_prefix'] ? $config['cache_prefix'] : 'admin';
        return "auth:{$cachePrefix}:groups:" . $uid;
    }

}