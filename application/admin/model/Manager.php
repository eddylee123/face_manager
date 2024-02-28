<?php

namespace app\admin\model;

use think\Model;


class Manager extends Model
{
    // 表名
//    protected $name = 'wage_inout';
//
//    // 自动写入时间戳字段
//    protected $autoWriteTimestamp = false;
//
//    // 定义时间戳字段名
//    protected $createTime = false;
//    protected $updateTime = false;
//    protected $deleteTime = false;
//
//    // 追加属性
//    protected $append = [
//
//    ];

    public $fieldMap = [
        'name' => '',
        'empNum' => '',
        'idCard' => '',
        'education' => '',
        'ethnicity' => '',
        'morningLocation' => '',
        'contactPhone' => '',
        'hireDate' => '',
        'jobLevel' => '',
        'status' => '',
        'marriageStatus' => '',
        'orgId' => '',
        'deptId' => '',
        //other
        'sex' => '',
        'address' => '',
        'IDDepartment' => '',
        'IDValidity' => '',
        'IDDate' => '',
        'Native' => '',
        'Major' => '',
        'Academy' => '',
        'Political' => '',
        'UrgencyMan' => '',
        'UrgencyTEL' => '',
        'UrgencyRelation' => '',
        'PayMode' => '',
        'HR_DeptID' => '',
        'AttenceMode' => '',
        'EndTimeLD' => '',
        'EndTimeJKZ' => '',
        'TryDate' => '',
        'KODate' => '',
        'KindFund' => '',
        'IsTry' => '',
        'email' => '',
        'job' => '',
        'position' => '',
        'transport' => '',
        'other' => [
        ],
    ];

}
