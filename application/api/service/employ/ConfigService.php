<?php


namespace app\api\service\employ;


use app\admin\model\Employee;
use app\admin\model\EmpRole;
use app\api\service\BaseService;

class ConfigService extends BaseService
{
    protected $empRoleModel;
    protected $empModel;

    public function __construct()
    {
        $this->empRoleModel = new EmpRole();
        $this->empModel = new Employee();
    }

    public function levelList(string $orgId)
    {
        $cs_list = $this->empRoleModel->csLevel($orgId);
        $kq_list = $this->empRoleModel->kqLevel($orgId);

        return compact('cs_list', 'kq_list');
    }

    public function empMap()
    {
        return [
            'emp_status' => $this->empModel->getStatusList(),
            'emp_sex' => $this->empModel->getSexList(),
            'emp_source' => $this->empModel->getEmpSourceList(),
            'emp_marry' => $this->empModel->getMarryList(),
            'emp_army' => $this->empModel->getArmyList(),
            'emp_trans' => $this->empModel->getTransList(),
            'emp_rel' => $this->empModel->getRelationList(),
            'emp_edu' => $this->empModel->getEduList(),
        ];
    }

}