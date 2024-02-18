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

    public function getList($start_date,$end_date,$org_id,$params=[],$offset=0,$limit=999)
    {
        $query = "SELECT * FROM [dbo].[tmp_caterlistB] WHERE [org_ID] = '{$org_id}'";
        if (!empty($start_date)) {
            $query .= " AND [日期] >= '{$start_date}'";
        }
        if (!empty($end_date)) {
            $query .= " AND [日期] <= '{$end_date}'";
        }
        if (!empty($params['星期'])) {
            $query .= " AND [星期] = '{$params['星期']}'";
        }
        $query .= " ORDER BY [日期] DESC  
        offset {$offset} rows fetch next {$limit} rows only";

        $rs = $this->query($query);
        return $rs;
    }

    public function countList($start_date,$end_date,$org_id,$params=[])
    {
        $query = "SELECT count(*) total FROM [dbo].[tmp_caterlistB] WHERE [org_ID] = '{$org_id}'";
        if (!empty($start_date)) {
            $query .= " AND [日期] >= '{$start_date}'";
        }
        if (!empty($end_date)) {
            $query .= " AND [日期] <= '{$end_date}'";
        }
        if (!empty($params['星期'])) {
            $query .= " AND [星期] = '{$params['星期']}'";
        }

        $rs = $this->query($query);
        return reset($rs)['total'];
    }

    public function getDetail($start_date,$end_date,$org_id,$params=[],$offset=0,$limit=999)
    {

        $query = "SELECT CONVERT(varchar(10) , CkDateTime,23) 打卡日期,CONVERT(varchar(10) , CkDateTime,8) 打卡时间
        ,A.EnrollNumber 工号,B.[Emp_Name] 姓名, 部门=B.HR_DeptID+'  '+b.deptName,A.XFType 餐别
        ,DeviceNo 机器号,right(A.CardId,10) as 卡号,'{$org_id}' 基地代码,CONVERT(varchar(10) , B.LeaveDate,23) 离职日期,
	    消费级别=case when b.ConsumLevel>=0 then '允许消费' when  b.ConsumLevel<0 then '禁止消费' end,
	    门禁级别=case when b.KqLevel<0 then '禁卡通行' when b.KqLevel>=0 then '允许通行' end,
	    部门代码=B.HR_DeptID,食堂=SC026,VerifyMode 消费模式
        FROM [dbo].CWAIA A with(nowait,nolock)
        left join [dbo].[View_EMP_ALL_2023] B with(nowait,nolock) on A.EnrollNumber=B.Emp_ID
        left join [dbo].CWASC C on a.DeviceNo=C.SC001 
        where CONVERT(varchar(10), CkDateTime,23)>='{$start_date}' and CONVERT(varchar(10),CkDateTime,23)<='{$end_date}'
        and B.Org_ID='{$org_id}'";

        if (!empty($params['工号'])) {
            $query .= " and B.Emp_ID='{$params['工号']}'";
        }
        if (!empty($params['姓名'])) {
            $query .= " and B.Emp_Name LIKE '{$params['姓名']}%'";
        }
        if (!empty($params['area'])) {
            $query .= " and SC026='{$params['area']}'";
        }
        $query .= " order by 打卡日期,[打卡时间]  
        offset {$offset} rows fetch next {$limit} rows only";

        $rs = $this->query($query);
        return $rs;
    }

    public function countDetail($start_date,$end_date,$org_id,$params=[])
    {

        $query = "SELECT count(*) total
        FROM [dbo].CWAIA A with(nowait,nolock)
        left join [dbo].[View_EMP_ALL_2023] B with(nowait,nolock) on A.EnrollNumber=B.Emp_ID
        left join [dbo].CWASC C on a.DeviceNo=C.SC001 
        where CONVERT(varchar(10), CkDateTime,23)>='{$start_date}' and CONVERT(varchar(10),CkDateTime,23)<='{$end_date}'
        and B.Org_ID='{$org_id}'";

        if (!empty($params['工号'])) {
            $query .= " and B.Emp_ID='{$params['工号']}'";
        }
        if (!empty($params['姓名'])) {
            $query .= " and B.Emp_Name LIKE '{$params['姓名']}%'";
        }
        if (!empty($params['area'])) {
            $query .= " and SC026='{$params['area']}'";
        }

        $rs = $this->query($query);
        return reset($rs)['total'];
    }
}