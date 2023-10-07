<?php

namespace bxkj_recommend\behavior;

use bxkj_recommend\Base;
use bxkj_recommend\model\User;

class Behavior extends Base
{
    protected $user;

    public function __construct(User &$user)
    {
        parent::__construct();
        $this->user = &$user;
    }
}