<?php

namespace app\promoter\controller;

use bxkj_common\CoreSdk;
use think\Db;
use think\facade\Request;

class Common extends Controller
{
    public function get_area()
    {
        $where = array();
        $country = input('country');
        $province = input('province');
        $city = input('city');
        $district = input('district');
        if (isset($country)) {
            $where['pid'] = empty($country) ? 1 : $country;
        } else if (!empty($province) || !empty($city) || !empty($district)) {
            $where['pid'] = !empty($province) ? $province : (!empty($city) ? $city : $district);
        }
        if (empty($where)) $this->error('请选择上级地址');
        $where['status'] = '1';
        $result = Db::name('region')->where($where)->field('id value,name,pid')->select();
        $result = $result ? $result : array(array('name' => '全部', 'value' => ''));
        $this->success('获取成功', $result);
    }

    public function get_levels()
    {
        $result = Db::name('exp_level')->field('levelid value,name,level_up')->select();
        $this->success('获取成功', $result);
    }

    public function get_region()
    {
        $pid = input('pid');
        if ($pid !== '0' && empty($pid)) $this->error('请选择上级地址');
        $where = array('pid' => $pid, 'status' => '1');
        $result = Db::name('region')->where($where)->field('id,name,pid')->select();
        return json_success($result, '获取成功');
    }

    public function send_sms_code()
    {
        $params = input();
        $scene = $params['scene'];
        $phone = $params['phone'];
        $phoneCode = $params['phone_code'];
        if (empty($scene)) return json_error(make_error('请输入场景值'));
        $sceneConfig = enum_array('sms_code_scenes', $scene);
        if (empty($sceneConfig)) return json_error(make_error('场景值不合法'));
        if ($sceneConfig['bind'] == 1) {
            if (!defined('AID') || empty(AID)) return make_error('请先登录', 1003);
            $phone2 = '';
            if (empty($phone2)) return make_error('您还没有绑定手机号');
            if (isset($phone) && $phone != $phone2) return make_error('输入的手机号和绑定的手机号不一致');
            $phone = $phone2;
        }
        if (empty($phone) || !validate_regex($phone, 'phone')) return json_error(make_error('手机号格式不正确'));
        $sdk = new CoreSdk();
        $result = $sdk->post('common/send_sms_code', array(
            'phone' => $phone,
            'scene' => $scene,
            'phone_code' => $phoneCode ? $phoneCode : '86'
        ));
        if (!$result) return json_error($sdk->getError());
        session('last_send_time', $result['send_time']);
        return json_success($result, '发送成功');
    }

    public function get_kpi_num_tip()
    {
        $kpi_num_time = cache('kpi_num_time');
        $now = time();
        $kpi_num_cons = cache('kpi_num_cons');
        $speed = cache('kpi_num_speed');
        if ($now > $kpi_num_time + 120) {
            $db = Db::name('kpi_cons');
            $where = [['rebuild', 'neq', 1]];
            $db->where($where);
            $agent_kpi_cons = enum_array("bean_trade_types");
            $rel_types = [];
            foreach ($agent_kpi_cons as $item){
                $rel_types[] = $item['value'];
            }
            if ($rel_types){
                $db->whereIn('rel_type', $rel_types);
            }
            $kpi_num_cons2 = $db->count();
            $diff = $kpi_num_cons - $kpi_num_cons2;
            $diffTime = $now - $kpi_num_time;
            cache('kpi_num_time', $now);
            $kpi_num_cons = $kpi_num_cons2;
            cache('kpi_num_cons', $kpi_num_cons2);
            $speed = round($diff / $diffTime);
            cache('kpi_num_speed', $speed);
        }
        $need = round($kpi_num_cons / $speed);
        $str = '';
        if ($need > 0) {
            $needStr = $need > 60 ? ('大约需要' . time_str($need, 'i')) : '5分钟内完成';
            $str = "剩余{$kpi_num_cons}条元数据-速度{$speed}/s-{$needStr}";
        }
        return json_success($str, '获取成功');
    }

}
