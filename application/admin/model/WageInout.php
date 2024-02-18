<?php

namespace app\admin\model;

use think\Model;


class WageInout extends Model
{

    

    

    // 表名
    protected $name = 'wage_inout';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public $fieldMap = [
        'incr_helper' => '帮工补贴',
        'incr_post' => '岗位补贴',
        'incr_night' => '夜班补贴',
        'incr_rent' => '租房补贴',
        'incr_traffic' => '交通补贴',
        'incr_turn' => '轮休补贴',
        'incr_exam' => '体检补贴',
        'incr_temperature' => '高/低温津贴',
        'incr_incentive' => '产能激励奖励',
        'incr_other' => '其他补贴',
        'decr_age' => '养老扣款',
        'decr_medical' => '医疗扣款',
        'decr_job' => '失业扣款',
        'decr_fund' => '住房公积金',
        'decr_age_2' => '补扣养老',
        'decr_medical_2' => '补扣医疗',
        'decr_job_2' => '补扣失业',
        'decr_fund_2' => '补扣住房公积金',
        'decr_love' => '爱心基金扣款',
        'decr_utility' => '水电费扣款',
        'decr_growth' => '成长费扣款',
        'decr_tax' => '代扣个税',
        'decr_urgent' => '急辞工扣款',
        'decr_clothe' => '工衣扣款',
        'decr_other' => '其他扣款',
    ];

}
