<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 体检信息管理
 *
 * @icon fa fa-circle-o
 */
class EmpRole extends Backend
{
    
    /**
     * EmpExam模型对象
     * @var \app\admin\model\EmpRole
     */
    protected $model = null;
    /**
     * @var \app\admin\model\Employee
     */
    protected $employeeModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\EmpRole();
        $this->employeeModel = new \app\admin\model\Employee();
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags','trim']);
        $emp2 = $this->request->param('emp2', '');
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);
            $filter_arr['emp_id_2'] = $emp2;
            //echo '<pre>';print_r($filter_arr);exit;
            $this->request->get(['filter'=>json_encode($filter_arr)]);
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
    }
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

}
