<?php


namespace app\admin\library;


class Hro
{
    const hrOldApi = "http://220.168.154.86:50522";

    public static function upEmpLevel($org_id, $emp_id, $cs_level, $kq_level)
    {
        $url = self::hrOldApi.'/upEmpLevel';
        $body = [
            "OrderName" => "UpEmpLevel",
            "data" => [
                "ORGID" => $org_id,
                "EMPID" => $emp_id,
                "XFLevel" => $cs_level,
                "MJLevel" => $kq_level
            ]
        ];
//        var_dump(json_encode($body,JSON_UNESCAPED_UNICODE));exit;

        $rs = curl_request($url, 'POST', $body);
        $data = [];
        if ($rs) {
            $data = json_decode($rs, true);
        }

        return $data;
    }
}