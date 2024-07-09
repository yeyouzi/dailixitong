<?php

namespace app\admin\controller\config;

use app\common\controller\Backend;
use app\admin\model\config\Level as LevelModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Db;
use think\model\Relation;
use app\admin\validate\config\Level as LevelValidate;


/**
 * 等级管理
 *
 * @icon fa fa-circle-o
 */
class Level extends Backend
{
    
    /**
     * Level模型对象
     * @var LevelModel
     */
    protected $model = null;

    protected $banMethod = ['del' , 'import' , 'destroy' , 'multi'];

    protected $selectpageFields = 'id,name,value';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new LevelModel;
        if(!$this->request->isAjax()){
            $this->assign('levelList' , $this->model->getLevelValueList());
        }
    }

    /**
     * 查看
     */
    public function index()
    {
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
                        }
                    ])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            $result = ["total" => $total, "rows" => $list ];

            return json($result);
        }
        $platformId = $this->getFirstPlatformId();
        $this->assignconfig('defaultPlatformId' , $platformId);
        $this->assign('defaultPlatformId' , $platformId);
        return $this->view->fetch();
    }

    /**
     * 添加
     * @return mixed
     * @throws \Exception
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                $result = false;
                Db::startTrans();
                try {
                    //进行验证
                    $validate = new LevelValidate();
                    $checkRes = $validate->scene('add')->check($params);
                    if(true !== $checkRes){
                        throw new ValidateException($validate->getError());
                    }

                    //判断是否已经存在该数值
                    if($this->model->isExistRecord(['platform_id' => $params['platform_id'] , 'value' => $params['value']])){
                        throw new Exception(__('Level value has exists'));
                    }
                    $result = $this->model->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (\PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }


    /**
     * 编辑
     * @param null $ids
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //进行验证
                    $validate = new LevelValidate();
                    $checkRes = $validate->scene('edit')->check($params);
                    if(true !== $checkRes){
                        throw new ValidateException($validate->getError());
                    }

                    //判断是否已经存在该数值
                    $isExistOption = [
                        ['platform_id', '=', $params['platform_id']],
                        ['value', '=', $params['value']],
                        ['id', '<>', $ids],
                    ];
                    if($this->model->isExistRecord($isExistOption)){
                        throw new Exception(__('Level value has exists'));
                    }
                    $result = $row->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (\PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }


}
