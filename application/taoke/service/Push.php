<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/31
 * Time: 15:32
 */
namespace app\taoke\service;

use app\admin\service\SysConfig;
use bxkj_module\service\Service;
use bxkj_push\AomyPush;

class Push extends Service
{
    public function send($type="buy", $userId, $data)
    {
        $config = new SysConfig();
        $template = $config->getConfig("push_template");
        $template = json_decode($template['value'], true);
        if($type == "buy") {//用户自购
            $title = $template['buySelf_title'];
            $content = $template['buySelf_content'];
//            $image = $template['buySelf_image'];
        }elseif ($type == "fansBuy"){//粉丝购买
            $title = $template['buyFans_title'];
            $content = $template['buyFans_content'];
//            $image = $template['buyFans_image'];
        }elseif ($type == "fansLevelUp"){//粉丝升级
            $title = $template['upgrade_title'];
            $content = $template['upgrade_content'];
//            $image = $template['upgrade_image'];
        }elseif ($type == "withdraw"){//提现
            $title = $template['withdraw_title'];
            $content = $template['withdraw_content'];
//            $image = $template['withdraw_image'];
        }
        $orderType = isset($data['orderType']) ? $data['orderType'] : 'C';
        if($orderType == "B"){
            $orderType = "天猫";
        }elseif ($orderType == "C"){
            $orderType = "淘宝";
        }elseif ($orderType == "P"){
            $orderType = "拼多多";
        }elseif ($orderType == "J"){
            $orderType = "京东";
        }

        $templateArr = [
            '/{APPNAME}/' => config('product.product_setting.name'),
            '/{NAME}/' => $data['username'],
            '/{TIME}/' => date('Y-m-d H:i:s', isset($data['time']) ? $data['time'] : time()),
            '/{COMMISSION}/' => isset($data['commission']) ? $data['commission'] : '0',
            '/{GOODNAME}/' => isset($data['goods_name']) ? $data['goods_name'] : '',
            '/{FANSNAME}/' => isset($data['nickname']) ? $data['nickname'] : '',
            '/{ORDERTYPE}/' => $orderType,
            '/{ORDER}/' => isset($data['orderId']) ? $data['orderId'] : '',
            '/{MONEY}/' => isset($data['money']) ? $data['money'] : '0',
            '/{ORDERMONEY}/' => isset($data['orderPrice']) ? $data['orderPrice'] : '0'
        ];
        preg_match_all('/\\{([A-Z]+?)\\}/', $content, $matches);
        $content = preg_replace(array_keys($templateArr), array_values($templateArr), $content);

        $AomyPush = new AomyPush();
        $AomyPush->setUser(array('user_id' => $userId))->allTo(array(
            'title' => $title,
            'text' => $content,
            'after_open' => 'go_app',
            'custom' => array(
                'header' => 'url',
                'url' => getJump('taoke', ['type' => $type])
            )
        ));
    }
}