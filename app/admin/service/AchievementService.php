<?php

namespace app\admin\service;

use app\admin\model\UserAttribute;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use app\admin\model\config\Level as LevelModel;
use app\admin\model\User as UserModel;
use app\admin\model\report\Achievement as AchievementModel;
use app\admin\model\log\UserBalanceLog as UserBalanceLogModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Log;


class AchievementService
{
    private $insertData = [];

    private $balanceData = [];

    private $userField = [];

    private $levelAllData = [];

    private $adminId;
    private $date;
    private $createTime;
    private $updateTime;

    public function __construct(?int $adminId = null )
    {
        $this->createTime = $this->updateTime = time();
        $this->date = date('Y-m-d');
        $this->adminId = $adminId;
        $this->userField = ['id' , 'app_id' , 'username' , 'referee_id' , 'referee_second_id' , 'mobile' , 'create_time' , 'update_time'];
    }

    /**
     * 业绩分红
     * @param string|array $dataSources
     * @param int $type 处理场景:0=文件，1=数组
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws \think\Exception
     */
    public function handleAchievementFileData($dataSources , int $type = 0)
    {
        //获取所有的等级配置
        $levelAllData = (new LevelModel)->getAllLevelInfo();
        if (empty($levelAllData)) {
            throw new Exception("请先配置会员级别数据");
        }
        $this->levelAllData = $levelAllData;

        if($type === 0){
            $data = $this->readFileData($dataSources);
        }else{
            $data[] = $dataSources;
        }

        $insertData = [];
        $field = $this->userField;
        foreach ($data as $v) {
            $this->insertData = [];
            //需要判断用户是否存在
            $userAttributeTmp = UserAttribute::where([
                'platform_id' => $v['platform_id'] ,
                'app_id' => $v['app_id']
            ])->find();

            if(empty($userAttributeTmp)){
                continue;
            }

            $tmpUser = UserModel::getOneUser(['id' => $userAttributeTmp->user_id] , $v['platform_id'] , $field);
            if (empty($tmpUser) || empty($tmpUser->userAttributeData)) {
                continue;
            }


            if ($v['total_profit'] > 0) {
                if (!isset($levelAllData[$tmpUser->userAttributeData->level_id])) {
                    throw new Exception("等级配置出错");
                }

                $tmpUser->setTotalBalance($tmpUser->id,  $v['platform_id'] , $v['total_profit']);

                $firstProfit = $this->percentCalculate($v['total_profit'] , $levelAllData[$tmpUser->userAttributeData->level_id]['percent']);

                $tmpArray = $this->getParentMoney($tmpUser , $firstProfit , $v['total_profit'] , $v['platform_id'] , $tmpUser->id);

                $insertData[] = [
                    'user_id' => $tmpUser->id,
                    'app_id' => $v['app_id'],
                    'total_profit' => $firstProfit,
                    'balance' => $v['total_profit'],
                    'platform_id' => $v['platform_id'],
                    'date' => $this->date,
                    'admin_id' => $this->adminId,
                    'remark' => '导入业绩数据',
                    'create_time' => $this->createTime,
                    'update_time' => $this->updateTime,
                    'settle_time' => $this->createTime
                ];

                if($firstProfit > 0){
                    $this->balanceData[] = [
                        'user_id' => $tmpUser->id,
                        'origin_uid' => $tmpUser->id,
                        'platform_id' => $v['platform_id'],
                        'money' => $firstProfit,
                        'remark' =>'推荐结算返佣',
                        'scene' => 0,
                        'create_time' => $this->createTime,
                        'update_time' => $this->updateTime
                    ];
                }

                $insertData = array_merge($insertData , $this->insertData);

            }

        }

        if(!empty($insertData)){
            foreach ($insertData as $val){
                UserModel::updateUserMoney([
                    'platform_id' => $val['platform_id'],
                    'user_id' => $val['user_id'],
                ] , $val['total_profit']);
            }
            (new AchievementModel)->saveAll($insertData);
        }

        if(!empty($this->balanceData)){
            $userBalanceLogModel = new UserBalanceLogModel;
            $userBalanceLogModel->saveAll($this->balanceData);
        }

    }

    /**
     * 使用yield进行返回数据
     * @param string $filePath
     * @return \Generator
     * @throws Exception
     */
    private function readFileData(string $filePath): \Generator
    {
        $reader = IOFactory::createReader('Xlsx');
        //打开文件
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        for ($j = 2; $j <= $highestRow; $j++) {
            $appId = trim($sheet->getCellByColumnAndRow(1, $j)->getValue());
            $totalProfit = trim($sheet->getCellByColumnAndRow(2, $j)->getValue());
            $platformId = trim($sheet->getCellByColumnAndRow(3, $j)->getValue());
            $tmpData = [
                'app_id' => $appId,
                'total_profit' => $totalProfit,
                'platform_id' => (int) $platformId,
                'row' => $j,
            ];
            yield $tmpData;
        }
    }

    /**
     * 计算百分数
     * @param $preData
     * @param $laterData
     * @return string|null
     */
    private function percentCalculate($preData , $laterData): ?string
    {
       return bcdiv( bcmul($preData , $laterData , 2) , 100 , 2);
    }


    /**
     * 获取日志数据
     * @param UserModel $userObj 用户对象
     * @param string|null $amount
     * @param string|null $totalProfit
     * @param int $platformId
     * @param int $originUid
     * @param int $i
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    private function getParentMoney($userObj , ?string $amount , ?string $totalProfit , int $platformId , int $originUid, int $i = 1 )
    {
        if($userObj->referee_id > 0){
            //获取上级
            $parentUserTmp = UserModel::getOneUser( [ 'id' => $userObj->referee_id ] , $platformId , $this->userField);
            Log::info($parentUserTmp->toArray());
            if( empty($parentUserTmp) || empty($parentUserTmp->userAttributeData) ){
                return [];
            }
            $secondProfit = $this->percentCalculate( $totalProfit , $this->levelAllData[$parentUserTmp->userAttributeData->level_id]['percent'] ) - $amount;
            //组装数据
            $this->insertData[] = [
                'user_id' => $parentUserTmp->id,
                'app_id' => $parentUserTmp->userAttributeData->app_id,
                'total_profit' => $secondProfit,
                'balance' => $totalProfit,
                'platform_id' => $platformId,
                'type' => 10,
                'date' => $this->date,
                'admin_id' => $this->adminId,
                'remark' => '导入业绩数据('. $i .'级获得)',
                'create_time' => $this->createTime,
                'update_time' => $this->updateTime,
                'settle_time' => $this->createTime
            ];
            $this->balanceData[] = [
                'user_id' => $parentUserTmp->id,
                'origin_uid' => $originUid,
                'platform_id' => $platformId,
                'money' => $secondProfit,
                'scene' => 0,
                'remark' => $i. '级推荐结算返佣',
                'create_time' => $this->createTime,
                'update_time' => $this->updateTime,
            ];

            Log::info($this->insertData);
            Log::info($this->balanceData);

            return $this->getParentMoney($parentUserTmp , $secondProfit  + $amount , $totalProfit , $platformId , $originUid , $i + 1);
        }else{
            return [];
        }
    }
}