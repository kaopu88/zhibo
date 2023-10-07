<?php


namespace bxkj_module\service;


use think\Db;
use think\facade\Log;
use think\facade\Env;
use bxkj_common\RedisClient;

class UserAddressBook extends Service
{
    public function save($user_id,$data)
    {
//        Log::init(['type' => 'File', 'path' => Env::get('runtime_path') . 'addbook/']);
//
//        Log::write($user_id);
//        Log::write($data);

        $redis = RedisClient::getInstance();
        $key = "book:{$user_id}";

        $isUpdate = 0;

        if ($redis->exists($key)) {
            $key = "book_new:{$user_id}";
            $isUpdate = 1;
        }

        $i = 0;
        $total = 50;
        $book_data = [];
        $strReplace=array(" ","　","-","+","#","*",";");
        while ( count($data) > $i ){
            $info = $data[$i];
            $phones = $info['p'];
            foreach ( $phones as $v ) {

//                Log::write($v);

                $v = str_replace($strReplace, '', $v);
                //小于7位跳过不记录
                if( strlen(trim($v)) < 7 || strlen(trim($v)) > 15 ){
                    continue;
                }

                //国内手机号码 查找是否存在,已存在则写入user_id
                //其他号码直接存储,不做后期redis比对
                if( preg_match('/^([+]?(86))?((13[0-9])|14[0-9]|(15[0-9])|(18[0-9]))[0-9]{8}$/', $v) ){
                    if( strlen($v) == 14 ) $v = substr($v,3,11);
                    if( strlen($v) == 13 ) $v = substr($v, 2, 11);
                    //写入redis
                    $redis->sadd($key,$v);
                }

//                Log::write($v);

                $book_data[$v] = $info['n'];
            }

            if( $isUpdate == 0 && $i == $total ){
                //入库
                $this->addAll($user_id,$book_data);
            }
            $i++;
        }

        $this->addAll($user_id,$book_data);

        if( $isUpdate == 1 ){
            $key2 = "book:{$user_id}";

            $delData = $redis->SDIFF($key2,$key);

            if( !empty($delData) ){
                $this->del($user_id,$delData);
            }

            $redis->Rename($key,$key2);
        }

        return true;
    }

    public function addAll($user_id,$data)
    {
        $follow = new Follow();

//        Log::init(['type' => 'File', 'path' => Env::get('runtime_path') . 'addbook/']);
//
//        Log::write($data);

        foreach ( $data as $k=>$v ){

//            Log::write($k);

            $insertData = [];
            $where = [];
            $insertData['user_id'] = $user_id;
            $insertData['phone'] = $k;
            $insertData['name'] = $v?: '未知';
            $insertData['status'] = 0;

            $where['user_id'] = $user_id;
            $where['phone'] = $k;

            if( preg_match('/^([+]?(86))?((13[0-9])|14[0-9]|(15[0-9])|(18[0-9]))[0-9]{8}$/', $k) ){

//                Log::write('手机号码');
//                Log::write('数据库查询');

                $phone_to_id = Db::name('user')->where(['phone'=>$k])->value('user_id');
                if( $phone_to_id == $user_id ) continue;
                if( !empty($phone_to_id) ){
                    $insertData['friend_id'] = $phone_to_id;
                    $isFollow = $follow->isFollow($user_id, $phone_to_id);
                    $insertData['is_follow'] = $isFollow ? '1' : '0';
                    $insertData['status'] = 1;
                }
            }

            $ab_id = Db::name('user_address_book')->where($where)->value('id');

            if( empty($ab_id) ){
                Db::name('user_address_book')->insert($insertData);
            } else {
                Db::name('user_address_book')->where(['id'=>$ab_id])->update($insertData);
            }

        }

        return true;
    }

    public function del($user_id,$data)
    {
//        Log::init(['type' => 'File', 'path' => Env::get('runtime_path') . 'delbook/']);
//
//        Log::write($user_id);
//        Log::write($data);

        $where = [];
        $where['user_id'] = $user_id;

        foreach ( $data as $v ){
            $where['phone'] = $v;
            Db::name('user_address_book')->where($where)->update(['status'=>0]);
        }

        return true;
    }
}