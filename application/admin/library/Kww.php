<?php


namespace app\admin\library;


use app\cache\BaseCache;
use think\Cookie;
use think\Request;
use traits\controller\Jump;

class Kww
{
//    const kww = "https://kwwhrp.kwwict.com:10213";    //生产
    const hrNewApi = "https://10.254.30.36:8801";    //测试

    const marryList = [
        'MARRIED'=>'已婚',
        'UNMARRIED'=>'未婚',
        'DIVORCE'=>'离异'
    ];

    const statusList = [
        'NORMAL'=>'正常',
        'LEAVE'=>'离职'
    ];

    public static function kww()
    {
        return Request::instance()->domain();
    }

    public static function token()
    {
        return Cookie::get(BaseCache::kww_token);
    }

    public static function header()
    {
        $tokenId = self::token();
//        $tokenId = "712802BCF4080F9E6BB8FFEF6D668C369E5917F5288DC592323B28E5D4C85429B5D2";

        return ["Tokenid: $tokenId"];
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
//        Cookie::delete(BaseCache::kww_token);
        $cookies = $_COOKIE;
        foreach ($cookies as $key => $value) {
            setcookie($key, '', time() - 3600);
        }

        $goStr = urlencode(self::kww()."/admin123.php/$go");
        $url1 = urlencode(self::kww()."/api");
        $url = self::kww()."/api/logout?go=$goStr&url=$url1";

        header("Location: ".$url);

        return $url;
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
        $header = self::header();
        $url = self::kww()."/m/buckets/app/{$appId}/objects";
        $body = [
            "file" => $file,
        ];

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
        $header = self::header();
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
     * 员工列表
     * @param $orgId
     * @param array $param
     * @return array|mixed
     * DateTime: 2024-02-26 16:08
     */
    public static function userList($orgId, $param=[])
    {
        $header = self::header();
        $url = self::hrNewApi."/api/w/dispatch";
        $body = [
            "service" => "employee.info.page",
            "version" => "1.0.0",
            "data" => $param
        ];

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
     * 员工详情
     * @param $empNum
     * @return array|mixed
     * DateTime: 2024-02-26 16:08
     */
    public static function userInfo($empNum)
    {
        $header = self::header();
        $url = self::hrNewApi."/api/w/dispatch";
        $body = [
            "service" => "employee.info.get",
            "version" => "1.0.0",
            "data" => [
                "empNum" => $empNum
            ]
        ];

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
     * 员工编辑
     * @param $param
     * @return array|mixed
     * DateTime: 2024-02-26 16:08
     */
    public static function modify($param)
    {
        $header = self::header();
        $url = self::hrNewApi."/api/w/dispatch";
        $body = [
            "service" => "employee.info.save",
            "version" => "1.0.0",
            "data" => $param
        ];

        $rs = curl_request($url, 'POST', $body, $header);;

        $data = [];
        if ($rs) {
            $data = json_decode($rs, true);
        }

        return $data;
    }

    /**
     * 单位列表
     * @param int $pid
     * @return array|mixed
     * DateTime: 2024-02-26 21:54
     */
    public static function orgList($pid=0)
    {
        $header = self::header();
        $url = self::hrNewApi."/api/w/dispatch";
        $body = [
            "service" => "employee.org.list",
            "version" => "1.0.0",
            "data" => [
                "pid" => $pid
            ]
        ];

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