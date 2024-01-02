<?php

namespace app\admin\model;

use think\Model;


class SocketCache extends Model
{

    

    

    // 表名
    protected $name = 'socket_cache';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];


    public static function sets($key, $val)
    {
        return self::insert([
            'key' => $key,
            'value' => $val,
        ]);
    }

    public static function gets($key)
    {
        return self::where(['key' => $key])->value('value');
    }

    public static function dels($key)
    {
        return self::where(['key' => $key])->delete();
    }

    







}
