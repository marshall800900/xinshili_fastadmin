<?php

namespace app\admin\controller\merchant;

use app\admin\common\library\AdminHelper;
use app\admin\common\library\DataEncryptHelper;
use app\common\controller\Backend;
use fast\Random;
use think\Db;
use think\exception\DbException;
use think\response\Json;
use think\Validate;

/**
 * 管理员管理
 *
 * @icon fa fa-circle-o
 */
class Admin extends Backend
{

    /**
     * Admin模型对象
     * @var \app\admin\model\merchant\Admin
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\merchant\Admin;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->field(['password', 'salt', 'token', 'google_secret', 'md5_key', 'white_ip', 'type'], true)
            ->where($where)
            ->where('type', AdminHelper::MERCHANT)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            if ($params) {
                Db::startTrans();
                try {
                    if (!Validate::is($params['password'], '\S{6,30}')) {
                        exception(__("Please input correct password"));
                    }

                    $params['nickname'] = $params['username'];
                    $params['salt'] = Random::alnum();
                    $params['type'] = AdminHelper::MERCHANT;
                    $params['avatar'] = '/assets/img/avatar.png'; //设置新管理员默认头像。

                    if (session('admin.type') != AdminHelper::ADMIN) {
                        $params['pid'] = session('admin.id');
                        $params['line'] = empty(session('admin.line')) ? ',' . session('admin.id') . ',' : session('admin.line') . session('admin.id') . ',';
                    }

                    $params['password'] = $this->auth->getEncryptPassword($params['password'], $params['salt']);

                    $params['md5_key'] = DataEncryptHelper::encrypt(strtoupper(Random::alpha(32)));

                    $result = $this->model->validate('Admin.add')->save($params);
                    if ($result === false) {
                        exception($this->model->getError());
                    }

                    $balance_log_type_list = [
                        'balance', 'lock_balance', 'unlock_balance', 'success_amount', 'rebate_amount'
                    ];

                    foreach ($balance_log_type_list as $type) {
                        $balance_log_list[] = [
                            'admin_id' => $this->model->id,
                            'type' => $type,
                            'balance' => 0,
                            'version' => 1
                        ];
                    }

                    db()->name('admin_balance')
                        ->insertAll($balance_log_list);

                    db()->name('auth_group_access')
                        ->insert([
                            'uid' => $this->model->id,
                            'group_id' => AdminHelper::MERCHANT_AUTH_GROUP_ID
                        ]);

                    Db::commit();
                } catch (\Exception $e) {
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
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            if ($params) {
                Db::startTrans();
                try {
                    if ($params['password']) {
                        if (!Validate::is($params['password'], '\S{6,30}')) {
                            exception(__("Please input correct password"));
                        }
                        $params['salt'] = Random::alnum();
                        $params['password'] = $this->auth->getEncryptPassword($params['password'], $params['salt']);
                    } else {
                        unset($params['password'], $params['salt']);
                    }
                    //这里需要针对username和email做唯一验证
                    $adminValidate = \think\Loader::validate('Admin');
                    $adminValidate->rule([
                        'username' => 'require|^.{6,15}$|chsAlphaNum|unique:admin,username,' . $row->id,
                        'password' => 'regex:\S{32}',
                    ]);

                    $result = $row->validate('Admin.edit')->save($params);
                    if ($result === false) {
                        exception($row->getError());
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $grouplist = $this->auth->getGroups($row['id']);
        $groupids = [];
        foreach ($grouplist as $k => $v) {
            $groupids[] = $v['id'];
        }
        $this->view->assign("row", $row);
        $this->view->assign("groupids", $groupids);
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        $this->error(__('No rows were deleted'));
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            $ids = array_intersect($this->childrenAdminIds, array_filter(explode(',', $ids)));
            // 避免越权删除管理员
            $childrenGroupIds = $this->childrenGroupIds;
            $adminList = $this->model->where('id', 'in', $ids)->where('id', 'in', function ($query) use ($childrenGroupIds) {
                $query->name('auth_group_access')->where('group_id', 'in', $childrenGroupIds)->field('uid');
            })->select();
            if ($adminList) {
                $deleteIds = [];
                foreach ($adminList as $k => $v) {
                    $deleteIds[] = $v->id;
                }
                $deleteIds = array_values(array_diff($deleteIds, [$this->auth->id]));
                if ($deleteIds) {
                    Db::startTrans();
                    try {
                        $this->model->destroy($deleteIds);
                        model('AuthGroupAccess')->where('uid', 'in', $deleteIds)->delete();
                        db()->name('admin_balance_log')->where('admin_id', 'in', $deleteIds)->delete();
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    $this->success();
                }
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('You have no permission'));
    }

    public function changeBalance($ids = null){
        if (!$ids) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $this->token();
        }

        $balance = db()->name('admin_balance')
            ->where('admin_id', $ids)
            ->where('type', '<>', 'success_amount')
            ->where('type', '<>', 'rebate_amount')
            ->sum('balance');
        $this->view->assign('balance', $balance);
        return $this->view->fetch();
    }

    /**
     * 生成拉单链接
     * @return string
     * @throws \think\Exception
     */
    public function copyCreatePayUrl(){
        if ($this->request->isPost()) {
            $merchant_id = input('merchant_id', '');
            $product_code = input('product_code', '');
            if (!$merchant_id)
                $this->error('参数错误');

            if (!$product_code)
                $this->error('参数错误');

            $pay_url = db()->name('config')
                ->where('name', 'pay_url')
                ->value('value');
            $url = rtrim($pay_url, '/') . '/index/createPayUrl?params=' . urlencode(DataEncryptHelper::encrypt([
                    'merchant_id' => $merchant_id,
                    'product_code' => $product_code,
                ]));
            $this->success('success', null, ['url' => $url]);
        }
        return $this->view->fetch();
    }
}
