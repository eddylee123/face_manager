<?php

namespace app\admin\controller;

use app\admin\library\Kww;
use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\Config;
use think\Cookie;
use think\Env;
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
            $url = '/index/logout';
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
//        if (Env::get('app.master')) {
            Kww::login();
            return;
//        } else {
//            $this->redirect('index/login_admin');
//            return;
//        }
    }

    public function login2()
    {
        $url = $this->request->get('url', 'index/index');
        $tokenId = $this->request->request('tokenId', '');
        empty($tokenId) && $tokenId = $this->request->request('tid', '');
        if(empty($tokenId)) {
            $this->error('登录失败，请咨询管理员');
        }

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
                    $url = '/notice?ref=addtabs';
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
        $isAdmin = $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);
        if ($isAdmin) {
            $this->success(__('Logout successful'), 'index/login_admin');
        } else {
            $url = Kww::logout('index/login');
            return;
        }
    }

}
