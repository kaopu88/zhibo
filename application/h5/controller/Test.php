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
