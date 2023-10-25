<?php

namespace app\h5\controller;

use bxkj_module\controller\Controller;
use think\facade\Env;
use think\Db;
class Test extends Controller
{
    public function test()
    {   
        $statcks = debug_backtrace();
        var_dump($statcks[2]);
         if ($statcks[2]) {
            $file = $statcks[2]['file'];
            // var_dump($file);
            $file = str_replace(Env::get('root_path'), DIRECTORY_SEPARATOR, $file);
            $location = $file . ($file ? ' ' : '') . 'line ' . $statcks[1]['line'];
        }
        // var_dump($file);
        var_dump($location);
        
        
        die;
    }
    
    public function flv()
    {
        
        // 本地 FLV 地址
        $localFlvUrl = input('flv','');
        if(empty($localFlvUrl))exit('直播链接不存在');
        // 创建 cURL 句柄
        $ch = curl_init();
        
        // 设置 cURL 选项
        curl_setopt($ch, CURLOPT_URL, $localFlvUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // 发起请求并获取响应
        $response = curl_exec($ch);
        
        // 检查请求是否成功
        if ($response === false) {
            // 处理请求失败的情况
            echo 'Error: ' . curl_error($ch);
        } else {
            // 设置响应头，将跨域设置为空
            header('Access-Control-Allow-Origin:');
        
            // 输出响应内容
            echo $response;
        }
        
        // 关闭 cURL 句柄
        curl_close($ch);
    }
    public function caipiao()
    {
        $url = "https://lotto.sina.cn/trend/qxc_qlc_proxy.d.html?lottoType=dlt&actionType=chzs&sourceName=bd&type=120";
        
        $html = file_get_contents($url);
        
        // $pattern = '/<tr\s+class="hasbb">.*?class="chartball02">(.*?)<\/td>/s';
        
        $pattern = '/<td class="[^"]*\bchartball\d*\b[^"]*"(.*)<\/td>/';
        preg_match_all($pattern, $html, $matches,PREG_SET_ORDER);
        
        $arr = [];
        $i=0;
        foreach ($matches as $k=>$v){
            if($k%7==0)$i++;
            $arr[$i][] = trim($v[1],'>');
        }
        
        $inArr = [];
        foreach ($arr as $v){
            $inArr[] = [
                'hong1'=>$v[0],
                'hong2'=>$v[1],
                'hong3'=>$v[2],
                'hong4'=>$v[3],
                'hong5'=>$v[4],
                'lan1'=>$v[5],
                'lan2'=>$v[6],
                ];
        }
        
        Db::name('test')->insertAll($inArr);
        var_dump($inArr);die;
    }
    
}
