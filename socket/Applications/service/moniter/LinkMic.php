<?php

namespace app\service\moniter;

use app\api\LinkMic as LinkMicBase;

class LinkMic
{

    public static function run(array $params)
    {
        if (!isset($params['method'])) $params['method'] = 'close';

        $params['method'] == 'exitRoom' ? LinkMicBase::endLinkMicByUser($params) : LinkMicBase::endLinkMicByAnchor($params);

        return true;
    }

}