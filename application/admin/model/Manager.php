<?php

namespace app\admin\model;

use app\admin\library\DormMq;
use app\admin\library\Kww;
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
        'other' => [
        ],
        'sex' => '性别',
        'address' => '家庭地址',
        'IDDepartment' => '发证机关',
        'IDValidity' => '有效期',
        'IDDate' => '发证日期',
        'Native' => '籍贯',
        'Major' => '专业',
        'Academy' => '院校',
        'Political' => '政治面貌',
        'UrgencyMan' => '紧急联系人',
        'UrgencyTEL' => '紧急联系电话',
        'UrgencyRelation' => '紧急联系关系',
        'PayMode' => '计薪方式',
        'AttenceMode' => '班别',
        'EndTimeLD' => '合同结束日期',
        'EndTimeJKZ' => '健康证结束日期',
        'TryDate' => '转正日期',
        'KQDate' => '考勤日期',
        'KindFund' => '爱心基金标准',
        'IsTry' => '工种',
        'Email' => '邮箱',
        'position' => '',
        'transport' => '交通方式',
        'empSource' => '员工来源',
        'Station' => '职务',
        'introducer' => '介绍人工号',
        'introducerUser' => '介绍人姓名',
        'introducerdept' => '介绍人部门',
        'introducerRemark' => '介绍人说明',
        'workExperience' => '工作经验',
        'empType' => '工作经验',
    ];

    /**
     * 新增正式工
     * @param $info
     * @return bool
     * DateTime: 2024-02-29 17:01
     */
    public function initInsert($info)
    {

        $base = [
            'name' => $info['emp_name'],
            'empNum' => $info['emp_id'],
            'idCard' => $info['id_card'],
            'education' => $info['education'],
            'ethnicity' => $info['folk'],
            'contactPhone' => $info['tel'],
            'hireDate' => strtotime($info['kq_date']) * 1000,
            'jobLevel' => '一线员工',
            'marriageStatus' => $this->getMarry($info['marry']),
            'orgId' => $info['org_id'],
            'deptId' => $info['dept_id'],
            'status' => 'FORMAL'
        ];

        $other = [
            'sex' => $info['sex'],
            'address' => $info['address'],
            'IDDepartment' => $info['id_department'],
            'IDValidity' => $info['id_validity'],
            'IDDate' => $info['id_date'],
            'Native' => $info['native_place'],
            'Major' => '',
            'Academy' => '',
            'Political' => $info['stand'],
            'UrgencyMan' => $info['urgency_man'],
            'UrgencyTEL' => $info['urgency_tel'],
            'UrgencyRelation' => $info['urgency_relation'],
            'PayMode' => '',
            'AttenceMode' => '',
            'EndTimeLD' => $info['contract_validate'],
            'EndTimeJKZ' => '',
            'TryDate' => '',
            'KQDate' => '',
            'KindFund' => '',
            'IsTry' => '',
            'Email' => '',
            'position' => '',
            'transport' => $info['transport'],
            'empSource' => '',
            'Station' => '',
            'introducer' => $info['intro_emp_id'],
            'introducerUser' => $info['intro_name'],
            'introducerdept' => '',
            'introducerRemark' => '',
            'workExperience' => $info['work_exp'],
            'empType' => $info['emp_source'],
        ];

        $base['other'] = $this->postOtherField($other);

        $rs = Kww::modify($base);
        if ($rs['success'] == true) {
            //住宿队列通知
            if ($info['transport'] == '住宿') {
                DormMq::producer([
                    'emp_id' => $info['emp_id'],
                    'emp_name' => $info['emp_name'],
                    'kq_date' => $info['kq_date'],
                ]);
            }
            return true;
        }
        return false;
    }


    private function getMarry($state)
    {
        $marry = '';
        if ($state == '未婚') {
            $marry = 'UNMARRIED';
        } elseif ($state == '已婚') {
            $marry = 'MARRIED';
        } elseif ($state == '离婚') {
            $marry = 'DIVORCE';
        }
        return $marry;
    }

    public function postOtherField($otherArr)
    {
        $other = [];
        foreach ($otherArr as $k=>$v) {
            if (empty($v)) continue;
            if (empty($this->fieldMap[$k])) continue;

            $other[] = [
                'field_label' => $this->fieldMap[$k],
                'field_name' => $k,
                'field_value' => $v,
            ];
        }

        return $other;
    }

}
