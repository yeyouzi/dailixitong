<?php

namespace app\admin\model;

use app\admin\model\config\Level as LevelModel;
use app\common\model\BaseModel;
use app\admin\model\config\Platform as PlatformModel;
use think\db\exception\DbException;
use think\facade\Db;
use think\model\relation\HasOne;


class UserAttribute extends BaseModel
{

    protected $name = 'user_attribute';


    /**
     * 初始化一下用户属性
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function initUserAttribute(int $userId): bool
    {
        //获取平台列表
        $platformList = PlatformModel::getAllPlatformId();
        if (empty($platformList)) {
            return true;
        }

        //遍历数据
        $insertData = [];
        foreach ($platformList as $v) {
            if ($this->isExistRecord(['platform_id' => $v, 'user_id' => $userId])) {
                continue;
            }

            $insertData[] = [
                'platform_id' => $v,
                'user_id' => $userId,
                'level_id' => LevelModel::getMinLevelId(['platform_id' => $v]),
                'app_id' => $v . '000' . $userId,
            ];
        }
        if(!empty($insertData)){
            $this->saveAll($insertData);
        }
        return true;
    }


    /**
     * 更新用户等级
     * @param array $option
     * @param int $levelId
     * @param int|null $appId
     * @return false
     */
    public function updateLevelId(array $option , int $levelId , ?int $appId)
    {
        if(empty($levelId)){
            return false;
        }
        $updateData = [
            'level_id' => $levelId
        ];
        if(!empty($appId)){
            $updateData['app_id'] = $appId;
        }
        $this->where($option)->save($updateData);
    }

    /**
     * 冻结分销商资金
     * @param array $option
     * @param int|float $money
     * @throws DbException
     */
    public static function freezeMoney(array $option , $money): void
    {
        Db::name('user_attribute')->where($option)->inc('freeze_money' , $money)->dec('money' , $money)->update();
    }

    /**
     * 提现驳回：解冻分销商资金
     * @param array $option
     * @param int|float $money
     * @throws DbException
     */
    public static function backFreezeMoney(array $option , $money): void
    {
        Db::name('user_attribute')->where($option)->dec('freeze_money' , $money)->inc('money' , $money)->update();
    }


    /**
     * 提现打款成功：累积提现佣金
     * @param array $option
     * @param $money
     * @throws DbException
     */
    public static function totalMoney(array $option, $money): void
    {
        Db::name('user_attribute')->where($option)->dec('freeze_money' , $money)->inc('total_money' , $money)->update();
    }

    /**
     * 关联等级
     * @return HasOne
     */
    public function level(): HasOne
    {
        return $this->hasOne(LevelModel::class , 'id' , 'level_id');
    }


}