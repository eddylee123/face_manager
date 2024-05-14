<?php


namespace app\api\service\employ;


use app\admin\library\Hro;
use app\admin\model\EmpExam;
use app\admin\model\EmpFile;
use app\admin\model\EmpImg;
use app\admin\model\Employee;
use app\api\service\api\CommonService;
use app\api\service\BaseService;
use fast\Arr;
use think\Env;
use think\Exception;

class InviteService extends BaseService
{
    protected $empModel;
    protected $fileModel;
    protected $examModel;
    protected $imgModel;

    public function __construct()
    {
        $this->empModel =  new Employee();
        $this->fileModel =  new EmpFile();
        $this->examModel =  new EmpExam();
        $this->imgModel =  new EmpImg();
    }

    public function empList(string $orgId, array $param)
    {
        $object = $this->initEmpModel($orgId, $param);

        $list = $object
            ->order('status,delete_time,create_id', 'asc')
            ->paginate(['list_rows' => $param['page_size'], 'page' => $param['page']])
            ->toArray();
        
        return $list;
    }

    public function initEmpModel(string $orgId, array $param)
    {
        $object = $this->empModel
            ->field('emp_id_3,card_id,card_id_enc', true)
            ->where('org_id', $orgId)
            ->where('status', 'egt' , 0)
            ->where('status', '<>' , 21);

        if (!empty($param['emp_id'])) {
            $object->where('emp_id','like', '%'.$param['emp_id'].'%');
        }
        if (!empty($param['emp_id_2'])) {
            $object->where('emp_id_2','like', '%'.$param['emp_id_2'].'%');
        }
        if (!empty($param['status'])) {
            $object->where('status', $param['status']);
        }
        if (!empty($param['emp_name'])) {
            $object->where('emp_name','like', '%'.$param['emp_name'].'%');
        }
        if (!empty($param['id_card'])) {
            $object->where('id_card', $param['id_card']);
        }
        if (!empty($param['emp_source'])) {
            $object->where('emp_source', $param['emp_source']);
        }
        if (!empty($param['tel'])) {
            $object->where('tel', $param['tel']);
        }
        if (!empty($param['come_start'])) {
            $object->where("come_start", '>=', $param['come_start']);
        }
        if (!empty($param['come_end'])) {
            $object->where("come_end", '<=', $param['come_end']);
        }
        if (!empty($param['kq_start'])) {
            $object->where("kq_start", '>=', $param['kq_start']);
        }
        if (!empty($param['kq_end'])) {
            $object->where("kq_end", '<=', $param['kq_end']);
        }
        if (!empty($param['exam_start'])) {
            $object->where("exam_start", '>=', $param['exam_start']);
        }
        if (!empty($param['exam_end'])) {
            $object->where("exam_end", '<=', $param['exam_end']);
        }

        return $object;
    }

    public function empAdd(string $orgId, array $params)
    {
        if (empty($orgId)) {
            app_exception('该账号未绑定基地信息，无法新增');
        }
        try {
            $exist = $this->empModel
                ->where(['id_card' => $params['id_card']])
                ->value('id');
            if ($exist) {
                app_exception('员工信息已存在，请勿重复注册');
            }
            //旧数据比对
            $rs = Hro::checkEmpStatus($params['id_card'], $params['id_date'], $params['id_validity']);
            if (!in_array($rs['code'], [100, 200])) {
                app_exception($rs['msg']);
            }
            //生成临时工号
            $params['emp_id_2'] = $this->empModel->getTempId($orgId);
            //更新操作步骤
            $params['status'] = 1;
            $params['org_id'] = $orgId;

            $result = $this->empModel->insert($params);
            if ($result === false) {
                app_exception('操作失败，请稍后再试');
            }

            return $result;
        } catch (Exception $e) {
            app_exception($e->getMessage());
        }
    }

    public function empEdit(string $emp2, array $params)
    {
        try {
            $row = $this->empModel->where(['emp_id_2'=>$emp2])->find();
            if (!$row){
                app_exception('数据不存在');
            }
            $params['is_new'] = 0;

            $result = $row->save($params);
            if ($result === false) {
                app_exception('操作失败，请稍后再试');
            }

            return $result;
        } catch (Exception $e) {
            app_exception($e->getMessage());
        }
    }

    public function empImage(string $empId)
    {
        return $this->empModel->empImage($empId);
    }

    public function saveImage(string $emp2,array $param)
    {
        $data = [];
        !empty($param['img_url']) && $data['img_url'] = $param['img_url'];
        !empty($param['dis_img_url']) && $data['dis_img_url'] = $param['dis_img_url'];

        if (empty($data)) {
            app_exception('照片不能为空');
        }
        if (empty($emp2)) {
            app_exception('临时工号异常');
        }
        $row = $this->empModel->where(['emp_id_2'=>$emp2])->find();
        if (empty($row)) {
            app_exception('员工信息异常');
        }
        $info = $this->fileModel->where('emp_id_2', $emp2)->find();
        if (empty($info['img_url'])) {
            if (empty($param['img_url'])) {
                app_exception('照片1不能为空');
            }
        }
        //更新照片
        if (!empty($info)) {
            $rs  = $info->allowField(true)->save($data);
        } else {
            $data['emp_id_2'] = $emp2;
            $rs = $this->fileModel->allowField(true)->save($data);
        }
        if ($rs === false) {
            app_exception('上传失败');
        }
        //更新操作步骤
        $emp_info = $this->empModel->where(['emp_id_2'=>$emp2,'status'=>1])->find();
        if ($emp_info) {
            $rs1 = $emp_info->save(['status'=>2]);
            if ($rs1 === false) {
                app_exception('操作失败，请稍后再试');
            }
        }

        return $rs;
    }

    public function infoByIdCard(string $orgId,string $idCard)
    {
        $empInfo = $this->empModel->where(['id_card'=>$idCard])->find();
        return $this->empInfo($orgId, $empInfo);
    }
    public function infoByNo2(string $orgId,string $emp2)
    {
        $empInfo = $this->empModel->where(['emp_id_2'=>$emp2])->find();
        return $this->empInfo($orgId, $empInfo);
    }

    public function empInfo(string $orgId, $empInfo)
    {
        //基础信息
        if (!$empInfo){
            app_exception('数据不存在');
        }

        //人脸信息
        $img = $this->fileModel->where(['emp_id_2'=>$empInfo['emp_id_2']])->column('img_url,dis_img_url');

        //体检信息
        $exam = $this->examModel->where(['emp_id_2' => $empInfo['emp_id_2']])->order('id desc')->find();

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
        //权限
        $role = [
            'kq_list' => [],
            'cs_list' => [],
        ];
        $roleConf = ConfigService::instance()->levelList($orgId);
        if ($empInfo['kq_level'] > 0) {
            $role['kq_list'] = split_param($empInfo['kq_level'], $roleConf['kq_list']);
        }
        if ($empInfo['cs_level'] > 0) {
            $role['cs_list'] = split_param($empInfo['cs_level'], $roleConf['cs_list']);
        }

        //部门编码
        $deptList = $this->empModel->getDept($orgId);
        $dept = $deptList['Data'] ?? [];

        return compact('empInfo','img','exam','role','dept');
    }

    public function empImg(string $emp2)
    {
        if (empty($emp2)) {
            app_exception('临时工号异常');
        }
        return $this->fileModel->where('emp_id_2', $emp2)->find();
    }

    public function setRole(array $params)
    {
        $emp2 = $params['emp_id_2'];
        $row = $this->empModel->where(['emp_id_2'=>$emp2])->find();
        if (!$row) {
            app_exception('数据不存在');
        }

        $data = [
            'cs_level' => -1,
            'kq_level' => -1,
            'is_new' => 0,
        ];
        if (!empty($params['cs_list'])) {
            $data['cs_level'] = array_sum($params['cs_list']);
        }
        if (!empty($params['kq_list'])) {
            $data['kq_level'] = array_sum($params['kq_list']);
        }
        $res = $row->save($data);
        if ($res === false) {
            app_exception('操作失败，请稍后再试');
        }

        return $res;
    }

    public function sign(string $idCard, string $orgId, array $params)
    {
        try {
            //基础信息
            $row = $this->empModel
                ->field('status,org_id,emp_source,emp_name')
                ->where(['id_card'=>$idCard,'status'=>0])
                ->find();
            if (!$row) {
                app_exception('暂无待确认基础资料，请核对后再试');
            }
            if (empty($row['come_date'])) $row['come_date'] = date('Y-m-d');
            if (!empty($orgId)) {
                $params['org_id'] = $orgId;
            }
            $params['status'] = 1;

            $result = $row->allowField(true)->save($params);
            if ($result !== false) {
                return $idCard;
            } else {
                app_exception($row->getError());
            }
        } catch (Exception $e) {
            app_exception($e->getMessage());
        }
    }

    public function report(string $idCard, array $params)
    {
        try {
            //基础信息
            $row = $this->empModel
                ->field('id,status,org_id,emp_source,emp_name')
                ->where(['id_card'=>$idCard])
                ->order('id', 'desc')
                ->find();
            if (!$row) {
                app_exception('数据不存在');
            }
            if ($row['status'] != 3){
                app_exception('当前员工暂无法报道，请确认状态后再试~');
            }

            //获取工号
            if (Env::get('app.master') == false) {
                if ($row['emp_source'] == '合同工') {
                    $params['emp_id'] = $this->empModel->getEmpHtg($row['org_id']);
                } else {
                    $params['emp_id'] = $this->empModel->getEmpLwg($row['org_id']);
                }
            } else {
                $params['emp_id'] = $this->empModel->getNewEmpId($row['org_id'], $row['emp_name'], $row['emp_source']);
            }
            //开启所有权限
            $roleConf = ConfigService::instance()->levelList($row['org_id']);
            $params['cs_level'] = array_sum(array_keys($roleConf['cs_list']));
            $params['kq_level'] = array_sum(array_keys($roleConf['kq_list']));
            $params['is_new'] = 0;
            $params['status'] = 4;
            $params['kq_date'] = date('Y-m-d');

            $result = $row->allowField(true)->save($params);
            if ($result !== false) {
                //初始化正式工
                (new \app\admin\model\Manager())->initInsert($this->empModel->get($row['id']));
                return $idCard;
            } else {
                app_exception($row->getError());
            }
        } catch (Exception $e) {
            app_exception($e->getMessage());
        }
    }

    public function export(string $orgId, array $param)
    {
        $object = $this->initEmpModel($orgId, $param);

        $list = $object
            ->order('status,create_time', 'asc')
            ->select();
        $list = collection($list)->toArray();

        if (!empty($list)) {
            $armyList = $this->empModel->getArmyList();
            $emp2In = array_column($list, 'emp_id_2');

            //体检信息
            $examArr = $this->examModel
                ->field(['status','emp_id_2','cert_date','cert_validity','id'])
                ->whereIn('emp_id_2', $emp2In)
                ->order("id","desc")
                ->distinct(true)
                ->column('id,status,cert_date,cert_validity', 'emp_id_2');
            //照片信息
            $imgArr = $this->imgModel
                ->whereNotNull('img_url')
                ->whereNotNull('dis_img_url')
                ->whereIn('emp_id_2', $emp2In)
                ->distinct(true)
                ->column('id', 'emp_id_2');

            $data = [];
            foreach ($list as $v) {
                $exam = '不合格';
                $cert_date = $cert_validity = '';
                if (!empty($examArr[$v['emp_id_2']])) {
                    $exam = $examArr[$v['emp_id_2']]['status'] == 1 ? '合格' : '不合格';
                    $cert_date = $examArr[$v['emp_id_2']]['cert_date'];
                    $cert_validity = $examArr[$v['emp_id_2']]['cert_validity'];
                }
                $img = !empty($imgArr[$v['emp_id_2']]) ? '已拍照' : '未拍照';

                $data[] = [
                    $v['emp_id_2'],
                    $v['emp_name'],
                    $v['sex'],
                    $v['id_card'],
                    $v['address'],
                    $v['age'],
                    $v['tel'],
                    $v['folk'],
                    $v['urgency_man'],
                    $v['urgency_tel'],
                    $v['urgency_relation'],
                    $v['shoe'],
                    $v['marry'],
                    $v['id_validity'],
                    $v['education'],
                    $v['emp_source'],
                    $exam,
                    $img,
                    $v['emp_id_2'],
                    $v['position'],
                    $v['intro_name'],
                    $v['intro_emp_id'],
                    $v['remark'],
                    $v['serv_company'],
                    $cert_date,
                    $cert_validity,
                    $armyList[$v['army']]
                ];
            }
            //上传excel
            $header = ['工号','姓名','性别','身份证号码','身份证住址','年龄','电话','民族','紧急联系人','紧急联系人电话','与本人关系','鞋码','婚否','身份证有效期',
                '学历','员工类型','体检结果','是否拍照','临时工号','意向岗位','介绍人','介绍人工号','口味王工作经验','劳务公司','健康证日期','健康证结束日期','是否退伍'];
            return CommonService::instance()->putExcel($header, $data, '招聘人员');
        }
    }
}