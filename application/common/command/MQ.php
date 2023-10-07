<?php

namespace app\common\command;

use app\mq\controller\Master;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\console\input\Argument;


class MQ extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('bxkj_mq')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart")
            /*->addOption('debug', 'debug', Option::VALUE_OPTIONAL,
                'The debug to server the debug mode', false)*/
            ->setDescription('rabbitMQ service run');
        
    }

    protected function execute(Input $input, Output $output)
    {
        $action = trim($input->getArgument('action'));

        /*$debug = trim($input->getArgument('debug'));

        if (empty($debug))
        {
            $debug = config('app.app_debug');
        }*/

        if (!in_array($action, ['start', 'stop', 'restart']))
        {
            $output->writeln("<error>Invalid argument action:{$action}, Expected start|stop|restart .</error>");
            return false;
        }

//        php -c %MQ_PHP_INI% %cd%\index.php mq/master/wstart/process/%2/env/%1/debug/%3

        $Master = new Master();

        $Master->start();

        if ('start' == $action) $output->writeln('Starting MQ Server...');

    	// 指令输出
    	$output->writeln('rabbitMQ run ok!');
    }
}
