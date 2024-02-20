<?php


namespace app\admin\controller;


use app\admin\model\EmpImg;
use app\admin\model\EmpRole;
use app\common\controller\Backend;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use think\Exception;
use think\Session;

class Employ extends Backend
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
     * @var \app\admin\model\ExamLog
     */
    protected $examLogModel = null;

    protected $admin;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Employee;
        $this->empImgModel = new EmpImg();
        $this->empRoleModel = new EmpRole();
        $this->empExamModel = new \app\admin\model\EmpExam();
        $this->examLogModel = new \app\admin\model\ExamLog();

        $this->admin = Session::get('admin');
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

        //操作条数
        $error = $success = $all = 0;

        //加载文件

        if (!$PHPExcel = $reader->load($filePath)) {
            $this->error(__('Unknown data format'));
        }
        $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
        $allRow = $currentSheet->getHighestRow(); //取得一共有多少行

        $dataArr = [];
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

            if (!is_id_number($id_card) || empty($username) || empty($exam_date)) {
                continue;
            }

            !empty($re_exam) && $re_exam = str_replace(array("\\r\\n", "\\r", "\\n"), "", $re_exam);
            !empty($remark) && $remark = str_replace(array("\\r\\n", "\\r", "\\n"), "", $remark);
            !empty($exam_date) && $exam_date = format_time($exam_date);
            !empty($cert_date) && $cert_date = format_time($cert_date);
            !empty($cert_validity) && $cert_validity = format_time($cert_validity);
            //判断去重
            $is = $this->empExamModel
                ->where(['id_card'=>$id_card,'exam_date'=>$exam_date])
                ->value('id');
            if (!empty($is)) continue;

            $dataArr[] = compact('username','age','id_card','tel','sex','cert_number',
                'exam_date','cert_date','cert_validity','remark','re_exam','state');
            $all++;
        }

        $time = time();
        foreach ($dataArr as $key=>$data) {
            //工号
            $emp_info = $this->model
                ->where(['id_card' => $data['id_card']])
                ->whereIn('status', [1,2,21,4])
                ->field('emp_id_2,status')
                ->find();
            if (!empty($emp_info)) {
                //状态判断
                $emp_id_2 = $emp_info['emp_id_2'];
                $state = $data['state'];
                unset($data['state']);

                $data['emp_id_2'] = $emp_id_2;
                $data['status'] = $state == '合格' ? 1 : 2;

                //更新体检信息
                $data['create_time'] = $time;
                $res2 = $this->empExamModel->insert($data);
                if ($res2 === false) {
                    //记录
                    $this->examLogModel->addLog($data['username'],$data['id_card'],0, $time,'数据更新失败', $emp_id_2);
                    $error++;
                    continue;
                }
                //记录
                $this->examLogModel->addLog($data['username'],$data['id_card'],1, $time,'成功', $emp_id_2);
                $success++;

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
                $this->examLogModel->addLog($data['username'],$data['id_card'],0, $time,'员工状态异常，无法导入');
                $error++;
            }
        }

        $this->success(sprintf("本次上传共%d条，成功%d条，失败%d条", $all, $success, $error),'', ['err_num'=>$error,'time'=>$time]);
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
}