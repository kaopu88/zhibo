<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Sms extends Service
{
    public function getTotal($get){
        $this->db = Db::name('sms');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        if ($get['is_code'] != '') {
            $where['is_code'] = $get['is_code'];
        }
        $this->db->setKeywords(trim($get['keyword']),'phone phone','number id','template,number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('sms');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        if ($result)
        {
            foreach ($result as &$item)
            {
                $content = Db::name('sms_template')->where('code',$item['template'])->value('content');
                $params = json_decode($item['params'],true);
                if ($item['is_code'] == '1'){
                    if (!check_auth('admin:sms:select_all')){
                        $params['number'] = '******';
                    }
                }
                $item['content'] = parse_tpl($content,$params);
                $item['result'] = json_decode($item['result'],true);
            }
            unset($item);
        }
        return $result;
    }
}
