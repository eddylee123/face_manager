<?php
namespace app\api\service\employ;

use app\admin\model\cwa\CaterB;
use app\admin\model\cwa\DoorInOut;
use app\admin\model\cwa\Views;
use app\api\service\BaseService;

class ReportService extends BaseService
{
    protected $viewsModel;

    public function __construct()
    {
        $this->viewsModel = new Views();
    }

    public function placeList(string $orgId, array $param)
    {
        $total = $this->viewsModel->placeCount($orgId,$param);

        $offset = ($param['page'] - 1) * $param['page_size'];
        $list = $this->viewsModel->placeList($orgId,$param,$offset,$param['page_size']);
        return array("total" => $total, "rows" => $list);
    }

    public function consumeList(string $orgId, array $param)
    {
        $total = $this->viewsModel->consumeCount($orgId, $param);

        $offset = ($param['page'] - 1) * $param['page_size'];
        $list = $this->viewsModel->consumeList($orgId,$param,$offset,$param['page_size'],$param['sort'],$param['order']);

        return array("total" => $total, "rows" => $list);
    }

    public function doorList(string $orgId, array $param)
    {
        $total = $this->viewsModel->doorCount($orgId, $param);

        $offset = ($param['page'] - 1) * $param['page_size'];
        $list = $this->viewsModel->doorList($orgId,$param,$offset,$param['page_size'],$param['sort'],$param['order']);
        return array("total" => $total, "rows" => $list);
    }

}