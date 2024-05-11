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