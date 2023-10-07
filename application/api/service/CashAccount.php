<?php

namespace app\api\service;
use app\common\service\Service;
use think\Db;

class CashAccount extends Service
{
    public function __construct()
    {
        parent::__construct();
    }

    public function bindAccount($inputData)
    {
        $type = $inputData['type'];
        $user_id = $inputData['user_id'];
        if ($type != 'alipay') return $this->setError('提现账号类型不正确');
        if (empty($inputData['account'])) return $this->setError('提现账号不能为空');
        if (empty($inputData['name'])) return $this->setError('姓名不能为空');
        $has = Db::name('cash_account')->where(array('type' => $type, 'user_id' => $user_id))->limit(1)->find();
        $data['name'] = $inputData['name'];
        $data['account'] = $inputData['account'];
        if (!$has) {
            $data['type'] = $type;
            $data['user_id'] = $user_id;
            $data['verify_status'] = '0';
            $data['create_time'] = time();
            $res = Db::name('cash_account')->insert($data);
        } else {
            if ($data['account'] != $has['account']) $data['verify_status'] = '0';
            $res = Db::name('cash_account')->where(array('id' => $has['id']))->update($data);
        }
        if (!$res) return $this->setError('保存失败');
        return ['id' => $has ? $has['id'] : $res['id']];
    }

    public function addAccount($inputData)
    {
        $data = $this->df->process('add@cash_account', $inputData)->output();
        if (!$data) return $this->df->getError();
        $num = Db::name('cash_account')->where(array('user_id' => $data['user_id'], 'delete_time' => null))->count();
        if ($num >= 20) return $this->setError('最多只能添加20个账号');
        $res = Db::name('cash_account')->insertGetId($data);
        if (!$res) return $this->setError('新增提现账号失败');
        $data['id'] = $res;
        return $data;
    }

    public function updateAccount($inputData)
    {
        if (empty($inputData['id'])) return $this->setError('提现账号不存在');
        $saveData = Db::name('cash_account')->where(array('id' => $inputData['id'], 'delete_time' => null, 'user_id' => $inputData['user_id']))
            ->find();
        if (empty($saveData)) return $this->setError('提现账号不存在');
        $data = $this->df->process('update@cash_account', $inputData, $saveData)->output();
        if (!$data) return $this->df->getError();
        if ($data['type'] != $saveData['type'] || $data['account'] != $saveData['account']) {
            $data['verify_status'] = '0';//重置验证状态
        }
        $num = Db::name('cash_account')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('编辑提现账号失败');
        return $num;
    }

    public function delAccount($inputData)
    {
        if (empty($inputData['id'])) return $this->setError('请选择提现账号');
        $ids = is_array($inputData['id']) ? implode(',', $inputData['id']) : $inputData['id'];
        if (count($ids) > 100) return $this->setError('超出范围');
        $num = Db::name('cash_account')->where(array('user_id' => $inputData['user_id'], 'delete_time' => null))
            ->whereIn('id', $ids)
            ->update(array(
                'delete_time' => time()
            ));
        if (!$num) return $this->setError('删除失败');
        return $num;
    }

    public function validateAccount($value, $rule, $data = null, $more = null)
    {
        $num = Db::name('cash_account')->where(array(
            'type' => $data['type'],
            'account' => $data['account'],
            'user_id' => $data['user_id'],
            'delete_time' => null
        ))->count();
        return $num == 0;
    }

    public function fillCardName($value, $rule, $data = null, $more = null)
    {
        $type = $data['type'];
        if ($type == 'bank') {
            $name = bank_card_info($value);
            $tmp['card_name'] = $name ? $name : '未知';
        } else {
            $tmp['card_name'] = enum_attr('cash_account_types', $type, 'name');
        }
        return $tmp;
    }

    public function getInfo($get)
    {
        if (empty($get['id'])) return $this->setError('请选择提现账号');
        $db = Db::name('cash_account');
        $this->setField($db);
        $info = $db->where(array('user_id' => $get['user_id'], 'id' => $get['id'], 'delete_time' => null))->find();
        if (empty($info)) return $this->setError('提现账号不存在');
        return $this->extendInfo($info, 'info');
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $db = Db::name('cash_account');
        $this->setWhere($db, $get)->setOrder($db, $get)->setField($db, 'list');
        $list = $db->limit($offset, $length)->select();
        $list = $list ? $list : array();
        foreach ($list as &$item) {
            $item = $this->extendInfo($item, 'list');
        }
        return $list;
    }

    protected function extendInfo($info, $mode = 'info')
    {
        $info['type_name'] = enum_attr('cash_account_types', $info['type'], 'name');
        $info['create_time'] = date('Y-m-d H:i', $info['create_time']);
        return $info;
    }

    public function getTotal($get)
    {
        $db = Db::name('cash_account');
        $this->setWhere($db, $get);
        $total = $db->count();
        return $total ? $total : 0;
    }

    protected function setField(&$db, $mode = 'info')
    {
        $db->field('id,type,user_id,card_name,account,name,verify_status,create_time');
        return $this;
    }

    protected function setWhere(&$db, $get)
    {
        $where = array('user_id' => $get['user_id'], 'delete_time' => null);
        $db->where($where);
        return $this;
    }

    protected function setOrder(&$db, $get)
    {
        if (empty($get['sort'])) {
            $db->order('create_time DESC');
        }
        return $this;
    }


}