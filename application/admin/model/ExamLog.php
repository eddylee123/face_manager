<?php

namespace app\admin\model;

use think\Model;


class ExamLog extends Model
{

    protected $pk = 'id';

    // 表名
    protected $name = 'exam_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
//    protected $updateTime = false;
//    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text'
    ];

    public function getStatusList()
    {
        return [
            '1' => '成功',
            '0' => '失败',
        ];
    }
    
    public function addLog($username, $id_card, $status, $create_time, $remark='', $emp2='')
    {
        $data = compact('username', 'id_card', 'status', 'create_time');
        if (!empty($remark)) $data['remark'] = $remark;
        if (!empty($emp2)) $data['emp_id_2'] = $emp2;

        return self::insert($data);
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
