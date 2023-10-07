<?php


namespace app\admin\controller;

use database\Backup as dbOper;
use think\facade\Log;

class Database extends Controller
{
    public $config;
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();
        $runtime_path = \think\facade\Env::get('root_path');
        //读取备份配置
        $config = array(
            'path'     => $runtime_path . 'public' . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR,
            'part'     => 20971520,
            'compress' => 0,
            'level'    => 9,
        );
        $this->config = $config;
    }

    public function getDatabase () {
        try {
            $database = new dbOper($this->config);
            return $database;
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    /**
     * 数据库管理
     * @return mixed
     */
    public function index()
    {
        $this->checkAuth('admin:database:index');
        $data_list = $this->getDatabase()->dataList();
        $this->assign('data_list', $data_list);
        return $this->fetch();
    }

    public function import()
    {
        $data_list = $this->getDatabase()->fileList();
        $this->assign('data_list', $data_list);
        return $this->fetch();
    }

    /**
     * 备份数据库 [参考原作者 麦当苗儿 <zuojiazi@vip.qq.com>]
     * @param string|array $ids 表名
     * @param integer $start 起始行数
     * @return mixed
     */
    public function export($ids = '', $start = 0)
    {
        $this->checkAuth('admin:database:export');
        if ($this->request->isPost()) {
            if (empty($ids)) {
                return $this->error('请选择您要备份的数据表！');
            }

            if (!is_array($ids)) {
                $tables[] = $ids;
            } else {
                $tables = $ids;
            }

            //检查是否有正在执行的任务
            $lock = "{$this->config['path']}backup.lock";
            if(is_file($lock)){
                return $this->error('检测到有一个备份任务正在执行，请稍后再试！');
            } else {
                if (!is_dir($this->config['path'])) {
                    mkdir($this->config['path'], 0755, true);
                }
                //创建锁文件
                file_put_contents($lock, $this->request->time());
            }

            //生成备份文件信息
            $file = [
                'name' => date('Ymd-His', $this->request->time()),
                'part' => 1,
            ];

            // 创建备份文件
            if($this->getDatabase()->setFile($file)->Backup_Init() !== false) {
                // 备份指定表
                foreach ($tables as $table) {
                    $start = $this->getDatabase()->setFile($file)->backup($table, $start);
                    while (0 !== $start) {
                        if (false === $start) {
                            return $this->error('备份出错！');
                        }
                        $start = $this->getDatabase()->setFile($file)->backup($table, $start[0]);
                    }
                }
                // 备份完成，删除锁定文件
                unlink($lock);
            }
            alog("system.database.back", '备份数据库：'.implode(",", $tables));
            return $this->success('备份完成。');
        }
        return $this->error('备份出错！');
    }

    /**
     * 恢复数据库 [参考原作者 麦当苗儿 <zuojiazi@vip.qq.com>]
     * @param string|array $ids 表名
     * @param integer $start 起始行数
     * @return mixed
     */
    public function restore($id = '')
    {
        $this->checkAuth('admin:database:restore');
        if (empty($id)) {
            return $this->error('请选择您要恢复的备份文件！');
        }

        $name  = date('Ymd-His', $id) . '-*.sql*';
        $path  = $this->config['path'] . $name;
        $files = glob($path);
        $list  = array();
        foreach($files as $name){
            $basename = basename($name);
            $match    = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
            $gz       = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
            $list[$match[6]] = array($match[6], $name, $gz);
        }

        // 检测文件正确性
        $last = end($list);

        Log::write('file-list');
        Log::write($list);

        if(count($list) === $last[0]){
            foreach ($list as $item) {
                $start = $this->getDatabase()->setFile($item)->import(0);
                // 导入所有数据
                while (0 !== $start) {
                    if (false === $start) {
                        return $this->error('数据恢复出错！');
                    }
                    $start = $this->getDatabase()->setFile($item)->import($start[0]);
                }
            }
            alog("system.database.restore", '回复数据库文件 ID：'.$id);
            return $this->success('数据恢复完成。');
        }
        return $this->error('备份文件可能已经损坏，请检查！');
    }

    /**
     * 优化数据表
     * @return mixed
     */
    public function optimize($ids = '')
    {
        $this->checkAuth('admin:database:optimize');
        if (empty($ids)) {
            return $this->error('请选择您要优化的数据表！');
        }

        if (!is_array($ids)) {
            $table[] = $ids;
        } else {
            $table = $ids;
        }

        try {
            $this->getDatabase()->optimize($table);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        alog("system.database.optimize", '优化数据库：'.implode(",", $table));
        return $this->success('数据表优化完成。');
    }

    /**
     * 修复数据表
     * @return mixed
     */
    public function repair($ids = '')
    {
        $this->checkAuth('admin:database:repair');
        if (empty($ids)) {
            return $this->error('请选择您要修复的数据表！');
        }

        if (!is_array($ids)) {
            $table[] = $ids;
        } else {
            $table = $ids;
        }

        try {
            $this->getDatabase()->repair($table);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        alog("system.database.repair", '修复数据库：'.implode(",", $table));
        return $this->success('数据表修复完成。');
    }

    /**
     * 删除备份
     * @return mixed
     */
    public function del($id = '')
    {
        $this->checkAuth('admin:database:delete');
        if (empty($id)) {
            return $this->error('请选择您要删除的备份文件！');
        }

        try {
            $this->getDatabase()->delFile($id);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        alog("system.database.del", '删除备份数据库文件 ID：'.$id);
        return $this->success('备份文件删除成功。');
    }
}