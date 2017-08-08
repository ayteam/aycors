<?php

namespace Aycors\Command;
/**
 * Created by PhpStorm.
 * User: anyuan
 * Date: 2017/08/08
 * Time: 10:02
 */
use think\console\Command;
use think\console\Input;
use think\console\Output;

class SendConfig extends Command
{
    public function configure()
    {
        $this->setName('cors:config')
             ->setDescription('send config to tp folder');
    }
    public function execute(Input $input, Output $output)
    {
        //获取默认配置文件
        $config = file_get_contents(VENDOR_PATH .'aycors/tp-cors/src/config.php');
        $sendConfigPath = CONF_PATH.'extra/';
        $sendConfigFile = $sendConfigPath.'cors.php';
        mkdir($sendConfigPath);

        return file_exists($sendConfigFile) ? true : file_put_contents($sendConfigFile,$config);
    }

}