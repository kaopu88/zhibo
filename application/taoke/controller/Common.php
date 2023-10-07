<?php

namespace app\taoke\controller;

use app\admin\service\RechargeLog;
use bxkj_common\CoreSdk;
use bxkj_common\DateTools;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\DescribeMediaInfosRequest;
use TencentCloud\Vod\V20180717\VodClient;
use think\Db;
use think\facade\Request;

class Common extends Controller
{
    public function get_qiniu_token()
    {
        $params = input();
        $type = $params['type'];
        $filename = $params['filename'];
        $query = $params['query'];
        $storer = $params['storer'];
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


    public function get_levels()
    {
        $result = Db::name('exp_level')->field('levelid value,name,level_up')->select();
        $this->success('获取成功', $result);
    }


}
