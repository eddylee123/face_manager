<?php
namespace app\api\service\employ;

use app\admin\model\cwa\CaterB;
use app\api\service\BaseService;

class ReportService extends BaseService
{
    protected $caterModel;

    public function __construct()
    {
        $this->caterModel = new CaterB();
    }

    public function placeTot(string $orgId, array $param)
    {
        $total = $this->caterModel->countList($param['start_time'],$param['end_time'],$orgId,$param);

        $offset = ($param['page'] - 1) * $param['page_size'];
        $list = $this->caterModel->getList($param['start_time'],$param['end_time'],$orgId,$param,$offset,$param['page_size']);
        return array("total" => $total, "rows" => $list);
    }

    public function consume(string $orgId, array $param)
    {
        $total = $this->caterModel->countDetail($orgId, $param);

        $offset = ($param['page'] - 1) * $param['page_size'];
        $list = $this->caterModel->getDetail($orgId,$param,$offset,$param['page_size'],$param['sort'],$param['order']);

        return array("total" => $total, "rows" => $list);
    }

}