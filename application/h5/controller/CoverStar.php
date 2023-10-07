<?php


namespace app\h5\controller;

use bxkj_module\service\User;
use bxkj_common\RedisClient;
use app\h5\service\CoverStar as coverMod;
use think\Db;

class CoverStar extends Controller
{
    public function index()
    {
        $avatar_img  = img_url('','','avatar');
        $this->assign('avatar_img', $avatar_img);
        $down_url = H5_URL . '/download.html';
        $this->assign('down_url', $down_url);
        return $this->fetch();
    }

    /*
     * 获取本月排行 + 上月第一
     */
    public function getranklist()
    {
        $params = request()->param();
        $length = $params['length'];
        $coverMod = new coverMod();
        $result = $coverMod->getranks($length);
        if (!$result) return $this->error($coverMod->getError());
        return $this->success('获取成功',$result);
    }

    /*
     * 获取当前登录用户信息,判断是否主播,主播则获取排行及票数信息
     */
    public function getuser()
    {
        $m = date('Ym');
        $key = "coverstar:m:{$m}";
        $user_id = request()->param('user_id');

        $user = new User();
        $user_info = $user->getUser($user_id,null,'user_id, nickname, avatar,is_anchor');

        if( $user_info['is_anchor'] == '1' ){
            $redis = RedisClient::getInstance();

            $votes = $redis->Zscore($key,$user_id);

            if( $votes === false ){
                $redis->zincrby($key, 0, $user_id);
                $user_info['rank'] = false;
            } else {
                $rank_history = $redis->get('coverstar:history:' . 'user:' . $user_id . ":m:{$m}");
                $ranks = $redis->Zrevrank($key,$user_id);

                if( $ranks !== false ){
                    $user_info['rank'] = $ranks + 1;
                }

                if( empty($rank_history) || $ranks == false ){
                    $user_info['ranking'] = 0;
                } else {
                    $rank_history = (int)$rank_history;

                    if( $rank_history > $user_info['rank'] ){
                        //升
                        $user_info['ranking'] = 2;
                    } elseif ( $rank_history < $user_info['rank']  ) {
                        //降
                        $user_info['ranking'] = 1;
                    } else {
                        $user_info['ranking'] = 0;
                    }
                }
            }

            $user_info['rank_vote'] = (int)$votes;

            $redis->set('coverstar:history:' . 'user:' . $user_id . ":m:{$m}", $user_info['rank']);
        }

        return json(['data'=>$user_info]);
    }

    public function tovote()
    {
        $user_id = (int)request()->param('user_id');
        $to_user_id = (int)request()->param('to_user_id');
        $vote = (int)request()->param('vote');

        if( empty($user_id) || empty($vote) || empty($to_user_id) ){
            $this->error('投票失败');
        }

        if( $user_id == $to_user_id ){
            $this->error('不能投票给自己');
        }

        if( $vote < 1 ){
            $this->error('投票失败');
        }

        $coverMod = new coverMod();

        $result = $coverMod->starToVote($user_id,$to_user_id,$vote);

        if (!$result) return $this->error($coverMod->getError());

        $this->success('投票成功');
    }

    //任务规则说明
    public function explain()
    {
        $explain = DB::name('article')->field('title, content')->where('mark', 'cover_star_vote')->find();

        if( empty($explain) ){
            $explain = [
                'title' => '活动规则',
                'content' => '暂无内容'
            ];
        }

        $this->assign('explain', $explain);

        return $this->fetch();
    }
}