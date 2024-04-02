<?php
namespace app\admin\model\cwa;

use think\Model;

class CaterB extends Model
{
    protected $connection = 'srv_kwwcwa';
    // 表名
    protected $table = 'tmp_caterlistB';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    public $weekList = [
        '星期一' => '星期一',
        '星期二' => '星期二',
        '星期三' => '星期三',
        '星期四' => '星期四',
        '星期五' => '星期五',
        '星期六' => '星期六',
        '星期天' => '星期天',
    ];

    public function getList($org_id,$params=[],$offset=0,$limit=999)
    {
        $date = date("Y-m-d");
        $query = "SELECT * FROM [dbo].[tmp_caterlistB] WHERE [org_ID] = '{$org_id}' AND [日期] < '{$date}'";
        if (!empty($params['start_time'])) {
            $query .= " AND [日期] >= '{$params['start_time']}'";
        }
        if (!empty($params['end_time'])) {
            $query .= " AND [日期] <= '{$params['end_time']}'";
        }
        if (!empty($params['星期'])) {
            $query .= " AND [星期] = '{$params['星期']}'";
        }
        $query .= " ORDER BY [日期] DESC  
        offset {$offset} rows fetch next {$limit} rows only";

        $rs = $this->query($query);
        return $rs;
    }

    public function countList($org_id,$params=[])
    {
        $date = date("Y-m-d");
        $query = "SELECT count(*) total FROM [dbo].[tmp_caterlistB] WHERE [org_ID] = '{$org_id}' AND [日期] < '{$date}'";
        if (!empty($params['start_time'])) {
            $query .= " AND [日期] >= '{$params['start_time']}'";
        }
        if (!empty($params['end_time'])) {
            $query .= " AND [日期] <= '{$params['end_time']}'";
        }
        if (!empty($params['星期'])) {
            $query .= " AND [星期] = '{$params['星期']}'";
        }

        $rs = $this->query($query);
        return reset($rs)['total'];
    }

    public function getDetail($org_id,$params=[],$offset=0,$limit=999,$sort='',$order='DESC')
    {
        $query = "SELECT * FROM View_XFALL_2024 WHERE [基地代码] = '{$org_id}'";

        if (!empty($params['start'])) {
            $query .= " AND [打卡时间] >= '{$params['start']}'";
        }
        if (!empty($params['end'])) {
            $query .= " AND [打卡时间] <= '{$params['end']}'";
        }
        if (!empty($params['工号'])) {
            $query .= " and [工号]='{$params['工号']}'";
        }
        if (!empty($params['姓名'])) {
            $query .= " and [姓名] LIKE '{$params['姓名']}%'";
        }
        if (!empty($params['食堂'])) {
            $query .= " and [食堂]='{$params['食堂']}'";
        }

        $query .= " ORDER BY [{$sort}] {$order}  
        offset {$offset} rows fetch next {$limit} rows only";

        $rs = $this->query($query);
        if ($rs) {
            foreach ($rs as &$v) {
                if (!empty($v['部门'])) {
                    $v['部门'] = substr($v['部门'], strpos($v['部门'], ' '));
                }
            }
        }
        return $rs;
    }

    public function countDetail($org_id,$params=[])
    {
        $query = "SELECT COUNT(*) total FROM View_XFALL_2024 WHERE [基地代码] = '{$org_id}'";

        if (!empty($params['start_time'])) {
            $query .= " AND [打卡时间] >= '{$params['start_time']}'";
        }
        if (!empty($params['end_time'])) {
            $query .= " AND [打卡时间] <= '{$params['end_time']}'";
        }
        if (!empty($params['工号'])) {
            $query .= " and [工号]='{$params['工号']}'";
        }
        if (!empty($params['姓名'])) {
            $query .= " and [姓名] LIKE '{$params['姓名']}%'";
        }
        if (!empty($params['食堂'])) {
            $query .= " and [食堂]='{$params['食堂']}'";
        }

        $rs = $this->query($query);
        return reset($rs)['total'];
    }
}