<?php


namespace app\api\controller\employ;


use app\api\controller\BaseController;
use app\api\service\employ\ConfigService;

class Config extends BaseController
{
    protected $configSer;

    public function __construct()
    {
        parent::_initialize();
        $this->configSer = new ConfigService();
    }

    public function empMap()
    {
        $rs = $this->configSer->empMap();

        app_response(200, $rs);
    }

    public function levelList()
    {
        $rs = $this->configSer->levelList($this->OrgId);

        app_response(200, $rs);
    }
}