<?php


namespace app\admin\controller;


use app\common\controller\Backend;
use think\Session;

class Refuse extends Backend
{

    /**
     * Employee模型对象
     * @var \app\admin\model\Employee
     */
    protected $model = null;

    /**
     * @var \app\admin\model\EmpExam
     */
    protected $empExamModel = null;

    protected $admin;

    public function _initialize()
    {
        parent::_initialize();

        $this->admin = Session::get('admin');
        $this->model = new \app\admin\model\Employee;
        $this->empExamModel = new \app\admin\model\EmpExam();
    }

    /**
     * 首页
     * @return string|\think\response\Json
     * DateTime: 2024-03-08 10:38
     */
    public function index()
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

            $ops = $this->request->request('op', '{}');
            $op = json_decode($ops, true);

            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);
            $filter_arr['org_id'] = $this->admin['org_id'];
            if (!empty($filter_arr['come_date'])) {
                [$start, $end] = explode(' - ', $filter_arr['come_date']);
                $filter_arr['come_date'] = join(',', [$start, $end]);
                $op['come_date'] = 'between';
            }
            if (!empty($filter_arr['exam_time'])) {
                [$start, $end] = explode(' - ', $filter_arr['exam_time']);
                $rsExam = $this->empExamModel
                    ->whereBetween('create_time', [strtotime($start), strtotime($end)])
                    ->column('emp_id_2');
                if (!empty($rsExam)) {
                    $filter_arr['emp_id_2'] = array_keys($rsExam);
                    $op['emp_id_2'] = 'in';
                }
                unset($filter_arr['exam_time']);
            }
//            echo '<pre>';print_r($filter_arr);exit;
            $this->request->get(['filter'=>json_encode($filter_arr)]);
            $this->request->get(['op'=>json_encode($op)]);
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where('status', '=' , 21)
                ->where($where)
                ->count();
            $list = $this->model
                ->where('status', '=' , 21)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        $this->assignconfig("sex_list", $this->model->getSexList());
        $this->assignconfig("marry_list", $this->model->getMarryList());
        $this->assignconfig("source_list", $this->model->getEmpSourceList());
        return $this->view->fetch();
    }
}