<?php

namespace app\admin\model;

use think\Config;
use think\Exception;
use think\Model;
use think\Request;


class RoleStatus extends Model
{

    

    

    // 表名
    protected $name = 'role_status';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    public function roleView()
    {
        return Config::get('site.role_view');
    }

    public function empSource()
    {
        return Config::get('site.emp_source');
    }

    public function roleStatus()
    {
        return Config::get('site.role_status');
    }

    public function orgList()
    {
        return Config::get('site.org_list');
    }

    public function roleUrls()
    {
        return Config::get('site.role_urls');
    }

    public function roleParams()
    {
        return Config::get('site.role_params');
    }

    public function toPage()
    {
        $data = [];
        foreach ($this->roleUrls() as $k=>$v) {
            $data[$k] = $this->roleView()[$k];
        }

        return $data;
    }


    public function getOperate($org_id, $operate, $status, $to_page, $emp_source, $params=[])
    {
        $rs = $this
            ->where(compact('org_id','operate','status','to_page','emp_source'))
            ->find();
        if (empty($rs)) {
            $rs = $this->getBaseOperate($operate);
        }
        $data['named'] = $this->roleView()[$to_page] ?? '';
        //参数处理
        $data['urls'] = $rs['url'];
        try {
            $args = explode(',', $rs['params']);
            switch (count($args)) {
                case 1:
                    $data['urls'] = sprintf($rs['url'], $params[$args[0]]);
                    break;
                case 2:
                    $data['urls'] = sprintf($rs['url'], $params[$args[0]], $params[$args[1]]);
                    break;
                case 3:
                    $data['urls'] = sprintf($rs['url'], $params[$args[0]], $params[$args[1]], $params[$args[2]]);
                    break;
            }
        } catch (Exception $e) {
            return $data;
        }
        !empty($data['urls']) && $data['urls'] = Request::instance()->baseFile().$data['urls'];

        return $data;
    }

    protected function getBaseOperate($operate)
    {
        $baseUrl = [
            'add' => '/employee/photo?emp2=%s',
            'edit' => '/employee/photo?emp2=%s',
            'sign' => '/employee/photo?emp2=%s',
            'photo' => '/employee/roles?emp2=%s',
            'exam' => '',
            'roles' => '',
            'report' => '',
        ];
        if (!empty($this->roleUrls()[$operate])) {
            $url = $this->roleUrls()[$operate];
        } else {
            $url = $baseUrl[$operate] ?? '';
        }
        return [
            'params' => 'emp_id_2',
            'url' => $url
        ];
    }

    public function setUrlAttr($value, $data)
    {
        return $this->roleUrls()[$data['to_page']] ?? '';
    }

    public function setParamsAttr($value, $data)
    {
        return $this->roleParams()[$data['to_page']] ?? '';
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
