<?php


namespace app\api\controller;

use think\Controller;
use think\Exception;
use think\Request;

class BaseController extends Controller
{

    /**
     * @var Request
     */
    protected $request;

    protected $OrgId = '';
    protected $User = [];
    protected $Data = [];

    public function _initialize()
    {
        //跨域请求检测
        check_cors_request();

        $this->request = Request::instance();

        try {
            $param = $this->request->param();
            $this->User = $param['userInfo'] ?? [];
            $this->OrgId = $param['userInfo']['orgCode'] ?? '';

            if (!is_string($param['data'])) {
                throw new Exception('请求参数异常');
            }

            !empty($param['data']) && $this->Data = json_decode($param['data'], true);

        } catch (Exception $e) {
            app_exception($e->getMessage());
        }


    }

}