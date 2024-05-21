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
    protected $empValid;

    public function __construct()
    {
        parent::_initialize();
        $this->inviteSer = new InviteService();
        $this->examSer = new ExamService();
        $this->empValid = new EmpValidate();
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
        if (!empty($this->Data['emp_id_2'])) {
            //编辑
            $result = $this->empValid->scene('edit')->check($this->Data);
            if (!$result) {
                app_exception($this->empValid->getError());
            }
            $emp2 = $this->Data['emp_id_2'];
            unset($this->Data['emp_id_2']);
            $rs = $this->inviteSer->empEdit($emp2, $this->Data);
        } else {
            //新增
            $result = $this->empValid->scene('add')->check($this->Data);
            if (!$result) {
                app_exception($this->empValid->getError());
            }
            $rs = $this->inviteSer->empAdd($this->OrgId, $this->Data);
        }

        app_response(200, $rs);
    }

    public function infoByNo2()
    {
        $result = $this->empValid->scene('info_no')->check($this->Data);
        if (!$result) {
            app_exception($this->empValid->getError());
        }
        $rs = $this->inviteSer->infoByNo2($this->OrgId, $this->Data['emp_id_2']);

        app_response(200, $rs);
    }

    public function infoByIdCard()
    {
        $result = $this->empValid->scene('info_id')->check($this->Data);
        if (!$result) {
            app_exception($this->empValid->getError());
        }
        $rs = $this->inviteSer->infoByIdCard($this->OrgId, $this->Data['id_card']);

        app_response(200, $rs);
    }

    public function saveImage()
    {
        $result = $this->empValid->scene('save_img')->check($this->Data);
        if (!$result) {
            app_exception($this->empValid->getError());
        }
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
        $result = $this->empValid->scene('set_exam')->check($this->Data);
        if (!$result) {
            app_exception($this->empValid->getError());
        }
        $rs = $this->examSer->setExam($this->Data);

        app_response(200, $rs);
    }

    public function setRole()
    {
        $result = $this->empValid->scene('set_role')->check($this->Data);
        if (!$result) {
            app_exception($this->empValid->getError());
        }
        $rs = $this->inviteSer->setRole($this->Data);

        app_response(200, $rs);
    }

    public function sign()
    {
        $result = $this->empValid->scene('sign')->check($this->Data);
        if (!$result) {
            app_exception($this->empValid->getError());
        }
        $rs = $this->inviteSer->sign($this->Data['id_card'], $this->OrgId, $this->Data);

        app_response(200, $rs);
    }

    public function report()
    {
        $result = $this->empValid->scene('report')->check($this->Data);
        if (!$result) {
            app_exception($this->empValid->getError());
        }
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

    public function examList()
    {
        $result = $this->empValid->scene('exam_list')->check($this->Data);
        if (!$result) {
            app_exception($this->empValid->getError());
        }

        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;
        $rs = $this->examSer->examList($this->Data);

        app_response(200, $rs);
    }
}