<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Session;

/**
 * 体检信息管理
 *
 * @icon fa fa-circle-o
 */
class EmpExam extends Backend
{
    
    /**
     * EmpExam模型对象
     * @var \app\admin\model\EmpExam
     */
    protected $model = null;
    /**
     * @var \app\admin\model\Employee
     */
    protected $employeeModel = null;

    protected $admin;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\EmpExam;
        $this->employeeModel = new \app\admin\model\Employee();
        $this->view->assign("sexList", $this->model->getSexList());
        $this->view->assign("statusList", $this->model->getStatusList());

        $this->admin = Session::get('admin');
    }

    public function import()
    {
        parent::import();
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags','trim']);
        $emp2= $this->request->param('emp2', '');
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

        $this->assignconfig("status_list", $this->model->getStatusList());
        return $this->view->fetch();
    }
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function exam2($emp2 = null)
    {
        $row = $this->empExamModel->where(['emp_id_2' => $emp2])->order('id desc')->find();
        $info = $this->model->where(['emp_id_2' => $emp2])->find();

        //状态判断
//        $step = $this->model->where(['emp_id_2'=>$emp2])->value('step');
//        if ($step != 3) {
//            $this->error('请按标准流程操作，谢谢配合');
//        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'trim');
            if ($params) {
                $params['emp_id_2'] = $emp2;
                $params['create_id'] = $this->admin['id'];
                if ($row) {
                    $res = $row->save($params);
                } else {
                    $res = $this->empExamModel->insert($params);
                }
                if ($res === false) {
                    $this->error($row->getError());
                }
                //更新操作步骤
                $emp_info = $this->model->where(['emp_id_2' => $emp2, 'step' => 3])->find();
                if (in_array($params['status'], [1,2])) {
                    if ($emp_info) {
                        $rs1 = $emp_info->save(['step' => 4, 'status' => 2]);
                        if ($rs1 === false) {
                            $this->error('数据异常');
                        }
                        //写入标签
//                        $this->empTagModel->addTag('exam', $emp2, $this->admin['id']);

                        $this->success('信息录入完成，请等待员工报道');
                    }
                } elseif (in_array($params['status'], [3,4]) && $emp_info['emp_source']=='劳务工') {
                    $named = '权限';
                    $url = $this->request->baseFile().'/employee/roles?emp2='.$emp2;
                    $this->success('操作成功，正在跳转……', '', compact('named','url'));
                }

                $this->success();
            }
            $this->error();
        }
        if (!$row) {
            $row = [
                'emp_id_2' => $emp2,
                'username' => '',
                'age' => '',
                'id_card' => '',
                'tel' => '',
                'sex' => '',
                'cert_number' => '',
                'exam_date' => '',
                'cert_date' => '',
                'cert_validity' => '',
                'hb' => '',
                'remark' => '',
                're_exam' => '',
                'exam_org' => '',
                'status' => '',
            ];
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}
