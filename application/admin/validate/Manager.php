<?php

namespace app\admin\validate;

use think\Validate;

class Manager extends Validate
{

    /**
     * 正则
     */
    protected $regex = ['format' => '[a-z0-9_\/]+'];

    /**
     * 验证规则
     */
    protected $rule = [
        'name'  => 'require|max:20',
        'contactPhone' => 'require|number|max:11',
        'status' => 'require',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过20个字符',
        'contactPhone.require' => '联系电话必须',
        'contactPhone.number'     => '联系电话格式错误',
        'contactPhone.max'     => '联系电话最多不能超过11个字符',
        'status.require' => '状态必须',
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
        'edit' => ['name', 'contactPhone']
    ];


}
