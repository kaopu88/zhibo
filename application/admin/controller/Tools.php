<?php

namespace app\admin\controller;

use app\admin\service\RechargeLog;
use bxkj_common\RedisClient;
use think\Db;

class Tools extends Controller
{
    public function repair_category()
    {
        $total = 0;
        $articles = Db::name('article')->select();
        foreach ($articles as $article) {
            $catId = $article['cat_id'] ? $article['cat_id'] : 37;
            $catInfo = Db::name('category')->where(['id' => $catId])->find();
            $catId = $catInfo ? $catInfo['id'] : 37;
            $pcatId = $catInfo ? $catInfo['pid'] : 2;
            if ($catId != $article['cat_id'] || $pcatId != $article['pcat_id']) {
                $num = Db::name('article')->where(['id' => $article['id']])->update([
                    'pcat_id' => $pcatId,
                    'cat_id' => $catId
                ]);
                if ($num) {
                    $total++;
                }
            }

        }
        var_dump($total);
        exit();
    }



    public function rebuild_exp_level()
    {
        $redis = RedisClient::getInstance();
        $redis->del('config:exp_level');
        $expList = Db::name('exp_level')->order('levelid asc')->field('levelid,level_up')->select();
        foreach ($expList as $item) {
            $redis->zAdd('config:exp_level', $item['level_up'], $item['levelid']);
        }
    }

    public function exchange_bean_quan()
    {
        $totalFee = input('total_fee');
        if (!validate_regex($totalFee, 'currency')) $this->success('查询成功', ['bean' => 0]);
        $bean = RechargeLog::exchangeBeanQuantity($totalFee);
        $this->success('查询成功', ['bean' => $bean]);
    }

    public function import_article()
    {
        $map=[
            '3'=>'44',
            '4'=>'45',
            '8'=>'43',
            '9'=>'46',
        ];
        $terms=[];
        foreach ($map as $termId=>$newCatId){
            $terms[]=$termId;
        }
        $relations=Db::table('bugu_term_relationships')->whereIn('term_id',$terms)->where('status','1')->select();
        $arr=[];
        foreach ($relations as $relation){
            $artInfo=Db::name('posts')->where('id',$relation['object_id'])->find();
            if($artInfo){
                $content=$artInfo['post_content']?$artInfo['post_content']:'';
                $data=[
                    'pcat_id'=>41,
                    'cat_id'=>$map[(string)$relation['term_id']],
                    'title'=>$artInfo['post_title']?$artInfo['post_title']:'',
                    'summary'=>msubstr(strip_tags($content), 0, 45, 'utf-8', false),
                    'content'=>$content,
                    'mobile_content'=>'',
                    'image'=>'',
                    'aid'=>1,
                    'create_time'=>strtotime($artInfo['post_date']),
                    'release_time'=>strtotime($artInfo['post_modified']?$artInfo['post_modified']:$artInfo['post_date']),
                ];
                $arr[]=$data;
            }
        }

        echo json_encode($arr);
        exit();
    }


}
