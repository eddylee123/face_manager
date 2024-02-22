<?php
namespace app\admin\controller\cwa;

use app\common\controller\Backend;
use think\Session;

class Door extends Backend
{
    /**
     * @var \app\admin\model\cwa\DoorInOut
     */
    protected $model = null;

    protected $admin;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\cwa\DoorInOut();

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

            if (!empty($filter_arr['打卡日期'])) {
                [$start, $end] = explode(' - ', $filter_arr['打卡日期']);
                $filter_arr['start'] = date('Y-m-d', strtotime($start));
                $filter_arr['end'] = date('Y-m-d', strtotime($end));
            } else {
                $filter_arr['start'] = date('Y-m-d', strtotime("-30 day"));
                $filter_arr['end'] = date('Y-m-d');
            }
            $total = $this->model->countList($this->admin['org_id'],$filter_arr);

            $list = $this->model->getList($this->admin['org_id'],$filter_arr,$offset,$limit);
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
    }

}