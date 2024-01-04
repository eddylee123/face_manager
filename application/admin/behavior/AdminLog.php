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
            \app\admin\model\AdminLog::record();
        }
    }
}
