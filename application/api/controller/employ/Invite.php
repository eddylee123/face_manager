<?php


namespace app\api\controller\employ;


use app\api\controller\BaseController;

class Invite extends BaseController
{
    public function __construct()
    {
        parent::_initialize();
    }

    public function lists()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;
        $this->Data['sort'] = $this->Data['sort'] ?? 'clock_time';
        $this->Data['order'] = $this->Data['order'] ?? 'desc';
    }
}