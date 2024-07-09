<?php

namespace app\admin\model;

use app\common\library\Token;
use app\common\model\MoneyLog;
use app\common\model\BaseModel;
use app\common\model\ScoreLog;
use fast\Random;
use think\facade\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\facade\Log;
use think\Model;
use app\admin\model\UserAttribute as UserAttributeModel;
use think\model\relation\BelongsTo;
use think\model\relation\HasOne;

class User extends BaseModel
{

    protected $pk = 'id';
    // 表名
    protected $name = 'user';

    // 追加属性
    protected $append = [

    ];

    public function getOriginData()
    {
        return $this->origin;
    }

    public static function onBeforeUpdate($row)
    {
        $changed = $row->getChangedData();
        //如果有修改密码
        if (isset($changed['password'])) {
            if ($changed['password']) {
                $salt = \fast\Random::alnum();
                $row->password = \app\common\library\Auth::instance()->getEncryptPassword($changed['password'], $salt);
                $row->salt = $salt;
                Token::clear($row->id);
            } else {
                unset($row->password);
            }
        }

        $changedata = $row->getChangedData();
        if (isset($changedata['money'])) {
            $origin = $row->getOrigin();
            MoneyLog::create([
                'user_id' => $row['id'], 'money' => $changedata['money'] - $origin['money'],
                'before'  => $origin['money'], 'after' => $changedata['money'], 'memo' => '管理员变更金额',
            ]);
        }
        if (isset($changedata['score'])) {
            $origin = $row->getOrigin();
            ScoreLog::create(['user_id' => $row['id'], 'score' => $changedata['score'] - $origin['score'], 'before' => $origin['score'], 'after' => $changedata['score'], 'memo' => '管理员变更积分']);
        }
    }

    public function getGenderList()
    {
        return ['1' => __('Male'), '0' => __('Female')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['prevtime'];

        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['logintime'];

        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['jointime'];

        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setPrevtimeAttr($value)
    {
        return $value && ! is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setLogintimeAttr($value)
    {
        return $value && ! is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setJointimeAttr($value)
    {
        return $value && ! is_numeric($value) ? strtotime($value) : $value;
    }

    public function group()
    {
        return $this->belongsTo('UserGroup', 'group_id', 'id')->joinType('LEFT');
    }


    /**
     * 后台添加用户
     * @param array $data
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function addUserByAdmin(array $data): bool
    {
        $secondUserId = 0;
        $allowField = ['app_id' , 'username' , 'nickname' , 'password' , 'salt' , 'referee_id' , 'referee_second_id' , 'mobile'];
        $data['username'] = strtoupper(Random::alpha(1)) . Random::numeric(14);
        $data['app_id'] = !empty($data['app_id']) ? $data['app_id'] : Random::numeric(9);
        $data['password'] = md5( !empty($data['password']) ? $data['password'] : '123' );

        if(isset($data['referee_id']) && $data['referee_id'] > 0){
            $refereeUser = $this->where('id' , $data['referee_id'])->find();
            if(empty($refereeUser)){
                throw new Exception('找不到推荐用户');
            }
            $secondUserId = $refereeUser->referee_id;
            $data['referee_second_id'] = $secondUserId;
        }
        $result = $this->allowField($allowField)->save($data);
        if( isset($data['referee_id']) && $data['referee_id'] > 0){
            $this->updateUserTeamNumber( $data['referee_id'] );//更新上级数据
            if($secondUserId > 0){
                $this->updateUserTeamNumber( $secondUserId );//更新上上级数据
            }
        }
        //为用户初始化用户属性
        $userAttribute = new UserAttributeModel;
        $userAttribute->initUserAttribute($this->id);
        return true;
    }

    /**
     * 后台更新用户信息
     * @param User $userInfo
     * @param array $data
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function updateUserByAdmin($userInfo , array $data): void
    {
        $firstUserId = null;
        $secondUserId = null;
        $lastRefereeId = $userInfo->referee_id;//旧的上级ID
        $newRefereeId = $data['referee_id'];//新的上级ID
        $newParentUser = null;
        //首先判断referee_id是否有改变
        if($lastRefereeId != $data['referee_id']){
            if($data['referee_id'] != 0){
                //则先获取到新的上级用户
                $newParentUser = $this->where('id' , $data['referee_id'])->find();
                if(empty($newParentUser)){
                    throw new Exception("找不到新推荐人ID");
                }
                $data['referee_second_id'] = $newParentUser->referee_id;
            }
        }

        //更新他的下级的referee_second_id
        $this->where('referee_id' , $userInfo->user_id)->update([
            'referee_second_id' => $newRefereeId
        ]);

        $userInfo->allowField(['nickname' , 'mobile' , 'status' , 'remark' , 'referee_id' , 'referee_second_id' ])->save($data);
        //更新等级
        $updateLevelData = [
            'platform_id' => $data['platform_id'],
            'user_id' => $userInfo->id,
        ];
//        if(isset($data['app_id'])){
//            $updateLevelData['app_id'] = $data['app_id'];
//        }
        (new UserAttributeModel())->updateLevelId($updateLevelData , $data['level_id'] , $data['app_id'] ?? null);

        if($lastRefereeId != $data['referee_id']){
            // 旧的上级用户
            if($lastRefereeId > 0 ){
                $lastRefereeUser = $this->where('id' , $lastRefereeId)->find();
                if(!empty($lastRefereeUser)){
                    $this->updateUserTeamNumber($lastRefereeId);//上一级
                    if( $lastRefereeUser->referee_id > 0){
                        $this->updateUserTeamNumber($lastRefereeUser->referee_id);//上二级
                    }
                }
            }



            //新的上级用户
            if(!empty($newRefereeId) && $newRefereeId > 0){
                $this->updateUserTeamNumber($newParentUser->id);//上一级
                if($newParentUser->referee_id > 0){
                    $this->updateUserTeamNumber($newParentUser->referee_id);//上二级
                }
            }
        }
    }

    /**
     * 更新团队信息
     * @param int $userId
     * @return bool
     */
    protected function updateUserTeamNumber(int $userId): bool
    {
        if(empty($userId)){
            return false;
        }
        $firstCount = 0;
        $secondCount = 0;
        $userModel = new User();
        //获取一级的id
        $firstUserId = $userModel->where(['referee_id' => $userId ])->column('id');
        Log::info($firstUserId);
        if(!empty($firstUserId)){
            $firstCount = count($firstUserId);
            //获取二级的id
            $secondUserId = $userModel->where('referee_id' , 'IN' , $firstUserId)->column('id');
            if(!empty($secondUserId)){
                $secondCount = count($secondUserId);
            }
        }

        //更新用户的 first_num 和 second_num
        $userModel->where('id' , $userId )->update([
            'first_num' => $firstCount,
            'second_num' => $secondCount,
        ]);

        return true;
    }


    /**
     * 获取单个用户
     * @param array $option 条件
     * @param int $platformId 平台ID
     * @param string $field 字段
     * @return User|array|mixed|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getOneUser(array $option , $platformId , $field = '*')
    {
        $userData = self::where($option)->field($field)->find();
        if(!empty($userData)){
            $userData->userAttributeData = UserAttributeModel::where([
                'platform_id' => $platformId,
                'user_id' => $userData->id,
            ])->find();
        }
        return $userData;
    }


    /**
     * 收益(业绩)累加
     * @param int $userId
     * @param int $platformId
     * @param $balance
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public static function setTotalBalance(int $userId , int $platformId , $balance )
    {
        $option = [
            'platform_id' => $platformId,
            'user_id' => $userId,
        ];
        $tmpUserAttribute = (new UserAttributeModel)->where($option)->find();
        if(empty($tmpUserAttribute)){
            throw new Exception("找不到用户当前的平台属性");
        }
        Db::name('user_attribute')->where($option)->inc('balance' , $balance)->update();
    }

    /**
     * 增加用户的佣金
     * @param array $option 条件
     * @param $money
     */
    public static function updateUserMoney(array $option , $money)
    {
        Db::name('user_attribute')->where($option)->inc('money', $money)->update();
    }


    /**
     * 获取团队总业绩
     * @param $userId
     * @return float|int|string
     */
    public function getUserTeamBalance($userId , int $platformId)
    {
        if(empty($platformId)){
            return "异常数据";
        }
        $startTime = time();
        //逐级去计算
        $account = 0;
        if(!is_array($userId)){
            $userId = [$userId];
        }
        for($i = 0 ; $i < 100 ;$i++){
            $userIds = $this->where('referee_id' , 'IN' , $userId)->column('id');
            if(empty($userIds)){
                return $account;
            }
            $account += UserAttributeModel::where([
                ['platform_id' , '=' , $platformId],
                ['user_id' , 'IN' , $userIds ],
            ])->sum('balance');
            $userId = $userIds;
            unset($userIds);
            if(time() - $startTime > 15){
                return "异常数据";
            }
        }
    }



    /**
     * 获取一级推荐用户
     * @return BelongsTo
     */
    public function firstUser()
    {
        return $this->belongsTo(self::class , 'referee_id' , 'id');
    }

    /**
     * 获取二级推荐用户
     * @return BelongsTo
     */
    public function secondUser()
    {
        return $this->belongsTo(self::class , 'referee_second_id' , 'id');
    }


    /**
     * 获取平台属性
     * @return HasOne
     */
    public function userAttribute()
    {
        return $this->hasOne(UserAttributeModel::class , 'user_id' , 'id');
    }


}
