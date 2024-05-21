<?php


namespace app\api\service\employ;


use app\admin\model\EmpExam;
use app\admin\model\Employee;
use app\admin\model\ExamLog;
use app\api\service\api\CommonService;
use app\api\service\BaseService;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use think\Db;
use think\Exception;

class ExamService extends BaseService
{
    protected $empModel;
    protected $examModel;

    public function __construct()
    {
        $this->empModel = new Employee();
        $this->examModel =  new EmpExam();

    }

    public function examList(array $param)
    {
        $object = $this->examModel
            ->field('emp_id,emp_id_2,cert_number,exam_date,cert_date,cert_validity,remark,status,create_time');

        if (!empty($param['emp_id'])) {
            $object->where('emp_id','like', '%'.$param['emp_id'].'%');
        }
        if (!empty($param['emp_id_2'])) {
            $object->where('emp_id_2','like', '%'.$param['emp_id_2'].'%');
        }
        if (!empty($param['status'])) {
            $object->where('status', $param['status']);
        }

        $list = $object
            ->order('create_time', 'asc')
            ->paginate(['list_rows' => $param['page_size'], 'page' => $param['page']])
            ->toArray();

        if (!empty($list['data'])) {
            $emp2Ids = array_column($list['data'], 'emp_id_2');
            $empArr = $this->empModel
                ->whereIn('emp_id_2', $emp2Ids)
                ->column('emp_id_2,emp_name,id_card,tel,birthday', 'emp_id_2');

            foreach ($list['data'] as &$v) {
                $empInfo = $empArr[$v['emp_id_2']] ?? [];

                $v['username'] = $empInfo['emp_name'] ?? '';
                $v['id_card'] = $empInfo['id_card'] ?? '';
                $v['tel'] = $empInfo['tel'] ?? '';
                $v['sex'] = $empInfo['sex'] ?? '';
                $v['sex'] = $empInfo['sex'] ?? '';
                $v['age'] = get_age($empInfo['birthday']);
            }
        }


        return $list;
    }

    public function setExam(array $params)
    {
        $emp_info = $this->empModel->where(['emp_id_2' => $params['emp_id_2']])->find();

        if (!$emp_info) {
            app_exception('员工信息异常');
        }
        //状态判断
        if (!in_array($emp_info['status'], [2,21,4])) {
            if ($emp_info['status'] < 2) {
                app_exception('请录入人脸后操作，谢谢配合');
            } else {
                app_exception('体检信息已通过，请勿重复操作');
            }
        }

        $params['username'] = $emp_info['emp_name'];
        $params['age'] = $emp_info['age'];
        $params['id_card'] = $emp_info['id_card'];
        $params['tel'] = $emp_info['tel'];
        $params['sex'] = $emp_info['sex'];
        $params['create_time'] = time();
        $res = $this->examModel->allowField(true)->save($params);

        if (!$res) {
            app_exception('体检信息录入失败');
        }
        //更新操作步骤
        if ($emp_info['status'] != 4) {
            $emp_status = $params['status'] == 1 ? 3 : 21;
            $rs1 = $emp_info->save(['status' => $emp_status]);
            if ($rs1 === false) {
                app_exception('数据异常');
            }
        }

        $msg = $params['status'] == 1 ? '信息录入完成，请等待员工报道' : '';
        return $msg;
    }

    public function import(string $file)
    {
        $filePath = CommonService::instance()->getObject($file);

        if (!is_file($filePath)) {
            app_exception('上传文件未找到');
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            app_exception('未知的文件格式');
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
            app_exception('未知的文件格式');
        }
        $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
        $allRow = $currentSheet->getHighestRow(); //取得一共有多少行

        $time = time();
        $dataArr = $errArr = [];
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
            $is = $this->examModel
                ->where(['id_card'=>$id_card,'exam_date'=>$exam_date])
                ->value('id');
            if (!empty($is)) continue;

            $data = compact('username','age','id_card','tel','sex','cert_number',
                'exam_date','cert_date','cert_validity','remark','re_exam','state');
            $all++;

            $emp_info = $this->empModel
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
                $res2 = $this->examModel->insert($data);
                if ($res2 === false) {
                    $err_msg = '数据更新失败';
                    $errArr[] = compact('cert_number','username','tel','id_card','exam_date','state','err_msg');
                    $error++;
                    continue;
                }
                //记录
                $success++;

                //重复数据判断
                if ($emp_info['status'] != 4) {
                    $emp_status = $data['status'] == 1 ? 3 : 21;
                    $res3 = $this->empModel->where(['emp_id_2'=>$emp_id_2])->update(['status'=>$emp_status]);
                    if ($res3 === false) {
                        throw new Exception('批量导入失败');
                    }
                }

            } else {
                //失败记录
                $err_msg = '员工状态异常，无法导入';
                $errArr[] = compact('cert_number','username','tel','id_card','exam_date','state','err_msg');
                $error++;
            }
        }

        //删除上传文件
        unlink($filePath);

        //导出失败记录
        $errData = [];
        if (!empty($errArr)) {
            $header = ['健康证号','姓名','身份证号','手机号','体检日期','是否合格','失败原因'];
            $errData = CommonService::instance()->putExcel($header, $errArr, '导入失败记录');
        }

        $msg = sprintf("本次上传共%d条，成功%d条，失败%d条", $all, $success, $error);
        return array_merge(['msg'=>$msg], $errData);

    }

}