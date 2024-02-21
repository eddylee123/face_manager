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
        if (empty($emp2)) $this->error('请求参数异常', $_SERVER['HTTP_REFERER']);

        $row = $this->empModel->where(['emp_id_2'=>$emp2])->find();
        if (!$row) $this->error('员工信息异常', $_SERVER['HTTP_REFERER']);

        $edu_exp_arr = $work_exp_arr = $family_member_arr = [];
        if(!empty($row['edu_exp'])) {
            $edu_arr = json_decode($row['edu_exp'], true);
            if (!empty(reset($edu_arr))) {
                $edu_exp_arr = $edu_arr;
            }
        }
        if(!empty($row['work_exp'])) {
            $work_arr = json_decode($row['work_exp'], true);
            if (!empty(reset($work_arr))) {
                $work_exp_arr = $work_arr;
            }
        }
        if(!empty($row['family_member'])) {
            $family_arr = json_decode($row['family_member'], true);
            if (!empty(reset($family_arr))) {
                $family_member_arr = $family_arr;
            }
            $family_num = count($family_member_arr);
            for ($i=0; $i<(3-$family_num); $i++) {
                array_push($family_member_arr, [
                    'name' => '&nbsp;',
                    'relation' => '&nbsp;',
                    'tel' => '&nbsp;',
                ]);
            }

        }
        //籍贯
        if(empty($row['native_place'])) {
            $row['native_place'] = substr($row['address'], 0, strpos($row['address'], '省'));
        }

        $this->assign('row', $row);
        $this->assign('edu_exp_arr', $edu_exp_arr);
        $this->assign('work_exp_arr', $work_exp_arr);
        $this->assign('family_member_arr', $family_member_arr);
        return $this->view->fetch('template');
    }

}