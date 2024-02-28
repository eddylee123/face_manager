<?php


namespace app\admin\library;

use app\admin\model\Employee;
use app\admin\model\Notice;
use app\cache\BaseCache;
use think\Exception;
use think\Url;

class Handle
{

    /**
     * 消息通知
     * DateTime: 2024-02-27 16:17
     */
    public static function notice()
    {
        $redis = alone_redis();
        $empModel = new Employee();
        $noticeModel = new Notice();

        $point = $redis->get(BaseCache::notice_point);
        $statTime = $point ?? '2023-10-01';
        $endTime = date('Y-m-d');
        $list = $empModel
            ->field('id,emp_name,emp_source')
            ->whereBetween('auth_date', [$statTime, $endTime])
            ->select();

        try {
            foreach ($list as $v) {
                $id = $noticeModel->where([
                    'type' => $noticeModel->type_auth,
                    'link_id' => $v['id']
                ])->value('id');
                if (!empty($id)) continue;

                $content = $v['emp_name']."({$v['emp_source']})";
                $noticeModel->addNotice($noticeModel->type_auth, $v['id'],$content);
            }

        } catch (Exception $e) {
            logs_write($e->getMessage());
        }
        $redis->set(BaseCache::notice_point, $endTime);
    }


    /**
     * 获取员工详情tab栏
     * @param $empNum
     * @return \string[][]
     * DateTime: 2024-02-27 16:36
     */
    public static function getEmpTab($empNum)
    {
        $tabList = [
            [
                'name' => '基础资料',
                'url' => 'manager/detail',
            ],
            [
                'name' => '其他资料',
                'url' => 'manager/other',
            ],
            [
                'name' => '消费明细',
                'url' => 'cwa/cater_b/emp',
            ],
            [
                'name' => '门禁明细',
                'url' => 'cwa/door/emp',
            ],
        ];

        foreach ($tabList as &$v) {
            $v['url'] = Url::build($v['url']."?empNum=$empNum");
        }

        return $tabList;
    }

    public static function reFiled($filedArr)
    {
        $data = [];
        foreach ($filedArr as $v) {
            if (empty($v['fieldName'])) {
                continue;
            }
            $data[$v['fieldName']] = $v['fieldValue'] ?? '';

        }

        return $data;
    }

    public static function toFiled($filedArr, $filedNew)
    {
        foreach ($filedArr as &$v) {

            if (empty($v['fieldName'])) {
                continue;
            }
            if (empty($filedNew[$v['fieldName']])) {
                continue;
            }
            $v['fieldValue'] = $filedNew[$v['fieldName']];
        }

        return $filedArr;
    }
}