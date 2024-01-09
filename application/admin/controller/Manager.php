<?php


namespace app\admin\controller;


use app\admin\model\EmpRole;
use app\common\controller\Backend;

class Manager extends Backend
{

    /**
     * @var EmpRole
     */
    protected $empRoleModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->empRoleModel = new EmpRole();
    }

    public function index()
    {
        $this->view->assign("cs_level_list", $this->empRoleModel->csLevel);
        $this->view->assign("kq_level_list", $this->empRoleModel->kqLevel);
        return $this->view->fetch('emp_info');
    }
}