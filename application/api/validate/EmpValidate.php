<?php


namespace app\api\validate;


use think\Validate;

class EmpValidate extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'id'  => 'require|number|max:11',
        'emp_name'  => 'require|max:20',
        'emp_id'  => 'require|max:20',
        'emp_id_2'  => 'require|max:20',
        'folk'  => 'require|max:30',
        'address'  => 'require|max:200',
        'education'  => 'require|max:20',
        'dept_id'  => 'require|max:50',
        'remark'  => 'require|max:200',
        'sex'  => 'require|max:10',
        'id_card'  => 'require|max:20',
        'id_department'  => 'require|max:200',
        'id_date'  => 'require|max:200',
        'id_validity'  => 'require|max:200',
        'birthday'  => 'require|max:200',
        'come_date'  => 'require|max:200',
        'kq_date'  => 'require|max:200',
        'try_date'  => 'require|max:200',
        'emp_source'  => 'require|max:20',
        'tel'  => 'require|max:15',
        'urgency_man'  => 'require|max:20',
        'urgency_relation'  => 'require|max:20',
        'urgency_tel'  => 'require|max:15',
        'marry'  => 'require|max:15',
        'age'  => 'require|max:11',
        'shoe'  => 'require|max:11',
        'army'  => 'require|max:11',
        'intro_name'  => 'require|max:20',
        'intro_emp_id'  => 'require|max:20',
        'position'  => 'require|max:20',
        'status'  => 'require|max:11',
        'transport'  => 'require|max:20',
        'cs_level'  => 'require|max:11',
        'kq_level'  => 'require|max:11',
        'contract_date'  => 'require|max:200',
        'contract_validate'  => 'require|max:200',
        'auth_date'  => 'require|max:200',
        'serv_company'  => 'require|max:200',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'id.require' => 'ID必须',
        'id.number'     => 'ID格式错误',
        'id.max'     => 'ID不能超过11个字符',
        'emp_name.require' => '姓名必须',
        'emp_name.max'     => '姓名不能超过20个字符',
        'emp_id.require' => '正式工号必须',
        'emp_id.max'     => '正式工号不能超过20个字符',
        'emp_id_2.require' => '临时工号必须',
        'emp_id_2.max'     => '临时工号不能超过20个字符',
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['emp_name'],
        'edit' => ['emp_id_2','emp_name'],
    ];
}