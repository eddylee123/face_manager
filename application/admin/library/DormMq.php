<?php


namespace app\admin\library;


use RocketMQ\ConsumeFromWhere;
use RocketMQ\Message;
use RocketMQ\MessageModel;
use RocketMQ\Producer;
use RocketMQ\PushConsumer;
use think\Log;

class DormMq
{
    const nameserver = '10.254.50.51:9876';
    const instance = 'dorm-sys-checkin';
    const topic = 'checkin-notification';
    const tag = 'checkin';

    /**
     * 消息发送
     * @param array $data
     * @return mixed
     * DateTime: 2024-03-28 11:45
     */
    public static function producer(array $data)
    {
        $body = json_encode($data);
        $producer = new  Producer(self::instance);
        $producer->setInstanceName(self::instance);
        $producer->setNamesrvAddr(self::nameserver);
        $producer->start();

        $message = new Message(self::topic, self::tag, $body);
        $sendResult = $producer->send($message);

        return $sendResult->getSendStatus();
    }
}