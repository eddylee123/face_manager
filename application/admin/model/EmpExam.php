<?php

namespace app\admin\model;

use think\Model;


class EmpExam extends Model
{
    protected $pk = 'id';
    // 表名
    protected $name = 'emp_exam';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
    ];
    

    
    public function getStatusList()
    {
        return [
//            0 => '待体检',
            1 => '合格',
            2 => '不合格'
        ];
    }

    public function getSexList()
    {
        return ['男' => __('男'), '女' => __('女')];
    }


    public function getSexTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sex']) ? $data['sex'] : '');
        $list = $this->getSexList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
