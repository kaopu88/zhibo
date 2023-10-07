<?php

namespace app\push\controller;

use bxkj_common\RabbitMqChannel;

class UserTransfer extends Api
{
    public function handler()
    {
        $json = input('data');
        if (empty($json)) return json_error('json empty');
        $data = json_decode($json, true);
        if (!is_array($data)) return json_error('数据格式不正确');
        $userTransfer = new \bxkj_module\service\UserTransfer();
        $userTransfer->setAsync(false)
            ->setAdmin($data['admin']['type'], $data['admin']['id']);
        if ($data['from']) {
            if ($data['from']['user_ids']) {
                $userTransfer->setFromUsers($data['from']['user_ids']);
            }
            if (!empty($data['from']['promoter_uid'])) {
                $userTransfer->setFromPromoter($data['from']['promoter_uid']);
            } else if (!empty($data['from']['agent_id'])) {
                $userTransfer->setFromAgent($data['from']['agent_id']);
            }
        }
        if ($data['target']) {
            if ($data['target']['promoter_uid']) {
                $userTransfer->setTargetPromoter($data['target']['promoter_uid']);
            }
            if ($data['target']['agent_id']) {
                $userTransfer->setTargetAgent($data['target']['agent_id']);
            }
        }
        if (!empty($data['ownAgentId'])) {
            $userTransfer->setOwnAgent($data['ownAgentId']);
        }

        if ($data['is_transfer']) {
            $userTransfer->setTransfer(true);
        }
        $res = $userTransfer->transfer();
        $callback = $data['callback'];
        $noticeData = [
            'method' => 'post',
            'timeout' => 3,
            'confirm' => '1',
            'url' => $callback
        ];
        if ($res) {
            $noticeData['fields'] = json_encode(['total' => $res]);
        } else {
            $error = $userTransfer->getError();
            $noticeData['fields'] = json_encode(['msg' => $error->message, 'status' => $error->status]);
        }
        if (!empty($callback)) {
            $rabbitChannel = new RabbitMqChannel(['common.callbacks']);
            $rabbitChannel->sendOnce('callback.url', $noticeData);
        }
        if (!$res) {
            return json_error($userTransfer->getError());
        } else {
            return json_success($res, '处理完成');
        }
    }
}
