<?php

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Config;
use think\facade\Env;
use think\console\input\Argument;


class Node extends Command
{
    protected function configure()
    {

        // 指令配置
        $this->setName('bxkj_node')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart")
            //->addOption('city', null, Option::VALUE_REQUIRED, 'city name')
            ->setDescription('node service run');
        // 设置参数
    }

    protected function execute(Input $input, Output $output)
    {
        $action = trim($input->getArgument('action'));

        if (!in_array($action, ['start', 'stop', 'restart']))
        {
            $output->writeln("<error>Invalid argument action:{$action}, Expected start|stop|restart .</error>");
            return false;
        }

        if ('start' == $action) $output->writeln('Starting node Server.....');

        $redis = Config::get('redis.');

        $db = Config::get('database.');

        $env = Env::get('RUN_ENV');

        $node_config = Config::get('app.node_config');
        $redis = [
            'host' => $redis['host'],
            'port' => $redis['port'],
            'db' => $redis['db'],
            'password' => $redis['auth'],
        ];

        $db = [
            'host' => $db['hostname'],
            'user' => $db['username'],
            'database' => $db['database'],
            'password' => $db['password'],
            'port' => $db['hostport'],
            'prefix' => $db['prefix']
        ];

        $node_config['redis'] = $redis;

        $node_config['mysql'] = $db;

        $node_config = json_encode($node_config);
        

   //     $command = "cd ".ROOT_PATH."application/node;node app.js '{$env}' '{$node_config}'";

         $command = "cd ".ROOT_PATH."application/node;node app.js '{$env}' '{$node_config}'";

        exec($command, $result, $status);
    	// 指令输出
    	$output->writeln($result);
    }
}
