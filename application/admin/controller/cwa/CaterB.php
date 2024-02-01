<?php
namespace app\admin\controller\cwa;

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
            $limit = $this->request->get("limit/d", 10);
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

        $date = $this->request->param('date', '');
        $area = $this->request->param('area', '');
        if (empty($date)) $this->error('日期不能为空');

        $this->request->filter(['strip_tags','trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            $offset = $this->request->get("offset/d", 0);
            $limit = $this->request->get("limit/d", 10);
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);
            $filter_arr['area'] = $area;

            $total = $this->model->countDetail($date,$date,$this->admin['org_id'],$filter_arr);

            $list = $this->model->getDetail($date,$date,$this->admin['org_id'],$filter_arr,$offset,$limit);
//            echo "<pre/>";print_r($list);exit;
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        $this->assignconfig('weekList' ,$this->model->weekList);
        return $this->view->fetch();
    }
}