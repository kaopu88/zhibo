<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/6/9 0009
 * Time: 上午 9:18
 */
namespace app\mq\callbacks;

use bxkj_module\service\Anchor;
use think\Db;
use PhpAmqpLib\Message\AMQPMessage;

class AnchorApplyAfter extends ConsumerCallback
{
    public function process(AMQPMessage $msg)
    {
        $data = json_decode($msg->body, true);
        if (!empty($data)) {
            $userId = $data['user_id'];
            $applyId = $data['apply_id'];
            $anchorApply = Db::name('anchor_apply')->where(['id' => $applyId, 'status' => 1])->find();
            if (empty($anchorApply)) return $this->ack($msg);
            $this->anchorHandler($userId);
        }
        $this->ack($msg);
    }

    public function anchorHandler($userId = '')
    {
        if (empty($userId)) {
            $this->log->notice('user error');
            return false;
        }
        $info = Db::name('sys_config')->where(['mark'=>'live'])->value('value');
        $configAll = json_decode($info,true);
        $live_setting = $configAll['live_setting']['user_live'];
        $anchorApply = Db::name('anchor_apply')->where(['user_id' => $userId, 'status' => 1])->find();

        if (empty($anchorApply)) {
            $this->log->notice('anchor error');
            return false;
        }
        if (empty($anchorApply['agent_id'])) {
            if ($live_setting['verify']) {
                //表明是需要平台审核
                $data['status'] = 3;
            } else {
                $anchorService = new Anchor();
                $res = $anchorService->create([
                    'agent_id' => $anchorApply['agent_id'],
                    'user_id' => $anchorApply['user_id'],
                    'force' => 0,
                    'admin' => [
                        'type' => 'erp',
                        'id' => AID
                    ]], 1);
                if (!$res) {
                    $this->log->notice($anchorService->getError() . ' error');
                    return false;
                }
                $data['status'] = 2;
            }
            Db::name('anchor_apply')->where(['id' => $anchorApply['id']])->update($data);
            return true;
        } else {
            //移交到公会审核主播
            $agent = Db::name('agent')->where(['id' => $anchorApply['agent_id'], 'delete_time' => null])->find();
            if (empty($agent))
            {
                $this->log->notice('agent error');
                return false;
            }
            $data['status'] = 4;
            Db::name('anchor_apply')->where(['id' => $anchorApply['id']])->update($data);
            return true;
        }
    }
}