<?php

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'username' => 'require|regex:\w{3,32}|unique:user',
        'nickname' => 'require',
        'password' => 'regex:\S{6,32}',
        'email'    => 'require|email|unique:user',
        'mobile'   => 'require|unique:user',
//        'app_id'   => 'require|unique:user',
        'amount'   => 'require',
        'platform_id'   => 'require',
        'user_id'   => 'require',
        'level_id'   => 'require',
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['nickname' , 'mobile' , 'referee_id'],
        'edit' => ['platform_id' , 'nickname', 'referee_id' , 'level_id'],
        'recharge'  => ['amount' , 'platform_id' , 'user_id' ],

    ];

    public function __construct()
    {
        $this->field = [
            'app_id' => __('App_id'),
            'referee_id' => __('Referee_id'),
            'username' => __('Username'),
            'nickname' => __('Nickname'),
            'password' => __('Password'),
            'email'    => __('Email'),
            'mobile'   => __('Mobile'),
            'level_id'   => __('Level_id'),
        ];
        parent::__construct();
    }
}
