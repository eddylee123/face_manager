<?php

namespace app\admin\controller;

use app\admin\library\DormMq;
use app\common\controller\Backend;
use think\Config;
use think\Db;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */

    public function index()
    {
//        return $this->view->fetch();
    }

    public function testmq()
    {
        $rs = DormMq::producer(['msg'=>'hello']);
        var_dump($rs);exit;
    }
}
