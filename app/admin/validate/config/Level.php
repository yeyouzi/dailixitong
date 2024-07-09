<?php

namespace app\admin\validate\config;

use think\Validate;

class Level extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'value' => 'require|gt:0',
        'platform_id' => 'require',
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
        'add'  => ['value' , 'platform_id'],
        'edit' => ['value'],
    ];


    public function __construct()
    {
        $this->field = [
            'value' => __('Value'),
            'platform_id' => __('Platform.name'),
        ];
        parent::__construct();
    }
    
}
