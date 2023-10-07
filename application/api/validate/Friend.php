<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/15
 * Time: 上午 11:51
 */

namespace app\api\validate;

use think\Validate;

class Friend extends Validate
{
    protected $rule = [
        'type'          => 'require|number',
        'msg_type'      => 'require|number',
        'img'           => 'checkIMgs:图片后缀不正确！',
        'video'         => 'checkVideo:视频后缀不正确！',
        'voice'         => 'checkVoice:声频后缀不正确！',
        'location'      => 'require',
        'content'       => 'max:5000',
        'status'        => 'require|number',
        'fcmid'         => 'require|number',
        'filter_id'     => 'require|number',
        'filter_type'   => 'require|in:1,2',
        'msgTpye'       => 'require|number',
        'filter_msg_id' => 'number',
        'report_msg_id' => 'number',
        'report_img'    => 'checkIMgs:图片后缀不正确！',
        'report_msg'    => 'max:100',
        'room_id'       => 'require|number',
        'goods_id'      => 'require|number',
        'goods_type'    => 'require|number',
        'at_id'         => 'require|number',
        'location'      => 'require',
        'msg_id'        => 'require|number',
        'sing_title'    => 'checkSingTitle:接唱歌名不能为空',
        'sing_author'    => 'checkSingAuthor:接唱作者不能为空',
        'user_id'       => 'require|number',

    ];
    protected $message = [
        'type.require'       => '发布类型不能为空',
        'type.number'        => '发布类型必须为数字',
        'msg_type.require'   => '接收人类型不能为空',
        'msg_type.number'    => '接收人类型必须为数字',
        'location.require'   => '定位信息不能为空',
        'content.max'        => '最大长度不能超过5000',
        'status.require'     => '点赞状态不能为空',
        'status.number'      => '点赞状态必须为数字',
        'fcmid.require'      => '关联信息id不能为空',
        'fcmid.number'       => '关联信息id必须为数字',
        'report_msg.require' => '举报信息内容不能为空',
        'room_id.require'    => '直播间id不能为空',
        'room_id.number'     => '直播间id必须为数字',
        'goods_id.require'   => '商品id不能为空',
        'goods_id.number'    => '商品id必须为数字',
        'goods_type.require' => '标识id不能为空',
        'goods_type.number'  => '标识id必须为数字',
        'msg_id.require'     => '删除id不能为空',
        'msg_id.number'      => '删除id必须为数字',
        'user_id.require'     => '用户id不能为空',
        'user_id.number'      => '用户id必须为数字',
    ];
    protected $scene = [
        'subMsg'         => ['type', 'msg_type', 'img', 'video', 'voice','content'],
        'getMsg'         => ['type'],
        'msg_live'       => ['fcmid'],
        'setFilter'      => ['filter_id', 'filter_type', 'msgTpye', 'filter_msg_id'],
        'report'         => ['report_msg_id', 'report_img', 'report_msg'],
        'getLiveRoomMsg' => ['room_id'],
        'getMsgGoods'    => ['goods_id', 'goods_type'],
        'atUserMsg'      => ['at_id'],
        'nearbyMessage'  => ['location'],
        'delMessage'     => ['msg_id'],
        'msgDetail'      => ['msg_id'],
        'mySender'       => ['user_id'],
    ];

    protected function checkIMgs($value, $rule, $data)
    {
        $val2Array = explode(',', $value);
        foreach ($val2Array as $k => $v) {
            if (!preg_match('/.*?(jpg|jpeg|gif|png)/', $v)) {
                return $rule;
            }
        }
        return true;
    }

    protected function checkVideo($value, $rule, $data)
    {
        $val2Array = explode(',', $value);
        foreach ($val2Array as $k => $v) {
            if (!preg_match('/.*?(avi|rmvb|rm|mp4|flv|mpg)/', $v)) {
                return $rule;
            }
        }
        return true;
    }

    protected function checkVoice($value, $rule, $data)
    {
        $val2Array = explode(',', $value);
        foreach ($val2Array as $k => $v) {
            if (!preg_match('/.*?(mp3|amr)/', $v)) {
                return $rule;
            }
        }
        return true;
    }


}