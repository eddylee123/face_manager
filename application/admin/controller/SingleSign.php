<?php


namespace app\admin\controller;


use app\admin\model\Admin;
use app\common\controller\Backend;
use fast\Random;
use think\Env;
use think\Request;
use think\Session;

class SingleSign extends Backend
{
    const cookie_key = 'username';
    protected $noNeedLogin = ['sync','login'];
    protected $appsMap = [];

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->appsMap = json_decode(Env::get('sign.apps_map', "[]"), true);
    }

    /**
     * 登录同步
     * @param Request $request
     * DateTime: 2023/6/8 15:35
     */
    public function sign()
    {
        $domain = $this->request->domain();
        $username = Session::get('admin')['username'];

        $param = [
            'code' => encryptDecrypt(self::cookie_key, $username),
        ];
        $build = http_build_query($param);
        $apps = [];
        foreach ($this->appsMap as $val) {
            if (strpos($val['url'], $domain) !== false) {
                //当前域名不同步
                continue;
            }
            $apps[] = [
                'name' => $val['name'],
                'url' => $val['url'] . 'login?' . $build
            ];
        }

        $this->assign('apps1', $apps);
        return $this->view->fetch();
    }

    /**
     * 远程同步登录
     * @param Request $request
     * DateTime: 2023/6/12 11:15
     */
    public function sync(Request $request)
    {
        $code = $request->get('code', '');
        $login = $request->get('login', 1);
        $redirect = $request->get('redirect', '');
        $redirect = urldecode($redirect);
        if (empty($code)) {
            $this->redirect($redirect);
            return;
        }

        if ($login) {
            $this->login(\request());
        } else {
            $this->logout(\request());
        }
        $this->redirect($redirect);
        return;
    }

    /**
     * 单点登录
     * @param Request $request
     * DateTime: 2023/6/8 11:13
     */
    public function login(Request $request)
    {
        $code = $request->get('code', '');
        if (empty($code)) {
            $this->redirect('index/login', [], 302);
            exit;
        }
        $header = $request->header();
        $appMapStr = join(',', array_column($this->appsMap, 'url'));
        if (empty($header['referer'])) {
            //非法域名跳转
            $this->redirect('index/login', [], 302);
            exit;
        }
        if (strpos($header['referer'], '?')) {
            $url = parse_url($header['referer']);
            $header['referer'] = $url['host'];
        }
        if (strrpos($appMapStr, $header['referer']) === false) {
            //非法域名跳转
            $this->redirect('index/login', [], 302);
            exit;
        }

        if (!Session::has("admin")) {
            //查询用户信息
            $username = encryptDecrypt(self::cookie_key, $code, true);
            $admin = Admin::where('username', $username)->where('status', 'normal')->find();
            if (empty($admin)) {
                $this->redirect('index/login', [], 302);
                exit;
            }

            $admin->loginfailure = 0;
            $admin->logintime = time();
            $admin->loginip = request()->ip();
            $admin->token = Random::uuid();
            $admin->save();
            //设置p3p请求头 单点登录
            header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
            Session::set("admin", $admin->toArray());
        }

        $this->redirect('index/index');
        exit;
    }

    /**
     * 单点登出
     * @param Request $request
     * DateTime: 2023/6/8 11:14
     */
    public function logout(Request $request)
    {
        $code = $request->get('code', '');
        if (empty($code)) {
            return;
        }
        //查询用户信息
        $username = encryptDecrypt(self::cookie_key, $code, true);
        $admin = Admin::where('username', $username)->where('status', 'normal')->find();
        if (empty($admin)) {
            return;
        }

        if ($admin) {
            $admin->token = '';
            $admin->save();
        }
        $this->logined = false; //重置登录状态
        //设置p3p请求头 跨域清空cookie信息 达到同步退出的效果
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        Session::delete("admin");
        return;
    }
}
