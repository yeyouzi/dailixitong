<?php

namespace app\api\controller;

use app\api\model\config\Level;
use app\api\model\report\Achievement;
use app\api\model\UserAttribute;
use fast\Random;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\facade\Db;
use think\facade\Validate;
use think\facade\Config;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\controller\Api;
use app\api\model\User as UserModel;
use app\api\model\config\Platform as PlatformModel;


/**
 * 会员接口.
 */
class User extends Api
{
    protected $noNeedLogin = ['login' ,  'register' , 'sendSms'];
    protected $noNeedRight = '*';
    /**
     * @var UserModel
     */
    protected $userModel;

    public function _initialize()
    {
        parent::_initialize();
        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
        $this->userModel = new UserModel();
    }



    /**
     * 会员登录.
     *
     * @param string $account 账号
     * @param string $password 密码
     */
    public function login()
    {
        $account = $this->request->request('phone');
        $password = $this->request->request('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $userInfo = $this->auth->getUserinfo();

            $lastPlatform = (new PlatformModel())->field(['id' , 'name'])->order('id' , 'asc')->find();
            //$data = ['userinfo' => $this->auth->getUserinfo()];
            $data = [
                'user_id' => $userInfo['id'],
                'token' => $userInfo['token'],
                'last_platform_id' => $lastPlatform['id'],
                'last_platform_name' => $lastPlatform['name'],
                'bg' => [
                    'apply' => cdnurl("/uploads/bg/20220429193153fb6041109.jpg", true),
                    'index' => cdnurl("/uploads/bg/20220429204116aa42d9406.png", true),
                    'withdraw_apply' => cdnurl("/assets/api/dealer-bg.png", true),
                ],
            ];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 获取用户详情
     */
    public function detail()
    {
        $platformId = $this->request->param('platformId' , 1);
        $firstBalanceAccount = $this->userModel->getTeamBalanceAccount($this->auth->id , $platformId , 1);
        $teamBalanceAccount = $this->userModel->getTeamBalanceAccount($this->auth->id , $platformId);
        $userInfo = $this->auth->getUserinfo();
        $userAttribute = $this->userModel->getUserAttribute($this->auth->id , $platformId);
        $userInfo['create_time_text'] = date('Y-m-d H:i:s' , $userInfo['create_time'] );
        $disabled = !($userAttribute->app_id == ($userAttribute->platform_id . '000' . $userAttribute->user_id));
        $todayMoney = (new Achievement())->getTodayMoney($this->auth->id , $platformId);
        $data = [
            'firstBalanceAccount' => $firstBalanceAccount,
            'account' => floor($teamBalanceAccount),
            'userInfo' => $userInfo,
            'userAttribute' => $userAttribute,
            'todayMoney' => $todayMoney,
            'disabled' => $disabled,
        ];
        $this->success('success', $data);
    }


    /**
     * 我的直推列表
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function teamList()
    {
        $platformId = $this->request->param('platformId' , 1);
        $type = $this->request->param('type' , 1);
        $search = $this->request->param('search' , null);
        $list = $this->userModel->getTeamList( $this->auth->id , $platformId , $type , $search);
        if(!empty($list)){
            foreach ($list as $v){
                $v->team_balance = $this->userModel->getTeamBalanceAccount($v->id, $platformId);
            }
        }
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 切换用户的平台的APP_ID
     */
    public function setAppId()
    {
        $platformId = $this->request->param('platformId');
        $appId = $this->request->param('appid');
        $userId = $this->auth->id;

        Db::startTrans();
        try {
            if(empty($platformId)){
                throw new Exception("请输入平台ID , 请重新登录");
            }
            if(empty($appId)){
                throw new Exception("请输入平台绑定ID");
            }
            if(!is_numeric($appId)){
                throw new Exception("平台绑定ID必须为数字");
            }
            //判断平台是否存在
            if(!(new PlatformModel)->isExistRecord(['id' => $platformId ])){
                throw new Exception("请输入正确的平台ID");
            }
            //判断平台中是有这个appId了
            $isExistOption = [
                ['platform_id' , '=', $platformId],
                ['app_id', '=' , $appId ],
                ['user_id','<>', $userId]
            ];
            $userAttribute = (new UserAttribute);
            if($userAttribute->isExistRecord($isExistOption)){
                throw new Exception("该平台绑定ID已存在，操作失败");
            }
            $option = [
                ['platform_id' , '=', $platformId],
                ['user_id', '=' , $userId ]
            ];
            $userAttributeInfo = $userAttribute->where($option)->find();
            if(empty($userAttributeInfo)){
                throw new Exception("找不到用户信息，请重新登录");
            }

            $userInfo = $this->userModel->where('id' , $userId)->find();
            if(empty($userInfo)){
                throw new Exception("找不到用户信息，请重新登录");
            }
//            $userInfo->save([
//                'app_id' => $appId
//            ]);

            $userAttributeInfo->save([
                'app_id' => $appId
            ]);

            Db::commit();
            $this->success("操作成功" , [
                'app_id' => $appId
            ]);
        }catch (Exception $e){
            Db::rollback();
            $this->success("操作失败");
        }
    }

    /**
     * 发送验证码
     */
    public function sendSms()
    {
        $smsCode = Random::numeric(6);
        $this->success("获取成功" , $smsCode);
    }


    /**
     * 注册会员.
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 邮箱
     * @param string $mobile 手机号
     * @param string $code 验证码
     */
    public function register()
    {
        //$username = $this->request->request('username' , '');
        $password = $this->request->request('password');
        $nickname = $this->request->request('nickname');
        $gender = $this->request->request('gender');
        $mobile = $this->request->request('phone');
        $refereeId = $this->request->request('referee_id' , 0);
        //$code = $this->request->request('code');
        if (!$password || empty($mobile) || empty($nickname)) {
            $this->error(__('Invalid parameters'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        Db::startTrans();
        try {
            //判断是否手机号是否已经注册
            if($this->userModel->isExistRecord(['mobile' => $mobile])){
                throw new Exception('此手机号已注册');
            }
            $data = [
                'app_id' => null,
                'nickname' => $nickname,
                'gender' => $gender,
                'password' => $password,
                'mobile' => $mobile,
                'referee_id' => $refereeId,
            ];
            $ret = $this->userModel->addUserByApi($data);
            Db::commit();
            $this->success(__('Sign up successful'));
        }catch (Exception $e){
            Db::rollback();
            $this->error($e->getMessage());
            $this->error(__('Sign up fail'));
        }

    }


    /**
     * 注销登录.
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

}
