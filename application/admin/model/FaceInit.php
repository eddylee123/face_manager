<?php

namespace app\admin\model;

use think\Model;


class FaceInit extends Model
{
    protected $pk = 'id';
    // 表名
    protected $name = 'face_init';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

}
