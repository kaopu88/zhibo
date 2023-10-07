<?php


namespace app\common\service;

class Validate
{
    use Callback;

    protected $rule = [
    ];

    protected $message = [
    ];

    protected $error = [];

    protected static $default_message = [

        'neq' => ':attribute不能等于{$neq}',
        'egt'         => ':attribute必须大于等于 {$egt}',
        'gt'          => ':attribute必须大于 {$gt}',
        'elt'         => ':attribute必须小于等于 {$elt}',
        'lt'          => ':attribute必须小于 {$lt}',
        'eq'          => ':attribute必须等于 {$eq}',
        'boolean'     => ':attribute必须是布尔值',
        'exists' => ':attribute存在',
        'require'     => ':attribute不能为空',
        'number'      => ':attribute必须是数字',
        'integer'     => ':attribute必须是整数',
        'float'       => ':attribute必须是浮点数',
        'email'       => ':attribute格式不符',
        'array'       => ':attribute必须是数组',
        'accepted'    => ':attribute必须是yes、on或者1',
        'date'        => ':attribute格式不符合',
        'file'        => ':attribute不是有效的上传文件',
        'image'       => ':attribute不是有效的图像文件',
        'alpha'       => ':attribute只能是字母',
        'alphaNum'    => ':attribute只能是字母和数字',
        'alphaDash'   => ':attribute只能是字母、数字和下划线_及破折号-',
        'activeUrl'   => ':attribute不是有效的域名或者IP',
        'chs'         => ':attribute只能是汉字',
        'chsAlpha'    => ':attribute只能是汉字、字母',
        'chsAlphaNum' => ':attribute只能是汉字、字母和数字',
        'chsDash'     => ':attribute只能是汉字、字母、数字和下划线_及破折号-',
        'url'         => ':attribute不是有效的URL地址',
        'ip'          => ':attribute不是有效的IP地址',
        'dateFormat'  => ':attribute必须使用日期格式 :rule',
        'in'          => ':attribute必须在 :rule 范围内',
        'notIn'       => ':attribute不能在 :rule 范围内',
        'between'     => ':attribute只能在 :1 - :2 之间',
        'notBetween'  => ':attribute不能在 :1 - :2 之间',
        'length'      => ':attribute长度不符合要求 :rule',
        'max'         => ':attribute长度不能超过 :rule',
        'min'         => ':attribute长度不能小于 :rule',
        'after'       => ':attribute日期不能小于 :rule',
        'before'      => ':attribute日期不能超过 :rule',
        'expire'      => '不在有效期内 :rule',
        'allowIp'     => '不允许的IP访问',
        'denyIp'      => '禁止的IP访问',
        'confirm'     => ':attribute和确认字段:2不一致',
        'different'   => ':attribute和比较字段:2不能相同',
        'unique'      => ':attribute已存在',
        'regex'       => ':attribute不符合指定规则',
        'method'      => '无效的请求类型',
        'token'       => '令牌数据无效',
        'fileSize'    => '上传文件大小不符',
        'fileExt'     => '上传文件后缀不符',
        'fileMime'    => '上传文件类型不符',
    ];

    private $is_bath = false;

    protected function parseRule($rules)
    {
        $rule_all = strpos($rules['rule'], '|') !== false ? explode('|', $rules['rule']) : [$rules['rule']];

        $params = isset($rules['params']) ? $rules['params'] : '';

        foreach ($rule_all as $rule)
        {
            list($class, $condition) = strpos($rule, ':') !== false ? explode(':', $rule) : [$rule, ''];

            strpos($condition, ',') !== false &&  $condition = explode(',', $condition);

            $rule_tree = ['class'=>$class, 'condition'=>$condition, 'params' => $params];

            yield $rule_tree;
        }
    }


    protected function parseDefaultMessage($field, $rule_class, $condition)
    {
        $meg_key = $field.'.'.$rule_class;

        if (array_key_exists($meg_key, $this->message))
        {
            return $this->replaceMessageVar($this->message[$meg_key], $condition);
        }

        if (array_key_exists($rule_class, self::$default_message))
        {
            $msg = self::$default_message[$rule_class];

            return str_replace('{$'.$rule_class.'}', $condition, $msg);
        }

        return "变量{$field}验证方法{$rule_class}未通过";
    }


    protected function replaceMessageVar($msg, $replace_val)
    {
        return strpos($msg, '{$') !== false ? preg_replace('/\{\$.*\}/', $replace_val, $msg) : $msg;
    }


    protected function parseMessage($field, $rule_class, $condition, $error_msg)
    {
        if ($error_msg === null)
        {
            return $this->parseDefaultMessage($field, $rule_class, $condition);
        }
        else{

            $message = strpos($error_msg, '|') !== false ? explode('|', $error_msg) : [$error_msg];

            foreach ($message as $e_msg)
            {
                if (strpos($e_msg, $rule_class.':') !== false)
                {
                    list(, $msg) = explode(':', $e_msg);

                    return $this->replaceMessageVar($msg, $condition);
                }
                elseif (strpos($e_msg, ':') === false)
                {
                    return $this->replaceMessageVar($e_msg, $condition);
                }
                /*else{
                    return $this->parseDefaultMessage($field, $rule_class, $condition);
                }*/
            }
        }
    }


    protected function parseErrorCode($field, $rule_class, $error_code)
    {

        return $error_code ? $error_code : 1;
    }


    protected function setError($field, $error_msg, $error_code=2000)
    {
        $this->error[$field] = [
            'error_msg' => $error_msg,
            'error_code' => $error_code,
        ];

        return true;
    }


    public function check(array &$params, $rules=[])
    {
        $rule_all = array_merge($this->rule, $rules);

        if (empty($rule_all)) return $this->setError('all', '未定义校验规则!');

        foreach ($rule_all as $field => $rule)
        {
            if (isset($rule['status']) && !$rule['status']) continue;

            if (is_array($rule))
            {
                if (!array_key_exists('rule', $rule)) return $this->setError('all', '校验参数不完整，请重新提交!');
            }
            else{
                $rule = ['rule'=>$rule];
            }

            $fields = strpos($field, '|') !== false ? explode('|', $field) : [$field];
            foreach ($fields as $field_name)
            {
                if (!array_key_exists($field_name, $params)){
                    // var_dump($field_name);die;
                    return $this->setError('all', '校验规则定义错误!');
                } 

                if ($this->validateItem($field_name, $params[$field_name], $rule)) return true;
            }
        }

        return false;
    }


    protected function validateItem($field, &$data, $rules)
    {
        foreach ($this->parseRule($rules) as $rule)
        {
            if (!method_exists($this, $rule['class'])) return $this->setError($field, "未定义验证方法'{$rule['class']}'");

            if ($this->{$rule['class']}($field, $data, $rule['condition'], $rule['params']))
//            if (!call_user_func_array([$this, $rule['class']], [$field, $data, $rule['condition'], $rule['params']]))
            {
                $msg = isset($rules['error_msg']) ? $rules['error_msg'] : null;

                $error_msg = $this->parseMessage($field, $rule['class'], $rule['condition'], $msg);

                $error_code = $this->parseErrorCode($field, $rule['class'], $rules['error_code']);

                return $this->setError($field, $error_msg, $error_code);
            }
        }

        return false;
    }


    public function getError()
    {
        return $this->error;
    }

}


trait Callback
{

    public function lt($field, $data, $condition)
    {
        return $data < $condition;
    }


    public function elt($field, $data, $condition)
    {
        return $data <= $condition;
    }


    public function notEmpty($field, $data, $condition)
    {
        return empty(trim($data));
    }


    public function filmSizeElt($field, $data, &$condition)
    {
        if (empty($data)) return true;

        $is_elt = $data <= $condition;

        if (!$is_elt)
        {
            $units = array('B', 'KB', 'MB');

            for ($i = 0; $condition >= 1024 && $i < 2; $i++){$condition /= 1024;}

            $condition = round($condition, 2) . $units[$i];
        }

        return $is_elt;
    }


    public function filmFormat($field, $data, &$condition)
    {
        $filmSuffix = pathinfo($data, PATHINFO_EXTENSION);

        $exists = in_array($filmSuffix, $condition) ? true : false;

        $exists || $condition = implode(',', $condition);

        return $exists;
    }


    public function filmTimeElt($field, $data, &$condition)
    {
        if (empty($data)) return true;

        $is_elt = $data <= $condition;

        if (!$is_elt)
        {
            if ($condition < 3600)
            {
                $condition = gmdate('i:s', $condition);
            }
            else if ($condition < 86400)
            {
                $condition = gmdate('h:i:s', $condition);
            }
        }
        return $is_elt;
    }


    public function gt($field, $data, $condition)
    {
        return $data > $condition;
    }


    public function egt($field, $data, $condition)
    {
        return $data >= $condition;
    }


    public function isFalse($field, $data, $condition)
    {
        return boolval($data) === false;
    }

    public function boolean($field, $data, $condition)
    {
        return boolval($data);
    }


    //本地处理未启用
    public function sensitive_bak($field, &$data, &$condition, $params)
    {
        $sensitive = Sensitive::init();

        if (0 === strcasecmp('file', $params['set_tree_mode']))
        {
            $sensitive->setTreeByFile($params['set_tree_path']);
        }
        else{
            $sensitive->setTreeByArray();
        }

        if (0 === strcasecmp('replace', $params['filter_mode']))
        {
            $data = $sensitive->replace($data, $params['filter_word']);

            return false;
        }
        else{

            $isBadWord = $sensitive->islegal($data);

            if ($isBadWord)
            {
                $badWord = $sensitive->getLegalBadWord();

                $badWord = implode(',', $badWord);

                $length = mb_strlen($badWord, 'utf-8');

                if ($length > 5)
                {
                    $badWord = mb_substr($badWord, 0, 5, 'utf-8');

                    $badWord = "'$badWord'等";
                }

                $condition = $badWord;
            }

            return $isBadWord;
        }
    }


    //远程处理
    public function sensitive($field, &$data, &$condition)
    {
        $res = file_get_contents(CORE_URL.'/filter/check?content='.urlencode($data));
		$res = json_decode($res, true);
        if (empty($res['data'])) return false;

        $length = mb_strlen($res['data'][0], 'utf-8');

        if ($length > 5)
        {
            $badWord = mb_substr($res['data'][0], 0, 5, 'utf-8');

            $res['data'][0] = $badWord.'等';
        }

        $condition = $res['data'][0];

        return true;
    }


    public function str_gt($field, $data, $condition)
    {
        $num = mb_strlen($data);

        return $num > $condition;
    }


    public function str_length($field, $data, &$condition)
    {
        $num = mb_strlen($data);

        if ($num < $condition[0] || $num > $condition[1])
        {
            $condition = implode(',', $condition);

            return true;
        }

        return false;
    }

}