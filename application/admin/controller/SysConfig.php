<?php
namespace app\admin\controller;

use app\core\service\filter\FilterHelper;
use bxkj_common\RedisClient;
use think\facade\Request;
use think\Db;
use think\facade\Env;
use bxkj_common\DataTemplate;

class SysConfig extends Controller
{

    public function test()
    {
        $ser = new \app\admin\service\SysConfig();
        dump($ser->resetConfig(true));
    }

    public function index()
    {
        $this->checkAuth('admin:sys_config:index');
        if (Request::isGet()) {
            $info = Db::name('sys_config')->where(['mark' => 'site'])->value('value');
            $info = json_decode($info, true);
            $forbiddenWords = file_get_contents(ROOT_PATH.'application/core/service/filter/dict.txt');

            if (!empty($info['refresh_text'])) {
                $info['refresh_text'] = implode("\n", $info['refresh_text']);
            }
            $this->assign('forbidden_words', $forbiddenWords);
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $data = input();
            //$data['forbidden_words'] = explode("\n",input('forbidden_words'));
            $forbiddenWords = file_put_contents(ROOT_PATH.'application/core/service/filter/dict.txt', $data['forbidden_words']);
            FilterHelper::loadFile();
            unset($data['forbidden_words']);
            $post = json_encode($data);
            $result = Db::name('sys_config')->where(['mark' => 'site'])->update(['value' => $post]);
            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("system_config.index.edit", '修改网站设置');
            $this->success('更新成功', $result);
        }
    }

    public function app()
    {
        $this->checkAuth('admin:sys_config:app');
        if (Request::isGet()) {
            $info = Db::name('sys_config')->where(['mark' => 'app'])->value('value');
            $this->assign('_info', json_decode($info, true));
            return $this->fetch();
        } else {
            $post = input();
            $post['test_user'] = explode(',', $post['test_user']);
            $post = json_encode($post);
            $result = Db::name('sys_config')->where(['mark' => 'app'])->update(['value' => $post]);
            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("system_config.app.edit", '修改公共配置');
            $this->success('更新成功', $result);
        }
    }

    public function video()
    {
        $this->checkAuth('admin:sys_config:video');
        if (Request::isGet()) {
            $info = Db::name('sys_config')->where(['mark' => 'video'])->value('value');
            $info = json_decode($info, true);
            $this->assign('_info', $info['vod']);
            return $this->fetch();
        } else {
            $post = json_encode(input());
            $result = Db::name('sys_config')->where(['mark' => 'video'])->update(['value' => $post]);
            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            $redis = new RedisClient();
            $redis->set('video_processMedia', input('vod.platform_config.ProcessMedia'));

            alog("system_config.video.edit", '修改视频配置');
            $this->success('更新成功', $result);
        }
    }

    public function third()
    {
        $this->checkAuth('admin:sys_config:third');
        if (Request::isGet()) {
            $info = Db::name('sys_config')->where(['mark' => 'third'])->value('value');

            $this->assign('_info', json_decode($info, true));
            return $this->fetch();
        } else {
            $post = json_encode(input());
            $result = Db::name('sys_config')->where(['mark' => 'third'])->update(['value' => $post]);
            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("system_config.third.edit", '修改服务配置');
            $this->success('更新成功', $result);
        }
    }

    public function live()
    {
        $this->checkAuth('admin:sys_config:live');
        if (Request::isGet()) {
            $data = Db::name('sys_config')->where(['mark' => 'live'])->value('value');
            $data = json_decode($data, true);
            $this->assign('_info', $data['live_setting']);
            $this->assign('_task', $data['task_setting']);
            return $this->fetch();
        } else {
            $post = json_encode(input());
            $result = Db::name('sys_config')->where(['mark' => 'live'])->update(['value' => $post]);
            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("system_config.live.edit", '修改直播配置');
            $this->success('更新成功', $result);
        }
    }

    public function upload()
    {
        $this->checkAuth('admin:sys_config:upload');
        if (Request::isGet()) {
            $data = Db::name('sys_config')->where(['mark' => 'upload'])->value('value');
            $info = json_decode($data, true);
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $post = input();
            $post['invite_imgs'] = explode(',', $post['invite_imgs']);
            $post = json_encode($post);
            $result = Db::name('sys_config')->where(['mark' => 'upload'])->update(['value' => $post]);
            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("system_config.upload.edit", '修改存储配置');
            $this->success('更新成功', $result);
        }
    }

    public function sms()
    {
        $this->checkAuth('admin:sys_config:sms');
        if (Request::isGet()) {
            $info = Db::name('sys_config')->where(['mark' => 'sms'])->value('value');
            $info = json_decode($info, true);
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $post = json_encode(input());
            $result = Db::name('sys_config')->where(['mark' => 'sms'])->update(['value' => $post]);
            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("system_config.sms.edit", '修改消息配置');
            $this->success('更新成功', $result);
        }
    }

    public function product()
    {
        $this->checkAuth('admin:sys_config:product');
        if (Request::isGet()) {
            $info = Db::name('sys_config')->where(['mark' => 'product'])->value('value');
            $info = json_decode($info, true);
            if (!empty($info['product_setting']['refresh_text'])) {
                $info['product_setting']['refresh_text'] = implode("\n", $info['product_setting']['refresh_text']);
            }
            $this->assign('_info', $info['product_setting']);
            return $this->fetch();
        } else {
            $data = input();
            $data['product_setting']['refresh_text'] = explode("\n",input('product_setting.refresh_text'));
            $post = json_encode($data);

            $result = Db::name('sys_config')->where(['mark' => 'product'])->update(['value' => $post]);
            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("system_config.product.edit", '修改产品信息');
            $this->success('更新成功', $result);
        }
    }

    public function payment()
    {
        $this->checkAuth('admin:sys_config:payment');
        if (Request::isGet()) {
            $info = Db::name('sys_config')->where(['mark' => 'payment'])->value('value');
            $info = json_decode($info, true);
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $post = json_encode(input());
            $result = Db::name('sys_config')->where(['mark' => 'payment'])->update(['value' => $post]);
            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("system_config.payment.edit", '修改支付配置');
            $this->success('更新成功', $result);
        }
    }

    public function agent()
    {
        if (Request::isGet()) {
            $info = Db::name('sys_config')->where(['mark' => 'agent'])->value('value');
            $info = json_decode($info, true);
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $post = json_encode(input());
            $result = Db::name('sys_config')->where(['mark' => 'agent'])->update(['value' => $post]);
            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("system_config.agent.edit", '修改合作商配置');
            $this->success('更新成功', $result);
        }
    }

    public function beauty()
    {
        $this->checkAuth('admin:sys_config:beauty');
        $info = Db::name('sys_config')->where(['mark' => 'beauty'])->value('value');
        if (Request::isGet()) {
            $info = json_decode($info, true);
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $data = input();

            if (empty($info)) {
                $post = json_encode($data);
                $result = Db::name('sys_config')->insert(['mark' => 'beauty', 'classified' => 'app', 'value' => $post]);
            } else {
                $settingData = json_decode($info, true);
                if ($data['beauty_setting']['beauty_status'] == 2) {
                    $data['beauty_setting']['tuohuan_beauty_ios_key'] = $settingData['beauty_setting']['tuohuan_beauty_ios_key'];
                    $data['beauty_setting']['tuohuan_beauty_android_key'] = $settingData['beauty_setting']['tuohuan_beauty_android_key'];
                    $data['beauty_setting']['beauty_ios_key'] = !empty($data['beauty_setting']['beauty_ios_key']) ? $data['beauty_setting']['beauty_ios_key'] : $settingData['beauty_setting']['beauty_ios_key'];
                    $data['beauty_setting']['beauty_android_key'] = !empty($data['beauty_setting']['beauty_android_key']) ? $data['beauty_setting']['beauty_android_key'] : $settingData['beauty_setting']['beauty_android_key'];
                }
                if ($data['beauty_setting']['beauty_status'] == 1) {
                    $data['beauty_setting']['beauty_ios_key'] = $settingData['beauty_setting']['beauty_ios_key'];
                    $data['beauty_setting']['beauty_android_key'] = $settingData['beauty_setting']['beauty_android_key'];
                    $data['beauty_setting']['tuohuan_beauty_ios_key'] = !empty($data['beauty_setting']['tuohuan_beauty_ios_key']) ? $data['beauty_setting']['tuohuan_beauty_ios_key'] : $settingData['beauty_setting']['tuohuan_beauty_ios_key'];
                    $data['beauty_setting']['tuohuan_beauty_android_key'] = !empty($data['beauty_setting']['tuohuan_beauty_android_key']) ? $data['beauty_setting']['tuohuan_beauty_android_key'] : $settingData['beauty_setting']['tuohuan_beauty_android_key'];
                }
                if ($data['beauty_setting']['beauty_status'] == 0) {
                    $data['beauty_setting']['tuohuan_beauty_ios_key'] = $settingData['beauty_setting']['tuohuan_beauty_ios_key'];
                    $data['beauty_setting']['tuohuan_beauty_android_key'] = $settingData['beauty_setting']['tuohuan_beauty_android_key'];
                    $data['beauty_setting']['beauty_ios_key'] = $settingData['beauty_setting']['beauty_ios_key'];
                    $data['beauty_setting']['beauty_android_key'] = $settingData['beauty_setting']['beauty_android_key'];
                }
                $post = json_encode($data);
                $result = Db::name('sys_config')->where(['mark' => 'beauty'])->update(['value' => $post]);
            }

            if ($result > 0) {
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("system_config.beauty.edit", '修改美颜配置');
            $this->success('操作成功', $result);
        }
    }

    public function upload_ico()
    {
        $file = request()->file('ico_img');
        if($file){
            $info = $file->validate(['size'=>102400,'ext'=>'ico,png'])->move(ROOT_PATH . 'public', 'favicon.ico');
            if($info){
                return $this->success('上传成功');
            }else{
                // 上传失败获取错误信息
                $this->error($file->getError());
            }
        }
    }

    protected function DataToTemplate($data)
    {
        $controller = strtolower(\think\facade\Request::controller());
        $action = strtolower(\think\facade\Request::action());
        $modulePath = Env::get('module_path');

        $filename = "$controller.$action.json";
        $filePath = $modulePath . 'data' . DIRECTORY_SEPARATOR . $filename;

        if (is_file($filePath)) {
            $json = file_get_contents($filePath);
            if ($json) {
                $jsonData = json_decode($json, true);
                if (isset($jsonData)) {
                    DataTemplate::parse($jsonData, $data);
                }
            }
        }
        return json_encode($data);
    }
}