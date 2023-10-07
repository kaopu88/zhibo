<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/08/10
 * Time: 下午 2:26
 */

namespace bxkj_module\service;

use think\Db;
class CashLog extends Service
{

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('cash_log');
        $count    = $this->db->where($condition)->count();
        if ($page_size == 0) {
            $list       = $this->db->field($field)
                ->where($condition)
                ->order($order)
                ->select();
            $page_count = 1;
        } else {
            $start_row = $page_size * ($page_index - 1);
            $list      = $this->db->field($field)
                ->where($condition)
                ->order($order)
                ->limit($start_row . "," . $page_size)
                ->select();
            if ($count % $page_size == 0) {
                $page_count = $count / $page_size;
            } else {
                $page_count = (int)($count / $page_size) + 1;
            }
        }
        return array(
            'data'        => $list,
            'total_count' => $count,
            'page_count'  => $page_count
        );
    }


    public function  sumcash($uid){
      return Db::name('cash_log')->where(['user_id'=>$uid])->sum('total');
    }


    //查询邀请了多少好友，赚了多少钱
    public function queryInvent($uid){
      $count =   Db::name('cash_log')->where(['user_id'=>$uid,'trade_type'=>'inviteFriends'])->count();
      $inventSum =  Db::name('cash_log')->where(['user_id'=>$uid,'trade_type'=>'inviteFriends'])->sum('total');
      if($count){
          return ['count'=>$count,'inventSum'=>$inventSum];
      }else{
          return [ ];
      }
    }

}