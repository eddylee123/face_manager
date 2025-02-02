<?php

namespace app\admin\controller;

use app\admin\library\Kww;
use app\admin\model\AdminLog;
use app\admin\model\EmpImg;
use app\admin\model\EmpRole;
use app\admin\model\SocketCache;
use app\common\controller\Backend;
use app\gateway\controller\Events;
use fast\Arr;
use think\Env;
use think\Exception;
use think\Session;

/**
 * 员工基础信息管理
 *
 * @icon fa fa-circle-o
 */
class Employee extends Backend
{

    /**
     * Employee模型对象
     * @var \app\admin\model\Employee
     */
    protected $model = null;

    /**
     * @var EmpImg
     */
    protected $empImgModel = null;

    /**
     * @var EmpRole
     */
    protected $empRoleModel = null;

    /**
     * @var \app\admin\model\EmpExam
     */
    protected $empExamModel = null;

    /**
     * @var \app\admin\model\RoleStatus
     */
    protected $roleStatusModel = null;

    /**
     * @var \app\admin\model\Notice
     */
    protected $noticeModel = null;


    protected $admin;
    protected $cs_list = [];
    protected $kq_list = [];

    protected $stepList = [
        '1' => '待拍照',
        '2' => '待体检',
        '3' => '待报道',
        '4' => '正常',
    ];

    public function _initialize()
    {
        parent::_initialize();

        $this->model = new \app\admin\model\Employee;
        $this->empImgModel = new EmpImg();
        $this->empRoleModel = new EmpRole();
        $this->empExamModel = new \app\admin\model\EmpExam();
        $this->roleStatusModel = new \app\admin\model\RoleStatus();
        $this->noticeModel = new \app\admin\model\Notice();
        $this->admin = Session::get('admin');
        $this->cs_list = $this->empRoleModel->csLevel($this->admin['org_id']);
        $this->kq_list = $this->empRoleModel->kqLevel($this->admin['org_id']);

        $this->view->assign("sexList", $this->model->getSexList());
        $this->view->assign("empSourceList", $this->model->getEmpSourceList());
        $this->view->assign("marryList", $this->model->getMarryList());
        $this->view->assign("armyList", $this->model->getArmyList());
        $this->view->assign("transList", $this->model->getTransList());
        $this->view->assign("eduList", $this->model->getEduList());
        $this->view->assign("folkList", $this->model->getFolkList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("relation_list", $this->model->getRelationList());
        $this->view->assign("statusListExam", $this->empExamModel->getStatusList());
        $this->view->assign("cs_level_list", $this->cs_list);
        $this->view->assign("kq_level_list", $this->kq_list);
        $this->view->assign("radio_list", [0=>'否',1=>'是']);
    }

    /**
     * 首页
     * @return string|\think\response\Json
     * DateTime: 2023/12/16 16:37
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags','trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }

            $ops = $this->request->request('op', '{}');
            $op = json_decode($ops, true);

            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);
            $filter_arr['org_id'] = $this->admin['org_id'];
            if (!empty($filter_arr['come_date'])) {
                [$start, $end] = explode(' - ', $filter_arr['come_date']);
                $filter_arr['come_date'] = join(',', [$start, $end]);
                $op['come_date'] = 'between';
            }
            if (!empty($filter_arr['kq_date'])) {
                [$start, $end] = explode(' - ', $filter_arr['kq_date']);
                $filter_arr['kq_date'] = join(',', [$start, $end]);
                $op['kq_date'] = 'between';
            }
            if (!empty($filter_arr['exam_time'])) {
                [$start, $end] = explode(' - ', $filter_arr['exam_time']);
                $rsExam = $this->empExamModel
                    ->whereBetween('create_time', [strtotime($start), strtotime($end)])
                    ->column('emp_id_2');
                if (!empty($rsExam)) {
                    $filter_arr['emp_id_2'] = array_keys($rsExam);
                    $op['emp_id_2'] = 'in';
                }
                unset($filter_arr['exam_time']);
            }
//            echo '<pre>';print_r($filter_arr);exit;
            $this->request->get(['filter'=>json_encode($filter_arr)]);
            $this->request->get(['op'=>json_encode($op)]);
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where('status', 'egt' , 0)
                ->where('status', '<>' , 21)
                ->where($where)
                ->count();
            $list = $this->model
                ->where('status', 'egt' , 0)
                ->where('status', '<>' , 21)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        //排除体检未通过筛选

        $this->assignconfig("sex_list", $this->model->getSexList());
        $this->assignconfig("marry_list", array_filter($this->model->getMarryList()));
        $this->assignconfig("status_list", $this->model->getStatusList());
        $this->assignconfig("source_list", $this->model->getEmpSourceList());
        return $this->view->fetch();
    }

    /**
     * 新增
     * @return string
     * DateTime: 2023/12/16 16:37
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'trim');
            if ($params) {
                if (!empty($params['id_card'])) {
                    $exist = $this->model
                        ->where(['id_card'=>$params['id_card']])
                        ->find();
                    if ($exist) {
                        $this->error('员工信息已存在，请勿重复注册');
                    }
                }
                //旧数据比对
                $rs = $this->model->checkEmpStatus($params['id_card'], $params['id_date'], $params['id_validity']);
                if (!in_array($rs['code'], [100, 200])) {
                    $this->error($rs['msg']);
                }
                //生成临时工号
                $params['emp_id_2'] = $this->model->getTempId($this->admin['org_id']);
                $params['create_id'] = $this->admin['id'];
                //更新操作步骤
                $params['status'] = 1;

                if (empty($this->admin['org_id'])) {
                    $this->error('该账号未绑定基地信息，无法新增');
                }
                $params['org_id'] = $this->admin['org_id'];

                try {
                    $result = $this->model->create($params);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    //保存身份证照
//                    $zp = $this->request->param('zp', '');
//                    if (!empty($zp)) {
//                        $this->empImgModel->savezp($params['emp_id_2'], $zp);
//                    }
//                    $url = $this->request->baseFile().'/employee/photo?emp2='.$params['emp_id_2'];
                    $arg = $this->roleStatusModel
                        ->getOperate($this->admin['org_id'],
                            'add',
                            1,
                            'photo',
                            $params['emp_source'],
                            ['emp_id_2'=>$params['emp_id_2']]
                        );
                    $this->success('', null, ['url'=>$arg['urls'], 'named'=>$arg['named']]);
                } else {
                    $this->error($this->model->getError());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $this->view->assign('come_date', date('Y-m-d'));
        return $this->view->fetch();
    }

    /**
     * 编辑
     * @param null $ids
     * @return string
     * DateTime: 2023/12/16 16:37
     */
    public function edit($ids = NULL)
    {

        $row = $this->model->where(['id'=>$ids])->find();
        if (!$row){
            $this->error(__('No Results were found'), $_SERVER['HTTP_REFERER']);
        }

        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a", [], 'trim');

            if ($params) {
                try {
                    if (!empty($this->admin['org_id'])) {
                        $params['org_id'] = $this->admin['org_id'];
                    }
                    $params['is_new'] = 0;

                    $result = $row->where(['id'=>$ids])->update($params);
                    if ($result !== false) {
                        //保存身份证照
//                        $zp = $this->request->param('zp', '');
//                        if (!empty($zp)) {
//                            $this->empImgModel->savezp($row['emp_id_2'], $zp);
//                        }
//                        $url = $this->request->baseFile().'/employee/photo?emp2='.$row['emp_id_2'];
                        $arg = $this->roleStatusModel
                            ->getOperate($this->admin['org_id'],
                                'edit',
                                1,
                                'photo',
                                $row['emp_source'],
                                $row
                        );
                        $this->success('', null, ['url'=>$arg['urls'], 'named'=>$arg['named']]);
                    } else {
                        $this->error($row->getError());
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        //部门编码
        $dept = $this->model->getDept($this->admin['org_id']);
        $deptList = $dept['Data'] ?? [];

        $this->view->assign("deptList", $deptList);
        $this->view->assign("row", $row);
        $this->view->assign("act", 'edit');
        return $this->view->fetch();
    }

    /**
     * 人脸拍照
     * @return string
     * DateTime: 2023/12/16 16:37
     */
    public function photo()
    {
        $emp2 = $this->request->param('emp2', '');

        $info = $this->empImgModel->where(['emp_id_2'=>$emp2])->find();
        //处理图片
        $info['img1'] = $info['img2'] = '';
        try {
            if (!empty($info['img_url'])) {
                if (is_file($info['img_url'])) {
                    $info['img1'] = "data:image/jpeg;base64," . base64_encode(file_get_contents($info['img_url']));
                }
            }
            if (!empty($info['dis_img_url'])) {
                if (is_file($info['dis_img_url'])) {
                    $info['img2'] = "data:image/jpeg;base64," . base64_encode(file_get_contents($info['dis_img_url']));
                }
            }
        } catch (Exception $e) {
            $this->error('系统数据异常', $_SERVER['HTTP_REFERER']);
        }

        $url = $named = '';
        $emp_info = $this->model->where(['emp_id_2'=>$emp2])->find();
        if ($emp_info['status'] == 1){
            //判断劳务工
            if ($emp_info['emp_source'] == '劳务工') {
                $arg = $this->roleStatusModel
                    ->getOperate($this->admin['org_id'],
                        'photo',
                        2,
                        'roles',
                        $emp_info['emp_source'],
                        $emp_info
                    );
                $named = $arg['named'];
                $url = $arg['urls'];
            }
        }

        $this->view->assign('url', $url);
        $this->view->assign('named', $named);
        $this->view->assign('emp2', $emp2);
        $this->view->assign('info', $info);
        return $this->view->fetch();
    }

    /**
     * 人脸上传
     * DateTime: 2023/12/16 16:37
     */
    public function uploadImg()
    {
        $emp2 = $this->request->param('emp2', '');
        $img1 = $this->request->param('img_url', '');
        $img2 = $this->request->param('dis_img_url', '');
        $pType = 'jpeg';

        if (empty($emp2)) {
            $this->error('工号异常');
        }
        $row = $this->model->where(['emp_id_2'=>$emp2])->find();
        if (empty($row)) {
            $this->error('员工信息异常');
        }
        $info = $this->empImgModel->where('emp_id_2', $emp2)->find();
        if (empty($info['img_url'])) {
            if (empty($img1)) {
                $this->error('照片1不能为空');
            }
        }

        $imgDir = "/www/winshare/";;
        $date = date("Y-m-d");
        $data = [];
        if (!empty($img1)) {
            $data['img_url'] = $imgDir.$emp2."_".$row['emp_name']."_".$date.".jpg";
        }
        if (!empty($img2)) {
            $data['dis_img_url'] = $imgDir.$emp2."A_".$row['emp_name']."_".$date.".jpg";
        }
        if (empty($data)) {
            $this->success('操作成功');
        }

        $res = Kww::uploadFace($emp2, $row['emp_name'], $img1, $pType, $img2, $pType);
        if (!empty($res['errorMessage'])) {
            $this->error($res['errorMessage']);
        }
        //更新照片
        if (!empty($info)) {
            $data['update_id'] = $this->admin['id'];
            $rs  = $info->save($data);
        } else {
            $data['emp_id_2'] = $emp2;
            $data['create_id'] = $this->admin['id'];
            $rs = $this->empImgModel->save($data);
        }
        if ($rs === false) {
            $this->error('上传失败');
        }
        //更新操作步骤
        $emp_info = $this->model->where(['emp_id_2'=>$emp2,'status'=>1])->find();
        if ($emp_info) {
            $rs1 = $emp_info->save(['status'=>2]);
            if ($rs1 === false) {
                $this->error();
            }
        }
        //写入记录
        AdminLog::record('人脸拍照', $data);

        //同步更新
        $this->model->where(['emp_id_2'=>$emp2])->update(['is_new'=>0]);

        $this->success('上传成功');
    }

    /**
     * 权限
     * @return string
     * DateTime: 2023/12/16 16:38
     */
    public function roles()
    {
        $emp2 = $this->request->param('emp2', '');
        $row = $this->model->where(['emp_id_2'=>$emp2])->find();
        if (!$row) {
            $this->error(__('No Results were found'), $_SERVER['HTTP_REFERER']);
        }
        $kq_arr = $cs_arr = [];
        !empty($row['kq_level']) && $kq_arr = split_param($row['kq_level'], $this->kq_list);
        !empty($row['cs_level']) && $cs_arr = split_param($row['cs_level'], $this->cs_list);
        //判断合同工
        if ($row['emp_source'] == '劳务工' && empty($row['auth_date'])) {
             $row['auth_date'] = date("Y-m-d", strtotime("+7 day"));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'strip_tags');
            $role_remark = $this->request->post("role_remark", '');
            $length = strlen($role_remark);
            if ($length < 10 || $length > 255) {
                $this->error('描述内容长度为10~255个字');
            }
            Arr::forget($params, ['emp_id_2','emp_name','id_card']);
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
                $params['is_new'] = 0;
                $res = $this->model->where(['emp_id_2'=>$emp2])->update($params);
                if ($res === false) {
                    $this->error($row->getError());
                }
                //操作记录
                $this->empRoleModel->addLog($emp2,$this->admin['id'], $params['cs_level'], $params['kq_level'], $role_remark);

                $this->success('');
            }
            $this->error();
        }


        $this->view->assign("row", $row);
        $this->view->assign("kq_arr", $kq_arr);
        $this->view->assign("cs_arr", $cs_arr);
        $this->view->assign("cs_level_list", $this->cs_list);
        $this->view->assign("kq_level_list", $this->kq_list);
        return $this->view->fetch();
    }

    /**
     * 体检信息
     * @param null $emp2
     * @return string
     * DateTime: 2023/12/16 16:38
     */
    public function exam($emp2 = null)
    {
        $emp_info = $this->model->where(['emp_id_2' => $emp2])->find();

        if (!$emp_info) {
            $this->error('员工信息异常', $_SERVER['HTTP_REFERER']);
        }
        //状态判断
        if (!in_array($emp_info['status'], [2,21,4])) {
            if ($emp_info['status'] < 2) {
                $this->error('请录入人脸后操作，谢谢配合', $_SERVER['HTTP_REFERER']);
            } else {
                $this->error('体检信息已通过，请勿重复操作', $_SERVER['HTTP_REFERER']);
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'trim');
            if ($params) {
                $params['emp_id_2'] = $emp2;
                $params['create_id'] = $this->admin['id'];
                $params['create_time'] = time();
                $res = $this->empExamModel->insert($params);

                if (!$res) {
                    $this->error('体检信息录入失败');
                }
                //更新操作步骤
                if ($emp_info['status'] != 4) {
                    $emp_status = $params['status'] == 1 ? 3 : 21;
                    $rs1 = $emp_info->save(['status' => $emp_status]);
                    if ($rs1 === false) {
                        $this->error('数据异常');
                    }
                }

                $msg = $params['status'] == 1 ? '信息录入完成，请等待员工报道' : '';
                $this->success($msg);
            }
            $this->error();
        }

        $emp_info['username'] = $emp_info['emp_name'];
        $this->view->assign("row", $emp_info);
        return $this->view->fetch();
    }

    /**
     * 读取身份证
     * DateTime: 2023/12/20 11:07
     */
    public function readCard() {

        $device = $this->request->param('device', '', 'trim');
        $flag = $this->request->param('flag', '');
        if (empty($device)) {
            $this->error('设备号获取失败，请稍后再试');
        }
//        var_dump($device);exit();
        $rs1 = Events::sendMessage($device, output(300, '开始身份读取'));
        if ($rs1['code'] != 200) {
            $this->error('设备异常，请稍后再试');
        }

        sleep(2);

        $card = SocketCache::gets($device);
        if (empty($card)) {
            $this->error('身份证读取失败,请稍后再试');
        }
        $card = json_decode($card, true);

        $card['birth'] = date('Y-m-d', strtotime($card['birth']));
        $card['begin'] = date('Y-m-d', strtotime($card['begin']));
        $card['end'] = date('Y-m-d', strtotime($card['end']));
        $card['age'] = get_age($card['birth']);
        $card['is'] = 1;
        $card['err_msg'] = '';
        if ($flag == 'add') {
            //旧数据比对
            $rs = $this->model->checkEmpStatus($card['id'], $card['begin'], $card['end']);
            $rs['code'] != 200 && $card['err_msg'] = $rs['msg'];
        }
        if ($flag == 'sign') {
            $rsE = $this->model->where(['id_card'=>$card['id']])->value('id');
            !$rsE && $card['is'] = 0;
        }

        SocketCache::dels($device);
        $this->success('', null, $card);
    }

    /**
     * 报到
     * @return string
     * DateTime: 2023/12/16 16:39
     */
    public function report()
    {
        $id_card = $this->request->param('id_card', '');
        if (empty($id_card)) {
            $this->error('请求参数异常', $_SERVER['HTTP_REFERER']);
        }
        //基础信息
        $row = $this->model->where(['id_card'=>$id_card])->order('id', 'desc')->find();

        //人脸信息
        $img = $this->empImgModel->where(['emp_id_2'=>$row['emp_id_2']])->find();
        $img['img1'] = $img['img2'] = '/assets/img/face_default.png';
        if ($img) {
            if (!empty($img['img_url'])) {
                if (is_file($img['img_url'])) {
                    $img['img1'] = "data:image/jpeg;base64," . base64_encode(file_get_contents($img['img_url']));
                }
            }
            if (!empty($img['img_url'])) {
                if (is_file($img['dis_img_url'])) {
                    $img['img2'] = "data:image/jpeg;base64," . base64_encode(file_get_contents($img['dis_img_url']));
                }
            }
        }
        //体检信息
        $exam = $this->empExamModel->where(['emp_id_2' => $row['emp_id_2']])->order('id desc')->find();
        //1226测试注释
//        if (!$img || !$exam){
//            $this->error('员工信息缺失，请补全后操作', $_SERVER['HTTP_REFERER']);
//        }

        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a", [], 'trim');

            if ($params) {
                try {
                    if ($row['status'] != 3){
                        $this->error('当前员工暂无法报道，请确认状态后再试~');
                    }
                    $params['status'] = 4;
                    $params['kq_date'] = date('Y-m-d');

                    $url = $named = '';
                    //获取工号
                    if (Env::get('app.master') == false) {
                        if ($row['emp_source'] == '合同工') {
                            $params['emp_id'] = $this->model->getEmpHtg($row['org_id']);
                        } else {
                            $params['emp_id'] = $this->model->getEmpLwg($row['org_id']);
                        }
                    } else {
                        $params['emp_id'] = $this->model->getNewEmpId($row['org_id'], $row['emp_name'], $row['emp_source']);
                    }
                    //开启所有权限
                    $params['cs_level'] = array_sum(array_keys($this->cs_list));
                    $params['kq_level'] = array_sum(array_keys($this->kq_list));
                    $params['is_new'] = 0;

                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        //初始化正式工
                        (new \app\admin\model\Manager())->initInsert($this->model->get($row['id']));

                        $this->success('操作成功', '', compact('named', 'url'));
                    } else {
                        $this->error($row->getError());
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        //部门编码
        $dept = $this->model->getDept($this->admin['org_id']);
        $deptList = $dept['Data'] ?? [];
        //进度处理
        $step = $row['status'] == 21 ? 2 : $row['status'];

        $this->view->assign("stepList", $this->stepList);
        $this->view->assign("step", $step);
        $this->view->assign("deptList", $deptList);
        $this->view->assign("row", $row);
        $this->view->assign("img", $img);
        $this->view->assign("exam", $exam);
        return $this->view->fetch();
    }

    /**
     * 员工信息
     * @param null $ids
     * @return string
     * DateTime: 2023/12/16 16:39
     */
    public function detail($ids = null)
    {

        //基础信息
        $row = $this->model->where(['id'=>$ids])->find();
        if (!$row){
            $this->error(__('No Results were found'));
        }

        $from = $this->request->param('from', '');
        if ($from == 'notice') {
            //消息标记已读
            $this->noticeModel->read($ids, $this->admin['id']);
        }
        $kq_arr = $cs_arr = [];
        !empty($row['kq_level']) && $kq_arr = split_param($row['kq_level'], $this->kq_list);
        !empty($row['cs_level']) && $cs_arr = split_param($row['cs_level'], $this->cs_list);

        //人脸信息
        $img = $this->empImgModel->where(['emp_id_2'=>$row['emp_id_2']])->find();
        $img['img1'] = $img['img2'] = '/assets/img/face_default.png';
        if ($img) {
            if (!empty($img['img_url'])) {
                if (is_file($img['img_url'])) {
                    $img['img1'] = "data:image/jpeg;base64," . base64_encode(file_get_contents($img['img_url']));
                }
            }
            if (!empty($img['img_url'])) {
                if (is_file($img['dis_img_url'])) {
                    $img['img2'] = "data:image/jpeg;base64," . base64_encode(file_get_contents($img['dis_img_url']));
                }
            }
        }
        //体检信息
        $exam = $this->empExamModel->where(['emp_id_2' => $row['emp_id_2']])->order('id desc')->find();

        if (!$exam){
            $exam = [
                'username' => '',
                'age' => '',
                'id_card' => '',
                'tel' => '',
                'sex' => '',
                'cert_number' => '',
                'exam_date' => '',
                'cert_date' => '',
                'cert_validity' => '',
                'hb' => '',
                'remark' => '',
                're_exam' => '',
                'exam_org' => '',
                'status' => 0,
            ];
        }
        //进度处理
        $step = $row['status'] == 21 ? 2 : $row['status'];
        $index = $this->request->baseFile().'/employee?ref=addtabs';
        //部门编码
        $dept = $this->model->getDept($this->admin['org_id']);
        $deptList = $dept['Data'] ?? [];

        $this->view->assign("deptList", $deptList);
        $this->view->assign("stepList", $this->stepList);
        $this->view->assign("step", $step);
        $this->view->assign("index", $index);
        $this->view->assign("row", $row);
        $this->view->assign("img", $img);
        $this->view->assign("exam", $exam);
        $this->view->assign("kq_arr", $kq_arr);
        $this->view->assign("cs_arr", $cs_arr);
        return $this->view->fetch();
    }

    public function device()
    {
        $org_id = $this->admin['org_id'];
        $device = Events::getDevice($org_id);

        $device_list = [];
        foreach ($device as $k=>$v) {
            [$a, $dev] = explode('_', $v);
            $device_list[$k] = '设备'.$dev;
        }

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'strip_tags');

            if (!empty($params['device'])) {
//                Events::leaveDevice($this->admin['org_id'], $params['device']);

                $this->success('设备绑定成功', null, ['device'=>$params['device']]);
            }
            $this->error();
        }
        $this->view->assign("device_list", $device_list);
        return $this->view->fetch();
    }

    public function empName($emp_id)
    {
        $rs = $this->model->getEmpInfo($emp_id);
        if ($rs['msg'] == '查询成功') {
            $this->success($rs['emp']['name']);
        }
        $this->error();
    }

    public function empInfo($emp_id)
    {
        $emp = $this->model->getRoleImg($emp_id, $this->cs_list, $this->kq_list);
        if (!empty($emp)) {
            $this->success('查询成功', '', $emp);
        }
        $this->error();
    }

    public function sign()
    {
        $id_card = $this->request->param('id_card', '');
        if (empty($id_card)) {
            $this->error('请求参数异常', $_SERVER['HTTP_REFERER']);
        }
        //基础信息
        $row = $this->model
            ->where(['id_card'=>$id_card,'status'=>0])
            ->find();
        if (empty($row)) {
            $this->error('暂无待确认基础资料，请核对后再试', $_SERVER['HTTP_REFERER']);
        }
        if (empty($row['come_date'])) $row['come_date'] = date('Y-m-d');

        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a", [], 'trim');
            if ($params) {
                try {
                    //旧数据比对
//                    $rs = $this->model->checkEmpStatus($params['id_card'], $params['id_date'], $params['id_validity']);
//                    if (!in_array($rs['code'], [100, 200])) {
//                        $this->error($rs['msg']);
//                    }
                    if (!empty($this->admin['org_id'])) {
                        $params['org_id'] = $this->admin['org_id'];
                    }
                    $params['status'] = 1;
                    $result = $row->where(['id'=>$row['id']])->update($params);
                    if ($result !== false) {
                        //保存身份证照
//                        $zp = $this->request->param('zp', '');
//                        if (!empty($zp)) {
//                            $this->empImgModel->savezp($row['emp_id_2'], $zp);
//                        }
//                        $url = $this->request->baseFile().'/employee/photo?emp2='.$row['emp_id_2'];
                        $arg = $this->roleStatusModel
                            ->getOperate($this->admin['org_id'],
                                'sign',
                                1,
                                'photo',
                                $row['emp_source'],
                                $row
                            );
                        $this->success('', null, ['url'=>$arg['urls'], 'named'=>$arg['named']]);
                    } else {
                        $this->error($row->getError());
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $this->view->assign("row", $row);
        $this->view->assign("act", 'sign');
        return $this->view->fetch('edit');
    }
}
