<?php


namespace app\api\controller\employ;


use app\api\controller\BaseController;
use app\api\service\employ\EmployeeService;

class Employee extends BaseController
{
    public function __construct()
    {
        parent::_initialize();
    }

    public function empList()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;

        $rs = EmployeeService::instance()->empList($this->OrgId, $this->Data);

        app_response(200, $rs);
    }
}