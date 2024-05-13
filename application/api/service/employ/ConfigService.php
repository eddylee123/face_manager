<?php


namespace app\api\service\employ;


use app\admin\model\Employee;
use app\admin\model\EmpRole;
use app\api\service\BaseService;

class ConfigService extends BaseService
{
    protected $empRoleModel;

    public function __construct()
    {
        $this->empRoleModel = new EmpRole();
    }

    public function levelList(string $orgId)
    {
        $cs_list = $this->empRoleModel->csLevel($orgId);
        $kq_list = $this->empRoleModel->kqLevel($orgId);

        return compact('cs_list', 'kq_list');
    }

}