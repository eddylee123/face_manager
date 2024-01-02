<?php

namespace app\admin\model;




class EmpRole
{

    public $role = [
        1 => '食堂消费',
        2 => '门禁',
        3 => '生产车间',
    ];

    public $csLevel = [
//        -1 => '无权限',
        1 => 'A食堂',
        10 => 'B食堂',
        100 => 'C食堂',
    ];

    public $kqLevel = [
//        -1 => '禁用所有门禁',
        1 => '1号门(大门)',
        10 => '2号门(车间1)',
        100 => '3号门(车间2)',
        1000 => '4号门(车间3)',
        10000 => '5号门(车间4)',
        100000 => '6号门(宿舍1)',
        1000000 => '7号门(宿舍2)',
        10000000 => '8号门(宿舍3)',
    ];

    public function roleList()
    {
        $roles = [];
        foreach ($this->role as $k=>$v) {
            array_push($roles, ['id'=>$k, 'name'=>$v]);
        }

        return $roles;
    }
    

    // 表名
    /*protected $name = 'emp_img';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];*/
    

    







}
