<?php

namespace app\api\controller;

use app\common\behavior\ResponseSend;
use app\common\controller\UserController;
use bxkj_common\RabbitMqChannel;
use app\common\service\Message as MessageModel;
use think\Db;

class Message extends UserController
{
    protected $catList = [];

    public function __construct()
    {
        parent::__construct();
        $this->catList = [
            [
                'cat_type'       => 'like',
                'title'          => '点赞',
                'unread'         => 0,
                'summary'        => '',
                'icon'           => DOMAIN_URL . '/static/common/image/message/digg.png',
                'url'            => getJump('message_list', ['cat_type' => 'like']),
                'last_send_time' => ''
            ],
            [
                'cat_type'       => 'at',
                'title'          => '@',
                'unread'         => 0,
                'summary'        => '',
                'icon'           => DOMAIN_URL . '/static/common/image/message/at.png',
                'url'            => getJump('message_list', ['cat_type' => 'at']),
                'last_send_time' => ''
            ],
            [
                'cat_type'       => 'follow',
                'title'          => '粉丝',
                'unread'         => 0,
                'summary'        => '',
                'icon'           => DOMAIN_URL . '/static/common/image/message/fans.png',
                'url'            => getJump('message_list', ['cat_type' => 'follow']),
                'last_send_time' => ''
            ],
            [
                'cat_type'       => 'comment',
                'title'          => '评论',
                'unread'         => 0,
                'summary'        => '',
                'icon'           => DOMAIN_URL . '/static/common/image/message/comment.png',
                'url'            => getJump('message_list', ['cat_type' => 'comment']),
                'last_send_time' => ''
            ],
            [
                'cat_type'       => 'service',
                'title'          => '官方客服',
                'unread'         => 0,
                'summary'        => '解答' . APP_NAME . '中遇到的使用问题',
                'icon'           => DOMAIN_URL . '/static/common/image/message/service.png',
                'url'            => getJump('message_list', ['cat_type' => 'service']),
                'last_send_time' => ''
            ],
            [
                'cat_type'       => 'system',
                'title'          => '系统通知',
                'unread'         => 0,
                'summary'        => '系统通知的消息',
                'icon'           => DOMAIN_URL . '/static/common/image/message/system_notice.png',
                'url'            => getJump('message_list', ['cat_type' => 'system']),
                'last_send_time' => ''
            ],
            [
                'cat_type'       => 'reward',
                'title'          => '打赏我的',
                'unread'         => 0,
                'summary'        => '打赏我所发布的视频消息',
                'icon'           => DOMAIN_URL . '/static/common/image/message/reward.png',
                'url'            => getJump('message_list', ['cat_type' => 'reward']),
                'last_send_time' => ''
            ],
        ];

        if(input('have_shopMall', 0) == 1){//有商城模块时是追加
            $shopMessageNav = [
                [
                    'cat_type'       => 'system_shop_push',
                    'title'          => '账户通知',
                    'unread'         => 0,
                    'summary'        => '账户通知的消息',
                    'icon'           => DOMAIN_URL . '/static/common/image/message/shop_notice.png',
                    'url'            => getJump('message_list', ['cat_type' => 'system_shop_push']),
                    'last_send_time' => ''
                ],
                [
                    'cat_type'       => 'delivery',
                    'title'          => '交易物流通知',
                    'unread'         => 0,
                    'summary'        => '交易物流通知的消息',
                    'icon'           => DOMAIN_URL . '/static/common/image/message/delivery.png',
                    'url'            => getJump('message_list', ['cat_type' => 'shop_system_push']),
                    'last_send_time' => ''
                ]
            ];
            $this->catList = array_merge($this->catList, $shopMessageNav);
        }
    }

    public function index()
    {
        $messages   = $this->catList;

        $msgModel   = new MessageModel();
        $createTime = preg_match('/\D/', trim($this->user['create_time'])) ? strtotime($this->user['create_time']) : $this->user['create_time'];
        $createTime = $createTime ?: '1580109635';
        foreach ($messages as &$message) {
            if ($message['cat_type'] != 'service') {
                $num  = $msgModel->getUnreadTotalCache($message['cat_type'], USERID, 'all', $createTime);
                $last = $msgModel->getLastMessage($message['cat_type'], USERID, $createTime);
                if ($last) {
                    $message['summary']        = $last['alt'];
                    $message['last_send_time'] = $last['time_detail'];
                }
                $num               = $num < 0 ? 0 : $num;
                $message['unread'] = (int)$num;
            }
        }
        return $this->success($messages, '获取成功');
    }

    public function getList()
    {
        $params            = input();
        $params['user_id'] = USERID;
        $msgModel          = new MessageModel();
        $offset            = isset($params['offset']) ? $params['offset'] : 0;
        $length            = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $exists            = false;
        if (empty($params['cat_type'])) return $this->jsonError('请选择消息类型');
        ResponseSend::$dataType = $params['cat_type'];
        foreach ($this->catList as $item) {
            if ($item['cat_type'] == $params['cat_type']) {
                $exists = true;
                break;
            }
        }
        $exists = $params['cat_type'] == 'reward' ? true : $exists;
        if (!$exists) return $this->jsonError('消息类型不正确');
        if($params['cat_type'] == "delivery"){
            $params['cat_type'] = 'system_shop_push';
            $params['type'] = 'order_delivery,order_take_delivery';
        }
        $createTime         = preg_match('/\D/', trim($this->user['create_time'])) ? strtotime($this->user['create_time']) : $this->user['create_time'];
        $params['reg_time'] = $createTime;
        $result             = $msgModel->getList($params, $offset, $length);
        return $this->success($result ? $result : [], '获取成功');
    }

    public function read()
    {
        $params = input();
        if (empty($params['id'])) return $this->jsonError('请选择消息ID');
        if (empty($params['type'])) return $this->jsonError('请选择消息类型');
        $msgModel = new MessageModel();
        if ($params['type'] == 'push') {
            $num = $msgModel->readPush(USERID, 'all', null, $params['id']);
        } else if ($params['type'] == 'reward_gift') {
            $num = $msgModel->readGiftMsg(USERID, $params['id']);
        } else {
            $num = $msgModel->read(USERID, $params['id']);
        }
        return $this->success(array('read_num' => $num), '阅读完成');
    }

    public function clearUnread()
    {
        $createTime = preg_match('/\D/', trim($this->user['create_time'])) ? strtotime($this->user['create_time']) : $this->user['create_time'];
        $msgModel   = new MessageModel();
        $num        = $msgModel->readPush(USERID, 'all', $createTime);
        $num2       = $msgModel->read(USERID);
        $num3       = $msgModel->readGiftMsg(USERID);
        return $this->success(array('read_num' => $num + $num2 + $num3), '清空成功');
    }

    public function getUnreadTotal()
    {
        $createTime = preg_match('/\D/', trim($this->user['create_time'])) ? strtotime($this->user['create_time']) : $this->user['create_time'];
        $msgModel   = new MessageModel();
        $total      = $msgModel->getUnreadTotalCache('', USERID, 'all', $createTime);
        return $this->success(['unread_total' => $total], '获取成功');
    }

    public function replyMsg()
    {
        $params = input();
        if ($params['type'] != 'reward_gift') return $this->jsonError('类型不正确');
        if (empty($params['id']) || empty($params['content'])) return $this->jsonError('消息参数不能为空');
        $log = Db::name('gift_log')->where(['id' => $params['id'], 'to_uid' => USERID])->find();
        if (empty($log)) return $this->jsonError('消息不存在');
        if ($log['msg_status'] == '2') return $this->jsonError('请勿重复回复');
        $update = ['reply_msg' => $params['content'], 'msg_status' => '2', 'reply_time' => time()];
        $num    = Db::name('gift_log')->where(['id' => $log['id']])->update($update);
        if (!$num) return $this->jsonError('回复失败');
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.at_friend', [
            'behavior' => 'at_friend',
            'data'     => [
                'user_id'     => $log['to_uid'],
                'friend_uids' => $log['user_id'],
                'scene'       => 'gift_reply',
                'log_id'      => $log['id'],
                'reply_msg'   => $params['content']
            ]
        ]);
        return $this->success(array(), '回复成功');
    }
}
