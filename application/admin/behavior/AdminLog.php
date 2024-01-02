<?php

namespace app\admin\behavior;

class AdminLog
{
    //排除日志
    const noLog = [
        '/employee/readCard'
    ];
    public function run(&$params)
    {
        //只记录POST请求的日志
        if (request()->isPost() && config('fastadmin.auto_record_log')) {
            //不不记录判断
            $funcExp = '';
            $arr = json_decode($params->getContent(), true);
            $parsedUrl = parse_url($arr['url']);
            $pathArr = explode('/', $parsedUrl['path']);
            if (!empty($pathArr[2]) && !empty($pathArr[3])) {
                $funcExp = sprintf("/%s/%s", $pathArr[2], $pathArr[3]);
            }

            if (!in_array($funcExp, self::noLog)) {
                \app\admin\model\AdminLog::record();
            }
        }
    }
}
