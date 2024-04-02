<?php
namespace app\api\controller\employ;

use app\api\controller\BaseController;
use app\api\service\employ\ReportService;

class Report extends BaseController
{
    public function __construct()
    {
        parent::_initialize();
    }

    /**
     * 食堂统计
     * DateTime: 2024-04-02 9:42
     */
    public function place()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;

        $rs = ReportService::instance()->placeList($this->OrgId, $this->Data);

        app_response(200, $rs);
    }

    /**
     * 消费列表
     * DateTime: 2024-04-02 9:42
     */
    public function consume()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;
        $this->Data['sort'] = $this->Data['sort'] ?? 'clock_time';
        $this->Data['order'] = $this->Data['order'] ?? 'desc';

        $rs = ReportService::instance()->consumeList($this->OrgId, $this->Data);

        app_response(200, $rs);
    }

    /**
     * 门禁列表
     * DateTime: 2024-04-02 9:42
     */
    public function door()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;
        $this->Data['sort'] = $this->Data['sort'] ?? 'clock_time';
        $this->Data['order'] = $this->Data['order'] ?? 'desc';

        $rs = ReportService::instance()->doorList($this->OrgId, $this->Data);

        app_response(200, $rs);
    }
}