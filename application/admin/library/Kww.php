<?php


namespace app\admin\library;


use app\cache\BaseCache;
use fast\Http;
use think\Cookie;
use think\Env;
use think\Request;
use traits\controller\Jump;

class Kww
{
//    const kww = "https://kwwhrp.kwwict.com:10213";    //生产
//    const hrNewApi = "https://10.254.30.36:8801";    //测试

    const marryList = [
        'MARRIED'=>'已婚',
        'UNMARRIED'=>'未婚',
        'DIVORCE'=>'离异'
    ];

    const statusList = [
        'ON_TRIAL'=>'试用',
        'FORMAL'=>'正常',
        'LEAVE'=>'离职'
    ];

    const orgMap = [
        '01' => '5',
        '02' => '6',
        '03' => '7',
        '04' => '8',
        '05' => '9',
        '06' => '10',
        '07' => '11',
        '08' => '12',
        '09' => '13',
        '10' => '15',
        '11' => '16',
        '12' => '17',
        '13' => '14',
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

        return ["Tokenid: $tokenId"];
    }

    /**
     * 登录
     * @return bool
     * DateTime: 2024-02-25 17:08
     */
    public static function login()
    {
        if (Env::get('app.master')) {
            $url = "http://10.254.30.50:8080/noauth/login";
        } else {
            $url = self::kww()."/api/noauth/login";
        }

        $body = [
            "url" =>  self::kww()."/api",
            "go" => self::kww()."/index/login2",
        ];

        $rs = curl_request($url, 'POST', $body);
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

        if (Env::get('app.master')) {
            $url = "http://10.254.30.50:8080/w/dispatch";
        } else {
            $url = self::kww() . "/api/w/dispatch";
        }
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
        $goStr = urlencode(self::kww()."/".$go);
        $urlPar = urlencode(self::kww()."/api");
        if (Env::get('app.master')) {
            $url = "http://10.254.30.50:8080/logout?go=$goStr&url=$urlPar";
        } else {
            $url = self::kww()."/api/logout?go=$goStr&url=$urlPar";
        }

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
        if (Env::get('app.master')) {
            $url = "http://10.254.30.50:8080/m/buckets/app/{$appId}/objects";
        } else {
            $url = self::kww()."/api/m/buckets/app/{$appId}/objects";
        }

        $body = [
            "file" => file_get_contents($file),
        ];

        $rs = curl_request($url, 'POST', $body, $header, false);;

        $data = [];
        if ($rs) {
            $data = json_decode($rs, true);
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
        if (Env::get('app.master')) {
            $url = "http://10.254.30.50:8080/w/dispatch";
        } else {
            $url = self::kww() . "/api/w/dispatch";
        }
        $body = [
            "service" => "face.face.upload",
            "version" => "1.0.0",
            "data" => [
                "empno" => $empId,
                "name" => $name,
            ],
        ];
        if (!empty($photo1)) {
            $body['data']['photo1'] = $photo1;
            $body['data']['photo1Type'] = $photo1Type;
        }
        if (!empty($photo2)) {
            $body['data']['photo2'] = $photo2;
            $body['data']['photo2Type'] = $photo2Type;
        }
//        var_dump(json_encode($body,JSON_UNESCAPED_UNICODE));exit;

        $rs = curl_request($url, 'POST', $body, $header);

        $data = [];
        if ($rs) {
            $data = json_decode($rs, true);
        }

        return $data;
    }


    /**
     * 员工列表
     * @param array $param
     * @return array|mixed
     * DateTime: 2024-02-26 16:08
     */
    public static function userList($param=[])
    {
        $header = self::header();
        if (Env::get('app.master')) {
            $url = "http://10.254.30.50:8080/w/dispatch";
        } else {
            $url = "http://220.168.154.86:18801/api/w/dispatch";
        }
        $body = [
//            "service" => "employee.info.page",
            "service" => "employee.info.allocation.page",
            "version" => "1.0.0",
            "data" => $param
        ];

        $rs = curl_request($url, 'POST', $body, $header);

        $data = ["total"=>0,"records"=>[]];
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
        if (Env::get('app.master')) {
            $url = "http://10.254.30.50:8080/w/dispatch";
        } else {
            $url = "http://220.168.154.86:18801/api/w/dispatch";
        }
        $body = [
            "service" => "employee.info.get",
            "version" => "1.0.0",
            "data" => [
                "empNum" => $empNum
            ]
        ];
//        var_dump(json_encode($body,JSON_UNESCAPED_UNICODE));exit;

        $rs = curl_request($url, 'POST', $body, $header);

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
        if (Env::get('app.master')) {
            $url = "http://10.254.30.50:8080/w/dispatch";
        } else {
            $url = "https://kwwhrp.kwwict.com:18801/api/w/dispatch";
        }
        $body = [
            "service" => "employee.info.save",
            "version" => "1.0.0",
            "data" => $param
        ];
        //var_dump(json_encode($body));exit;

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
        if (Env::get('app.master')) {
            $url = "http://10.254.30.50:8080/w/dispatch";
        } else {
            $url = self::kww() . "/api/w/dispatch";
        }
        $body = [
            "service" => "employee.org.list",
            "version" => "1.0.0",
            "data" => [
                "pid" => $pid
            ]
        ];

        $rs = curl_request($url, 'POST', $body, $header);;

        $data = ["total"=>0,"records"=>[]];
        if ($rs) {
            $datas = json_decode($rs, true);
            if (isset($datas['data'])) {
                $data = json_decode($datas['data'], true);
            }
        }

        return $data;
    }

    /**
     * 获取临时工号
     * @param $orgId
     * @return mixed|string
     * DateTime: 2024-03-05 14:09
     */
    public static function getTmpNo($orgId)
    {
        if (Env::get('app.master')) {
            $url = "http://10.254.30.43:8085/emp/tmpNo?baseNo=$orgId";
        } else {
            $url = "http://10.254.30.36:8099/emp/tmpNo?baseNo=$orgId";
        }
        $rs = Http::get($url);

        $tmpNo = '';
        if ($rs) {
            $data = json_decode($rs, true);
            if (!empty($data['data'])) {
                $tmpNo = $data['data'];
            }
        }

        return $tmpNo;
    }

    /**
     * 新增获取工号
     * @param $param
     * @return array|mixed
     * DateTime: 2024-06-04 15:42
     */
    public static function saveGetId($param)
    {
        if (Env::get('app.master')) {
            $url = "http://10.254.30.43:8090/employee/info/save";
        } else {
            $url = "http://10.254.30.59:8090/employee/info/save";
        }
        $body = [
            "service" => "employee.info.save",
            "version" => "1.0.0",
            "data" => json_encode($param)
        ];
        $rs = curl_request($url, 'POST', $body);;
        var_dump($rs);exit();

        $data = [];
        if ($rs) {
            $data = json_decode($rs, true);
        }

        return $data;
    }
}