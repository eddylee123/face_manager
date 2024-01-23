<?php

namespace app\admin\model;

use think\Model;


class Notice extends Model
{

    

    

    // 表名
    protected $name = 'notice';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public $type_auth = 'auth_date';

    public $readList = [
        '1' => '已读',
        '0' => '未读',
    ];


    public function typeMap()
    {
        return [
            $this->type_auth => '权限到期'
        ];
    }

    public function addNotice($type_auth, $link_id, $content='')
    {
        $data = [
            'type' => $type_auth,
            'link_id' => $link_id,
            'title' => $this->typeMap()[$type_auth] ?? '',
            'content' => $content,
            'create_time' => time()
        ];
        return $this->insert($data);
    }

    public function read($link_id)
    {
        $row = $this->where(['link_id'=>$link_id])->find();
        return $row->save(['is_read'=>1]);
    }


    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
