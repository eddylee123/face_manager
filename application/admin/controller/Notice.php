<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 消息通知
 *
 * @icon fa fa-circle-o
 */
class Notice extends Backend
{
    
    /**
     * Notice模型对象
     * @var \app\admin\model\Notice
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Notice;

    }

    public function import()
    {
        parent::import();
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
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);
            //$filter_arr['is_del'] = '0';
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
        /*$arr1 = [
            ['checkbox'=>true],
            ['field'=> 'spread_item', 'title'=> '推荐人' ,'placeholder'=> 'id或手机号' ,  'operate'=> "LIKE %...%" ],
            ['field'=> 'add_time', 'title'=> __('add_time'), 'operate'=> 'RANGE', 'addclass'=> 'datetimerange', 'autocomplete'=> false],//封装的方法带不过去
            ];
        $this->assignconfig('columns' ,json_encode($arr1));*/
        return $this->view->fetch();
    }
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

}
