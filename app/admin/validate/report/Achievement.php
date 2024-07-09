<?php

namespace app\admin\validate\report;

use think\Validate;

class Achievement extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'app_id'   => 'require',
        'platform_id'   => 'require',
        'total_profit'   => 'require|>:0',
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
        'add'  => [ 'app_id' , 'total_profit' , 'platform_id'],
        'edit' => [],
    ];

    public function __construct()
    {
        $this->field = [
            'app_id' => __('App_id'),
            'total_profit' => __('Balance'),
            'platform_id' => __('Platform.name'),
        ];
        parent::__construct();
    }
    
}
