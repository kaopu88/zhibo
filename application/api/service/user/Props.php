<?php


namespace app\api\service\user;


use app\common\service\Service;
use think\Db;

class Props extends Service
{
    protected static $unit = ['d' => '天', 'w'=>'周', 'm'=>'月', 'y' => '年'];

    protected static $page = 6;

    public function getPropsList($p)
    {
        $page = empty($p) ? 0 : ($p-1)*self::$page;

        $res = Db::name('props')->where(['status'=>1])->field('name, cover_icon as icon, id, describe')->order('sort desc')->limit($page,self::$page)->select();

        if (empty($res)) return [];

        foreach ($res as $key => &$val)
        {
            $item = Db::name('props_bean')->where(['status'=>1, 'props_id'=>$val['id']])->field('id as item_id, price, unit, discount, length')->order('length')->select();

            if (!empty($item))
            {
                foreach ($item as &$item_val)
                {
                    $item_val['unit'] = sprintf('%s个%s', $item_val['length'], self::$unit[$item_val['unit']]);

                    $item_val['price'] = ($item_val['price']*$item_val['discount']).APP_BEAN_NAME;

                    $item_val['discount'] = $item_val['discount'] >= 1 ? '' : ($item_val['discount']*10).'折';

                    unset($item_val['length']);
                }

                $val['price_desc'] = sprintf('%s/月', $item[0]['price']);

                $val['seo_desc'] = '最新上架';

                $val['item'] = $item;

                unset($val['id']);
            }
            else{
                unset($res[$key]);
            }
        }

        return $res;
    }


    public function getPropsItem($id)
    {
        $res = Db::name('props')
            ->alias('p')
            ->join('props_bean pb', 'p.id=pb.props_id')
            ->where(['p.status'=>1, 'pb.status'=>1, 'pb.id'=>$id])
            ->field('p.name, p.user_icon, p.id, pb.price, pb.discount, pb.length, pb.unit')
            ->find();

        return $res;

    }
}