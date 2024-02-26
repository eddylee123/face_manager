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
        'contactPhone' => 'require|number',
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
        'modify' => ['name', 'contactPhone','status']
    ];

//    public function __construct(array $rules = [], $message = [], $field = [])
//    {
//        $this->field = [
//            'name'  => __('Name'),
//            'title' => __('Title'),
//        ];
//        $this->message['name.format'] = __('Name only supports letters, numbers, underscore and slash');
//        parent::__construct($rules, $message, $field);
//    }

}
