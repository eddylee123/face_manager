<?php

namespace app\admin\controller;

use app\admin\model\WageInout;
use app\common\controller\Backend;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use think\Exception;
use think\Session;

/**
 * 工资记录
 *
 * @icon fa fa-circle-o
 */
class WagePay extends Backend
{
    
    /**
     * WagePay模型对象
     * @var \app\admin\model\WagePay
     */
    protected $model = null;

    /**
     * @var WageInout
     */
    protected $inoutModel = null;

    protected $admin;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\WagePay;
        $this->inoutModel = new WageInout();

        $this->admin = Session::get('admin');

        $this->assign('orgList', $this->model->orgList());
        $this->assign('statusList', $this->model->statusMap);
        $this->assign('typeList', $this->model->typeMap);
        $this->assign('sendList', $this->model->sendMap);
    }

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
            $filter = $this->request->request('filter');
            $filter_arr = json_decode($filter , true);
            $filter_arr['org_id'] = $this->admin['org_id'];
            //echo '<pre>';print_r($filter_arr);exit;
            $this->request->get(['filter'=>json_encode($filter_arr)]);
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
        $this->assignconfig('org_list', $this->model->orgList());
        $this->assignconfig('status_list', $this->model->statusMap);
        $this->assignconfig('type_list', $this->model->typeMap);
        $this->assignconfig('send_list', $this->model->sendMap);
        return $this->view->fetch();
    }

    public function detail()
    {
        $payId = $this->request->param('ids', '');
        $inout = $this->inoutModel->where(['pay_id'=>$payId])->find();

        $this->assign('row', $inout);
        $this->assign('fields', $this->inoutModel->fieldMap);
        return $this->view->fetch();
    }

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
//        $payIns = $inoutIns = [];
        $payFiled = array_flip($this->model->fieldMap);
        $inoutFiled = array_flip($this->inoutModel->fieldMap);
        $payArr = array_keys($payFiled);
        $inoutArr = array_keys($inoutFiled);
        try {
            if (!$PHPExcel = $reader->load($filePath)) {
                $this->error(__('Unknown data format'));
            }
            $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
            $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
            $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
            $maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
            $fields = [];
            for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $fields[] = $val;
                }
            }

            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $values = [];
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $values[] = is_null($val) ? '' : $val;
                }
                $payRow = $inoutRow = [];
                $temp = array_combine($fields, $values);
                foreach ($temp as $k => $v) {
                    if (in_array($k, $payArr) && isset($payFiled[$k])) {
                        if ($k == '薪资月份') {
                            $v = format_time($v);
                        }
                        $payRow[$payFiled[$k]] = $v;
                    }
                    if (in_array($k, $inoutArr) && isset($inoutFiled[$k])) {
                        $inoutRow[$inoutFiled[$k]] = $v;
                    }
                }
                if ($payRow) {
                    $payRow['org_id'] = (string)$this->admin['org_id'];
                    $payRow['create_time'] = date('Y-m-d H:i:s');
                    if (isset($payRow['type'])) {
                        $payRow['type'] = $payRow['type'] == '短信' ? 2 : 1;
                    }

                    $payId = $this->model->insertGetId($payRow);
                    if ($inoutRow){
                        $inoutRow['pay_id'] = $payId;
                        $this->inoutModel->insert($inoutRow);
                    }
                }

            }
//            echo "<pre>";print_r($payIns);exit;
//            if ($payIns) model('WagePay')->insertAll($payIns);
//            if ($inoutIns) model('WageInout')->insertAll($inoutIns);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

        $this->success();
    }
}
