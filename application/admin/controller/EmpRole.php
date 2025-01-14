<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\common\controller\Backend;
use think\Session;

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
    /**
     * @var Admin
     */
    protected $adminModel = null;

    protected $org;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\EmpRole();
        $this->employeeModel = new \app\admin\model\Employee();
        $this->adminModel = new Admin();
        $this->org = Session::get('admin')['org_id'] ?? 0;
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags','trim']);
        $emp2 = $this->request->param('emp2', '');
        $emp_id = $this->request->param('emp_id', '');
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);
            if (!empty($emp_id)) {
                $filter_arr['emp_id'] = $emp_id;
            } else {
                $filter_arr['emp_id_2'] = $emp2;
            }

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
            $cs_level = $this->model->csLevel($this->org);
            $kq_level = $this->model->kqLevel($this->org);
            $admins = array_column($list, 'create_id');
            $adminArr = $this->adminModel->whereIn('id', $admins)->column('nickname', 'id');
            foreach ($list as &$v) {
                //拼接参数
                $v['nickname'] = $adminArr[$v['create_id']]['nickname'] ?? '';
                $split_arr = split_param($v['cs_level'], $cs_level);
                sort($split_arr);
                $split_val = array_map(function ($item) use ($cs_level){
                    return $cs_level[$item] ?? '';
                }, $split_arr);
                $v['cs_text'] = implode(',', $split_val);

                $split_arr = split_param($v['kq_level'], $kq_level);
                sort($split_arr);
                $split_val = array_map(function ($item) use ($kq_level){
                    return $kq_level[$item] ?? '';
                }, $split_arr);
                $v['kq_text'] = implode(',', $split_val);
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
    }

    public function lists()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags','trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);

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
            $cs_level = $this->model->csLevel($this->org);
            $kq_level = $this->model->kqLevel($this->org);
            $admins = array_column($list, 'create_id');
            $adminArr = $this->adminModel->whereIn('id', $admins)->column('nickname', 'id');
            $emp_id_2 = array_column($list, 'emp_id_2');
            $emp2Arr = $this->employeeModel->whereIn('emp_id_2', $emp_id_2)->column('emp_name', 'emp_id_2');
            $emp_id = array_column($list, 'emp_id');
            $empArr = $this->employeeModel->whereIn('emp_id', $emp_id)->column('emp_name', 'emp_id');

            foreach ($list as &$v) {
                //拼接参数
                $v['nickname'] = $adminArr[$v['create_id']]['nickname'] ?? '';
                $split_arr = split_param($v['cs_level'], $cs_level);
                sort($split_arr);
                $split_val = array_map(function ($item) use ($cs_level){
                    return $cs_level[$item] ?? '';
                }, $split_arr);
                $v['cs_text'] = implode(',', $split_val);

                $split_arr = split_param($v['kq_level'], $kq_level);
                sort($split_arr);
                $split_val = array_map(function ($item) use ($kq_level){
                    return $kq_level[$item] ?? '';
                }, $split_arr);
                $v['kq_text'] = implode(',', $split_val);
                if (!empty($v['emp_id_2'])) {
                    $v['emp_name'] = $emp2Arr[$v['emp_id_2']]['emp_name'] ?? '';
                } elseif (!empty($v['emp_id'])) {
                    $v['emp_name'] = $empArr[$v['emp_id']]['emp_name'] ?? '';
                }

            }
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
