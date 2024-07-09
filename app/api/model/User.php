<?php

namespace app\api\model;

use app\api\model\UserAttribute as UserAttributeModel;
use app\common\model\BaseModel;
use fast\Random;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\Exception;
use think\facade\Log;
use think\Model;
use think\model\Relation;
use think\model\relation\BelongsTo;
use think\model\relation\HasOne;

class User extends BaseModel
{
    protected $pk = 'id';

    protected $name = 'user';

    protected $setAllowField = ['id', 'username', 'nickname', 'mobile', 'avatar', 'score' , 'referee_id' , 'first_num' , 'second_num' , 'app_id' , 'create_time' , 'update_time'];

    // 追加属性
    protected $append = [
        'create_time_text',
    ];

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    /**
     * 获取团队业绩
     * @param int $userId
     * @param int $platformId
     * @return float|int
     */
    public function getTeamBalanceAccount(int $userId , int $platformId , $len = 100)
    {
        //逐级去计算
        $account = 0;
        if(!is_array($userId)){
            $userId = [$userId];
        }
        for($i = 0 ; $i < $len ;$i++){
            $userIds = $this->where('referee_id' , 'IN' , $userId)->column('id');
            if(empty($userIds)){
                return $account;
            }
            $account += UserAttributeModel::where([
                ['platform_id' , '=' , $platformId],
                ['user_id' , 'IN' , $userIds ],
            ])->sum('balance');
//            $account += $this->where('id' , 'IN', $userIds)->sum('balance');
            $userId = $userIds;
            unset($userIds);
        }
        // $account = $this->where('referee_id',$userId)->sum('balance') + $this->where('referee_second_id',$userId)->sum('balance');
         return  $account;
    }


    /**
     * 获取个人团队列表
     * @param int $userId
     * @param int $platformId
     * @return User[]|array|Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getTeamList(int $userId , int $platformId , int $type , $search = null)
    {
        $option = $type == 1 ? ['referee_id' => $userId] : ['referee_second_id' => $userId];
        $list = $this
            ->with([
                'userAttribute' => function(Relation $relation) use($platformId){
                    $relation->where('platform_id' , $platformId);
                }
            ])
            ->when(!empty($search) , function(Query $query) use($search , $platformId){
                $query->where('id' , 'IN' , function(Query $query) use($search , $platformId){
                    $query->name('user_attribute')->field(['user_id'])->where([
                        'platform_id' => $platformId,
                        'app_id' => $search
                    ]);
                });
            })
            ->field($this->setAllowField)
            ->where($option)
            ->select();
        return $list;
    }


    /**
     * 获取用户的属性值
     * @param int $userId
     * @param int $platformId
     * @return UserAttribute|array|mixed|\think\Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getUserAttribute(int $userId , int $platformId)
    {
        return UserAttributeModel::where([
            ['platform_id' , '=' , $platformId],
            ['user_id' , '=' , $userId ],
        ])->with([
            'level' => function(Relation $relation){

            }
        ])->find();
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
    public function addUserByApi(array $data): bool
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
     * 获取平台属性
     * @return HasOne
     */
    public function userAttribute()
    {
        return $this->hasOne(UserAttributeModel::class , 'user_id' , 'id');
    }




}