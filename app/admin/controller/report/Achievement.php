<?php

namespace app\admin\controller\report;

use app\admin\service\AchievementService;
use app\common\controller\Backend;

use app\admin\model\report\Achievement as AchievementModel;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Db;
use think\model\Relation;
use app\admin\validate\report\Achievement as AchievementValidate;

/**
 * 业绩导入记录管理
 *
 * @icon fa fa-circle-o
 */
class Achievement extends Backend
{
    
    /**
     * Achievement模型对象
     * @var AchievementModel
     */
    protected $model = null;


    protected $banMethod = ['del' , 'import' , 'edit' , 'multi'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new AchievementModel;

    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with([
                        'platform' => function(Relation $relation){
                            //$relation
                        },
                        'user' => function(Relation $relation){
                            $relation->field(['id' , 'app_id' , 'username' , 'nickname' , 'mobile']);
                        }
                    ])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            $result = ["total" => $total, "rows" => $list];
            return json($result);
        }
        $platformId = $this->getFirstPlatformId();
        $this->assignconfig('defaultPlatformId' , $platformId);
        $this->assign('defaultPlatformId' , $platformId);
        return $this->view->fetch();
    }


    /**
     * 手动添加业绩
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                Db::startTrans();
                try {
                    //进行验证
                    $validate = new AchievementValidate();
                    $checkRes = $validate->scene('add')->check($params);
                    if (true !== $checkRes) {
                        throw new ValidateException($validate->getError());
                    }

                    $data = [
                        'app_id' => $params['app_id'],
                        'total_profit' => $params['total_profit'],
                        'platform_id' => (int) $params['platform_id'],
                        'row' => 1,
                    ];
                    $achievementService = new AchievementService($this->auth->id);
                    $achievementService->handleAchievementFileData( $data , 1);
                    Db::commit();
                    $this->success();
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }


    /**
     * 业绩导入
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function data_import()
    {
        //保存上传的文件
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = app()->getRootPath().DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.$file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, [ 'xlsx'])) {
            $this->error(__('Unknown data format'));
        }
        Db::startTrans();
        try {
            $achievementService = new AchievementService($this->auth->id);
            $achievementService->handleAchievementFileData($filePath , 0);
            Db::commit();
            $this->success();
        }catch (Exception $e){
            Db::rollback();
            $this->error($e->getMessage());
        }
    }


}
