<?php

namespace app\admin\model\log;

use app\admin\model\config\Platform as PlatformModel;
use app\admin\model\User as UserModel;
use app\common\model\BaseModel;
use think\model\relation\BelongsTo;


class UserBalanceLog extends BaseModel
{

    // 表名
    protected $name = 'user_balance_log';

    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'scene_text',
        'create_time_text',
        'update_time_text'
    ];
    

    
    public function getSceneList()
    {
        return ['0' => __('Scene 0'), '1' => __('Scene 1')];
    }


    public function getSceneTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['scene'] ?? '');
        $list = $this->getSceneList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['update_time'] ?? '');
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


    /**
     * @return BelongsTo
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(PlatformModel::class , 'platform_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class , 'user_id', 'id');
    }

}
