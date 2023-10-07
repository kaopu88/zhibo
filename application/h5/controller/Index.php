<?php

namespace app\h5\controller;

use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        $this->assign('product_logo', config('app.product_setting.logo'));
        $this->assign('product_name', config('app.product_setting.name'));
        $this->assign('prefix_name', APP_PREFIX_NAME);
        $this->assign('bean_name', APP_BEAN_NAME);
        $this->assign('slogan', config('app.product_setting.slogan'));

        return $this->fetch();
    }

    public function webview_test()
    {
        return $this->fetch();
    }

    public function etest(){
        for($i=1;$i<6;$i++){
           $this->test();
        }
    }
    public  function test(){
        $redis = RedisClient::getInstance();
        $lockKey = "lock:group_id_15" ;
        $status = true;
        while ($status) {
            $value = time() + 2;
            $lock = $redis->setnx($lockKey, $value);
//                    $redis->setnx($lockKey.$pintuan_order_info['group_id'], $order['order_id']);
            var_dump($lock);
            var_dump(!empty($lock));
            if ($lock || ($redis->get($lockKey) < time() && $redis->getSet($lockKey, $value) < time())) {
                //给锁设置生存时间
                $redis->expire($lockKey, 1);
                //加入组
                $res = 0;
                if ($res == 0) {
                    if ($redis->ttl($lockKey))
                        $redis->del($lockKey);

                    $status = false;
                }

            } else {
                /**
                 * 如果存在有效锁这里做相应处理
                 *      等待当前操作完成再执行此次请求
                 *      直接返回
                 */
                usleep(2);//等待2秒后再尝试执行操作
            }
        }
    }
}