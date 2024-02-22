<?php

namespace app\admin\controller;

use app\admin\library\Kww;
use app\admin\model\AdminLog;
use app\cache\BaseCache;
use app\common\controller\Backend;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Validate;
use think\Db;
use fast\Form;
use think\Session;
/**
 * 后台首页
 * @internal
 */
class Index extends Backend
{

    protected $noNeedLogin = ['login', 'login2', 'check_code', 'login_admin'];
    protected $noNeedRight = ['index', 'logout' , 'check_code'];
    protected $layout = '';
    protected $sql;

    public function _initialize()
    {
        parent::_initialize();
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
    }

    public function test()
    {
        $this->model =Db::connect("sql_oa")->name("hrmresource");
        $list = $this->model->query("select top 10 loginid,lastname, b.jobtitle,b.departmentid,subcompanyid1 from hrmresource b"); 
        $list = json_decode(json_encode($list ,  320) ,  true);

        echo '<pre>';print_r($list);exit;
    }


    /**
     * 后台首页
     */
    public function index()
    {
        if(empty($this->view->site['fixedpage'])){
            $url = '/admin123.php/index/logout';
            $msg = APP_PATH . 'extra/site.php文件缺失，请重命名'.APP_PATH . 'extra/site_copy.php为此文件';
            $this->error($msg, $url , '' , 8);
        }
        //左侧菜单
        $map = [
//            'dashboard' => 'hot',
            'addon'     => ['new', 'red', 'badge'],
            'auth/rule' => __('Menu'),
            'general'   => ['new', 'purple'],
        ];
        $notice = (new \app\admin\model\Notice())->where(['is_read'=>0])->count();
        if ($notice > 0) {
            $map['notice/index'] = [$notice, 'red', 'badge'];
        }
        list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar($map, $this->view->site['fixedpage']);
        $action = $this->request->request('action');
        if ($this->request->isPost()) {
            if ($action == 'refreshmenu') {
                $this->success('', null, ['menulist' => $menulist, 'navlist' => $navlist]);
            }
        }
        $this->view->assign('menulist', $menulist);
        $this->view->assign('navlist', $navlist);
        $this->view->assign('fixedmenu', $fixedmenu);
        $this->view->assign('referermenu', $referermenu);
        $this->view->assign('title', __('Home'));
        return $this->view->fetch();
    }

    /**
     * 管理员登录
     */
    public function login()
    {
        Kww::login();
        return;
    }

    public function login2()
    {
        $url = $this->request->get('url', 'index/index');
        $tokenId = $this->request->request('tokenId', '');
        if(empty($tokenId)) {
            $this->error('登录失败，请咨询管理员');
        }
        //token缓存
        Cookie::set(BaseCache::kww_token, $tokenId);
        $userInfo = Kww::getUser($tokenId);
//        var_dump($userInfo);exit;
        if (!empty($userInfo['data']['user']['userEname'])) {

            $username = $userInfo['data']['user']['userEname'];
            $password = '000000';

            AdminLog::setTitle(__('Login'));
            $result = $this->auth->login($username, $password, 86400);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                $s_admin = Session::get('admin');
                if(empty($s_admin['secret'])){
                    $url = '/notice?ref=addtabs';
                }

                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }
    }

    public function login_admin()
    {
        $url = $this->request->get('url', 'index/index');
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $code = $this->request->post('secret');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,30',
                '__token__' => 'require|token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];
            if (Config::get('fastadmin.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }
            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login'));
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0 , $code);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                $s_admin = Session::get('admin');
                if(empty($s_admin['secret'])){
                    $url = 'admin123.php/notice?ref=addtabs';
                }

                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            $this->redirect($url);
        }
        $background = Config::get('fastadmin.login_background');
        $background = $background ? (stripos($background, 'http') === 0 ? $background : config('site.cdnurl') . $background) : '';
        $this->view->assign('background', $background);
        $this->view->assign('title', __('Login'));
        Hook::listen("admin_login_init", $this->request);
        return $this->view->fetch('login');
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);
        Kww::logout('index/login');
        return;
//        $this->success(__('Logout successful'), 'index/login');
    }

    public function clear_url(){
        $post = $this->request->post('code');
        if($post){
            $this->model =Db::connect("analysis_db")->name("kww_orders");
            if(stripos($post , 'http')===false){
                $code_arr['0']=  trim($post);
            }
            else{
                $code_arr = explode('c=' , $post);
                if(!empty($code_arr['1'])){
                    $code_arr = explode('&' , $code_arr['1']);
                }
            }
            $sql = " select order_id from kww_orders where `code`='{$code_arr['0']}' limit 1";                
            $rows = $this->model->query($sql);
            if(empty($rows)){
                //ZREM kww_datacode_award 74120650252262192
                //HDEL kww_code_info 74120650252262192
                logs_write($code_arr['0'] , __LINE__);
                $redis = alone_redis();
                $status1 = $redis->zrem('kww_datacode_award' , $code_arr['0']);
                $status2 = $redis->hdel('kww_code_info' , $code_arr['0']);
                if($status1 && $status2){
                    $this->success('清除缓存成功');
                }
                else{
                    $this->success('操作成功');
                }

            }
            else{
                $this->success('订单已经存在，不能清除');
            }
        }
        else{
            $html = "<form method='post' action='/admin123.php/index/clear_url'><div class='cen'>";
            $html .= "<style type='text/css'>
                html,body{line-height:28px;font-size:14px;}
                .btn{background:blue;border:none;color:#fff;line-height:30px;height:30px;}
                .cen{margin:180px auto;text-align:center;}
                .text{width:400px;line-height:28px;border:1px solid #ddd;text-indent:3px;}
                </style>";
            $html .= Form::text('id','',['name'=>'code','placeholder'=>'防伪码或者扫码地址url（已生成订单会有提示）', 'class'=>'text']);
            $html .= Form::button('清除缓存',['type'=>'submit', 'class'=>"btn btn-default area_hide"]);
            echo $html; 
        }
    }

    public function order_sel(){
        $post = $this->request->post();
        if($post){
            $this->model =Db::connect("analysis_db")->name("kww_orders");
            $where = [];
            foreach($post as $k=>$r){
                $post[$k] = trim($r);
            }
            if(!empty($post['order_no'])){
                $where[] = " a.order_no ='{$post['order_no']}' ";
            }
            if(!empty($post['award_name'])){
                $where[] = " a.award_name ='{$post['award_name']}' ";
            }
            if(!empty($post['code'])){
                $where[] = " a.code ='{$post['code']}' ";
            }
            if(!empty($post['order_id'])){
                $where[] = " a.order_id ='{$post['order_id']}' ";
            }
            if(!empty($post['open_id'])){
                $where[] = " a.open_id ='{$post['open_id']}' ";
            }
            if(!empty($post['event_name'])){
                $where[] = " b.event_name like '%{$post['event_name']}%' ";
            }
            if(empty($where)){
                $this->success('没有查询到订单，请修改查询条件');
            }
            $where_str = implode(" and " , $where);
            $sql = "select a.order_id,a.order_no,a.is_lucky,a.code,b.event_name,a.award_name,a.is_send,a.open_id,a.add_time,d.user_name '姓名',d.tel '电话1',d.tel2 '电话2',concat('`',d.card_num) '身份证' from kww_orders a inner join kww_events b on a.event_id = b.event_id left join kww_order_ext d on a.order_no=d.order_no where $where_str";               
            //echo $sql; exit();
            $lists = $this->model->query($sql);
            if(!empty($lists)){
                $this->view->assign("array", $lists);
                return $this->view->fetch();
            }
            else{
                $this->success('没有查询到订单，请修改查询条件');
            }
        }
        else{
            $html = "<form method='post' action='/admin123.php/index/order_sel'><div class='cen'>";
            $html .= "<style type='text/css'>
                html,body{line-height:28px;font-size:14px;}
                .btn{background:blue;border:none;color:#fff;line-height:30px;height:30px;}
                .cen{margin:180px auto;}
                .text{width:195px;line-height:28px;border:1px solid #ddd;text-indent:3px;}
                .area_hide{cursor:pointer}
                </style>";
            $html .= '&nbsp;订单号:' . Form::text('id','',['name'=>'order_no','placeholder'=>'', 'class'=>'text']).'&nbsp;&nbsp;';
            $html .= '&nbsp;防伪码:' . Form::text('id','',['name'=>'code','placeholder'=>'', 'class'=>'text']).'&nbsp;&nbsp;';
            //$html .= '奖品名:' . Form::text('id','',['name'=>'award_name','placeholder'=>'', 'class'=>'text']).'&nbsp;&nbsp;';
            //$html .= '活动名:' . Form::text('id','',['name'=>'event_name','placeholder'=>'模糊查询,不用输入全部活动名', 'class'=>'text']).'&nbsp;&nbsp;';
            $html .="<br><br>";
            $html .= '订单id:' . Form::text('id','',['name'=>'order_id','placeholder'=>'', 'class'=>'text']).'&nbsp;&nbsp;';
            $html .= 'open_id:' . Form::text('id','',['name'=>'open_id','placeholder'=>'', 'class'=>'text']).'&nbsp;&nbsp;';
            $html .= Form::button('查询订单',['type'=>'submit', 'class'=>"btn btn-default area_hide"]);
            echo $html; 
        }
    }

    function sql_action(){
        $sql = $this->request->post('sql');
        $tbl = $this->request->post('tbl');
        $date = $this->request->post('date');
        if($sql){
            if($tbl && $date){
                $end_date = date('Y-m-d', strtotime($date)+86400);
                //$end_date = $date;
                $this->model =Db::connect("analysis_db")->name("kww_orders");
                $this->sqlsrv = Db::connect("sql_server")->name("qr_sales");
                $sql = str_replace('curdate()' , "'".$end_date."'" , $sql);
                //echo $sql; exit();
                $lists = $this->model->query($sql);
                if(!empty($lists)){
                    $str = "INSERT INTO $tbl ";
                    $sql1 = '';
                    foreach($lists['0'] as $k=>$r){
                        $sql1 .= $sql1 ? ",$k":"$k";
                    }
                    $insert = $str .= "(" . $sql1 .") values";
                    foreach($lists as $k=>$r){
                        $sql2 = '';
                        foreach($r as $key=>$val){
                            $sql2 .= $sql2 ? ",'$val'":"'$val'";
                        }
                        $str .='(' . $sql2 . ')' . ',';

                        if($k%999==998){
                            $str = trim($str, ',').';';
                            //$status = $this->sqlsrv->query($str);
                            //logs_write($status , __LINE__);
                            $str .= $insert;
                        }
                    }
                    if($k%999!=998){
                        $str = trim($str, ',').';';
                    }
                    replace_write($str , $tbl.'_'.$date);
                    $this->success('写入的sql文件路径在'.'/pro/log/'.date('Ym').'/'.$tbl.'_'.$date.'.sql');
                }
                else{
                    $this->success('分库数据查询到的数据为0');
                }
            }
            else{
                $this->error('分库数据查询到的数据为0'); 
            }
        }
        else{
            $html = "<form method='post' action='/admin123.php/index/sql_action'><div class='cen'>";
            $html .= "<style type='text/css'>
                html,body{line-height:28px;font-size:14px;}
                .btn{background:blue;border:none;color:#fff;line-height:30px;height:30px;}
                .cen{margin:180px auto;text-align:center;}
                .text{width:400px;line-height:28px;border:1px solid #ddd;text-indent:3px;}
                .area_hide{cursor:pointer}
                </style>";
            $html .= '表名:' . Form::text('id','',['name'=>'tbl','placeholder'=>'', 'class'=>'text']).'<br><br>';
            $html .= '日期:' . Form::text('id','',['name'=>'date','placeholder'=>'', 'class'=>'text']).'<br><br>';
            $html .= '<textarea name="sql" rows="15" style="width:80%"></textarea>'.'<br>'.'<br>';
            $html .= Form::button('sql操作',['type'=>'submit', 'class'=>"btn btn-default area_hide"]);
            echo $html; 
        }
    }

}
