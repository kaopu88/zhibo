<?php

namespace app\core\model;

use think\Model;

class UserSetting extends Model
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}