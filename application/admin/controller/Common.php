<?php

namespace app\admin\controller;

use app\admin\service\RechargeLog;
use bxkj_common\CoreSdk;
use bxkj_common\DateTools;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use bxkj_module\service\UserRedis;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\DescribeMediaInfosRequest;
use TencentCloud\Vod\V20180717\VodClient;
use think\Db;
use think\facade\Request;
use Wcs\MgrAuth;

class Common extends Controller
{
    public function get_video_info()
    {
        $FileIds = [input('video_id')];
        if (empty($FileIds)) $this->error('视频ID不能为空');
        $vod_config = config('app.vod');
        if ($vod_config['platform'] != 'tencent') $this->error('不支持的点播平台');
        $qcloud = $vod_config['platform_config'];
        $cred = new Credential($qcloud['secret_id'], $qcloud['secret_key']);
        $httpProfile = new HttpProfile();
        $httpProfile->setReqTimeout($qcloud['timeout']?:60);// 请求超时时间，单位为秒(默认60秒)
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod("HmacSHA256");  // 指定签名算法(默认为HmacSHA256)
        $clientProfile->setHttpProfile($httpProfile);
        $client = new VodClient($cred, $qcloud['region'], $clientProfile);
        $req = new DescribeMediaInfosRequest();
        $req->FileIds = $FileIds;
        try {
            $resp = json_encode($client->DescribeMediaInfos($req));
        } catch (TencentCloudSDKException $exception) {
            $errCode = $exception->getErrorCode();
            $this->error('MediaInfo Error ' . $errCode . ' ' . $exception->getMessage());
        }
        $resp = json_decode($resp, true);
        $videoInfo = $resp['MediaInfoSet'][0];
        if (!$videoInfo) $this->error('获取视频信息失败');
        if (empty($videoInfo['MetaData'])) $this->error('请刷新页面，刷新前注意保存信息');
        $this->success('获取视频信息成功', [
            'basicInfo' => $videoInfo['BasicInfo'],
            'metaData' => $videoInfo['MetaData']
        ]);
    }

    public function get_qiniu_token()
    {
        $params = input();
        $type = $params['type'];
        $filename = $params['filename'];
        $query = $params['query'];
        $storer = $params['storer'];
        // $storer = 'wsyun';
      
        if (empty($type)) $this->error('上传类型不能为空');
        if (empty($filename)) $this->error('文件名不能为空');
        $sdk = new CoreSdk();
        $result = $sdk->post('common/get_qiniu_token', array(
            'type' => $type,
            'filename' => $filename,
            'aid' => defined('AID') ? AID : '',
            'user_key' => 'aid',
            'query' => $query ? $query : '',
            'storer' => $storer
        ));
        if (!$result) return $this->error($sdk->getError()->message);
        return $this->success('获取成功', $result);
    }

    public function img_crop()
    {
        $params = input();
        $key = $params['key'];
        if (empty($key)) $this->error('key值不能为空');
        if ($params['width'] <= 0 || $params['height'] <= 0) $this->error('图片宽度或者高度不合法');
        $sdk = new CoreSdk();
        $result = $sdk->post('common/qiniu_img_crop', array(
            'key' => $key,
            'x' => $params['x'],
            'y' => $params['y'],
            'w' => $params['width'],
            'h' => $params['height']
        ));
        if (!$result) return $this->error($sdk->getError()->message);
        return $this->success('获取成功', [
            'src' => $result['url']
        ]);
    }

    public function get_region()
    {
        $pid = input('pid');
        if ($pid !== '0' && empty($pid)) $this->error('请选择上级地址');
        $where = array('pid' => $pid, 'status' => '1');
        $result = Db::name('region')->where($where)->field('id,name,pid')->select();
        return json_success($result, '获取成功');
    }

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

    public function get_category()
    {
        $where = array();
        $pcat_id = input('pcat_id');
        $cat_id = input('cat_id');
        $type = input('type');
        if ($type) {
            $pid = Db::name('category')->where('mark',$type)->value('id');
            $where['pid'] = $pid;
        }else{
            $where['pid'] = $pcat_id;
        }
        $where['status'] = '1';
        $result = Db::name('category')->where($where)->field('id value,name,pid')->select();
        $result = $result ? $result : array(array('name' => '全部', 'value' => ''));
        $this->success('获取成功', $result);
    }

    public function get_music_category()
    {
        $result = Db::name('music_category')->field('id value,name')->select();
        $this->success('获取成功', $result);
    }

    public function get_levels()
    {
        $result = Db::name('exp_level')->field('levelid value,name,level_up')->select();
        $this->success('获取成功', $result);
    }

    public function get_work_types()
    {
        $result = Db::name('work_types')->field('name, type value')->select();
        $result = $result ? $result : array(array('name' => '全部', 'value' => ''));
        $this->success('获取成功', $result);
    }

    public function exchange_bean_quan()
    {
        $totalFee = input('total_fee');
        if (!validate_regex($totalFee, 'currency')) $this->success('查询成功', ['bean' => 0]);
        $bean = RechargeLog::exchangeBeanQuantity($totalFee);
        $this->success('查询成功', ['bean' => $bean]);
    }

    public function send_sms()
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

    public function query_cons()
    {
        if (Request::isGet()) {
            return $this->fetch();
        } else {

            $num = cache('query_cons_num');
            if ($num && $num >= 3) {
                $this->error('前面有很多用户正在查询，请您稍后');
            }
            $num = $num + 1;
            cache('query_cons_num', $num, 300);

            $startTime = input('start_time');
            $endTime = input('end_time');
            $userId = input('user_id');
            if (empty($startTime) || empty($endTime) || empty($userId)) {
                $this->error('参数不全');
            }
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
            $time = mktime(0, 0, 0, 9, 1, 2018);
            $total = 0;
            $totalFee = 0;
            if ($startTime < $time) {
                $t1 = $startTime;
                $t2 = $time;
                $config = [
                    'hostname' => 'rm-8vbergl96621ruci8.mysql.zhangbei.rds.aliyuncs.com',
                    'database' => 'live_ihuanyu_tv',
                    'username' => 'huanyutv_rds',
                    'password' => 'Xu8244288*',
                    'debug' => false,
                    'hostport' => 3306,
                    'prefix' => 'cmf_'
                ];
                $config = array_merge(config('database.'), $config);
                $totalFee = Db::connect($config)->name('users_coinrecord')->where([
                    ['uid', 'eq', $userId],
                    ['isvirtual', 'eq', '0'],
                    ['addtime', '>=', $t1],
                    ['addtime', '<', $t2],
                ])->sum('totalcuckoo');
                $total += $totalFee;
            }
            $totalFee2 = 0;
            if ($endTime > $time) {
                $t1 = max($time, $startTime);
                $t2 = $endTime;
                $totalFee2 = Db::name('gift_log')->where([
                    ['user_id', 'eq', $userId],
                    ['isvirtual', 'eq', '0'],
                    ['create_time', '>=', $t1],
                    ['create_time', '<', $t2],
                ])->sum('price');
                $total += $totalFee2;
            }
            //'，其中9月1日前的累计是' . $totalFee . '，9月1日后是' . $totalFee2
            cache('query_cons_num', $num - 1);
            $this->success('累计'.config('app.product_info.bean_name').'为：' . $total . '折合人民币' . ($total / 100));
        }
    }

    public function enviroment()
    {
        echo '<pre>';
        var_dump(get_enviroment_details());
        echo '</pre>';
        exit();
    }

    public function kpi_transfer()
    {
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $startTime = input('start_time');
            $endTime = input('end_time');
            $userId = input('user_id');
            $oldPromoterUid = input('old_promoter_uid');
            $newPromoterUid = input('new_promoter_uid');
            if (empty($startTime) || empty($endTime) || empty($userId) || empty($oldPromoterUid) || empty($newPromoterUid)) {
                $this->error('参数不全');
            }
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
            if ($startTime >= $endTime) $this->error('时间选择不正确');
            $user = Db::name('user')->where(['user_id' => $userId, 'delete_time' => null])->find();
            if (empty($user)) $this->error('用户不存在');
            $oldPromoter = Db::name('promoter')->where(['user_id' => $oldPromoterUid, 'delete_time' => null])->find();
            if (empty($oldPromoter)) $this->error('原'.config('app.promoter_name').'不存在');
            $newPromoter = Db::name('promoter')->where(['user_id' => $newPromoterUid, 'delete_time' => null])->find();
            if (empty($newPromoter)) $this->error('新'.config('app.promoter_name').'不存在');
            $log = [
                'user_id' => $user['user_id'],
                'aid' => 1,
                'old_agent_id' => $oldPromoter['agent_id'] ? $oldPromoter['agent_id'] : 0,
                'old_promoter_uid' => $oldPromoterUid,
                'agent_id' => $newPromoter['agent_id'] ? $newPromoter['agent_id'] : 0,
                'promoter_uid' => $newPromoterUid ? $newPromoterUid : 0,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => '0',
                'create_time' => time(),
            ];
            $id = Db::name('kpi_transfer_log')->insertGetId($log);
            if (!$id) $this->error('创建记录失败');
            $httpClient = new HttpClient([
                'timeout' => 2,
                'base' => config('app.push_service_url')
            ]);
            $httpClient->post('/kpi_transfer/handler', ['id' => $id]);//异步的处理业绩变更问题

            alog("system.kpi.transfer", "移交用户 USER_ID：".$oldPromoterUid." 业绩到 用户USER_ID：".$newPromoterUid." 名下");
            $this->success('移交完成,系统正在处理');
        }
    }

    public function get_log()
    {
        $redis = RedisClient::getInstance();
        $date = date('Ymd');
        $json = $redis->get("cache:tt:{$date}");
        if (empty($json)) {
            echo 'cache:tt empty<br/>';
        } else {
            echo $json;
        }
        echo '<br/>-------------------<br/>';
        $json2 = $redis->get("cache:tt2:{$date}");

        if (empty($json2)) {
            echo 'cache:tt empty<br/>';
        } else {
            echo $json2;
        }
        exit();
    }

    public function set_review()
    {
        $status = input('status', '0');
        $key = "config:ios:review";
        $redis = RedisClient::getInstance();
        $redis->set($key, $status);
        echo 'success';
        exit();
    }

    public function test33()
    {
        $data = $this->getDateByInterval('2019-04-08', '2019-07-29', 'day');
        $dbConfig = [
            'type' => 'mysql',
            'hostname' => 'rm-8vbergl96621ruci8.mysql.zhangbei.rds.aliyuncs.com',
            'database' => 'live_ihuanyu_tv',
            'username' => 'huanyutv_rds',
            'password' => 'Xu8244288*',
            'debug' => false,
            'hostport' => 3306,
            'prefix' => 'bx_'
        ];
        foreach ($data as $item){
            $time = strtotime($item);
            $day = str_replace('-','',$item);
            $week = DateTools::getWeekNum($time);
            Db::connect($dbConfig)->name('kpi_millet')->where(['day'=>$day])->update(['week'=>$week]);
            Db::connect($dbConfig)->name('kpi_fans')->where(['day'=>$day])->update(['week'=>$week]);
            Db::connect($dbConfig)->name('kpi_cons')->where(['day'=>$day])->update(['week'=>$week]);
        }
        echo 'done';
    }

    /**
     * 查询指定时间范围内的所有日期，月份，季度，年份
     *
     * @param $startDate   指定开始时间，Y-m-d格式
     * @param $endDate     指定结束时间，Y-m-d格式
     * @param $type        类型，day 天，month 月份，quarter 季度，year 年份
     * @return array
     */
    function getDateByInterval($startDate, $endDate, $type)
    {
        if (date('Y-m-d', strtotime($startDate)) != $startDate || date('Y-m-d', strtotime($endDate)) != $endDate) {
            return '日期格式不正确';
        }

        $tempDate = $startDate;
        $returnData = [];
        $i = 0;
        if ($type == 'day') {    // 查询所有日期
            while (strtotime($tempDate) < strtotime($endDate)) {
                $tempDate = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($startDate)));
                $returnData[] = $tempDate;
                $i++;
            }
        } elseif ($type == 'month') {    // 查询所有月份以及开始结束时间
            while (strtotime($tempDate) < strtotime($endDate)) {
                $temp = [];
                $month = strtotime('+' . $i . ' month', strtotime($startDate));
                $temp['name'] = date('Y-m', $month);
                $temp['startDate'] = date('Y-m-01', $month);
                $temp['endDate'] = date('Y-m-t', $month);
                $tempDate = $temp['endDate'];
                $returnData[] = $temp;
                $i++;
            }
        } elseif ($type == 'quarter') {    // 查询所有季度以及开始结束时间
            while (strtotime($tempDate) < strtotime($endDate)) {
                $temp = [];
                $quarter = strtotime('+' . $i . ' month', strtotime($startDate));
                $q = ceil(date('n', $quarter) / 3);
                $temp['name'] = date('Y', $quarter) . '第' . $q . '季度';
                $temp['startDate'] = date('Y-m-01', mktime(0, 0, 0, $q * 3 - 3 + 1, 1, date('Y', $quarter)));
                $temp['endDate'] = date('Y-m-t', mktime(23, 59, 59, $q * 3, 1, date('Y', $quarter)));
                $tempDate = $temp['endDate'];
                $returnData[] = $temp;
                $i = $i + 3;
            }
        } elseif ($type == 'year') {    // 查询所有年份以及开始结束时间
            while (strtotime($tempDate) < strtotime($endDate)) {
                $temp = [];
                $year = strtotime('+' . $i . ' year', strtotime($startDate));
                $temp['name'] = date('Y', $year) . '年';
                $temp['startDate'] = date('Y-01-01', $year);
                $temp['endDate'] = date('Y-12-31', $year);
                $tempDate = $temp['endDate'];
                $returnData[] = $temp;
                $i++;
            }
        }
        return $returnData;
    }


}
