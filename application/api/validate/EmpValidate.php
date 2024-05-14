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
        'img_url'  => 'require|max:255',
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
        'folk.require' => '民族必须',
        'folk.max'     => '名族内容过长',
        'address.require' => '地址必须',
        'address.max'     => '地址内容过长',
        'education.require' => '学历必须',
        'education.max'     => '学历内容过长',
        'dept_id.require' => '部门必须',
        'dept_id.max'     => '部门内容过长',
        'remark.require' => '备注必须',
        'remark.max'     => '备注内容过长',
        'sex.require' => '性别必须',
        'sex.max'     => '性别内容过长',
        'id_card.require' => '身份证必须',
        'id_card.max'     => '身份证内容过长',
        'id_department.require' => '发证机关必须',
        'id_department.max'     => '发证机关内容过长',
        'id_date.require' => '发证日期必须',
        'id_date.max'     => '发证日期内容过长',
        'id_validity.require' => '失效日期必须',
        'id_validity.max'     => '失效日期内容过长',
        'birthday.require' => '生日必须',
        'birthday.max'     => '生日内容过长',
        'come_date.require' => '报名日期必须',
        'come_date.max'     => '报名日期内容过长',
        'kq_date.require' => '入职日期必须',
        'kq_date.max'     => '入职日期内容过长',
        'emp_source.require' => '员工类型必须',
        'emp_source.max'     => '员工类型内容过长',
        'tel.require' => '电话必须',
        'tel.max'     => '电话内容过长',
        'urgency_man.require' => '紧急联系人必须',
        'urgency_man.max'     => '紧急联系人内容过长',
        'urgency_relation.require' => '关系必须',
        'urgency_relation.max'     => '关系内容过长',
        'urgency_tel.require' => '紧急电话必须',
        'urgency_tel.max'     => '紧急电话内容过长',
        'marry.require' => '婚否必须',
        'marry.max'     => '婚否内容过长',
        'age.require' => '年龄必须',
        'age.max'     => '年龄内容过长',
        'shoe.require' => '鞋码必须',
        'shoe.max'     => '鞋码内容过长',
        'army.require' => '是否退伍必须',
        'army.max'     => '是否退伍内容过长',
        'status.require' => '员工状态必须',
        'status.max'     => '员工状态内容过长',
        'transport.require' => '交通方式必须',
        'transport.max'     => '交通方式内容过长',
        'contract_date.require' => '合同开始日期必须',
        'contract_date.max'     => '合同开始日期内容过长',
        'contract_validate.require' => '合同结束日期必须',
        'contract_validate.max'     => '合同结束日期内容过长',
        'auth_date.require' => '临时权限结束日期必须',
        'auth_date.max'     => '临时权限结束日期内容过长',
        'serv_company.require' => '劳务公司必须',
        'serv_company.max'     => '劳务公司内容过长',
        'img_url.require' => '图片1必须',
        'img_url.max'     => '图片1内容过长',
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
            'add' => ['emp_name','sex','address','education','folk','id_card','id_department','id_date','id_validity','birthday','emp_source','tel','urgency_man','urgency_relation','urgency_tel','marry','age','shoe','army','transport'],
        'edit' => ['emp_id_2','emp_name','sex','address','education','folk','id_card','id_department','id_date','id_validity','birthday','emp_source','tel','urgency_man','urgency_relation','urgency_tel','marry','age','shoe','army','transport'],
        'save_img' => ['emp_id_2','img_url'],
        'info_id' => ['id_card'],
        'info_no' => ['emp_id_2'],
        'set_role' => ['emp_id_2','cs_list','kq_list'],
        'set_exam' => ['emp_id_2','cert_number','exam_date','cert_date','cert_validity','status'],
        'sign' => ['id_card'],
        'report' => ['id_card','contract_date','contract_validate','dept_id'],
    ];
}