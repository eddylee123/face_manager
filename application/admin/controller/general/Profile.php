<?php

namespace app\admin\controller\general;

use app\admin\model\Admin;
use app\common\controller\Backend;
use fast\Random;
use think\Session;
use think\Validate;

/**
 * 个人配置
 *
 * @icon fa fa-user
 */
class Profile extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            $this->model = model('AdminLog');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->where('admin_id', $this->auth->id)
                ->order($sort, $order)
                ->paginate($limit);

            //$date1 = date('Y-m-d' , strtotime('+30 day')); 
            //$sql = "select title,sign_time from fa_member where sign_time is not null"; 
            //$arr =$this->model->query($sql);

            $result = array("total" => $list->total(), 'sign_str'=>'' , "rows" => $list->items());

            //if(!empty($arr)){
                //$sign_str = ''; 
                //foreach($arr as $r){
                    //if($date1>= date('Y-') . date('m-d' , strtotime($r['sign_time']))){
                        ////$sign_str .= $r["title"] . "-需要续年费<br>"; 
                    //}
                //}
                //$result['sign_str'] = $sign_str; 
            //}

            return json($result);
        }
        $this->view->assign('data' ,check_create('create'));
        return $this->view->fetch();
    }

    /**
     * 更新个人信息
     */
    public function update()
    {
        if ($this->request->isPost()) {
            //$this->token();
            $params = $this->request->post("row/a");
            $params = array_filter(array_intersect_key(
                $params,
                array_flip(array('email', 'nickname', 'password', 'avatar' , 'secret'))
            ));
            unset($v);
            if (!Validate::is($params['email'], "email")) {
                $this->error(__("Please input correct email"));
            }
            if (isset($params['password'])) {
                if (!Validate::is($params['password'], "/^[\S]{6,16}$/")) {
                    $this->error(__("Please input correct password"));
                }
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
            }
            $exist = Admin::where('email', $params['email'])->where('id', '<>', $this->auth->id)->find();
            if ($exist) {
                $this->error(__("Email already exists"));
            }
            if ($params) {
                $admin = Admin::get($this->auth->id);
                $admin->save($params);
                if(empty($params['secret'])){
                    unset($params['secret']);
                }
                else{
                    $admin['secret'] = $params['secret'];
                }

                //因为个人资料面板读取的Session显示，修改自己资料后同时更新Session
                Session::set("admin", $admin->toArray());
                $this->success();
            }
            $this->error();
        }
        return;
    }
}
