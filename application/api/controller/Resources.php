<?php

namespace app\api\controller;
use app\common\controller\Controller;
use think\Db;

class Resources extends Controller
{
    public function getList()
    {
        $params = request()->param();
        $type = $params['type'];
        $typeArr = explode('_',$type);
        $cateName = $typeArr[0];
        $typeName = $typeArr[1];
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 200 ? 200 : $params['length']) : PAGE_LIMIT;
        $catIds = [];
        if (!empty($cateName)) {
            $catInfo = Db::name('category')->where(['mark' => $cateName])->find();
            if (!empty($catInfo)) {
                if ($catInfo['level'] == 1) {
                    $catInfos = Db::name('category')->where(['pid' => $catInfo['id']])->select();
                    $catInfos = $catInfos ? $catInfos : [];
                    foreach ($catInfos as $info) {
                        $catIds[] = $info['id'];
                    }
                } else {
                    $catIds[] = $catInfo['id'];
                }
            }
        }
        $resources = [];
        if (!empty($catIds)) {
            $where = 'status = "1" and cat_id in('.implode(',',$catIds).')';
            if (!empty($typeName)){
                $where .= ' and '.$typeName .' = 1';
            }
            $resources = Db::name('resources')->where($where)->order('sort desc,create_time desc')->limit($offset, $length)->select();
        }
        return $this->success($resources ? $resources : [], '获取成功');
    }

    public function getInfo()
    {
        $params = request()->param();
        $id = $params['id'];
        $where = ['status' => '1'];
        if (empty($id)) return $this->jsonError('请选择资源');
        $where['id'] = $id;
        $resource = Db::name('resources')->where($where)->find();
        if (empty($resource)) return $this->jsonError('资源不存在');
        return $this->success($resource ? $resource : []);
    }
}
