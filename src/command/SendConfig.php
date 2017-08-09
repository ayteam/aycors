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
        $content = file_get_contents(VENDOR_PATH .'ayteam/aycors/src/config.php');
        $configPath = CONF_PATH.'extra/';
        $configFile = $configPath.'cors.php';
        //判断目录是否存在
        if (!file_exists($configPath)) {
            mkdir($configPath, 0755, true);
        }
        //判断文件是否存在
        if (is_file($configFile)) {
            throw new \InvalidArgumentException(sprintf('The config file "%s" already exists', $configFile));
        }
        if (false === file_put_contents($configFile, $content)) {
            throw new \RuntimeException(sprintf('The config file "%s" could not be written to "%s"', $configFile,$configPath));
        }
        $output->writeln('create cors.php ok');
    }

}