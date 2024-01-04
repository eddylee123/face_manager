<?php


namespace app\admin\library;

use fast\Http;
use think\Cookie;

/**
 * 依时丽接口
 * Class Ysl
 * @package app\admin\library
 */
class Ysl
{
    protected $companyCode = '800058';
    protected $appKey = '86469cb71e9645bcbd04ee55fda37e19';
    protected $domain = 'https://gaogu.golgu.com.cn';

    public function photoHandle($pid, $photo)
    {
        //获取token
//        $key = 'face:check:ysl:ysl_token';
//        $redis = alone_redis();
//        $token = $redis->get($key);
//        if (empty($token)) {
//            $res = $this->getToken();
//                if ($res['code'] == 200) {
//                    $redis->set($key, $res['data']['token'], 7200);
//                    $token = $res['data']['token'];
//                }
//            }
//        if (empty($token)) {
//            return output(0, 'token异常');
//        }
        $token = $this->getTokenNew();

        $rs = $this->checkPhoto($token, $pid, $photo);

        if (empty($rs) || empty($rs['code'])) {
            return output(0, '图片检测失败');
        }
        return $rs;
    }
    /**
     * 获取服务商token
     * @return bool|string
     * DateTime: 2023/11/24 15:04
     */
    public function getToken()
    {
        $method = 'POST';
        $disposeUrl = '/api/refresh/gettoken.htm';

        $timestamp = date('Y-m-d H:i:s');
        $body = [
            'companyCode' => $this->companyCode,
            'timesTamp' => $timestamp,
            'hashCode' => $this->setCode($timestamp)
        ];

        return self::curlRequest($disposeUrl, $method, $body);
    }

    public function getTokenNew()
    {
        $url = "http://10.254.30.36:8081/face/ysl/token";
        return Http::get($url);
    }


    /**
     * 照片检测
     * @param $token
     * @param $pid
     * @param $photo
     * @return mixed
     * DateTime: 2023/12/6 16:00
     */
    public function checkPhoto($token, $pid, $photo)
    {

        $method = 'POST';
        $disposeUrl = '/api/check/checkphoto.htm';

        //保存图片
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $photo, $result)){
//            $dir = ROOT_PATH.'/public/photo';
            $dir = "/www/winshare2";
            if (!is_dir($dir)) {
                mkdir($dir,0777,true);
            }

//            $ext = $result[2];
            $ext = 'jpg';
            $new_file = $dir."/{$pid}.{$ext}";
            $file_base64 = str_replace($result[1], '', $photo);
            $save = file_put_contents($new_file, base64_decode($file_base64));
            if (!$save){
                return output(0);
            }

            //检测
            if ($ext == 'jpg') {
                $type = 1;
            } elseif ($ext == 'png') {
                $type = 2;
            } else {
                $type = 3;
            }
            $body = [
                'companyCode' => $this->companyCode,
                'token' => $token,
                'photoid' => $pid,
                'type' => $type,
                'photo' => $file_base64,
                'ischeck' => 1,
            ];

            $rs = self::curlRequest($disposeUrl, $method, $body);
            if (empty($rs) || empty($rs['code'])) {
                return output(0);
            }
//            $rs = [
//                'code'=>200,
//                'msg'=>'成功'
//            ];
//            $new_file = 'E:\face_shaoyang\img_bak2\z230600340.jpg';

            return output($rs['code'], $rs['msg'], ['new_file'=>$new_file]);
        }

        return output(0);
    }

    protected function setCode($timestamp)
    {
        return strtolower(md5($this->companyCode.$this->appKey.$timestamp));
    }

    public function curlRequest($disposeUrl, $method = '', $data = '', $header = [])
    {
        $url = $this->domain.$disposeUrl;
        $data_string = json_encode($data);

        $action = curl_init();
        curl_setopt($action, CURLOPT_URL, $url);
        curl_setopt($action, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($action, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($action, CURLOPT_HEADER, 0);
        curl_setopt($action, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($action, CURLOPT_SSL_VERIFYHOST, 0);
        if (strtoupper($method) == 'POST') {
            curl_setopt($action, CURLOPT_POST, 1);
            curl_setopt($action, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($action, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );
        }
        $result = curl_exec($action);
        curl_close($action);

        $result = json_decode($result, true);
        return $result;
    }
}