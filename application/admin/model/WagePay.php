<?php

namespace app\admin\model;

use think\Config;
use think\Model;


class WagePay extends Model
{

    

    

    // 表名
    protected $name = 'wage_pay';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public $statusMap = [
        1 => '未确认',
        2 => '已确认',
        0 => '未读',
    ];

    public $typeMap = [
        1 => 'H5确认',
        2 => '短信确认',
    ];

    public $sendMap = [
        0 => '未发送',
        1 => '已发送',
    ];

    public $fieldMap = [
        'wage_month' => '薪资月份',
        'emp_id' => '新工号',
        'emp_name' => '姓名',
        'process' => '工序环节',
        'weekend_day' => '周末天数',
        'official_day' => '法定出勤天数',
        'need_day' => '应出勤天数',
        'work_date' => '实际出勤天数',
        'work_time' => '实际工时（h)',
        'over_day' => '超产天数',
        'turn_day' => '轮休天数',
        'paid_day' => '带薪假天数',
        'leave_day' => '请假天数',
        'night_day' => '夜班天数',
        'mass_score' => '质量分',
        'pay_piece' => '计件工资',
        'pay_piece_sub' => '计时/计件工资小计',
        'work_piece' => '帮工计件',
        'pay_time' => '计时工资',
        'pay_practice' => '实习工资',
        'pay_over_1' => '加班费（超产1）',
        'pay_over_2' => '加班费（超产2）',
        'pay_weekend' => '周末加班费',
        'pay_official' => '法定加班费',
        'pay_fixed' => '定量工资',
        'quality' => '质量奖',
        'pay_clean' => '清理保养工资',
        'pay_return' => '质量返还奖',
        'pay_disc' => '纪律奖',
        'pay_all' => '全勤奖',
        'wage_adjust' => '工资调整',
        'pay_able' => '应发工资',
        'pay_actual' => '实发工资',
        'type'  => '确认方式',
    ];

    public function orgList()
    {
        return Config::get('site.org_list');
    }


}
