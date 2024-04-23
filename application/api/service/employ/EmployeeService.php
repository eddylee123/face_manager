<?php


namespace app\api\service\employ;


use app\api\service\BaseService;

class EmployeeService extends BaseService
{
    protected $empModel;
    protected $empExamModel;

    public function __construct()
    {
        $this->empModel = new \app\admin\model\Employee();
        $this->empExamModel = new \app\admin\model\EmpExam();
    }

    public function empList(string $orgId, array $param)
    {
        $object = $this->empModel
            ->field('emp_id,emp_id_2,emp_name,folk,address,education,education,org_id,sex,
            id_card,come_date,kq_date,emp_source,tel,marry,status,create_time');

        if (!empty($orgId)) {
            $object->where("ORG_ID", $orgId);
        }
        if (!empty($param['status'])) {
            $object->where("status", $param['status']);
        }
        if (!empty($param['emp_id'])) {
            $object->where("emp_id", $param['emp_id']);
        }
        if (!empty($param['emp_id_2'])) {
            $object->where("emp_id_2", $param['emp_id_2']);
        }
        if (!empty($param['id_card'])) {
            $object->where("id_card", 'like', '%'.$param['id_card'].'%');
        }
        if (!empty($param['emp_name'])) {
            $object->where("emp_name", 'like', '%'.$param['emp_name'].'%');
        }
        if (!empty($param['emp_source'])) {
            $object->where("emp_source", $param['emp_source']);
        }
        if (!empty($param['tel'])) {
            $object->where("tel", $param['tel']);
        }
        if (!empty($param['come_start'])) {
            $object->where("come_date", '>=', $param['come_start']);
        }
        if (!empty($param['come_end'])) {
            $object->where("come_date", '<=', $param['come_end']);
        }
        if (!empty($param['kq_start'])) {
            $object->where("kq_date", '>=', $param['kq_start']);
        }
        if (!empty($param['kq_end'])) {
            $object->where("kq_date", '<=', $param['kq_end']);
        }
        //体检时间
        if (!empty($param['exam_start']) || !empty($param['exam_end'])) {
            $where = [];
            !empty($param['exam_start']) && $where['create_time'] = ['>=', $param['exam_start']];
            !empty($param['exam_end']) && $where['create_time'] = ['<=', $param['exam_end']];

            $rsExam = $this->empExamModel
                ->where($where)
                ->column('emp_id_2');
            if (!empty($rsExam)) {
                $object->whereIn('emp_id_2', array_keys($rsExam));
            }
        }

        $list = $object
            ->order('status,create_time', 'asc')
            ->paginate(['list_rows' => $param['page_size'], 'page' => $param['page']])
            ->toArray();

        return $list;
    }
}