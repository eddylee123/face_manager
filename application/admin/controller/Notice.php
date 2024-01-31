<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\common\controller\Backend;
use think\Session;

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
    /**
     * @var Admin
     */
    protected $adminModel = null;

    protected $admin;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Notice;
        $this->adminModel = new Admin();
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
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);
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
            $admins = array_column($list, 'admin_id');
            $adminArr = $this->adminModel->whereIn('id', $admins)->column('nickname', 'id');
            foreach ($list as &$v) {
                //拼接参数
                $v['nickname'] = $adminArr[$v['admin_id']]['nickname'] ?? '';
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        $this->assignconfig('readList' ,$this->model->readList);
        $this->assignconfig('typeList' ,$this->model->typeMap());
        return $this->view->fetch();
    }
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 批量已读
     * DateTime: 2024-01-31 9:24
     */
    public function read()
    {
        $nid = $this->request->param('nid', '');
        if (empty($nid)) $this->error('操作记录不能为空');

        $rs = $this->model->whereIn('id', $nid)->update([
            'is_read'=>1,
            'admin_id'=>$this->admin['id'],
            'update_time'=>time()
        ]);
        if ($rs !== false) {
            $this->success('操作成功');
        }
        $this->error('操作成功');
    }
}
