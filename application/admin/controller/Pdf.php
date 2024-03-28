<?php


namespace app\admin\controller;


use app\common\controller\Backend;
use Mpdf\Mpdf;
use think\Config;

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

    public function multiMake()
    {
        $nid = $this->request->param('nid', '');
        if (empty($nid)) $this->error('操作记录不能为空');

        $idArr = explode(',', $nid);
        $list = $this->empModel->whereIn('id', $idArr)->select();
        if (!$list) $this->error('员工信息异常', $_SERVER['HTTP_REFERER']);
        $list = collection($list)->toArray();
        foreach ($list as &$v) {
            if(!empty($v['edu_exp'])) {
                $edu_arr = json_decode($v['edu_exp'], true);
                if (!empty(reset($edu_arr))) {
                    $v['edu_exp_arr'] = $edu_arr;
                }
            }
            if(!empty($v['work_exp'])) {
                $work_arr = json_decode($v['work_exp'], true);
                if (!empty(reset($work_arr))) {
                    $v['work_exp_arr'] = $work_arr;
                }
            }
            if(!empty($v['family_member'])) {
                $family_arr = json_decode($v['family_member'], true);
                if (!empty(reset($family_arr))) {
                    $v['family_member_arr'] = array_map(function ($item){
                        if (empty($item['relation'])) {
                            $item['relation'] = '';
                        }
                        return $item;
                    },$family_arr);
                }
                $family_num = count($v['family_member_arr']);
                for ($i=0; $i<(3-$family_num); $i++) {
                    array_push($v['family_member_arr'], [
                        'name' => '&nbsp;',
                        'relation' => '&nbsp;',
                        'tel' => '&nbsp;',
                    ]);
                }

            }
            //籍贯
            if(empty($v['native_place'])) {
                $v['native_place'] = substr($v['address'], 0, strpos($v['address'], '省'));
            }
        }

        $this->assign('list', $list);
        return $this->view->fetch('multi');
    }

    public function preExam()
    {
        $nid = $this->request->param('nid', '');

        if ($this->request->isPost()) {
            $exam_date = $this->request->post("exam_date/s", '');
            $report_date = $this->request->post("report_date/s", '');
            if (empty($exam_date) || empty($report_date)) {
                $this->error('请选择打印时间');
            }

            $this->redirect('pdf/multiExam',compact('nid','exam_date','report_date'));
        }

        return $this->view->fetch();
    }

    public function multiExam()
    {
        $nid = $this->request->param('nid', '');
        if (empty($nid)) $this->error('操作记录不能为空');
        $exam_date = $this->request->param("exam_date/s", '');
        $report_date = $this->request->param("report_date/s", '');

        $idArr = explode(',', $nid);
        $listA = $this->empModel->whereIn('id', $idArr)->column('emp_name');
        if (!$listA) $this->error('员工信息异常', $_SERVER['HTTP_REFERER']);

        $content = Config::get('site.exam_doc06');
        if (empty($content)) {
            $this->error('打印模板异常');
        }
        $list = [];
        $examStr = date('Y年m月d日', strtotime($exam_date));
        $reportStr = date('Y年m月d日', strtotime($report_date));
        $thisStr = date('Y年m月d日');
        foreach ($listA as $name=>$v) {
            $list[] = sprintf($content, $name, $examStr, $reportStr, $thisStr);
        }

        $this->assign('list', $list);
        return $this->view->fetch('multi_exam');
    }

}