<?php

namespace app\admin\behavior;

class AdminLog
{
    const noLog = [
        '/employee/readCard'
    ];
    public function run(&$params)
    {
        //只记录POST请求的日志
        if (request()->isPost() && config('fastadmin.auto_record_log')) {
            //不不记录判断
            $arr = json_decode($params->getContent(), true);
            $parsedUrl = parse_url($arr['url']);
            \app\admin\model\AdminLog::record();
        }
    }
}
