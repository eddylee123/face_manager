<?php

namespace app\admin\model;




use fast\Http;
use think\Model;

class EmpRole extends Model
{
    protected $pk = 'id';

    public $role = [
        1 => '食堂消费',
        2 => '门禁',
        3 => '生产车间',
    ];

    public $csLevel = [
//        -1 => '无权限',
        1 => 'A食堂',
        10 => 'B食堂',
        100 => 'C食堂',
    ];

    public $kqLevel = [
//        -1 => '禁用所有门禁',
        1 => '1号门(大门)',
        10 => '2号门(车间1)',
        100 => '3号门(车间2)',
        1000 => '4号门(车间3)',
        10000 => '5号门(车间4)',
        100000 => '6号门(宿舍1)',
        1000000 => '7号门(宿舍2)',
        10000000 => '8号门(宿舍3)',
    ];

    // 表名
    protected $name = 'emp_role';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';

    // 追加属性
    protected $append = [
//        'cs_text',
//        'kq_text',
    ];

    public function addLog($emp_id_2,$create_id, $cs_level, $kq_level, $remark)
    {
        $data = compact('emp_id_2','create_id', 'cs_level', 'kq_level', 'remark');
        $data['create_time'] = time();

        return self::insert($data);
    }

    public function addLogEmp($emp_id,$create_id, $cs_level, $kq_level, $remark,$is_emp=0)
    {
        $data = compact('emp_id','create_id', 'cs_level', 'kq_level', 'remark','is_emp');
        $data['create_time'] = time();

        return self::insert($data);
    }

    public function csLevel($org_id)
    {
        $rs = $this->getRoleList($org_id, 'STLX');
        $data = [];
        foreach ($rs as $name=>$item) {
            $k = str_pad(1, $item, "0", STR_PAD_RIGHT);
            $data[$k] = $name;
        }
        return $data;
    }

    public function kqLevel($org_id)
    {
        $rs = $this->getRoleList($org_id, 'MJLX');
        $data = [];
        foreach ($rs as $name=>$item) {
            $k = str_pad(1, $item, "0", STR_PAD_RIGHT);
            $data[$k] = $name;
        }
        return $data;
    }

    public function getRoleList($org_id, $key='MJLX')
    {
        $url = "http://220.168.154.86:50522/GETViewMacTeam?ORGID={$org_id}";
        $rs = Http::get($url);
        if (!empty($rs)) {
            $arr = json_decode($rs, true);
            return $arr[$key] ?? [];
        }
        return  [];
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

//    protected function getCsTextAttr($value, $data)
//    {
//        $split_arr = split_param($data['cs_level'], $this->csLevel);
//        sort($split_arr);
//        $split_val = array_map(function ($item){
//            return $this->csLevel[$item] ?? '';
//        }, $split_arr);
//
//        return implode('|', $split_val);
//    }
//
//    protected function getKqTextAttr($value, $data)
//    {
//        $split_arr = split_param($data['kq_level'], $this->kqLevel);
//        sort($split_arr);
//        $split_val = array_map(function ($item){
//            return $this->kqLevel[$item] ?? '';
//        }, $split_arr);
//
//        return implode('|', $split_val);
//    }
    







}
