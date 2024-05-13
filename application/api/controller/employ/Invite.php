<?php


namespace app\api\controller\employ;


use app\api\controller\BaseController;
use app\api\service\employ\ExamService;
use app\api\service\employ\InviteService;
use app\api\validate\EmpValidate;

class Invite extends BaseController
{
    protected $inviteSer;
    protected $examSer;

    public function __construct()
    {
        parent::_initialize();
        $this->inviteSer = new InviteService();
        $this->examSer = new ExamService();
    }

    public function empList()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;
        $rs = $this->inviteSer->empList($this->OrgId, $this->Data);

        app_response(200, $rs);
    }

    public function saveEmp()
    {
        $validate = new EmpValidate();
        if (!empty($this->Data['emp_id_2'])) {
            //编辑
            $result = $validate->scene('edit')->check($this->Data);
            if (!$result) {
                app_exception($validate->getError());
            }
            $emp2 = $this->Data['emp_id_2'];
            unset($this->Data['emp_id_2']);
            $rs = $this->inviteSer->empEdit($emp2, $this->Data);
        } else {
            //新增
            $result = $validate->scene('add')->check($this->Data);
            if (!$result) {
                app_exception($validate->getError());
            }
            $rs = $this->inviteSer->empAdd($this->OrgId, $this->Data);
        }

        app_response(200, $rs);
    }

    public function infoByNo2()
    {
        $rs = $this->inviteSer->infoByNo2($this->OrgId, $this->Data['emp_id_2']);

        app_response(200, $rs);
    }

    public function infoByIdCard()
    {
        $rs = $this->inviteSer->infoByIdCard($this->OrgId, $this->Data['id_card']);

        app_response(200, $rs);
    }

    public function saveImage()
    {
        $rs = $this->inviteSer->saveImage($this->Data['emp_id_2'], $this->Data);

        app_response(200, $rs);
    }

    public function empImage()
    {
        $rs = $this->inviteSer->empImage($this->Data['emp_id']);

        app_response(200, $rs);
    }

    public function setExam()
    {
        $rs = $this->examSer->setExam($this->Data);

        app_response(200, $rs);
    }

    public function setRole()
    {
        $rs = $this->inviteSer->setRole($this->Data);

        app_response(200, $rs);
    }

    public function sign()
    {
        $rs = $this->inviteSer->sign($this->Data['id_card'], $this->OrgId, $this->Data);

        app_response(200, $rs);
    }

    public function report()
    {
        $rs = $this->inviteSer->report($this->Data['id_card'], $this->Data);

        app_response(200, $rs);
    }

    public function empExport()
    {
        $rs = $this->inviteSer->export($this->OrgId, $this->Data);

        app_response(200, $rs);
    }

    public function examImport()
    {
        $rs = $this->examSer->import($this->Data['file']);

        app_response(200, $rs);
    }
}