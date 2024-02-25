<?php


namespace app\admin\library;


use app\cache\BaseCache;
use think\Cookie;
use think\Request;
use traits\controller\Jump;

class Kww
{
//    const kww = "https://kwwhrp.kwwict.com:10213";    //生产
    const hr_api = "https://10.254.30.36:8801";    //测试

    public static function kww()
    {
        return Request::instance()->domain();
    }

    public static function token()
    {
        return Cookie::get(BaseCache::kww_token);
    }

    /**
     * 登录
     * @return bool
     * DateTime: 2024-02-25 17:08
     */
    public static function login()
    {
//        if (Cookie::has("apache_")) {
//            Cookie::delete("apache_");
//            self::logout();
//        }

        $url = self::kww()."/api/noauth/login";
        $body = [
            "url" =>  self::kww()."/api",
            "go" => self::kww()."/admin123.php/index/login2",
        ];

        $rs = curl_request($url, 'POST', $body);
//        var_dump($rs);exit;
        header("Location: ".$rs);
        return true;
    }

    /**
     * 用户信息
     * @param $tokenId
     * @return array|mixed
     * DateTime: 2024-02-25 17:08
     */
    public static function getUser($tokenId)
    {
        //token缓存
        Cookie::forever(BaseCache::kww_token, $tokenId);

        $url = self::kww()."/api/w/dispatch";
        $body = [
            "service" => "user.self.info",
            "version" => "1.0.0",
            "data" => [
                "tokenId" => $tokenId
            ],
        ];
        $header = ["Tokenid: $tokenId"];

        $rs = curl_request($url, 'POST', $body, $header);;

        $data = [];
        if ($rs) {
            $datas = json_decode($rs, true);
            if (isset($datas['data'])) {
                $data = json_decode($datas['data'], true);
            }
        }

        return $data;
    }

    /**
     * 登出
     * @param $go
     * @return bool
     * DateTime: 2024-02-25 17:07
     */
    public static function logout($go)
    {
        //删缓存
        Cookie::delete(BaseCache::kww_token);

        $url = self::kww()."/api/logout?go=".self::kww()."/admin123.php/$go";

        header("Location: ".$url);

        return true;
    }

    /**
     * 文件上传
     * @param $appId
     * @param $file
     * @return array|bool|mixed
     * DateTime: 2024-02-25 17:07
     */
    public static function uploadFile($appId, $file)
    {
        $tokenId = self::token();

        $url = self::kww()."/m/buckets/app/{$appId}/objects";
        $body = [
            "file" => $file,
        ];
        $header = ["Tokenid: $tokenId"];

        $rs = curl_request($url, 'POST', $body, $header);;

        $data = [];
        if ($rs) {
            $data = json_decode($rs['data'], true);
        }

        return $data;
    }

    /**
     * 人脸照片上传
     * @param $empId
     * @param $name
     * @param $photo1
     * @param $photo1Type
     * @param string $photo2
     * @param string $photo2Type
     * @return array|bool|mixed
     * DateTime: 2024-02-25 17:07
     */
    public static function uploadFace($empId, $name, $photo1, $photo1Type, $photo2='', $photo2Type='jpg')
    {
        $tokenId = self::token();

        $url = self::kww()."/api/w/dispatch";
        $body = [
            "service" => "face.face.upload",
            "version" => "1.0.0",
            "data" => [
                "empno" => $empId,
                "name" => $name,
                "photo1" => $photo1,
                "photo1Type" => $photo1Type,
                "photo2" => $photo2,
                "photo2Type" => $photo2Type,
            ],
        ];
        $header = ["Tokenid: $tokenId"];

        $rs = curl_request($url, 'POST', $body, $header);;

        $data = [];
        if ($rs) {
            $datas = json_decode($rs, true);
            if (isset($datas['data'])) {
                $data = json_decode($datas['data'], true);
            }
        }

        return $data;
    }


    public static function userList($orgId, $param=[])
    {
//        $tokenId = self::token();
        $tokenId = "470882CF758C53D58EB9FAFD61D81015BD7017F5288DC592323B28E5D4C85429B5D2";

        $url = self::hr_api."/api/w/dispatch";
        $body = [
            "service" => "employee.info.page",
            "version" => "1.0.0",
            "data" => $param
        ];
        $header = ["Tokenid: $tokenId"];

        $rs = curl_request($url, 'POST', $body, $header);;

        $data = [];
        if ($rs) {
            $datas = json_decode($rs, true);
            if (isset($datas['data'])) {
                $data = json_decode($datas['data'], true);
            }
        }

        return $data;
    }

    public static function modify($param)
    {
        $tokenId = self::token();
        $tokenId = "470882CF758C53D58EB9FAFD61D81015BD7017F5288DC592323B28E5D4C85429B5D2";

        $url = self::hr_api."/api/w/dispatch";
        $body = [
            "service" => "employee.info.page",
            "version" => "1.0.0",
            "data" => $param
        ];
        $header = ["Tokenid: $tokenId"];

        $rs = curl_request($url, 'POST', $body, $header);;

        $data = [];
        if ($rs) {
            $datas = json_decode($rs, true);
            if (isset($datas['data'])) {
                $data = json_decode($datas['data'], true);
            }
        }

        return $data;
    }
}