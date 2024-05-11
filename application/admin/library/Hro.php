<?php


namespace app\admin\library;


use fast\Http;

class Hro
{
    const hrOldApi = "http://220.168.154.86:50522";

    /**
     * 更新正式工权限
     * @param $org_id
     * @param $emp_id
     * @param $cs_level
     * @param $kq_level
     * @return array|mixed
     * DateTime: 2024-02-29 15:39
     */
    public static function upEmpLevel($org_id, $emp_id, $cs_level, $kq_level)
    {
        $url = self::hrOldApi.'/UpEmpLevel';
        $body = [
            "OrderName" => "upEmpLevel",
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

    public static function checkEmpStatus($id_card, $start, $end)
    {
        $str = self::hrOldApi."/GETEmpStatus?IDCARDS=%s&sDate=%s&eDate=%s";
        $url = sprintf($str, $id_card, $start, $end);
        $rs = Http::get($url);
        if (!empty($rs)) {
            $arr = json_decode($rs, true);
            if ($arr['st'] != 0) {
                $msg = !empty($arr['msg']) ? $arr['msg'] : '员工状态异常，请核实后操作';
                if (in_array($arr['st'], ['999'])) {
                    //不能注册错误返回
                    return output(0, $msg);
                } else {
                    return output(100, $msg);
                }
            }
            return output(200);
        }
        return output(-1, '员工信息核对失败');
    }


}