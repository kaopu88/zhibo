<?php
namespace app\h5\controller;


use bxkj_common\CoreSdk;
use bxkj_module\controller\Web;
use think\Db;
use think\Request;


class Pk extends Web
{


    //PK规则说明
    public function explain()
    {
        $explain = DB::name('article')->field('title, content')->where('mark', 'pk_explain')->find();

        $this->assign('explain', $explain);

        return $this->fetch();
    }


    //PK历史战绩
    public function record(Request $request)
    {
        $win_ratio = 0; //胜率

        $profit = 0; //收益

        $params = $request->param();

        $pk = Db::name('live_pk')->where('active_id', $params['user_id'])->field('pk_res, active_id, target_id, active_income, target_income')->whereOr('target_id', $params['user_id'])->select();

        if (!empty($pk))
        {
            foreach ($pk as $value)
            {
                if ($params['user_id'] == $value['active_id'])
                {
                    $profit += $value['active_income'];

                    $value['pk_res'] == 1 && $win_ratio++;
                }
                else{
                    $profit += $value['target_income'];

                    $value['pk_res'] == -1 && $win_ratio++;
                }
            }
            $win_ratio = round($win_ratio/count($pk), 2)*100;
        }

        $this->assign('win_ratio', $win_ratio);

        $this->assign('uid', $params['user_id']);

        $this->assign('profit', number_format2($profit));

        $this->assign('pk_num', count($pk));

        return $this->fetch();
    }

    //ajax获取历史成绩
    public function getRecord()
    {
        $data = [];

        $params = input();

        $pk = Db::name('live_pk')->where('active_id', $params['user_id'])->whereOr('target_id', $params['user_id'])->order('id desc')->paginate(['list_rows' =>15, 'page' => $params['p']])->toArray();

        if (!empty($pk))
        {
            $coreSdk = new CoreSdk();

            foreach ($pk['data'] as $value)
            {
                $opponent = $params['user_id'] == $value['active_id'] ? $value['target_id'] : $value['active_id'];

                $opponent_info = $coreSdk->post('user/get_user', ['user_id'=>$opponent]);

                $data[] = [
                    'win_color' => $value['pk_res'] == 1 ? 'rgb(75,236,81)' : ($value['pk_res'] == -1 ? 'rgb(250,73,113)' : 'rgb(113,179,240)'),
                    'win_string' => $value['pk_res'] == 1 ? '胜利' : ($value['pk_res'] == -1 ? '失败' : '平局'),
                    'opponent' => [
                        'user_id' => $opponent_info['user_id'],
                        'nickname' => $opponent_info['nickname'],
                        'avatar' => $opponent_info['avatar'],
                    ],
                ];
            }
        }

        $this->success('ok', ['lists'=>$data, 'page_num'=>ceil($pk['total']/15)]); //历史战绩
    }







}