<?php

namespace app\core\controller;

use app\core\service\AdContent;
use think\facade\Request;

class Beauty extends Controller
{
    //获取广告内容
    public function index()
    {
        $appSetting = config('app.');
        $config = $appSetting['beauty_setting'];
        if (empty($config)) return json_error('未配置美颜');
        if ($config['beauty_status'] == 2) {
            $iosByteCode = explode(',', $config['beauty_ios_key']);
            $androidByteCode = explode(',', $config['beauty_android_key']);
            $config['beauty_ios_key'] = $this->getEnCodeString($iosByteCode);
            $config['beauty_android_key'] = $this->getEnCodeString($androidByteCode);
        }
        if ($config['beauty_status'] == 1) {
            $config['beauty_ios_key'] = $config['tuohuan_beauty_ios_key'];
            $config['beauty_android_key'] = $config['tuohuan_beauty_android_key'];
        }
        $tmp = copy_array($config, ['beauty_status', 'beauty_ios_key', 'beauty_android_key']);
        return json_success($tmp, '获取成功');
    }

    private function getEnCodeString(array $byteCode)
    {
        $mutabStr = '';
        for ($i = 0; $i < count($byteCode); $i++) {
            $temp = sprintf("%c", $byteCode[$i]);
            $mutabStr = $mutabStr . $temp;
        }
        $mutabStr = base64_encode($mutabStr);
        return $mutabStr;
    }
}
