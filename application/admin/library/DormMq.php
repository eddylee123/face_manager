<?php


namespace app\admin\library;



use RocketMQ\Producer;
use think\Env;

class DormMq
{
    const instance = 'dorm-sys-checkin';
    const topic = 'checkin-notification';
    const group = 'dorm-checkin';
    const tag = 'checkin';

    public static function nameserver()
    {
        return Env::get('rocketmq.nameserver', '');
    }
    /**
     * 消息发送
     * @param array $data
     * @return mixed
     * DateTime: 2024-03-28 11:45
     */


    function newProducer($group)
    {
        $producer = new Producer($group);
        $ret = $producer->setNameServerAddress("10.203.165.25:9876");
        $producer->setGroupName($group);
        $producer->setInstanceName($group);
        if ($ret == 1) {
            echo "error" . getLatestErrorMessage();
        }

        $producer->setLogPath("/tmp/");
        $ret = $producer->start();
        if ($ret != 0) {
            echo "start error" . getLatestErrorMessage() . "\n";
        }
        return $producer;
    }
}