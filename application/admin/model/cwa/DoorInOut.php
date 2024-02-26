<?php


namespace app\admin\model\cwa;


use think\Model;

class DoorInOut extends Model
{
    protected $connection = 'srv_kwwcwa';
    // 表名
    protected $table = 'View_DoorInOut_List_CDM';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    public function getList($org_id,$params=[],$offset=0,$limit=999,$sort='',$order='DESC')
    {
        $query = "SELECT * FROM [dbo].[View_DoorInOut_List_2024] WHERE [基地代码] = '{$org_id}'";
        if (!empty($params['工号'])) {
            $query .= " AND [工号] = '{$params['工号']}'";
        }
        if (!empty($params['姓名'])) {
            $query .= " AND [姓名] = '{$params['姓名']}'";
        }
        if (!empty($params['位置ID'])) {
            $query .= " AND [位置ID] = '{$params['位置ID']}'";
        }
        if (!empty($params['start'])) {
            $query .= " AND [打卡时间] >= '{$params['start']}'";
        }
        if (!empty($params['end'])) {
            $query .= " AND [打卡时间] <= '{$params['end']}'";
        }

        $query .= " ORDER BY [{$sort}] {$order}  
        offset {$offset} rows fetch next {$limit} rows only";

        $rs = $this->query($query);
        return $rs;
    }

    public function countList($org_id,$params=[])
    {
        $query = "SELECT count(*) total FROM [dbo].[View_DoorInOut_List_2024] WHERE [基地代码] = '{$org_id}'";
        if (!empty($params['工号'])) {
            $query .= " AND [工号] = '{$params['工号']}'";
        }
        if (!empty($params['姓名'])) {
            $query .= " AND [姓名] = '{$params['姓名']}'";
        }
        if (!empty($params['位置ID'])) {
            $query .= " AND [位置ID] = '{$params['位置ID']}'";
        }
        if (!empty($params['start'])) {
            $query .= " AND [打卡时间] >= '{$params['start']}'";
        }
        if (!empty($params['end'])) {
            $query .= " AND [打卡时间] <= '{$params['end']}'";
        }

        $rs = $this->query($query);
        return reset($rs)['total'];
    }
}