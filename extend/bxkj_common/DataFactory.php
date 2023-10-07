<?php

namespace bxkj_common;

use think\Db;
use think\facade\Env;

class DataFactory
{
    const NOT_EXISTS = 'not_exists';//不存在
    const EXISTS = 'exists';//存在
    const VALUE_EMPTY = 'value_empty';//存在且为空
    const NOT_EMPTY = 'not_empty';//存在且不为空
    const NOT_HAS = 'not_has';//没有值,不存在或者为空
    const ANY = 'any';//不管任何情况
    const VALUE_CHANGE = 'value_change';//值发生改变
    const VALUE_KEEP = 'value_keep';//值没有发生改变

    public static $preRules = [];//预置规则
    protected $ruleFilePath;//规则文件目录
    protected $defaultRuleFileName = 'rules';//默认规则文件
    protected $errorTpls = array(
        'deny' => '{$fields}：非法输入！',//拒绝错误消息模板
        'must' => '{$fields}：必须输入！',//必须错误消息模板
        'validate' => '{$fields}：输入错误！',//输入错误消息模板
    );
    protected $error;//错误
    protected $inputData;//原始输入数据
    protected $data;//当前已经生产的数据
    protected $callHandle;//回调句柄
    protected $savedData;//已保存的数据
    protected $where;//查询已保存数据的条件
    protected $rules = [];//当前生产规则

    public function __construct(&$handle = null)
    {
        $path = Env::get('config_path');
        $this->ruleFilePath = $path . 'rules';
        if (isset($handle)) {
            $this->callHandle = $handle;
        }
    }

    public function setHandle(&$handle)
    {
        $this->callHandle = $handle;
        return $this;
    }

    public function table($table)
    {
        if (!is_array($this->rules)) $this->rules = [];
        $this->rules['table'] = $this->parseTableRule($table);
        return $this;
    }

    //处理数据入口
    public function process($ruleName, $data, $savedData = null)
    {
        $this->data = $data;
        $this->inputData = $data;
        $this->savedData = $savedData;
        $this->error = null;//清空错误
        $this->rules = $this->getRules($ruleName);//获取已解析的规则数组
        if ($this->rules) {
            $this->data = $this->map();//映射数据
            if (!empty($this->rules['table'])) $this->where = $this->getWhereByPks();
            if (!isset($this->savedData)) {
                $this->savedData = !empty($this->where) ? $this->getSavedData($this->where) : array();
            }
            if (!$this->error) $this->deny();
            if (!$this->error) $this->must();
            if (!$this->error && !empty($this->rules['ignore'])) {
                $this->data = $this->ignore();
            }
            if (!$this->error) $this->validate();
            if (!$this->error && !empty($this->rules['fill'])) {
                $this->data = $this->fill();
            }
        }
        return $this;
    }

    //获取映射数据
    public function map($data = null, $real = true, $map = null)
    {
        $data = isset($data) ? $data : $this->inputData;
        $map = isset($map) ? $this->parseMapRule($map) : ($this->rules['map'] ? $this->rules['map'] : array());
        $newData = array();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $newData[$this->getMapField($key, $real, $map)] = $value;
            }
        }
        return $newData;
    }

    //获取映射的字段名
    public function getMapField($name, $real = true, $map = null)
    {
        $map = isset($map) ? $this->parseMapRule($map) : $this->rules['map'];
        if ($real) {
            foreach ($map as $realName => $falseName) {
                if ($falseName == $name) return $realName;
            }
            return $name;
        } else {
            return isset($map[$name]) ? $map[$name] : $name;
        }
    }

    //拒绝字段
    public function deny($data = null, $denyFields = null)
    {
        $data = isset($data) ? $this->map($data) : $this->data;
        $denyFields = isset($denyFields) ? $this->parseFieldsRule($denyFields) : $this->rules['deny'];
        foreach ($data as $key => $value) {
            if (in_array($key, $denyFields)) {
                $this->generateError('deny', $key, $value, $this->errorTpls['deny']);
                return false;
            }
        }
        return true;
    }

    //必填字段
    public function must($data = null, $mustFields = null)
    {
        $data = isset($data) ? $this->map($data) : $this->data;
        $mustFields = isset($mustFields) ? $this->parseFieldsRule($mustFields) : $this->rules['must'];
        if (!empty($mustFields)) {
            foreach ($mustFields as $field) {
                if (!is_array($data) || !isset($data[$field])) {
                    $this->generateError('must', $field, $data[$field], $this->errorTpls['must']);
                    return false;
                }
            }
        }
        return true;
    }

    //忽略字段
    public function ignore($data = null, $ignoreFields = null)
    {
        $data = isset($data) ? $this->map($data) : $this->data;
        $ignoreFields = isset($ignoreFields) ? $this->parseFieldsRule($ignoreFields) : $this->rules['ignore'];
        foreach ($ignoreFields as $field) {
            list($name, $condition) = explode(':', $field);
            if (empty($condition) || $condition == 'empty') {
                if (is_array($data) && isset($data[$name]) && !validate_regex($data[$name], 'require')) {
                    unset($data[$name]);
                }
            } else if ($condition == 'keep') {
                if (is_array($data) && isset($this->savedData) && isset($data[$name]) && $data[$name] == $this->savedData[$name]) {
                    unset($data[$name]);
                }
            }
        }
        return $data;
    }

    //验证数据
    public function validate($data = null, $validateRule = null)
    {
        $data = isset($data) ? $this->map($data) : $this->data;
        $validateRule = isset($validateRule) ? $validateRule : $this->rules['validate'];
        if (!empty($validateRule)) {
            foreach ($validateRule as $rule) {
                $res = $this->executeRule($rule, $data, $this->savedData, $result);
                $ok = true;
                if ($res) {
                    if (is_string($result)) {
                        $tpl = $result;
                        $ok = false;
                    } else {
                        $ok = $result;
                    }
                    if (!$ok) {
                        $tpl = isset($tpl) ? $tpl : (isset($rule[3]) ? $rule[3] : $this->errorTpls['validate']);
                        $this->generateError('validate', $res['fields'], $res['value'], $tpl);
                        return false;
                    }
                }
            }
        }
        return true;
    }

    //执行通用规则
    protected function executeRule($rule, $data, $savedData, &$result)
    {
        $field = isset($rule[0]) ? $rule[0] : '*';//默认是所有字段
        $fun = $rule[1];//验证方式
        $condition = isset($rule[4]) ? $rule[4] : self::EXISTS;//默认是存在就验证
        $res = $this->validateCondition($data, $savedData, $field, $condition);
        if ($res) {
            $param = array($res['value'], $rule[2], $data, array(
                'df' => &$this,
                'fields' => $res['fields'],
                'savedData' => $this->savedData,
                'table' => $this->rules['table']
            ));
            if (is_array($fun)) {
                $fun = count($fun) == 0 ? array_unshift($fun, $this->callHandle) : $fun;
            } else {
                if (strpos($fun, ':') === 0) {
                    $fun = substr($fun, 1);
                } else if (strpos($fun, '@') === 0) {
                    $fun = array($this->callHandle, substr($fun, 1));
                } else if (!empty($fun)) {
                    //内置方法
                    if (in_array($fun, array())) {
                        $fun = array($this, $fun);
                    } else {
                        $oldFun = $fun;
                        $fun = '\\bxkj_common\\DataFactoryCallback::' . $fun;
                    }
                } else {
                    $this->error = make_error('validate function undefined');
                    return false;
                }
            }
            if (isset($oldFun) && !method_exists((new DataFactoryCallback()), $oldFun)) {
                $this->error = make_error($fun . ' undefined 1');
                return false;
            } else if (is_string($fun) && !isset($oldFun) && !function_exists($fun)) {
                $this->error = make_error($fun . ' undefined 2');
                return false;
            } else if (is_array($fun) && !method_exists($fun[0], $fun[1])) {
                $this->error = make_error($fun[1] . ' undefined 3');
                return false;
            }
            $result = call_user_func_array($fun, $param);
            return $res;
        }
        return false;
    }

    //判断验证条件
    protected function validateCondition($data, $savedData, $field, $condition)
    {
        $alone = false;//单独字段
        $values = array();
        if (empty($field) || $field === '*') {
            $fields = array();
            foreach ($data as $k => $v) {
                $fields[] = $k;
                $values[] = $v;
            }
            return array('value' => $values, 'fields' => $fields);
        }
        if (is_string($field)) {
            $alone = strpos($field, ',') !== false ? false : true;
            $fields = explode(',', $field);
        } else {
            $fields = $field;
        }
        $conditions = is_string($condition) ? explode('|', $condition) : $condition;
        foreach ($conditions as $condition) {
            foreach ($fields as $name) {
                if ($condition !== self::ANY && (
                        ($condition === self::EXISTS && !isset($data[$name])) ||
                        ($condition === self::NOT_EXISTS && isset($data[$name])) ||
                        ($condition === self::NOT_EMPTY && (!isset($data[$name]) || empty($data[$name]))) ||
                        ($condition === self::VALUE_EMPTY && (!isset($data[$name]) || !empty($data[$name]))) ||
                        ($condition === self::NOT_HAS && !empty($data[$name])) ||
                        ($condition === self::VALUE_CHANGE && $data[$name] == $savedData[$name]) ||
                        ($condition == self::VALUE_KEEP && $data[$name] != $savedData[$name])
                    )) {
                    return false;
                }
                if ($alone) return array('value' => $data[$name], 'fields' => $name);
                if (isset($data[$name])) {
                    $values[] = $data[$name];
                }
            }
        }
        return array('value' => $values, 'fields' => $fields);
    }

    //填充数据
    public function fill($data = null, $fillRule = null)
    {
        $data = isset($data) ? $this->map($data) : $this->data;
        $fillRule = isset($fillRule) ? $fillRule : $this->rules['fill'];
        foreach ($fillRule as $rule) {
            //填充少一个参数
            $rule[4] = $rule[3];
            $rule[3] = '';
            $res = $this->executeRule($rule, $data, $this->savedData, $result);
            if ($res) {
                $merge = array();//关联数组直接合并
                $arr = array();//索引数组按字段位置赋值
                if (isset($result)) {
                    $result = is_array($result) ? $result : array($result);
                    foreach ($result as $key => $val) {
                        if (is_string($key)) $merge[$key] = $val;
                        if (is_int($key)) $arr[] = $val;
                    }
                    $fields = is_array($res['fields']) ? $res['fields'] : array($res['fields']);
                    for ($i = 0; $i < count($fields); $i++) {
                        if (isset($arr[$i]))
                            $data[$fields[$i]] = $arr[$i];
                    }
                    $data = array_merge($data, $merge);
                }
            }
        }
        return $data;
    }

    //输出处理结果
    public function output($exportFields = null)
    {
        if ($this->error) return false;
        return $exportFields === false ? $this->data : ($this->export(null, $exportFields));//false 返回处理后的完整数据
    }

    //按照数据库字段导出数据
    public function export($data = null, $exportFields = null)
    {
        $data = isset($data) ? $this->map($data) : $this->data;
        if (empty($this->rules['table']) && !isset($exportFields)) {
            return $data;
        }
        $exportFields = isset($exportFields) ? $this->parseFieldsRule($exportFields) : $this->getFields();
        $exportData = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $exportFields)) {
                $exportData[$key] = $value;
            }
        }
        return $exportData;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function getFields()
    {
        if (empty($this->rules) || empty($this->rules['table'])) return array();
        return $this->rules['table']['fields'];
    }

    //获取字段别名（不存在则返回字段名）
    public function getAlias($name)
    {
        $alias = isset($this->rules['alias']) ? $this->rules['alias'] : [];
        return isset($alias[$name]) ? $alias[$name] : $name;
    }

    //获取规则
    public function getRules($ruleName)
    {
        $ruleData = is_array($ruleName) ? $ruleName : $this->loadRules($ruleName);
        if ($ruleData === false) return false;
        $ruleArr = $this->parseRules($ruleData);
        return $ruleArr;
    }

    //通过规则名称加载规则数组
    protected function loadRules($ruleName)
    {
        $ruleNameArr = explode('.', $ruleName);//文件名.规则名
        $ruleNameArr2 = explode('@', $ruleName);//规则名@表名

        $fileName = $ruleNameArr[0];
        $ruleKey = isset($ruleNameArr[1]) ? $ruleNameArr[1] : '';
        if (empty($ruleKey)) {
            $ruleKey = $fileName;
            $fileName = $this->defaultRuleFileName;
        }
        if (!isset(self::$preRules[$fileName]))
        {
            $rule = config('rules.');

            self::$preRules[$fileName] = $rule;
        }

        self::$preRules[$fileName] = isset(self::$preRules[$fileName]) ? self::$preRules[$fileName] : array();
        if (!isset(self::$preRules[$fileName][$ruleKey])) {
            $this->error = make_error('rule:' . $ruleName . ' not exist');
            return false;
        }
        $tmp = self::$preRules[$fileName][$ruleKey];
        if (!empty($ruleNameArr2[1]) && empty($tmp['table'])) {
            $tmp['table'] = $ruleNameArr2[1];
        }
        return $tmp;
    }

    //解析规则
    protected function parseRules($ruleData)
    {
        //解析继承
        if (!empty($ruleData['extends'])) {
            $extendsArr = is_array($ruleData['extends']) ? $ruleData['extends'] : explode(',', $ruleData['extends']);
            $baseArr = $this->getRules($extendsArr[0]);
            for ($i = 1; $i < count($extendsArr); $i++) {
                $tmpArr = $this->getRules($extendsArr[$i]);
                $baseArr = $this->mergeRules($baseArr, $tmpArr);
            }
        }
        if (!empty($ruleData['table'])) $ruleData['table'] = $this->parseTableRule($ruleData['table']);
        $fields = (isset($ruleData['table']) && isset($ruleData['table']['fields'])) ? $ruleData['table']['fields'] : array();
        $ruleData['deny'] = $this->parseDenyRule(array($ruleData['deny'], $ruleData['allow']), $fields);
        if (isset($ruleData['allow'])) unset($ruleData['allow']);
        $tmp = [];
        foreach ($ruleData as $key => $value) {
            $key = strtolower($key);
            if (in_array($key, array('deny', 'must', 'ignore'))) {
                $tmp[$key] = $this->parseFieldsRule($value);
            } else if (in_array($key, array('table', 'validate', 'fill'))) {
                $tmp[$key] = $value;
            } else if ($key == 'map' || $key == 'alias') {
                $tmp[$key] = $this->parseMapRule($value);
            }
        }
        if (!empty($baseArr)) $tmp = $this->mergeRules($baseArr, $tmp);
        return $tmp;
    }

    //合并规则
    protected function mergeRules($rules1, $rules2)
    {
        foreach ($rules2 as $key => $value) {
            if ($key == 'table') {
                $rules1['table'] = $value;
            } else if (in_array($key, array('validate', 'fill', 'must', 'ignore', 'map', 'deny', 'alias'))) {
                if (isset($rules1[$key])) {
                    foreach ($value as $k => $v) {
                        if (is_int($k)) {
                            if (!in_array($v, $rules1[$key])) $rules1[$key][] = $v;
                        } else {
                            $rules1[$key][$k] = $v;
                        }
                    }
                } else {
                    $rules1[$key] = $value;
                }
            }
        }
        return $rules1;
    }

    //解析表规则
    protected function parseTableRule($table)
    {
        if (is_string($table)) $table = $this->getTableStructure($table);
        if (isset($table['pks'])) $table['pks'] = $this->parseFieldsRule($table['pks']);
        if (isset($table['fields'])) $table['fields'] = $this->parseFieldsRule($table['fields']);
        return $table;
    }

    //解析map规则
    protected function parseMapRule($rule)
    {
        if (empty($rule)) return array();
        $map = array();
        if (is_string($rule)) {
            $arr = explode(',', $rule);
            foreach ($arr as $value) {
                list($real, $false) = preg_split('/\s+/', $value);
                $map[$real] = $false;
            }
        } else if (is_array($rule)) {
            foreach ($rule as $key => $value) {
                if (is_int($key)) {
                    list($real, $false) = preg_split('/\s+/', $value);
                    $map[$real] = $false;
                } else {
                    $map[$key] = $value;
                }
            }
        }
        return $map;
    }

    //解析拒绝规则
    protected function parseDenyRule($rule, $tableFields = array())
    {
        $fields = array();
        $denyArr = is_string($rule) ? $this->parseFieldsRule($rule) : (isset($rule[0]) ? $this->parseFieldsRule($rule[0]) : null);
        $allowArr = is_string($rule) ? null : (isset($rule[1]) ? $this->parseFieldsRule($rule[1]) : null);
        if (isset($denyArr)) {
            $fields = array_merge($fields, $denyArr);
        }
        if (isset($allowArr)) {
            foreach ($tableFields as $name) {
                if (!in_array($name, $allowArr)) $fields[] = $name;
            }
        }
        return $fields;
    }

    //解析字符串类型的字段列表
    protected function parseFieldsRule($tmp)
    {
        $tmp = is_string($tmp) ? explode(',', $tmp) : $tmp;
        $fields = array();
        foreach ($tmp as $value) {
            if (!empty($value)) $fields[] = $value;
        }
        return $fields;
    }

    //获取已保存数据
    protected function getSavedData($where)
    {
        if (empty($this->rules['table']) || empty($this->rules['table']['name'])) return array();
        $result = Db::name($this->rules['table']['name'])->where($where)->find();
        return $result ? $result : array();
    }

    //获取已保存数据的查询条件数组
    public function getWhereByPks($data = null, $pks = null)
    {
        $data = isset($data) ? $data : $this->data;
        $where = array();
        $pks2 = $this->rules['table']['pks'] ? $this->rules['table']['pks'] : array();
        $pks = isset($pks) ? (is_string($pks) ? explode(',', $pks) : $pks) : $pks2;
        foreach ($pks as $key) {
            if (!empty($data[$key])) {
                $where[$key] = $data[$key];
            }
        }
        return $where;
    }

    public function hasDeleteTime()
    {
        $fields = $this->rules['table']['fields'] ? $this->rules['table']['fields'] : array();
        return in_array('delete_time', $fields);
    }

    //生成错误信息
    protected function generateError($type, $fields, $value, $tpl)
    {
        if (is_array($fields)) {
            foreach ($fields as $index => $value) {
                $fields[$index] = $this->getAlias($value);
            }
        }
        $fields = is_array($fields) ? implode(',', $fields) : $this->getAlias($fields);
        $value = is_array($value) ? implode(',', $value) : $value;
        $vals = array('fields' => $fields, 'value' => $value);
        foreach ($vals as $key => $value) {
            $tpl = preg_replace('/\{\$' . $key . '\}/', $value, $tpl);
        }
        $tpl = preg_replace('/\{\$.+\}/U', '', $tpl);
        $this->error = make_error($tpl);
    }

    //获取表结构
    protected function getTableStructure($tableName)
    {
        $key = "table:{$tableName}";
        $arr = cache($key);
        $appDebug = false;
        //$appDebug = config('app.app_debug');
        if (empty($arr) || $appDebug||true) {
            $prefix = config('database.prefix');
            $result = Db::query('DESC `' . $prefix . $tableName . '`');
            if (!$result) return false;
            $pks = array();
            $fields = array();
            foreach ($result as $item) {
                if ($item['Key'] == 'PRI') {
                    $pks[] = $item['Field'];
                }
                $fields[] = $item['Field'];
            }
            $arr = array(
                'name' => $tableName,
                'pks' => $pks,
                'fields' => $fields
            );
            cache($key, $arr);
        }
        return $arr;
    }

    //获取最新的表数据
    public function getLastData($merge = true)
    {
        $saved = isset($this->savedData) ? $this->savedData : array();
        if ($merge) $saved = array_merge($saved, $this->data);
        return $saved;
    }

}