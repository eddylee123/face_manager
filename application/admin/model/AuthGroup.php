<?php

namespace app\admin\model;

use think\Config;
use think\Model;

class AuthGroup extends Model
{
    protected $pk = 'id';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getNameAttr($value, $data)
    {
        return __($value);
    }

    public function getOrgList()
    {
        return Config::get('site.org_list');
    }
}
