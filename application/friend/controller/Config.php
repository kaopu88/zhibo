<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/15
 * Time: 下午 7:17
 */

namespace app\friend\controller;

use app\admin\service\SysConfig;
use app\friend\service\FriendCircleClassfiy;
use app\friend\service\FriendCircleClassfiyone;
use app\friend\service\FriendCircleMessage;
use app\friend\service\FriendCircleMessageExpress;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class Config extends Controller
{
    /**
     *
     * @return mixed
     */
    public function index()
    {
    }

    /**
     * 基础配置
     * @return mixed
     */
    public function baseconfig()
    {
        $rest = $this->checkAuth('friend:config:baseconfig');
        $ser  = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("friend");
            $info = json_decode($info['value'], true);
            if (empty($info)) {
                $info['is_open'] = 1;
            }
            $this->assign('_info', $info);
        } else {
            $post = json_encode(input());
            if ($ser->getConfig("friend")) {
                $result = $ser->updateConfig(['mark' => 'friend'], ['value' => $post]);
            } else {
                $result = $ser->addConfig(['mark' => 'friend', 'classified' => 'friend', 'value' => $post]);
            }
            if ($result > 0) {
                $ser->resetConfig();
            }
            $redis = new RedisClient();
            $redis->del('cache:friend_config');
            $arr  = [];
            $ser  = new SysConfig();
            $info = $ser->getConfig("friend");
            if (empty($info)) return [];
            $redis->set('cache:friend_config', $info['value']);
            $redis->expire('cache:friend_config', 4 * 3600);
            alog("friend.config.set", '编辑交友管理设置');
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    //表白词库管理
    public function expresslist()
    {
        $rest       = $this->checkAuth('friend:config:expressList');
        $get        = input();
        $friend     = new FriendCircleMessageExpress();
        $total      = $friend->getTotal($get);
        $page       = $this->pageshow($total);
        $list       = $friend->getList($get, $page->firstRow, $page->listRows);
        $classfiy   = new FriendCircleClassfiy();
        $rest       = $classfiy->getQuery(['isdel' => 0, 'status' => 1, 'masterid' => 3], "*", 'id');
        $flclassfiy = [];
        foreach ($rest as $k1 => $v1) {
            $flclassfiy[$v1['id']] = $v1['child_name'];
        }

        foreach ($list as $k => $v) {
            $list[$k]['clname'] = $flclassfiy[$v['classid']];
            if (empty($v['from'])) {
                $list[$k]['comfrom'] = "网络";
            } else {
                $list[$k]['comfrom'] = $v['from'] ;
            }
        }
        $classfiy = new FriendCircleClassfiy();
        $rest     = $classfiy->getQuery(['isdel' => 0, 'status' => 1, 'masterid' => 3], "*", 'id');
        foreach ($rest as $k => $v) {
            $earry[] = [
                'name'  => $v['child_name'],
                'value' => $v['id'],
            ];
        }
        $this->assign('_earry', json_encode($earry));
        $this->assign('_list', $list);
        return $this->fetch();
    }

    //表白词库添加
    public function addexpress()
    {
        $this->checkAuth('friend:config:addexpress');
        if (Request::isGet()) {
            $info       = [];
            $get        = input();
            $classfiy   = new FriendCircleClassfiy();
            $rest       = $classfiy->getQuery(['isdel' => 0, 'status' => 1, 'masterid' => 3], "*", 'id');
            $flclassfiy = [];
            foreach ($rest as $k1 => $v1) {
                $flclassfiy[] = [
                    'name'  => $v1['child_name'],
                    'value' => $v1['id'],
                ];;
            }
            $this->assign('_flclassfiy', $flclassfiy);
            $this->assign('_info', $info);
            return $this->fetch();
        } else {

            $FriendCircleMessageExpress = new FriendCircleMessageExpress();
            $post                       = input();

            if(empty($post['classid'])){
                $this->error('分类不能为空');
            }
            if(empty($post['content'])){
                $this->error('内容不能为空');
            }
            $result                     = $FriendCircleMessageExpress->backstageadd($post);
            if (!$result) $this->error($FriendCircleMessageExpress->getError());
            alog("friend.expresslist.add", '新增表白推荐词 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function change_status()
    {
        $this->checkAuth('friend:config:change_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $friendnMsgExpress = new FriendCircleMessageExpress();
        $num               = $friendnMsgExpress->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("friend.expresslist.edit", '编辑表白推荐词 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    //修改信息
    public function editexpress()
    {
        $friendExpress = new FriendCircleMessageExpress();
        if (Request::isGet()) {
            $id   = input('id');
            $info = $friendExpress->info($id);
            if (empty($info)) $this->error('信息不存在');
            $classfiy   = new FriendCircleClassfiy();
            $rest       = $classfiy->getQuery(['isdel' => 0, 'status' => 1, 'masterid' => 3], "*", 'id');
            $flclassfiy = [];
            foreach ($rest as $k1 => $v1) {
                $flclassfiy[] = [
                    'name'  => $v1['child_name'],
                    'value' => $v1['id'],
                ];;
            }
            $this->assign('_flclassfiy', $flclassfiy);
            $this->assign('_info', $info);
            return $this->fetch('addexpress');
        } else {
            $post   = input();
            $result = $friendExpress->backstageedit($post);
            if (!$result) $this->error($friendExpress->getError());
            alog("friend.expresslist.edit", '编辑表白推荐词 ID：'.$post['id']);
            $this->success('修改成功', $result);
        }
    }

    //删除信息
    public function delexpress()
    {
        $friendExpress = new FriendCircleMessageExpress();
        $this->checkAuth('friend:config:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = $friendExpress->del($ids);
        if (!$num) $this->error('删除失败');
        alog("friend.expresslist.del", '删除表白推荐词 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    //交友分类列表
    public function classlist()
    {
        $this->checkAuth('friend:config:classlist');
        $get          = input();
        $fClassfiy    = new FriendCircleClassfiy();
        $total        = $fClassfiy->getTotal($get);
        $page         = $this->pageshow($total);
        $list         = $fClassfiy->getList($get, $page->firstRow, $page->listRows);
        $menuArray    = enum_array('friend_master_classfiy');
        $menu         = [];
        $classOne     = new FriendCircleClassfiy();
        $classOneList = $classOne->getQuery(['level'=>0,'isdel'=>0], "*", "id desc");
        foreach ($classOneList as $k => $v) {
            $menu[$v['id']] = $v['child_name'];
            $emuArray[]   = [
                'name'  => $v['child_name'],
                'value' => $v['id'],
            ];
        }

        foreach ($list as $k1 => $v1) {
            if($v1['level']==1){
                $list[$k1]['clname'] = $menu[$v1['masterid']];
                $list[$k1]['clname'] = $menu[$v1['masterid']];
            }else{
                $list[$k1]['clname'] = '顶级分类';
            }
        }

        $this->assign('_classfiyArray', json_encode(array_values($emuArray)));
        $this->assign('_list', $list);
        return $this->fetch();
    }

    //添加分类列表
    public function addclassfiy()
    {
        $this->checkAuth('friend:config:addclassfiy');
        if (Request::isGet()) {
            $info         = [];
            $get          = input();
            $classOne     = new FriendCircleClassfiy();
            $classOneList = $classOne->getQuery(['level'=>0,'isdel'=>0], "*", "id desc");
            foreach ($classOneList as $k => $v) {
                $menu[$k] = $v['child_name'];
                $emuArray[]   = [
                    'name'  => $v['child_name'],
                    'value' => $v['id'],
                ];
            }
            $this->assign('_classfiyone', $emuArray);
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $FriendClassfiy = new FriendCircleClassfiy();
            $post           = input();

            if (empty($post['masterid'])) {
                $this->error('主分类不能为空');
             //   $post['level'] = 0;
            }else{
                $post['level'] = 1;
            }
            $rest = $FriendClassfiy->classfiyCheck($post);
            if ($rest['code'] != 1) {
                $this->error($rest['msg']);
            }
            $result = $FriendClassfiy->backstageadd($post);
            if (!$result) $this->error($FriendClassfiy->getError());
            alog("friend.expresslist.add_cate", '新增表白推荐词分类 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    //改变子分类状态
    public function changeclassfiystatus()
    {
        $this->checkAuth('friend:config:change_classfiy_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $friendnMsgExpress = new FriendCircleClassfiy();
        $num               = $friendnMsgExpress->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("friend.expresslist.edit_cate", '编辑表白推荐词分类 ID：'.implode(",", $ids)."<br>修改状态:".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    //修改分类数据editclassfiy
    public function editclassfiy()
    {
        $FriendClassfiy = new FriendCircleClassfiy();
        if (Request::isGet()) {
            $id   = input('id');
            $info = $FriendClassfiy->info($id);
            if (empty($info)) $this->error('信息不存在');
            $classOneList = $FriendClassfiy->getQuery(['level'=>0,'isdel'=>0], "*", "id");
            foreach ($classOneList as $k => $v) {
                $menu[$k] = $v['child_name'];
                $emuArray[]   = [
                    'name'  => $v['child_name'],
                    'value' => $v['id'],
                ];
            }
            $this->assign('_classfiyone', $emuArray);
            $this->assign('_info', $info);
            return $this->fetch('addclassfiy');
        } else {
            $post   = input();
            $result = $FriendClassfiy->backstageedit($post);
            if (!$result) $this->error($FriendClassfiy->getError());
            alog("friend.expresslist.edit_cate", '编辑表白推荐词分类 ID：'.$post['id']);
            $this->success('修改成功', $result);
        }
    }

    public function delclassfiy()
    {
        $friendExpress = new FriendCircleClassfiy();
        $this->checkAuth('friend:config:delclassfiy');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $find =  $friendExpress->getQuery(['id'=>$ids[0]], "*", 'id');
        if($find[0]['level']==0){
            $total  = $friendExpress->getTotal(['masterid'=>$find[0]['id']]);
            if($total>0){
                $this->error('下面还有子分类，不能删除');
            }
        }
        $num = $friendExpress->del($ids);
        if (!$num) $this->error('删除失败');
        alog("friend.expresslist.del_cate", '删除表白推荐词分类 ID：'.implode(",", $ids));

        $this->success("删除成功，共计删除{$num}条记录");
    }

    //系统一级分类列表
    public function classfiyonelist()
    {
        $this->checkAuth('friend:config:classfiyonelist');
        $get               = input();
        $FriendClassfiyone = new FriendCircleClassfiyone();
        $total             = $FriendClassfiyone->getTotal($get);
        $page              = $this->pageshow($total);
        $list              = $FriendClassfiyone->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function addclassfiyone()
    {
        if (Request::isGet()) {
            $info = [];
            $get  = input();
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $FriendClassfiyone = new FriendCircleClassfiyone();
            $post              = input();
            if (empty($post['class_name'])) {
                $this->error('录入字段不能为空');
            }
            $rest = $FriendClassfiyone->classfiyCheck($post);
            if ($rest['code'] != 1) {
                $this->error($rest['msg']);
            }
            $result = $FriendClassfiyone->backstageadd($post);
            if (!$result) $this->error($FriendClassfiyone->getError());
            alog("friend.expresslist.add", '新增表白推荐词 ID：'.$result);

            $this->success('新增成功', $result);
        }
    }

    public function editclassfiyone()
    {
        $FriendClassfiyone = new FriendCircleClassfiyone();
        if (Request::isGet()) {
            $id   = input('id');
            $info = $FriendClassfiyone->info($id);
            if (empty($info)) $this->error('信息不存在');
            $this->assign('_info', $info);
            return $this->fetch('addclassfiyone');
        } else {
            $post   = input();
            $result = $FriendClassfiyone->backstageedit($post);
            if (!$result) $this->error($FriendClassfiyone->getError());
            alog("friend.expresslist.edit", '编辑表白推荐词 ID：'.$post['id']);


            $this->success('修改成功', $result);
        }
    }

    public function del(){
        $friendExpress = new FriendCircleMessageExpress();
        $this->checkAuth('friend:config:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = $friendExpress->del($ids);
        if (!$num) $this->error('删除失败');
        alog("friend.expresslist.del", '删除表白推荐词 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }


}