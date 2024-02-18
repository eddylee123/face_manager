<?php


namespace app\admin\library;

use app\admin\model\Employee;
use app\admin\model\Notice;
use app\cache\BaseCache;
use think\Exception;

class Handle
{

    protected $redis;
    protected $empModel;
    protected $noticeModel;

    public function __construct()
    {
        $this->redis = alone_redis();
        $this->empModel = new Employee();
        $this->noticeModel = new Notice();
    }

    public function notice()
    {
        $point = $this->redis->get(BaseCache::notice_point);
        $statTime = $point ?? '2023-10-01';
        $endTime = date('Y-m-d');
        $list = $this->empModel
            ->field('id,emp_name,emp_source')
            ->whereBetween('auth_date', [$statTime, $endTime])
            ->select();

        try {
            foreach ($list as $v) {
                $id = $this->noticeModel->where([
                    'type' => $this->noticeModel->type_auth,
                    'link_id' => $v['id']
                ])->value('id');
                if (!empty($id)) continue;

                $content = $v['emp_name']."({$v['emp_source']})";
                $this->noticeModel->addNotice($this->noticeModel->type_auth, $v['id'],$content);
            }

        } catch (Exception $e) {
            logs_write($e->getMessage());
        }
        $this->redis->set(BaseCache::notice_point, $endTime);
    }
}