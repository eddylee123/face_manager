<?php

namespace app\admin\model;

use fast\Http;
use think\Db;
use think\Model;
use traits\model\SoftDelete;


class Employee extends Model
{

    use SoftDelete;

    protected $pk = 'id';
    // 表名
    protected $name = 'employee';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = 'delete_time';

    // 追加属性
    protected $append = [
        'sex_text',
        'emp_source_text',
        'marry_text',
        'create_time_text',
        'update_time_text',
        'delete_time_text'
    ];
    

    
    public function getSexList()
    {
        return ['男' => __('男'), '女' => __('女')];
    }

    public function getEmpSourceList()
    {
        return ['合同工' => __('合同工'), '劳务工' => __('劳务工')];
    }

    public function getMarryList()
    {
        return [
            '' => '',
            '未婚' => __('未婚'),
            '已婚' => __('已婚'),
            '离婚' => __('离婚'),
            '丧偶' => __('丧偶'),
        ];
    }

    public function getArmyList()
    {
        return ['0' => __('否'), '1' => __('是')];
    }

    public function getTransList()
    {
        return [
            '' => '',
            '步行' => '步行',
            '自行车' => '自行车',
            '电动车' => '电动车',
            '汽车' => '汽车',
            '火车' => '火车',
            '高铁' => '高铁',
            '飞机' => '飞机',
            '轮船' => '轮船'
            ];
    }

    public function getRelationList()
    {
        return [
            '' => '',
            '夫妻' => '夫妻',
            '父母' => '父母',
            '子女' => '子女',
            '其他' => '其他',
        ];
    }

    public function getStatusList()
    {
        return [
            '0' => '基础资料待确认',
            '1' => '待拍照',
            '2' => '待体检',
            '3' => '待报道',
            '4' => '正常',
            '21' => '不符合要求',
        ];
    }

    public function getEduList()
    {
        return [
            '初中' => '初中',
            '高中(中专)' => '高中(中专)',
            '大专' => '大专',
            '本科' => '本科',
            '硕士' => '硕士',
            '博士' => '博士',
        ];
    }

    public function getFolkList()
    {
        return [
            '汉族' => '汉族',
            '蒙古族' => '蒙古族',
            '回族' => '回族',
            '藏族' => '藏族',
            '维吾尔族' => '维吾尔族',
            '苗族' => '苗族',
            '彝族' => '彝族',
            '壮族' => '壮族',
            '布依族' => '布依族',
            '朝鲜族' => '朝鲜族',
            '满族' => '满族',
            '侗族' => '侗族',
            '瑶族' => '瑶族',
            '白族' => '白族',
            '土家族' => '土家族',
            '哈尼族' => '哈尼族',
            '哈萨克族' => '哈萨克族',
            '傣族' => '傣族',
            '黎族' => '黎族',
            '傈僳族' => '傈僳族',
            '佤族' => '佤族',
            '畲族' => '畲族',
            '高山族' => '高山族',
            '拉祜族' => '拉祜族',
            '水族' => '水族',
            '东乡族' => '东乡族',
            '纳西族' => '纳西族',
            '景颇族' => '景颇族',
            '柯尔克孜族' => '柯尔克孜族',
            '土族' => '土族',
            '达翰尔族' => '达翰尔族',
            '么佬族' => '么佬族',
            '羌族'  => '羌族',
            '撒拉族' => '撒拉族',
            '毛南族' => '毛南族',
            '仡佬族' => '仡佬族',
            '锡伯族' => '锡伯族',
            '阿昌族' => '阿昌族',
            '普米族' => '普米族',
            '塔吉克族' => '塔吉克族',
            '怒族' => '怒族',
            '乌孜别克族' => '乌孜别克族',
            '俄罗斯族' => '俄罗斯族',
            '鄂温克族' => '鄂温克族',
            '德昂族' => '德昂族',
            '保安族' => '保安族',
            '裕固族' => '裕固族',
            '京族' => '京族',
            '塔塔尔族' => '塔塔尔族',
            '独龙族' => '独龙族',
            '鄂伦春族' => '鄂伦春族',
            '赫哲族' => '赫哲族',
            '门巴族' => '门巴族',
            '珞巴族' => '珞巴族',
            '基诺族' => '基诺族',
            ];
    }

    public function empErr()
    {
        return [
            '1' => '身份证号码错误',
            '3' => '身份证生效日期错误',
            '4' => '身份证到期',
            '666' => '重复入职',
            '888' => '黑名单用户,请仔细检查',
            '999' => '黑名单,禁止入职',
        ];
    }

    public function getNewEmpId($org_id, $uid, $source)
    {
        $str = "http://220.168.154.86:50522/GETNewEmpno?ORGID=%s&MEMPID=%s&EMPType=%s";
        $type = $source == '合同工' ? 'GH01' : 'GH03';
        $url = sprintf($str, $org_id, $uid, $type);
        $rs = Http::get($url);
        $emp_id = '';
        if (!empty($rs)) {
            $arr = json_decode($rs, true);
            if ($arr['st'] == 0) {
                $emp_id = $arr['NewEmpId'];
            }
        }
        $exist = $this->where(['emp_id'=>$emp_id])->value('id');
        if ($exist) {
            return $this->getNewEmpId($org_id, $uid, $source);
        }
        return $emp_id;
    }

    public function getEmpInfo($emp_id)
    {
        $url = "http://220.168.154.86:50522/EMPInfo?MEMPID={$emp_id}";
        $rs = Http::get($url);
        if (!empty($rs)) {
            return json_decode($rs, true);
        }
        return  ['msg'=>'查询失败'];
    }

    public function getDept($org_id)
    {
        $url = "http://220.168.154.86:50522/GETViewDepartment?OrgID={$org_id}";
        $rs = Http::get($url);
        if (!empty($rs)) {
            return json_decode($rs, true);
        }
        return  ['msg'=>'查询失败'];
    }
    /**
     * 获取劳务工号
     * @param $org_id
     * @return mixed
     * DateTime: 2023/12/21 10:24
     */
    public function getEmpLwg($org_id)
    {
        $str = "set nocount on;
        set nocount on;exec KwwSys.dbo.[GetNewEmpID] 'L23%s','00000','%s','ADMIN','GH03'
        set nocount off;";
        $sql = sprintf($str, $org_id, $org_id);
        $rs = Db::query($sql);
        $emp_id = '';
        if (!empty($rs)) {
            $emp_id = reset($rs)['NewEmpId'];
            $this->setEmpId($emp_id);
        }
        return $emp_id;
    }

    /**
     * 获取合同工号
     * @param $org_id
     * @return mixed
     * DateTime: 2023/12/21 10:24
     */
    public function getEmpHtg($org_id)
    {
        $str = "set nocount on;
        exec KwwSys.dbo.[GetNewEmpID] 'Z23%s','00000','%s','ADMIN','GH01'
        set nocount off;";
        $sql = sprintf($str, $org_id, $org_id);
        $rs = Db::query($sql);
        $emp_id = '';
        if (!empty($rs)) {
            $emp_id = reset($rs)['NewEmpId'];
            $this->setEmpId($emp_id);
        }
        return $emp_id;
    }

    public function checkEmpStatus($id_card, $start, $end)
    {
        $str = "http://220.168.154.86:50522/GETEmpStatus?IDCARDS=%s&sDate=%s&eDate=%s";
        $url = sprintf($str, $id_card, $start, $end);
        $rs = Http::get($url);
        if (!empty($rs)) {
            $arr = json_decode($rs, true);
            if ($arr['st'] != 0) {
//                $empErr = $this->empErr();
//                $msg = !empty($empErr[$arr['st']]) ? $empErr[$arr['st']] : '员工状态异常，请核实后操作';
                $msg = !empty($arr['msg']) ? $arr['msg'] : '员工状态异常，请核实后操作';
                if (in_array($arr['st'], ['999'])) {
                    //不能注册错误返回
                    return output(0, $msg);
                } else {
                    return output(100, $msg);
                }
            }
            return output(200);
        }
        return output(-1, '员工信息核对失败');
    }

    public function getSexTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sex']) ? $data['sex'] : '');
        $list = $this->getSexList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function setEmpId($emp_id)
    {
        $str = "update HR_TempUserCode set IsUse=1  where SE003='GGH' and ASKCODE='%s' and IsUse=0";
        $sql = sprintf($str, $emp_id);
        Db::execute($sql);
        return true;
    }


    public function getEmpSourceTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['emp_source']) ? $data['emp_source'] : '');
        $list = $this->getEmpSourceList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getMarryTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['marry']) ? $data['marry'] : '');
        $list = $this->getMarryList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getDeleteTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['delete_time']) ? $data['delete_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setDeleteTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }



}
