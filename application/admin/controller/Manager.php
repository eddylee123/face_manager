<?php


namespace app\admin\controller;


use app\admin\model\EmpRole;
use app\common\controller\Backend;
use think\Session;

class Manager extends Backend
{

    /**
     * @var EmpRole
     */
    protected $empRoleModel = null;

    protected $org;

    public function _initialize()
    {
        parent::_initialize();
        $this->empRoleModel = new EmpRole();
        $this->org = Session::get('admin')['org_id'] ?? 0;
    }

    public function index()
    {
        $this->view->assign("cs_level_list", $this->empRoleModel->csLevel($this->org));
        $this->view->assign("kq_level_list", $this->empRoleModel->kqLevel($this->org));
        return $this->view->fetch('emp_info');
    }
}