<?php

namespace app\admin\model;

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
        return [
            '01' => '集团(万达)',
            '02' => '向家堤',
            '03' => '经开区',
            '04' => '黄家湖',
            '05' => '桃江基地',
            '06' => '邵阳基地',
            '07' => '东澳基地',
            '08' => '后安',
            '09' => '沧水铺',
            '13' => '槟榔城',
        ];
    }
}
