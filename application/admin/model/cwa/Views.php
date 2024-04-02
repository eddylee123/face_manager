<?php


namespace app\admin\model\cwa;


use think\Model;

class Views extends Model
{
    protected $connection = 'srv_kwwcwa';

    public function placeCount($org_id,$params=[])
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

    /**
     * 食堂统计
     * @param $org_id
     * @param array $params
     * @param int $offset
     * @param int $limit
     * @return mixed
     * DateTime: 2024-04-02 11:53
     */
    public function placeList($org_id,$params=[],$offset=0,$limit=999)
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

    /**
     * 消费列表
     * @param $org_id
     * @param array $params
     * @param int $offset
     * @param int $limit
     * @param string $sort
     * @param string $order
     * @return mixed
     * DateTime: 2024-04-02 11:54
     */
    public function consumeList($org_id,$params=[],$offset=0,$limit=999,$sort='',$order='DESC')
    {
        $query = "SELECT * FROM view_consume_2024 WHERE [org_id] = '{$org_id}'";

        if (!empty($params['start_time'])) {
            $query .= " AND [clock_time] >= '{$params['start_time']}'";
        }
        if (!empty($params['end_time'])) {
            $query .= " AND [clock_time] <= '{$params['end_time']}'";
        }
        if (!empty($params['emp_id'])) {
            $query .= " and [emp_id]='{$params['emp_id']}'";
        }
        if (!empty($params['emp_name'])) {
            $query .= " and [emp_name] LIKE '{$params['emp_name']}%'";
        }
        if (!empty($params['place_no'])) {
            $query .= " and [place_no]='{$params['place_no']}'";
        }

        $query .= " ORDER BY [{$sort}] {$order}  
        offset {$offset} rows fetch next {$limit} rows only";

        return $this->query($query);
    }

    public function consumeCount($org_id,$params=[])
    {
        $query = "SELECT COUNT(*) total FROM view_consume_2024 WHERE [org_id] = '{$org_id}'";

        if (!empty($params['start_time'])) {
            $query .= " AND [clock_time] >= '{$params['start_time']}'";
        }
        if (!empty($params['end_time'])) {
            $query .= " AND [clock_time] <= '{$params['end_time']}'";
        }
        if (!empty($params['emp_id'])) {
            $query .= " and [emp_id]='{$params['emp_id']}'";
        }
        if (!empty($params['emp_name'])) {
            $query .= " and [emp_name] LIKE '{$params['emp_name']}%'";
        }
        if (!empty($params['place_no'])) {
            $query .= " and [place_no]='{$params['place_no']}'";
        }

        $rs = $this->query($query);
        return reset($rs)['total'];
    }

    /**
     * 门禁列表
     * @param $org_id
     * @param array $params
     * @param int $offset
     * @param int $limit
     * @param string $sort
     * @param string $order
     * @return mixed
     * DateTime: 2024-04-02 11:54
     */
    public function doorList($org_id,$params=[],$offset=0,$limit=999,$sort='',$order='DESC')
    {
        $query = "SELECT * FROM [dbo].[view_door_2024] WHERE [org_id] = '{$org_id}'";

        if (!empty($params['emp_id'])) {
            $query .= " AND [emp_id] = '{$params['emp_id']}'";
        }
        if (!empty($params['emp_name'])) {
            $query .= " AND [emp_name] = '{$params['emp_name']}'";
        }
        if (!empty($params['door_id'])) {
            $query .= " AND [door_id] = '{$params['door_id']}'";
        }
        if (!empty($params['start_time'])) {
            $query .= " AND [clock_time] >= '{$params['start_time']}'";
        }
        if (!empty($params['end_time'])) {
            $query .= " AND [clock_time] <= '{$params['end_time']}'";
        }

        $query .= " ORDER BY [{$sort}] {$order}  
        offset {$offset} rows fetch next {$limit} rows only";

        $rs = $this->query($query);
        return $rs;
    }

    public function doorCount($org_id,$params=[])
    {
        $query = "SELECT count(*) total FROM [dbo].[view_door_2024] WHERE [org_id] = '{$org_id}'";

        if (!empty($params['emp_id'])) {
            $query .= " AND [emp_id] = '{$params['emp_id']}'";
        }
        if (!empty($params['emp_name'])) {
            $query .= " AND [emp_name] = '{$params['emp_name']}'";
        }
        if (!empty($params['door_id'])) {
            $query .= " AND [door_id] = '{$params['door_id']}'";
        }
        if (!empty($params['start_time'])) {
            $query .= " AND [clock_time] >= '{$params['start_time']}'";
        }
        if (!empty($params['end_time'])) {
            $query .= " AND [clock_time] <= '{$params['end_time']}'";
        }

        $rs = $this->query($query);
        return reset($rs)['total'];
    }
}