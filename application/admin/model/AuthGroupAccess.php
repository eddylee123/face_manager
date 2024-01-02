<?php

namespace app\admin\model;

use think\Model;

class AuthGroupAccess extends Model
{
    public function is_admin_rule(){
        $rule_arr = $this->field('group_id')->where("uid=".session('admin.id'))->select();
        $group = array_column($rule_arr ,  'group_id'); 
        return in_array(1 ,  $group);
    }
}
