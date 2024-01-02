<?php

namespace app\gateway\controller;

use app\admin\model\SocketCache;
use GatewayWorker\Lib\Gateway;
use think\Env;
use think\Exception;

class Events
{
    /**
     * 每个进程启动
     * @param $worker
     */
    public static function onWorkerStart($worker)
    {

    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $reqData 具体消息
     */
    public static function onMessage($client_id, $reqData)
    {
        if (Env::get('app.debug') == true) {
            echo $reqData.PHP_EOL;
        }

        $info = json_decode($reqData, true);
        //数据格式验证
        if (!is_array($info)) {
            Gateway::sendToClient($client_id, json_encode(output(3, '数据格式有误')));
            return false;
        }

        if (!empty($info['device_code'])) {
            $device_code = $info['device_code'];
            //绑定设备ip
            if(Gateway::isUidOnline($device_code) == 0){
                [$org, $device] = explode('_', $device_code);
                Gateway::bindUid($client_id, $device_code);
                //加入基地分组
                Gateway::joinGroup($client_id, strval($org));

                Gateway::sendToClient($client_id, json_encode(output(200, '设备绑定成功')));
                return true;
            }
        }

        if($info['code'] == 300) {
            //暂存
            $device_code = Gateway::getUidByClientId($client_id);
            if (empty(SocketCache::gets($device_code))) {
                SocketCache::sets($device_code, json_encode($info['data']));
            }
        }

        Gateway::sendToClient($client_id, json_encode(output(200, '设备正常')));
        return true;
    }


    /**
     * 当连接断开时触发的回调函数
     * @param $client_id
     */
    public static function onClose($client_id)
    {
//        $edgeId = $_SESSION['gw_uid'];
//        General::close($edgeId);
//        Gateway::sendToClient($client_id, json_encode(['ret' => "0", 'ret_msg' => "disconnect"]));
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public static function onError($client_id, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 向客户端发送数据
     * @param $device_code
     * @param $reqData
     * @return array
     */
    public static function sendMessage($device_code, $reqData)
    {
        include_once dirname(__FILE__) . '/../const.php';
        Gateway::$registerAddress = GW_REGISTER_ADDRESS;
        Gateway::$persistentConnection = false;

        try {
            if (Gateway::isUidOnline($device_code) == 0) {
                return output(0, $device_code);
            }
            Gateway::sendToUid($device_code, json_encode($reqData, JSON_UNESCAPED_UNICODE));

            return output(200);
        } catch (Exception $e) {
            return output(0);
        }

    }

    public static function getDevice($org_id)
    {
        include_once dirname(__FILE__) . '/../const.php';
        Gateway::$registerAddress = GW_REGISTER_ADDRESS;
        return Gateway::getUidListByGroup($org_id);
    }

//    public static function leaveDevice($org_id, $device)
//    {
//        include_once dirname(__FILE__) . '/../const.php';
//        Gateway::$registerAddress = GW_REGISTER_ADDRESS;
//        $clientId = Gateway::getClientIdByUid($device);
//        $clientId = $clientId[0];
//        Gateway::leaveGroup((int)$clientId, $org_id);
//    }
}