<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\library\Auth;
use app\admin\model\User as UserModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Db;
use think\model\Relation;
use app\admin\validate\User as UserValidate;
use app\admin\model\UserAttribute as UserAttributeModel;
use app\admin\service\RechargeService;

/**
 * 会员管理.
 *
 * @icon fa fa-user
 */
class User extends Backend
{
    //protected $relationSearch = true;

    protected $searchFields = 'id,username,nickname';
    /**
     * @var UserModel
     */
    protected $model = null;

    protected $banMethod = ['del', 'destroy', 'multi'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new UserModel();
    }

    /**
     * 查看.
     */
    public function index()
    {
        //设置过滤方法
        $platformId = $this->getFirstPlatformId();
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $userModel = new UserModel;
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            [$where, $sort, $order, $offset, $limit, $platformIdTmp , $levelIdTmp , $appIdTmp] = $this->setBuildparams();
            $platformId = !empty($platformIdTmp) ? $platformIdTmp : $platformId;
            $field = ['id', 'app_id', 'username', 'nickname', 'mobile', 'first_num', 'second_num', 'gender', 'referee_id', 'referee_second_id', 'create_time', 'update_time', 'status'];
            $userAttributeOption = [];
            if(!empty($platformId)){
                $userAttributeOption[] = ['platform_id' , '=' , $platformId];
            }
            if(!empty($levelIdTmp)){
                $userAttributeOption[] = ['level_id' , '=' , $levelIdTmp];
            }
            if(!empty($appIdTmp)){
                $userAttributeOption[] = ['app_id' , '=' , $appIdTmp];
            }
            $whenFunction = function(Query $query) use($userAttributeOption){
                $query->where('id' , 'IN' , function(Query $query) use($userAttributeOption){
                    $query->name('user_attribute')->field(['user_id'])->where($userAttributeOption);
                });
            };
            $total = $this->model
                ->when(!empty($userAttributeOption) , $whenFunction)
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->when(!empty($userAttributeOption) , $whenFunction)
                ->with([
                    'firstUser' => function (Relation $relation) {
                        $relation->field(['id', 'app_id', 'nickname', 'username']);
                    },
                    'secondUser' => function (Relation $relation) {
                        $relation->field(['id', 'app_id', 'nickname', 'username']);
                    },
                    'userAttribute' => function (Relation $relation) use ($platformId) {
                        $relation->where('platform_id', $platformId)->with([
                            'level' => function (Relation $relation) {
                                $relation->field(['id', 'name', 'value', 'platform_id']);
                            }
                        ]);
                    }
                ])
                ->where($where)
                ->field($field)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
                $v->hidden(['password', 'salt']);
                $v->platform_id = $platformId;
                //团队业绩
                $v->team_balance = $userModel->getUserTeamBalance($v->id, $platformId);
            }
            $result = ['total' => $total, 'rows' => $list, 'platformId' => $platformId];
            return json($result);
        }

        $this->assignconfig('defaultPlatformId', $platformId);
        $this->assign('defaultPlatformId', $platformId);
        return $this->view->fetch();
    }


    /**
     * 添加用户
     * @return mixed
     * @throws \think\Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                Db::startTrans();
                try {
                    //进行验证
                    $validate = new UserValidate();
                    $checkRes = $validate->scene('add')->check($params);
                    if (true !== $checkRes) {
                        throw new ValidateException($validate->getError());
                    }
                    $result = $this->model->addUserByAdmin($params);
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
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑.
     */
    public function edit($ids = null)
    {
        $platformId = $this->request->param('platformId');
        $row = $this->model->where('id' , $ids)->with([
            'userAttribute' => function (Relation $relation) use ($platformId) {
                $relation->where('platform_id', $platformId);
            }
        ])->find();
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                Db::startTrans();
                try {
                    //进行验证
                    $validate = new UserValidate();
                    $checkRes = $validate->scene('edit')->check($params);
                    if (true !== $checkRes) {
                        throw new ValidateException($validate->getError());
                    }

                    if(!isset($params['mobile'])){
                        throw new ValidateException('缺少手机号');
                    }

                    //判断手机号是否已经存在
                    $mobileOption = [
                        ['id' , '<>' , $ids],
                        ['mobile' , '=' , $params['mobile']],
                    ];
                    if($this->model->isExistRecord($mobileOption)){
                        throw new ValidateException('手机号已存在');
                    }

                    //判断app_id是否已经存在
                    $isExistOption = [
                        ['platform_id' , '=' , $params['platform_id'] ],
                        ['app_id' , '=' , $params['app_id'] ],
                        ['user_id' , '<>' , $ids ],
                    ];
                    if((new UserAttributeModel)->isExistRecord($isExistOption)){
                        throw new Exception(__('App id has exists'));
                    }
                    $row->updateUserByAdmin( $row , $params);
                    Db::commit();
                    $this->success();
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
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $this->view->assign('platformId' , $platformId);
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        Auth::instance()->delete($row['id']);
        $this->success();
    }


    /**
     * 充值
     * @param int $ids
     * @param int $platformId
     * @return mixed
     * @throws \Exception
     */
    public function recharge(int $ids, int $platformId)
    {
        //获取用户数据
        $row = (new UserAttributeModel)->where([
            'platform_id' => $platformId,
            'user_id' => $ids,
        ])->find();
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                Db::startTrans();
                try {
                    $params['user_id'] = $ids;
                    $params['platform_id'] = $platformId;
                    //进行验证
                    $validate = new UserValidate();
                    $checkRes = $validate->scene('recharge')->check($params);
                    if (true !== $checkRes) {
                        throw new ValidateException($validate->getError());
                    }

                    $rechargeService = (new RechargeService($this->auth->id));
                    $rechargeService->handle($params);
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
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }


    /**
     * 清除平台下所有的业绩和佣金
     * @param int $platformId
     * @throws \Exception
     */
    public function clear_all(int $platformId)
    {
        if(empty($platformId)){
            $this->error(__('Require platform'));
        }
        if($this->request->isAjax()){
            Db::startTrans();
            try{
                $res = (new UserAttributeModel())->where([
                    'platform_id' => $platformId
                ])->update([
                    'money' => 0,
                    'total_money' => 0,
                    'balance' => 0
                ]);
                if($res === false){
                    throw new Exception('操作失败');
                }
                Db::commit();
                $this->success();
            }catch (Exception $e){
                Db::rollback();
                $this->error($e->getMessage() ?: '操作失败');
            }
        }
    }

    /**
     * 清除平台下某用户的业绩和佣金
     * @param int $ids 用户ID
     * @param int $platformId
     * @throws \Exception
     */
    public function clear_one( int $ids, int $platformId)
    {
        if(empty($platformId) || empty($ids) ){
            $this->error(__('Require platform'));
        }
        if($this->request->isAjax()){
            Db::startTrans();
            try{
                $res = (new UserAttributeModel())->where([
                    'platform_id' => $platformId,
                    'user_id' => $ids
                ])->update([
                    'money' => 0,
                    'total_money' => 0,
                    'balance' => 0
                ]);
                if($res === false){
                    throw new Exception('操作失败');
                }
                Db::commit();
                $this->success();
            }catch (Exception $e){
                Db::rollback();
                $this->error($e->getMessage() ?: '操作失败');
            }
        }
    }


    /**
     * 重写条件
     * @param null $searchfields
     * @param null $relationSearch
     * @return array
     */
    protected function setBuildparams($searchfields = null, $relationSearch = null)
    {
        $filter = $this->request->get('filter', '');
        $op = $this->request->get('op', '', 'trim');
        $filter = (array)json_decode($filter, true);
        $op = (array)json_decode($op, true);
        $filter = $filter ? $filter : [];
        $otherWhere = [];
        $platformId = null;
        $levelId = null;
        $appId = null;
        if(!empty($filter)){
            if (isset($filter['platform_id'])) {
                $platformId = $filter['platform_id'];
                unset($filter['platform_id'], $op['platform_id']);
            }

            if(isset($filter['level_id'])){
                $levelId = $filter['level_id'];
                unset($filter['level_id'], $op['level_id']);
            }
            if(isset($filter['userAttribute.app_id'])){
                $appId = $filter['userAttribute.app_id'];
                unset($filter['userAttribute.app_id'], $op['userAttribute.app_id']);
            }

            $this->request->setRequest([
                'filter' => json_encode($filter),
                'op' => json_encode($op),
            ]);
        }

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();

        return [$where, $sort, $order, $offset, $limit, $platformId , $levelId , $appId];
    }


}
