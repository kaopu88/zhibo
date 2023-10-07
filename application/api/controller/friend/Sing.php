<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/21
 * Time: 上午 10:53
 */

namespace app\api\controller\friend;

use app\admin\service\SysConfig;
use app\api\service\Follow as FollowModel;
use app\common\controller\UserController;
use app\friend\service\FriendCircleAuthor;
use app\friend\service\FriendCircleComment;
use app\friend\service\FriendCircleLyric;
use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;

class Sing extends UserController
{
    public function __construct()
    {
        parent::__construct();
        $redis       = new RedisClient();
        $cacheFriend = $redis->exists('cache:friend_config');
        if (empty($cacheFriend)) {
            $arr  = [];
            $ser  = new SysConfig();
            $info = $ser->getConfig("friend");
            if (empty($info)) return [];
            $redis->setex('cache:friend_config', 4 * 3600, $info['value']);
        }
        $friendConfigRes       = $redis->get('cache:friend_config');
        $this->friendConfigRes = json_decode($friendConfigRes, true);
        if ($this->friendConfigRes['is_open'] == 0) {
            $errorMsg = '未开启交友功能';
            if (!empty($errorMsg)) {
                throw new ApiException((string)$errorMsg, 1);
            }
        }
    }

    /**
     * 热门歌词
     */
    public function getHot()
    {
        $lyrics = new FriendCircleLyric();
        $rest   = $lyrics->getQuery(['is_hot' => 1, 'status' => 1], 'id,title,initial,lyrics,author', 'id desc');
        foreach ($rest as $k => $v) {
            $lyricsArray = [];
            $restArray   = unserialize($v['lyrics']);
            foreach ($restArray as $k1 => $v1) {
                $lyricsArray[] = [
                    'lyrics_key' => $k1 + 1,
                    'value'      => $v1,
                ];
            }
            $rest[$k]['lyrics'] = $lyricsArray;
        }
        return $this->success($rest, '查询成功');
    }

    /**
     * 获取作者列表A-Z排序
     *
     */
    public function getAuthorList()
    {
        $authorService = new FriendCircleAuthor();
        $rest          = $authorService->getQuery([], '*', 'initial');
        return $this->success($rest, '查询成功');
    }

    /**
     * 根据作者名返还相关的歌词
     *
     */
    public function getAuthorWorks()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Sing();
        $result   = $validate->scene('getAuthorWorks')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $lyrics     = new FriendCircleLyric();
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $rest       = $lyrics->pageQuery($page_index, $page_size, ['status' => 1, 'author_id' => $params['author_id']], 'id desc', '*');
        if (!empty($rest['data'])) {
            foreach ($rest['data'] as $k => $v) {
                $rest['data'][$k]['lyrics'] = s2array($v['lyrics']);
            }
        }
        return $this->success($rest, '查询成功');
    }

    /**
     * 搜索歌名作者
     *
     */
    public function searchList()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Sing();
        $result   = $validate->scene('searchList')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $lyrics = new FriendCircleLyric();
        //  $where1[] = ['author|title', 'like', '%' . $get['keyword'] . '%'];
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $where[]    = ['author|title', 'like', '%' . $params['key'] . '%'];
        $rest       = $lyrics->pageQuerySearch($page_index, $page_size, $where, 'id desc', "*");
        return $this->success($rest, '查询成功');
    }

    /**
     * 点击换一个更换歌词
     *
     */
    public function changeAnother()
    {
        $lyrics         = new FriendCircleLyric();
        $count          = $lyrics->countTotal(['status' => 1]);
        $rand           = rand(1, $count);
        $rest           = $lyrics->find(['id' => $rand]);
        $lyrics2array   = s2array($rest['lyrics']);
        $rest['lyrics'] = $lyrics2array;
        return $this->success($rest, '查询成功');
    }

    /**
     * 接唱接口
     *
     */
    public function marebulabulas()
    {
        $userId = USERID;
        $submit = submit_verify('marebulabulas' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('subMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        if ($params['extend_type'] == 3 && !empty($params['extend_talk'])) {
            if (empty($params['sing_title']) || empty($params['sing_author'])) {
                return $this->jsonError('接唱的歌词和作者不能为空');
            }
        }
        if (!empty($params['privateid'])) {
            $followModel  = new FollowModel();
            $privateArray = explode(',', trim($params['privateid'], ','));
            if (!empty($privateArray)) {
                foreach ($privateArray as $k => $v) {
                    $followInfo = $followModel->getFollowInfo(USERID, $v);
                    if (empty($followInfo['is_follow'])) {
                        return $this->jsonError('您还未关注该用户');
                    }
                }
            }
        }
        if ($this->friendConfigRes['msg_examine'] == 1) {
            $status = 0;
        } else {
            $status = 1;
        }
        //首先在评论去加入一条接唱评论
        $sysplus2Array            = json_decode($params['systemplus'], true);
        $paramscomment['fcmid']   = $sysplus2Array['parent_id'];
        $paramscomment['content'] = '';
        $paramscomment['uid']     = $userId;
        $paramscomment['voice']   = $params['voice'];
        $friendComment            = new FriendCircleComment();
        if ($this->friendConfigRes['msg_commment_examine'] == 1) {
            $paramscomment['status'] = 0;
        } else {
            $paramscomment['status'] = 1;
        }
        $rest = $friendComment->add($paramscomment);
        if (!$rest) return $this->jsonError('操作失败');
        //自动发布一条合成的合唱动态
        $singUserArray = explode(',', $sysplus2Array['uid']);
        if (!empty($singUserArray)) {
            $singFrined = '';
            foreach ($singUserArray as $k => $v) {
                $singFrined = $singFrined . '@' . userMsg($v, 'nickname')['nickname'];
            }
        }
        $params['content'] = '我和@' . trim($singFrined) . '一起合唱了' . $params['sing_author'] . '的《' . $params['sing_title'] . '》，快来听听吧!';
        $rest              = systemSend($userId, $params['content'], $params['picture'], $params['video'], $params['voice']
            , $params['location'], $params['type'], $params['msg_type'], $params['title'], $params['extend_type'],
            $params['privateid'], $params['systemtype'], $params['systemplus'], $params['extend_talk'],
            $params['extend_circle'], $params['render_type'], $params['cover_url'], $params['dynamic_title'], $params['address'], $status, $params['sing_title'], $params['sing_author']);
        if ($rest['code'] == -1) return $this->jsonError($rest['msg']);
        return $this->success($rest['rest'], '发布成功');
    }

    /**
     * 作者或歌词点击接口
     *
     */
    public function lyricsorAuthor()
    {
        $redis      = RedisClient::getInstance();
        $params     = request()->param();
        $validate   = new \app\api\validate\Sing();
        $result     = $validate->scene('lyricsorAuthor')->check($params);
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $where = [];
        if ($params['stype'] == 1) {
            $where = ['sing_title' => $params['key']];
            $msg1  = new FriendCircleLyric();
            $rest  = $msg1->mySing($page_index, $page_size, $where, 'id desc', '*');
        }
        if ($params['stype'] == 2) {
            $where = ['sing_author' => $params['key']];
            $msg2  = new FriendCircleLyric();
            $rest  = $msg2->mySing($page_index, $page_size, $where, 'id desc', '*');
        }
        if (!empty($rest['data'])) {
            foreach ($rest['data'] as $k => $v) {
                $sysplus2Array = json_decode($v['systemplus'], true);
                $singUserArray = explode(',', trim($sysplus2Array['uid'], ","));
                if (!empty($singUserArray)) {
                    $singFrined = [];
                    foreach ($singUserArray as $k1 => $v1) {
                        $singFrined[] = userMsg($v1, 'user_id,avatar,nickname,gender');
                    }
                } else {
                    $singFrined = [];
                }
                $lyrics      = new FriendCircleLyric();
                $singlyrics  = unserialize($lyrics->find(['id' => $v['extend_talk']])['lyrics']);
                $lyricsArray = [];
                foreach ($singlyrics as $k2 => $v2) {
                    $lyricsArray[] = [
                        'lyrics_key' => $k2 + 1,
                        'value'      => $v2,
                    ];
                }
                $lyricskey = $sysplus2Array['id'];
                $songArray = explode(',', trim($sysplus2Array['parent_id'], ","));
                $songs     = [];
                if (!empty(array_filter($songArray))) {
                    foreach ($songArray as $ksong => $vsong) {
                        $redisGet = $redis->get("bx_friend_msg:" . $vsong);
                        $songs[]  = json_decode($redisGet, true)[0]['voice'];
                    }
                }
                $songs[]                  = $v['voice'];
                $rest['data'][$k]['song'] = [
                    'singFrined' => array_filter($singFrined),
                    'singlyrics' => $lyricsArray,
                    'lyricskey'  => $lyricskey,
                    'songs'      => $songs,
                ];
            }
        }
        return $this->success($rest, '获取成功');
    }

    /**
     * 我的发布
     * @return Json
     */
    public function mySenderSing()
    {
        $redis      = RedisClient::getInstance();
        $params     = request()->param();
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $lyric      = new FriendCircleLyric();
        $rest       = $lyric->mySing($page_index, $page_size, ['uid' => USERID, 'extend_type' => 3], 'id desc', "*");
        if (!empty($rest['data'])) {
            foreach ($rest['data'] as $k => $v) {
                $sysplus2Array = json_decode($v['systemplus'], true);
                $singUserArray = explode(',', trim($sysplus2Array['uid'], ","));
                if (!empty($singUserArray)) {
                    $singFrined = [];
                    foreach ($singUserArray as $k1 => $v1) {
                        $singFrined[] = userMsg($v1, 'user_id,avatar,nickname,gender');
                    }
                } else {
                    $singFrined = [];
                }
                $lyrics      = new FriendCircleLyric();
                $singlyrics  = unserialize($lyrics->find(['id' => $v['extend_talk']])['lyrics']);
                $lyricsArray = [];
                foreach ($singlyrics as $k2 => $v2) {
                    $lyricsArray[] = [
                        'lyrics_key' => $k2 + 1,
                        'value'      => $v2,
                    ];
                }
                $lyricskey = $sysplus2Array['id'];
                $songArray = explode(',', trim($sysplus2Array['parent_id'], ","));
                $songs     = [];
                if (!empty(array_filter($songArray))) {
                    foreach ($songArray as $ksong => $vsong) {
                        $redisGet = $redis->get("bx_friend_msg:" . $vsong);
                        $songs[]  = json_decode($redisGet, true)[0]['voice'];
                    }
                }
                $songs[]                  = $v['voice'];
                $rest['data'][$k]['song'] = [
                    'singFrined' => array_filter($singFrined),
                    'singlyrics' => $lyricsArray,
                    'lyricskey'  => $lyricskey,
                    'songs'      => $songs,
                ];
            }
        }
        return $this->success($rest, '获取成功');
    }
}