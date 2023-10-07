<?php


namespace app\api\service\music;


use app\api\service\Music;
use bxkj_module\exception\ApiException;
use think\Db;


class MusicData extends Music
{
    protected $offset = 0;

    protected $length = 9;


    public function page($params)
    {
        $this->offset = isset($params['offset']) ? $params['offset'] : 0;

        $this->length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;

        return $this;
    }



    public function musicsByOrder(array $where, $order)
    {
        $where1 = [
            'status' => 1,
        ];

        $where = array_merge($where1, $where);

        $res = Db::name('music')
            ->where($where)
            ->field('title, image, singer, lrc_link lrc, duration, id music_id, link, channel, channel_file_id')
            ->order($order)
            ->limit($this->offset, $this->length)
            ->select();
        return $res;
    }

    //搜索音乐
    public function searchMusic($words)
    {
        $where = [
            ['status', '=', 1],
            ['title', 'like', "%{$words}%"],
        ];

        //先到库内查找(歌曲名称、歌曲简介内容、专辑名称、歌词内容)
        $res = Db::name('music')
            ->where($where)
            ->field('title, image, singer, lrc_link lrc, id music_id, duration, link, channel, channel_file_id')
            ->limit($this->offset, $this->length)
            ->select();

        if (!empty($res)) return $res;

        return [];
    }



    //某个分类下的音乐
    public function musicsByCategoryId($id)
    {
        $where = [
            'category_id' => $id,
            'status' => 1,
        ];

        $res = Db::name('music')
            ->field('title, image, singer, lrc_link lrc, id music_id, duration, link, channel, channel_file_id')
            ->where($where)
            ->order('use_num')
            ->limit($this->offset, $this->length)
            ->select();

        return $res;
    }


    //音乐分类列表
    public function categoryByAll()
    {
        $rs = Db::name('music_category')->field('id category_id, name title, icon, is_recommend')->select();

        return $rs;
    }


    //音乐下的相关视频
    public function videosByMusicId($id, $order)
    {
        $prefix = config('database.prefix');

        $sql = "SELECT f.*, u.nickname, u.avatar FROM {$prefix}video f INNER JOIN {$prefix}music m ON f.music_id=m.id AND m.status=1 INNER JOIN {$prefix}user u ON f.user_id=u.user_id WHERE m.id=? ORDER BY ? LIMIT ?, ?";

        return Db::query($sql, [$id, $order, $this->offset, $this->length]);
    }


    //获取收藏夹内的音乐
    public function restoreFavorite($user_id)
    {
        $res = Db::name('music_favorites')
            ->where(['user_id' => $user_id])
            ->select();

        return $res;
    }


    //移除收藏夹内的音乐
    public function removeByFavorite($user_id, $music_id)
    {
        $res = Db::name('music_favorites')
            ->where(['user_id' => $user_id, 'music_id' => $music_id])
            ->delete();

        return $res;
    }


    //添加到收藏夹内
    public function addByFavorite($user_id, $music_id)
    {
        $data = [
            'user_id' => $user_id,
            'music_id' => $music_id,
            'create_time' => time()
        ];

        try{
            $res = Db::name('music_favorites')
                ->strict(false)
                ->insert($data);
        } catch (ApiException $e) {
            return true;
        }

        return $res;
    }


    //收藏夹内的音乐列表
    public function musicListByFavorite($user_id)
    {
        $prefix = config('database.prefix');

        $sql = "SELECT m.channel, m.channel_file_id, m.title, m.singer, m.link, m.duration, m.lrc_link lrc, m.image, m.id music_id FROM {$prefix}music_favorites mf INNER JOIN {$prefix}music m ON mf.music_id=m.id AND m.`status`=1 WHERE mf.user_id={$user_id} ORDER BY mf.create_time DESC LIMIT {$this->offset}, {$this->length}";

        return Db::query($sql);

    }


    /**
     * 记录音乐使用历史
     * @param array $params
     * @return bool
     */
    public function insertLog(array $params)
    {
        $exists = Db::name('music_use_log')->where(['user_id' => USERID, 'music_id' => $params['music_id'], 'scene' => $params['scene']])->find();

        if (!$exists)
        {
            Db::name('music_use_log')->strict(false)->insert([
                'user_id' => USERID,
                'music_id' => $params['music_id'],
                'scene' => $params['scene'],
                'create_time' => time(),
            ]);
        }
        else{
            Db::name('music_use_log')->where('id', $exists['id'])->update([
                'update_time' => time(),
            ]);
        }

        return true;
    }


    public function useMusicList()
    {
        $res = Db::name('music_use_log')->alias('mulog')
            ->join('music m', 'mulog.music_id = m.id')
            ->field('title, image, singer, lrc_link lrc, mulog.music_id, duration, link, channel, channel_file_id')
            ->order('mulog.id desc, mulog.update_time desc')
            ->limit($this->offset, $this->length)
            ->select();

        return $res;
    }


    //向第三方合作方请求
    protected function searchMusicByThird()
    {


    }

}