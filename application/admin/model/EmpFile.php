<?php

namespace app\admin\model;

use think\Model;


class EmpFile extends Model
{
    // 表名
    protected $name = 'emp_file';
    
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
