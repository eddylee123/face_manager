<?php


namespace app\admin\controller;


use app\common\controller\Backend;
use Mpdf\Mpdf;

class Pdf extends Backend
{
    /**
     * Employee模型对象
     * @var \app\admin\model\Employee
     */
    protected $empModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->empModel = new \app\admin\model\Employee();

        $this->view->assign("check_list", [1=>'是',0=>'否']);
    }

    public function make()
    {
        $emp2 = $this->request->param('emp2');
        if (empty($emp2)) $this->error('请求参数异常');

        $row = $this->empModel->where(['emp_id_2'=>$emp2])->find();
        if (!$row) $this->error('员工信息异常');

        $edu_exp_arr = $work_exp_arr = $family_member_arr = [];
        if(!empty($row['edu_exp'])) {
            $edu_exp_arr = json_decode($row['edu_exp'], true);
        }
        if(!empty($row['work_exp'])) {
            $work_exp_arr = json_decode($row['work_exp'], true);
        }
        if(!empty($row['family_member'])) {
            $family_member_arr = json_decode($row['family_member'], true);
        }

        $this->assign('row', $row);
        $this->assign('edu_exp_arr', $edu_exp_arr);
        $this->assign('work_exp_arr', $work_exp_arr);
        $this->assign('family_member_arr', $family_member_arr);
        return $this->view->fetch('template');
    }

}