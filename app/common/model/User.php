<?php

namespace app\common\model;

/**
 * 会员模型.
 */
class User extends BaseModel
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [
        'url',
    ];

    /**
     * 获取个人URL.
     *
     * @param string $value
     * @param array  $data
     *
     * @return string
     */
    public function getUrlAttr($value, $data)
    {
        return '/u/'.$data['id'];
    }

    /**
     * 获取头像.
     *
     * @param string $value
     * @param array  $data
     *
     * @return string
     */
    public function getAvatarAttr($value, $data)
    {
        if (! $value) {
            //如果不需要启用首字母头像，请使用
            //$value = '/assets/img/avatar.png';
            $value = letter_avatar($data['nickname']);
        }

        return $value;
    }

    /**
     * 获取会员的组别.
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::find($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     *
     * @param string $value
     * @param array  $data
     *
     * @return object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array) json_decode($value, true));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);

        return (object) $value;
    }

    /**
     * 设置验证字段.
     *
     * @param mixed $value
     *
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;

        return $value;
    }

    
}
