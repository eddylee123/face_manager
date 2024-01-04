<?php

namespace app\admin\controller;

use app\admin\library\Ysl;
use app\admin\model\AdminLog;
use app\admin\model\EmpImg;
use app\admin\model\EmpRole;
use app\admin\model\SocketCache;
use app\common\controller\Backend;
use app\gateway\controller\Events;
use fast\Arr;
use fast\Random;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use think\Exception;
use think\Session;

/**
 * 员工基础信息管理
 *
 * @icon fa fa-circle-o
 */
class Employee extends Backend
{

    const worker_key = "face:worker_group:%s";

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
     * @var Ysl
     */
    protected $ysl = null;

    protected $admin;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Employee;
        $this->ysl = new Ysl();
        $this->empImgModel = new EmpImg();
        $this->empRoleModel = new EmpRole();
        $this->empExamModel = new \app\admin\model\EmpExam();

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
        $this->view->assign("cs_level_list", $this->empRoleModel->csLevel);
        $this->view->assign("kq_level_list", $this->empRoleModel->kqLevel);
        $this->view->assign("radio_list", [0=>'否',1=>'是']);
        $this->admin = Session::get('admin');
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
            $op = [];
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
                ->where($where)
                ->count();
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        $this->assignconfig("sex_list", $this->model->getSexList());
        $this->assignconfig("status_list", $this->model->getStatusList());
        return $this->view->fetch();
    }
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

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
                        ->where(['id_card'=>$params['id_card'],'resign'=>0])
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
                $params['emp_id_2'] = 'T'.date('y').$this->admin['org_id'].Random::numeric(5);
                $params['create_id'] = $this->admin['id'];
                //更新操作步骤
                $params['status'] = 1;

                if (empty($this->admin['org_id'])) {
                    $this->error('该账号未绑定基地信息，无法新增');
                }
                $params['org_id'] = $this->admin['org_id'];
                $params['dept_id'] = $this->admin['org_id'];

//                echo "<pre/>";
//                print_r($params);exit;

                try {
                    $result = $this->model->create($params);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $url = $this->request->baseFile().'/employee/photo?emp2='.$params['emp_id_2'];
                    $this->success('', null, ['url'=>$url]);
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
                        $params['dept_id'] = $this->admin['org_id'];
                    }
                    $params['is_new'] = 0;
                    $result = $row->where(['id'=>$ids])->update($params);
                    if ($result !== false) {
                        $url = $this->request->baseFile().'/employee/photo?emp2='.$row['emp_id_2'];
                        $this->success('', null, ['url'=>$url]);
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
            //判断合同工
            if ($emp_info['emp_source'] == '劳务工') {
                $named = '权限';
                $url = $this->request->baseFile().'/employee/roles?emp2='.$emp2;
            } else {
                $named = '体检信息';
                $url = $this->request->baseFile().'/employee/exam?emp2='.$emp2;
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
        $base64 = $this->request->param('photo', '');
        $flag = $this->request->param('flag', '');
        $emp2 = $this->request->param('emp2', '');

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)){
            //人像检测
            if ($flag === 'dis_img_url') {
                $pid = $emp2.'A';
                $other_field = 'img_url';
            } else {
                $pid = $emp2;
                $other_field = 'dis_img_url';
            }
            $res = $this->ysl->photoHandle($pid, $base64);;
            if ($res['code'] != 200) {
                return outJson($res);
            }

            //更新数据
            $data = [
                $flag => $res['data']['new_file']
            ];
            $info = $this->empImgModel->where('emp_id_2', $emp2)->find();
            if (!empty($info)) {
                $data['update_id'] = $this->admin['id'];
                $rs  = $info->save($data);
            } else {
                $data['emp_id_2'] = $emp2;
                $data['create_id'] = $this->admin['id'];
                $rs = $this->empImgModel->insert($data);
            }

            if ($rs === false) {
                return outJson(output(0));
            }

            if (!empty($info[$other_field])) {
                //更新操作步骤
                $emp_info = $this->model->where(['emp_id_2'=>$emp2,'status'=>1])->find();
                if ($emp_info) {
                    $rs1 = $emp_info->save(['status'=>2]);
                    if ($rs1 === false) {
                        return outJson(output(0));
                    }
                }
                //写入记录
                Arr::forget($data, $flag);
                AdminLog::record('人脸拍照', $data);
            }

            return outJson(output(200));
        }
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
        if (!empty($row['kq_level'])) {
            $kq_arr = split_param($row['kq_level'], $this->empRoleModel->kqLevel);
            $cs_arr = split_param($row['cs_level'], $this->empRoleModel->csLevel);
        }
        //判断合同工
//        $url = $named = '';
        if ($row['emp_source'] == '劳务工' && $row['status'] == 2) {
//            $named = '体检信息';
//            $url = $this->request->baseFile().'/employee/exam?emp2='.$emp2;
            empty($row['auth_date']) && $row['auth_date'] = date("Y-m-d", strtotime("+7 day"));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'strip_tags');
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

                $this->success('');
            }
            $this->error();
        }


        $this->view->assign("row", $row);
        $this->view->assign("kq_arr", $kq_arr);
        $this->view->assign("cs_arr", $cs_arr);
        $this->view->assign("cs_level_list", $this->empRoleModel->csLevel);
        $this->view->assign("kq_level_list", $this->empRoleModel->kqLevel);
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
     * 导入
     * DateTime: 2023/12/16 16:38
     */
    public function import()
    {
        $file = $this->request->request('file');

        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = ROOT_PATH . DS . 'public' . DS . $file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            $this->error(__('Unknown data format'));
        }
        if ($ext === 'csv') {
            $fileO = fopen($filePath, 'r');
            $filePath = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp = fopen($filePath, "w");
            $n = 0;
            while ($line = fgets($fileO)) {
                $line = rtrim($line, "\n\r\0");
                $encoding = mb_detect_encoding($line, ['utf-8', 'gbk', 'latin1', 'big5']);
                if ($encoding != 'utf-8') {
                    $line = mb_convert_encoding($line, 'utf-8', $encoding);
                }
                if ($n == 0 || preg_match('/^".*"$/', $line)) {
                    fwrite($fp, $line . "\n");
                } else {
                    fwrite($fp, '"' . str_replace(['"', ','], ['""', '","'], $line) . "\"\n");
                }
                $n++;
            }
            fclose($fileO) || fclose($fp);

            $reader = new Csv();
        } elseif ($ext === 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }

        $err_logs = $add_data = $up_data = $emps4 = $emps5 = [];

        //加载文件

            if (!$PHPExcel = $reader->load($filePath)) {
                $this->error(__('Unknown data format'));
            }
            $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
            $allRow = $currentSheet->getHighestRow(); //取得一共有多少行

            for ($currentRow = 3; $currentRow <= $allRow; $currentRow++) {
                $cert_number = trim($currentSheet->getCellByColumnAndRow(1, $currentRow)->getValue());
                $username = trim($currentSheet->getCellByColumnAndRow(2, $currentRow)->getValue());
                $age = trim($currentSheet->getCellByColumnAndRow(3, $currentRow)->getValue());
                $id_card = trim($currentSheet->getCellByColumnAndRow(4, $currentRow)->getValue());
                $tel = trim($currentSheet->getCellByColumnAndRow(5, $currentRow)->getValue());
                $sex = trim($currentSheet->getCellByColumnAndRow(6, $currentRow)->getValue());
                $exam_date = trim($currentSheet->getCellByColumnAndRow(7, $currentRow)->getValue());
                $cert_date = trim($currentSheet->getCellByColumnAndRow(8, $currentRow)->getValue());
                $cert_validity = trim($currentSheet->getCellByColumnAndRow(9, $currentRow)->getValue());
                $re_exam = trim($currentSheet->getCellByColumnAndRow(10, $currentRow)->getValue());
                $remark = trim($currentSheet->getCellByColumnAndRow(11, $currentRow)->getValue());
                $state = trim($currentSheet->getCellByColumnAndRow(12, $currentRow)->getValue());

                !empty($re_exam) && $re_exam = str_replace(array("\\r\\n", "\\r", "\\n"), "", $re_exam);
                !empty($remark) && $remark = str_replace(array("\\r\\n", "\\r", "\\n"), "", $remark);
                !empty($exam_date) && $exam_date = format_time($exam_date);
                !empty($cert_date) && $cert_date = format_time($cert_date);
                !empty($cert_validity) && $cert_validity = format_time($cert_validity);

                $data = compact('username','age','id_card','tel','sex','cert_number',
                    'exam_date','cert_date','cert_validity','remark','re_exam');

                //工号
                $emp_info = $this->model
                    ->where(['id_card' => $id_card])
                    ->whereIn('status', [2,21,4])
                    ->field('emp_id_2,status')
                    ->find();
                if (!empty($emp_info)) {
                    //状态判断
                    $emp_id_2 = $emp_info['emp_id_2'];
                    //状态判断
                    $data['emp_id_2'] = $emp_id_2;
                    $data['status'] = $state == '合格' ? 1 : 2;

                    //更新体检信息
                    $data['create_time'] = time();
                    $res2 = $this->empExamModel->insert($data);
                    if ($res2 === false) {
                        throw new Exception('批量导入失败');
                    }
                    //重复数据判断
                    if ($emp_info['status'] != 4) {
                        $emp_status = $data['status'] == 1 ? 3 : 21;
                        $res3 = $this->model->where(['emp_id_2'=>$emp_id_2])->update(['status'=>$emp_status]);
                        if ($res3 === false) {
                            throw new Exception('批量导入失败');
                        }
                    }

                } else {
                    //失败记录
                    $data['state'] = $state;
                    $err_logs[] = $data;
                }
            }

            //导出异常
            if (!empty($err_logs)) {
                $errRow = count($err_logs);
                $secRow = $allRow - $errRow - 2;
                $this->success(sprintf("操作成功：本次导入成功%d条，失败%d条", $secRow, $errRow));
//                return $this->exportAction($err_logs);
            }

            $this->success('操作成功');

        $this->success('操作成功');
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
        if ($flag == 'add') {
            //旧数据比对
            $card['err_msg'] = '';
            $rs = $this->model->checkEmpStatus($card['id'], $card['begin'], $card['end']);
            if ($rs['code'] != 200) {
                $card['err_msg'] = $rs['msg'];
            }
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
        $row = $this->model->where(['id_card'=>$id_card])->find();

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
                    //获取工号
                    $url = $named = '';
                    $params['emp_id'] = $this->model->getNewEmpId($row['org_id'], $row['emp_name'], $row['emp_source']);
                    //开启所有权限
                    $params['cs_level'] = array_sum(array_keys($this->empRoleModel->csLevel));
                    $params['kq_level'] = array_sum(array_keys($this->empRoleModel->kqLevel));
                    $params['is_new'] = 0;

                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
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
        $kq_arr = $cs_arr = [];
        if (!empty($row['kq_level'])) {
            $kq_arr = split_param($row['kq_level'], $this->empRoleModel->kqLevel);
            $cs_arr = split_param($row['cs_level'], $this->empRoleModel->csLevel);
        }
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
        $stepList = [
            '1' => '待拍照',
            '2' => '待体检',
            '3' => '待报道',
            '4' => '正常',
        ];
        $step = $row['status'] == 21 ? 2 : $row['status'];
        $index = $this->request->baseFile().'/employee?ref=addtabs';
        $this->view->assign("stepList", $stepList);
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

    public function export()
    {
        $op = [];
        $filter = $this->request->request('filter');
        $filter_array = json_decode(urldecode(base64_decode($filter)) , true);
        $filter_arr = [];
        foreach ($filter_array as $key=>$val) {
            if (!strpos($val['name'], "-operate") && !empty($val['value'])) {
                $filter_arr[$val['name']] = $val['value'];
            }
        }
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
        }
        unset($filter_arr['exam_time']);
//            echo '<pre>';print_r($filter_arr);exit;
        $this->request->get(['filter'=>json_encode($filter_arr)]);
        $this->request->get(['op'=>json_encode($op)]);
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        //导出
        $list = $this->model->where($where)->order($sort, $order)->select();
        $list = collection($list)->toArray();
        $this->exportTable($list);
    }
    /**
     * 导出数据
     * @param $data
     * DateTime: 2023/12/16 16:39
     */
    public function exportTable($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $armyList = $this->model->getArmyList();
        
        //设置表头
        $sheet->setCellValue('A1', '工号');
        $sheet->setCellValue('B1', '姓名');
        $sheet->setCellValue('C1', '性别');
        $sheet->setCellValue('D1', '身份证号码');
        $sheet->setCellValue('E1', '身份证住址');
        $sheet->setCellValue('F1', '年龄');
        $sheet->setCellValue('G1', '电话');
        $sheet->setCellValue('H1', '民族');
        $sheet->setCellValue('I1', '紧急联系人');
        $sheet->setCellValue('J1', '紧急联系人电话');
        $sheet->setCellValue('K1', '与本人关系');
        $sheet->setCellValue('L1', '鞋码');
        $sheet->setCellValue('M1', '婚否');
        $sheet->setCellValue('N1', '身份证有效期');
        $sheet->setCellValue('O1', '学历');
        $sheet->setCellValue('P1', '员工类型');
        $sheet->setCellValue('Q1', '体检结果');
        $sheet->setCellValue('R1', '是否拍照');
        $sheet->setCellValue('S1', '临时工号');
        $sheet->setCellValue('T1', '意向岗位');
        $sheet->setCellValue('U1', '介绍人');
        $sheet->setCellValue('V1', '介绍人工号');
        $sheet->setCellValue('W1', '口味王工作经验');
        $sheet->setCellValue('X1', '劳务公司');
        $sheet->setCellValue('Y1', '健康证日期');
        $sheet->setCellValue('Z1', '健康证结束日期');
        $sheet->setCellValue('AA1', '是否退伍');

        //改变此处设置的长度数值
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('N')->setWidth(15);
        $sheet->getColumnDimension('S')->setWidth(15);

        if (!empty($data)) {
            $emp2In = array_column($data, 'emp_id_2');
            //体检信息
            $listE = $this->empExamModel
                ->field(['status','emp_id_2','cert_date','cert_validity','id'])
                ->whereIn('emp_id_2', $emp2In)
                ->order("id","desc")
                ->distinct(true)
                ->select();
            $examList = collection($listE)->toArray();
            $examArr = array_column($examList, null, 'emp_id_2');
            //照片信息
            $imgArr = $this->empImgModel
                ->whereNotNull('img_url')
                ->whereNotNull('dis_img_url')
                ->whereIn('emp_id_2', $emp2In)
                ->distinct(true)
                ->column('id', 'emp_id_2');

            $row = 2;
            foreach ($data as $k => $v) {
                $exam = '不合格';
                $cert_date = $cert_validity = '';
                if (!empty($examArr[$v['emp_id_2']])) {
                    $exam = $examArr[$v['emp_id_2']]['status'] == 1 ? '合格' : '不合格';
                    $cert_date = $examArr[$v['emp_id_2']]['cert_date'];
                    $cert_validity = $examArr[$v['emp_id_2']]['cert_validity'];
                }
                $img = !empty($imgArr[$v['emp_id_2']]) ? '已拍照' : '未拍照';

                //输出表格
                $sheet->setCellValueExplicitByColumnAndRow(1, $row, $v['emp_id'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(2, $row, $v['emp_name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(3, $row, $v['sex'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $row, $v['id_card'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $row, $v['address'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(6, $row, $v['age'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(7, $row, $v['tel'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(8, $row, $v['folk'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(9, $row, $v['urgency_man'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(10, $row, $v['urgency_tel'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(11, $row, $v['urgency_relation'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(12, $row, $v['shoe'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(13, $row, $v['marry'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(14, $row, $v['id_validity'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(15, $row, $v['education'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(16, $row, $v['emp_source'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(17, $row, $exam, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(18, $row, $img, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(19, $row, $v['emp_id_2'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(20, $row, $v['position'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(21, $row, $v['intro_name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(22, $row, $v['intro_emp_id'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(23, $row, $v['remark'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(24, $row, $v['serv_company'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(25, $row, $cert_date, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(26, $row, $cert_validity, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(27, $row, $armyList[$v['army']], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $row++;
            }
        }


        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = '员工信息'.date('ymdhis',time()).'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        return;
    }

    public function empInfo($emp_id)
    {
        $rs = $this->model->getEmpInfo($emp_id);
        if ($rs['msg'] == '查询成功') {
            $this->success($rs['emp']['name']);
        }
        $this->error();
    }

    public function exportAction($data)
    {

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $file_name = "体检导入异常" . date("Ymd/his") . ".csv";

        $out = "健康证号,姓名,年龄,身份证号,手机号,性别,体检日期,证件日期,证件结束日期,证件复查情况,乙肝,余项,结论\r\n";

        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $tmp = array(
                    $v['cert_number'],
                    $v['username'],
                    $v['age'],
                    $v['id_card'] . "\t",
                    $v['tel'] . "\t",
                    $v['sex'],
                    $v['exam_date'],
                    $v['cert_date'],
                    $v['cert_validity'],
                    $v['re_exam'],
                    $v['hb'],
                    $v['remark'],
                    $v['state']
                );
                $out .= implode(",", $tmp);
                $out .= "\r\n";
            }
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $file_name);
        header('Cache-Control: max-age=0');
        //echo $out;
        echo mb_convert_encoding($out, 'gb2312', 'utf-8');
        exit;
    }


}
