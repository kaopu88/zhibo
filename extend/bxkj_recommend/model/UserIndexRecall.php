<?php

namespace bxkj_recommend\model;

use bxkj_recommend\ProDb;
use bxkj_recommend\ProRedis;
use think\Db;
use bxkj_recommend\exception\Exception;

class UserIndexRecall extends Model
{
    public function __construct(User &$user)
    {
        parent::__construct();
    }

    //开始召回
    public function start()
    {
    }

    public function getRecallNum()
    {
        return 1;
    }
}