<?php


namespace app\admin\controller;


use app\admin\model\EmpRole;
use app\common\controller\Backend;
use fast\Http;
use think\Session;

class Manager extends Backend
{

    /**
     * @var EmpRole
     */
    protected $empRoleModel = null;
    /**
     * @var \app\admin\model\Employee
     */
    protected $empModel = null;

    protected $org;
    protected $admin;
    protected $cs_list = [];
    protected $kq_list = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->empRoleModel = new EmpRole();
        $this->empModel = new \app\admin\model\Employee();
        $this->admin = Session::get('admin');
        $this->org = $this->admin['org_id'] ?? 0;
        $this->cs_list = $this->empRoleModel->csLevel($this->admin['org_id']);
        $this->kq_list = $this->empRoleModel->kqLevel($this->admin['org_id']);
    }

    public function index()
    {
        $this->view->assign("cs_level_list", $this->empRoleModel->csLevel($this->org));
        $this->view->assign("kq_level_list", $this->empRoleModel->kqLevel($this->org));
        return $this->view->fetch('emp_info');
    }

    public function update()
    {
        $emp_id = $this->request->param('emp_id', '');
        $xf = $this->request->param('xf', '-1');
        $mj = $this->request->param('mj', '-1');
        if (empty($emp_id)) $this->error('工号不能为空');

        $kq_arr = $cs_arr = [];
        if (!empty($xf)) $cs_arr = split_param($xf, $this->cs_list);
        if (!empty($mj)) $kq_arr = split_param($mj, $this->kq_list);;

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'strip_tags');
            if ($params) {
                if (!empty($params['cs_level'])) {
                    $params['cs_level'] = array_sum($params['cs_level']);
                } else {
                    $params['cs_level'] = -1;
                }
                if (!empty($params['kq_level'])) {
                    $params['kq_level'] = array_sum($params['kq_level']);
                } else {
                    $params['kq_level'] = -1;
                }
            }
            $reqUrl = "http://220.168.154.86:50522/UpEmpLevel";
            $par = [
                "OrderName" => "UpEmpLevel",
                "data" => [
                    "ORGID" => $this->org,
                    "EMPID" => $emp_id,
                    "XFLevel" => $params['cs_level'],
                    "MJLevel" => $params['kq_level']
                ]
            ];
            $rs = Http::post($reqUrl, json_encode($par));
            $rs = json_decode($rs, true);
            if (!$rs) $this->error('数据请求失败');
            if ($rs['result'] == 1) {
                //权限同步
                $empInfo = $this->empModel->where(['emp_id'=>$emp_id])->find();
                if ($empInfo) {
                    $empInfo->save([
                        'cs_level'=>$params['cs_level'],
                        'kq_level'=>$params['kq_level'],
                    ]);
                }
                //操作记录
                $this->empRoleModel->addLogEmp($emp_id,
                    $this->admin['id'],
                    $params['cs_level'],
                    $params['kq_level'],
                    '确认修改',
                    1);

                $this->success('修改成功');
            }
            $this->error('修改失败');
        }

        $this->view->assign("row", compact('emp_id'));
        $this->view->assign("kq_arr", $kq_arr);
        $this->view->assign("cs_arr", $cs_arr);
        $this->view->assign("cs_level_list", $this->cs_list);
        $this->view->assign("kq_level_list", $this->kq_list);
        return $this->view->fetch();
    }
}