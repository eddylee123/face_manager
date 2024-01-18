<?php

namespace app\admin\model;

use think\Model;


class EmpImg extends Model
{
    protected $pk = 'id';
    // 表名
    protected $name = 'emp_img';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    public function createOrUpdate($emp2, $data)
    {
        $img = $this->where(['emp_id_2'=>$emp2])->find();
        if ($img) {
            return $img->save($data);
        } else {
            $data = array_merge($data, ['emp_id_2'=>$emp2]);
            return $this->save($data);
        }
    }

    public function savezp($emp2, $filePath)
    {
        $dir = '/www/idPhotos';
        if (empty($filePath)) {
            return false;
        }
        $rs = save_file($dir, $emp2.'.jpg', $filePath);
        if (!$rs) {
            return false;
        }

        //新增数据
        return $this->createOrUpdate($emp2, ['id_photo'=>$rs]);
    }







}
