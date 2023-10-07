<?php

namespace bxkj_recommend\model;

use bxkj_recommend\Calc;
use bxkj_recommend\VideoUpdater;

class UserTagScore extends TagScore
{
    //评估视频总分
    public function evaluate()
    {
        $total = 0;
        $now = time();
        foreach ($this->proportion as $range => $arr) {
            if (Calc::validateRange($timeline, $range)) {
                foreach ($arr as $key => $tmpArr) {
                    $pro = $tmpArr['pro'];
                    $funName = 'get' . parse_name($key, 1, true) . 'Score';
                    $score = call_user_func_array([$this, $funName], [$tmpArr]);
                    $total += round($score * $pro);
                }
                break;
            }
        }
        return $total;
    }
}