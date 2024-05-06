<?php


namespace app\admin\controller;


use app\admin\library\Handle;
use app\admin\library\Hro;
use app\admin\library\Kww;
use app\admin\model\EmpRole;
use app\admin\model\FaceInit;
use app\common\controller\Backend;
use fast\Http;
use think\Config;
use think\Session;
use think\Url;
use think\Validate;

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

    /**
     * @var \app\admin\model\Manager
     */
    protected $managerModel = null;

    protected $faceModel = null;

    protected $org;
    protected $admin;
    protected $cs_list = [];
    protected $kq_list = [];

    protected $payList = ['计时','计件'];
    protected $attenceList = ['白班','晚班'];

    public function _initialize()
    {
        parent::_initialize();
        $this->empRoleModel = new EmpRole();
        $this->empModel = new \app\admin\model\Employee();
        $this->managerModel = new \app\admin\model\Manager();
        $this->faceModel = new FaceInit();
        $this->admin = Session::get('admin');
        $this->org = $this->admin['org_id'] ?? 0;

        $this->view->assign("sexList", $this->empModel->getSexList());
        $this->view->assign("empSourceList", $this->empModel->getEmpSourceList());
        $this->view->assign("armyList", $this->empModel->getArmyList());
        $this->view->assign("transList", $this->empModel->getTransList());
        $this->view->assign("relation_list", $this->empModel->getRelationList());
        $this->view->assign('statusList', Kww::statusList);
        $this->view->assign('marryList', Kww::marryList);
        $this->view->assign('eduList', $this->empModel->getEduList());
        $this->view->assign('folkList', $this->empModel->getFolkList());
        $this->view->assign("cs_level_list", $this->empRoleModel->csLevel($this->org));
        $this->view->assign("kq_level_list", $this->empRoleModel->kqLevel($this->org));
    }

    public function index()
    {
        return $this->view->fetch('emp_info');
    }

//    public function update()
//    {
//        $emp_id = $this->request->param('emp_id', '');
//        $xf = $this->request->param('xf', '-1');
//        $mj = $this->request->param('mj', '-1');
//        if (empty($emp_id)) $this->error('工号不能为空');
//
//        $kq_arr = $cs_arr = [];
//        if (!empty($xf)) $cs_arr = split_param($xf, $this->cs_list);
//        if (!empty($mj)) $kq_arr = split_param($mj, $this->kq_list);;
//
//        if ($this->request->isPost()) {
//            $params = $this->request->post("row/a", [], 'strip_tags');
//            if ($params) {
//                if (!empty($params['cs_level'])) {
//                    $params['cs_level'] = array_sum($params['cs_level']);
//                } else {
//                    $params['cs_level'] = -1;
//                }
//                if (!empty($params['kq_level'])) {
//                    $params['kq_level'] = array_sum($params['kq_level']);
//                } else {
//                    $params['kq_level'] = -1;
//                }
//            }
//            $reqUrl = "http://220.168.154.86:50522/UpEmpLevel";
//            $par = [
//                "OrderName" => "UpEmpLevel",
//                "data" => [
//                    "ORGID" => $this->org,
//                    "EMPID" => $emp_id,
//                    "XFLevel" => $params['cs_level'],
//                    "MJLevel" => $params['kq_level']
//                ]
//            ];
//            $rs = Http::post($reqUrl, json_encode($par));
//            $rs = json_decode($rs, true);
//            if (!$rs) $this->error('数据请求失败');
//            if ($rs['result'] == 1) {
//                //权限同步
//                $empInfo = $this->empModel->where(['emp_id'=>$emp_id])->find();
//                if ($empInfo) {
//                    $empInfo->save([
//                        'cs_level'=>$params['cs_level'],
//                        'kq_level'=>$params['kq_level'],
//                    ]);
//                }
//                //操作记录
//                $this->empRoleModel->addLogEmp($emp_id,
//                    $this->admin['id'],
//                    $params['cs_level'],
//                    $params['kq_level'],
//                    '确认修改',
//                    1);
//
//                $this->success('修改成功');
//            }
//            $this->error('修改失败');
//        }
//
//        $this->view->assign("row", compact('emp_id'));
//        $this->view->assign("kq_arr", $kq_arr);
//        $this->view->assign("cs_arr", $cs_arr);
//        return $this->view->fetch();
//    }

    public function lists()
    {
        $this->request->filter(['strip_tags','trim']);
        if ($this->request->isAjax()) {
            $offset = $this->request->get("offset/d", 0);
            $pageSize = $this->request->get("limit/d", 999999);
            if ($offset > 0) {
                $currentPage = ($offset / $pageSize) + 1;
            } else {
                $currentPage = 1;
            }
            $orgId = Kww::orgMap[$this->org] ?? '10';
//            $empSeqId = 1;
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);

            $param = array_merge($filter_arr, compact('currentPage', 'pageSize','orgId'));
            $list = Kww::userList($param);
            if(!empty($list['records'])) {
                foreach ($list['records'] as &$v) {
                    if (!empty($v['hireDate'])) {
                        $v['hireDate'] = date('Y-m-d', ($v['hireDate'] / 1000));
                    }
                }
            }

            $result = array("total" => $list['total'], "rows" => $list['records']);
            return json($result);
        }

        $this->assignconfig('statusList', Kww::statusList);
        $this->assignconfig('marryList', Kww::marryList);
        return $this->view->fetch('lists');
    }

    public function detail()
    {
        $empNum = $this->request->get("empNum/s", '');
        $row = Kww::userInfo($empNum);
//        echo '<pre>';print_r($row);exit;
        if (!$row){
            $this->error(__('No Results were found'), $_SERVER['HTTP_REFERER']);
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'trim');
            if ($params) {

                $validate = new \app\admin\validate\Manager();
                $result = $validate->scene('edit')->check($params);
                if (!$result) {
                    $this->error($validate->getError());
                }
                $params['other'] = Handle::toFiled($this->managerModel->fieldMap, $params['other']);
                $params = array_merge($params, [
                    'id' => $row['id'],
                    'empNum' => $row['empNum'],
                    'empSeqId' => 1, //一线
                ]);
                if (!empty($params['hireDate'])) {
                    $params['hireDate'] = strtotime($params['hireDate']) * 1000;
                }

//                echo '<pre>';print_r($params['other']);exit;
                $rs = Kww::modify($params);
                if ($rs['success'] == true) {
                    $this->success('操作成功');
                }
                $this->error('操作失败');
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $orgConf = array_values(Config::get('site.org_list'));
        $orgList = array_combine($orgConf, $orgConf);
        //部门编码
        $dept = $this->empModel->getDept($this->admin['org_id']);
        $deptList = $dept['Data'] ?? [];

        //字段重组
        $arr = array_map(function (){
            return '';
        }, $this->managerModel->fieldMap);
        $rowNew = array_merge($arr, $row);
        $birthday = substr($rowNew['idCard'], 6, 8);
        $rowNew['birthday'] = date("Y-m-d", strtotime($birthday));
        $rowNew['age'] = get_age($rowNew['birthday']);
        if (!empty($row['other'])) {
            $rowNew = array_merge($rowNew, Handle::reFiled($row['other']));
        }
        if (!empty($rowNew['hireDate'])) {
            $rowNew['hireDate'] = date('Y-m-d', ($rowNew['hireDate'] / 1000));
        }

        $this->assign('row', $rowNew);
        $this->assign('orgList', $orgList);
        $this->assign('deptList', $deptList);
        $this->assign('payList', $this->payList);
        $this->assign('attenceList', $this->attenceList);
        $this->view->assign('tabList', Handle::getEmpTab($empNum)); //tab

        return $this->view->fetch();
    }

    public function other()
    {
        $empNum = $this->request->get("empNum/s", '');
//        $row = Kww::userInfo($empNum);
//        if (!$row){
//            $this->error(__('No Results were found'), $_SERVER['HTTP_REFERER']);
//        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'trim');
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
                $rs = Hro::upEmpLevel($this->org, $empNum, $params['cs_level'], $params['kq_level']);
                if (empty($rs['result'])) $this->error($rs['msg']);
                if ($rs['result'] == 1) {
                    //权限同步
                    $empInfo = $this->empModel->where(['emp_id'=>$empNum])->find();
                    if ($empInfo) {
                        $empInfo->save([
                            'cs_level'=>$params['cs_level'],
                            'kq_level'=>$params['kq_level'],
                        ]);
                    }
                    //操作记录
                    $this->empRoleModel->addLogEmp($empNum,
                        $this->admin['id'],
                        $params['cs_level'],
                        $params['kq_level'],
                        '确认修改',
                        1);

                    $this->success('操作成功');
                }
                $this->error('操作失败');
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $orgConf = array_values(Config::get('site.org_list'));
        $orgList = array_combine($orgConf, $orgConf);

        $cs_list = $this->empRoleModel->csLevel($this->admin['org_id']);
        $kq_list = $this->empRoleModel->kqLevel($this->admin['org_id']);
        $empInfo = $this->empModel->getRoleImg($empNum, $cs_list, $kq_list);
//        echo"<pre>";print_r($empInfo);exit;

        $this->view->assign('orgList', $orgList);
        $this->view->assign('empInfo', $empInfo);
        $this->view->assign("cs_level_list", $cs_list);
        $this->view->assign("kq_level_list", $kq_list);
        $this->view->assign('tabList', Handle::getEmpTab($empNum)); //tab

        return $this->view->fetch();
    }

    /**
     * 拍照
     * @return string
     * DateTime: 2024-04-22 9:52
     */
    public function photo()
    {
        $empNum = $this->request->get("empNum/s", '');
        $uname = $this->request->get("uname/s", '');
        $photo = $this->empModel->empImage($empNum);

        $this->view->assign('empNum', $empNum);
        $this->view->assign('uname', $uname);
        $this->view->assign('photo', $photo);
        return $this->view->fetch();
    }

    /**
     * 人脸上传
     * DateTime: 2023/12/16 16:37
     */
    public function uploadImg()
    {
        $empNum = $this->request->param('empNum', '');
        $uname = $this->request->param('uname', '');
        $img1 = $this->request->param('img_url', '');
        $img2 = $this->request->param('dis_img_url', '');
        $pType = 'jpeg';

        if (empty($empNum)) {
            $this->error('工号异常');
        }
        $info = $this->faceModel->where('emp_id', $empNum)->find();
        if (empty($info['img_url'])) {
            if (empty($img1)) {
                $this->error('照片1不能为空');
            }
        }
        $imgDir = "/www/winshare/";;
        $date = date("Y-m-d");
        $data = [];
        if (!empty($img1)) {
            $data['img_url'] = $imgDir.$empNum."_".$uname."_".$date.".jpg";
        }
        if (!empty($img2)) {
            $data['dis_img_url'] = $imgDir.$empNum."A_".$uname."_".$date.".jpg";
        }
        if (empty($data)) {
            $this->success('操作成功');
        }

        $res = Kww::uploadFace($empNum, $uname, $img1, $pType, $img2, $pType);
        if (!empty($res['errorMessage'])) {
            $this->error($res['errorMessage']);
        }

        //更新照片
        if (!empty($info)) {
            $data['update_id'] = $this->admin['id'];
            $rs  = $info->save($data);
        } else {
            $data['emp_id'] = $empNum;
            $data['org_id'] = $this->admin['org_id'];
            $data['create_id'] = $this->admin['id'];
            $rs = $this->faceModel->save($data);
        }
        if ($rs === false) {
            $this->error('上传失败');
        }

        $this->success('上传成功');
    }

    public function putObj()
    {
        $pid = $this->request->param('pid');
        $file = $this->request->post('file');var_dump($pid,$file);exit;
        $this->success('上传成功','', Kww::uploadObject($pid,$file));

    }
}