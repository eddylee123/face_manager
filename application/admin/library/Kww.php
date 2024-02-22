<?php


namespace app\admin\library;


use think\Cookie;

class Kww
{
//    const kww = "https://kwwhrp.kwwict.com:10213";    //生产
        const kww = "https://10.254.30.36:8800";    //测试

    public static function login()
    {
//        if (Cookie::has("apache_")) {
//            Cookie::delete("apache_");
//            self::logout();
//        }

        $url = self::kww."/api/noauth/login";
        $body = [
            "url" =>  self::kww."/api",
            "go" => self::kww."/admin123.php/index/login2",
        ];

        $rs = curl_request($url, 'POST', $body);
//        var_dump($rs);exit;
        header("Location: ".$rs);
        return;
    }

    public static function getUser($tokenId)
    {
        $url = self::kww."/api/w/dispatch";
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

    public static function logout($go)
    {
        $url = self::kww."/api/logout?go=".self::kww."/admin123.php/$go";

        header("Location: ".$url);

        return;
    }
}