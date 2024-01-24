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
            $ops = $this->request->request('op', '{}');
            $op = json_decode($ops, true);

            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);
            $filter_arr['org_ID'] = $this->admin['org_id'];
            if (!empty($filter_arr['日期'])) {
                [$start, $end] = explode(' - ', $filter_arr['日期']);
                $filter_arr['日期'] = join(',', [$start, $end]);
                $op['日期'] = 'between';
            }
            $this->request->get(['filter'=>json_encode($filter_arr)]);
            $this->request->get(['op'=>json_encode($op)]);
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->count();

            $list = $this->model
                ->where($where)
                ->order("{$sort} {$order}")
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        $this->assignconfig('weekList' ,$this->model->weekList);
        return $this->view->fetch();
    }

    public function detail()
    {

        $date = $this->request->param('date', '');
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