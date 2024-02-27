<?php
namespace app\admin\controller\cwa;

use app\admin\library\Handle;
use app\common\controller\Backend;
use think\Session;

class CaterB extends Backend
{
    /**
     * @var \app\admin\model\cwa\CaterB
     */
    protected $model = null;

    protected $admin;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\cwa\CaterB();

        $this->admin = Session::get('admin');
    }

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

            $offset = $this->request->get("offset/d", 0);
            $limit = $this->request->get("limit/d", 999);
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);
            $start = $end = '';
            if (!empty($filter_arr['日期'])) {
                [$start, $end] = explode(' - ', $filter_arr['日期']);
                $start = date('Y-m-d', strtotime($start));
                $end = date('Y-m-d', strtotime($end));
            }
            $total = $this->model->countList($start,$end,$this->admin['org_id'],$filter_arr);

            $list = $this->model->getList($start,$end,$this->admin['org_id'],$filter_arr,$offset,$limit);
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        $this->assignconfig('weekList' ,$this->model->weekList);
        return $this->view->fetch();
    }

    public function detail()
    {

        $this->request->filter(['strip_tags','trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            $offset = $this->request->get("offset/d", 0);
            $limit = $this->request->get("limit/d", 999);
            $sort = $this->request->get("sort/s", '打卡时间');
            $order = $this->request->get("order/s", 'DESC');
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);

            if (!empty($filter_arr['打卡时间'])) {
                [$start, $end] = explode(' - ', $filter_arr['打卡时间']);
                $filter_arr['start'] = date('Y-m-d', strtotime($start));
                $filter_arr['end'] = date('Y-m-d 23:59:59', strtotime($end));
            } else {
                $filter_arr['start'] = date('Y-m-d', strtotime("-30 day"));
                $filter_arr['end'] = date('Y-m-d 23:59:59');
            }

            $total = $this->model->countDetail($this->admin['org_id'],$filter_arr);

            $list = $this->model->getDetail($this->admin['org_id'],$filter_arr,$offset,$limit,$sort,$order);
//            echo "<pre/>";print_r($list);exit;
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        $this->assignconfig('csLevel', ['A'=>'A食堂','B'=>'B食堂']);
        return $this->view->fetch();
    }

    public function emp()
    {
        $empNum = $this->request->get("empNum/s", '');
        if (empty($empNum)) {
            $this->error('用户信息异常');
        }
        $this->request->filter(['strip_tags','trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            $offset = $this->request->get("offset/d", 0);
            $limit = $this->request->get("limit/d", 999);
            $sort = $this->request->get("sort/s", '打卡时间');
            $order = $this->request->get("order/s", 'DESC');
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);

            if (!empty($filter_arr['打卡时间'])) {
                [$start, $end] = explode(' - ', $filter_arr['打卡时间']);
                $filter_arr['start'] = date('Y-m-d', strtotime($start));
                $filter_arr['end'] = date('Y-m-d 23:59:59', strtotime($end));
            } else {
                $filter_arr['start'] = date('Y-m-d', strtotime("-30 day"));
                $filter_arr['end'] = date('Y-m-d 23:59:59');
            }
            $filter_arr['工号'] = $empNum;

            $total = $this->model->countDetail($this->admin['org_id'],$filter_arr);

            $list = $this->model->getDetail($this->admin['org_id'],$filter_arr,$offset,$limit,$sort,$order);
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        $this->view->assign('tabList', Handle::getEmpTab());
        $this->assignconfig('csLevel', ['A'=>'A食堂','B'=>'B食堂']);
        return $this->view->fetch();
    }
}