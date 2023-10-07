<?php

namespace app\common\behavior;

use app\common\service\DsSession;

class ApiEnd
{
    public function run()
    {
        DsSession::save();
    }
}