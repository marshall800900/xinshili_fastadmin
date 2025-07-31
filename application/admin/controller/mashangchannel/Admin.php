<?php

namespace app\admin\controller\mashangchannel;

use app\admin\common\library\AdminHelper;
use app\admin\common\library\CommonHelper;
use app\common\controller\Backend;
use fast\Random;
use fast\Tree;
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
     * @var \app\admin\model\mashangchannel\Admin
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\mashangchannel\Admin;
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
        if ($this->request->isAjax()) {
            $where_extra = [
                'a.type' => AdminHelper::MASHANG
            ];

            if (session('admin.type') != AdminHelper::ADMIN)
                $where_extra['a.line'] = ['like', '%,' . session('admin.id') . ',%'];


            $balance_sql = db()->name('admin_balance')
                ->field('admin_id, sum(balance) balance')
                ->group('admin_id')
                ->where('type', '<>', 'success_amount')
                ->buildSql();

            $today_mashang_report_sql = db()->name('mashang_report')
                ->field('admin_id, sum(success_order_amount) today_success_order_amount, sum(from_success_order_amount) today_from_success_order_amount, sum(team_success_order_amount) today_team_success_order_amount')
                ->where('date_key', date('Y-m-d'))
                ->group('admin_id')
                ->buildSql();

            $yesterday_mashang_report_sql = db()->name('mashang_report')
                ->field('admin_id, sum(success_order_amount) yesterday_success_order_amount, sum(from_success_order_amount) yesterday_from_success_order_amount, sum(team_success_order_amount) yesterday_team_success_order_amount')
                ->where('date_key', date('Y-m-d', time() - 86400))
                ->group('admin_id')
                ->buildSql();

            $admin_list = db()->name("admin")->alias('a')
                ->field(['a.password', 'a.salt', 'a.token', 'a.google_secret', 'a.md5_key', 'a.white_ip', 'a.type'], true)
                ->field('
                    a.id,a.pid,a.username,a.loginfailure, a.logintime, a.loginip, a.createtime, a.status,
                    balance.balance,
                    today_report.today_success_order_amount, today_report.today_from_success_order_amount, today_report.today_team_success_order_amount,
                    yesterday_report.yesterday_success_order_amount, yesterday_report.yesterday_from_success_order_amount, yesterday_report.yesterday_team_success_order_amount
                ')
                ->join($balance_sql . ' balance', 'balance.admin_id = a.id', 'left')
                ->join($today_mashang_report_sql . ' today_report', 'today_report.admin_id = a.id', 'left')
                ->join($yesterday_mashang_report_sql . ' yesterday_report', 'yesterday_report.admin_id = a.id', 'left')
                ->where($where_extra)
                ->order('id desc')
                ->select();

            if ($admin_list) {
                foreach ($admin_list as &$value) {
                    $value['balance'] = CommonHelper::buildFloatValue($value['balance']);

                    $value['today_success_order_amount'] = CommonHelper::buildFloatValue($value['today_success_order_amount']);
                    $value['today_from_success_order_amount'] = CommonHelper::buildFloatValue($value['today_from_success_order_amount']);
                    $value['today_team_success_order_amount'] = CommonHelper::buildFloatValue($value['today_team_success_order_amount']);

                    $value['yesterday_success_order_amount'] = CommonHelper::buildFloatValue($value['yesterday_success_order_amount']);
                    $value['yesterday_from_success_order_amount'] = CommonHelper::buildFloatValue($value['yesterday_from_success_order_amount']);
                    $value['yesterday_team_success_order_amount'] = CommonHelper::buildFloatValue($value['yesterday_team_success_order_amount']);
                    $value['pid'] = $value['pid'] == session('admin.id') ? 0 : $value['pid'];
                }
            }

            Tree::instance()->init($admin_list)->icon = ['&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;'];
            $admin_list = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'username');
            $result = array("total" => count($admin_list), "rows" => $admin_list);

            return json($result);
        }
        return $this->view->fetch();
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
                    $params['type'] = AdminHelper::MASHANG;
                    $params['avatar'] = '/assets/img/avatar.png'; //设置新管理员默认头像。

                    if (session('admin.type') != AdminHelper::ADMIN) {
                        $params['pid'] = session('admin.id');
                        $params['line'] = empty(session('admin.line')) ? ',' . session('admin.id') . ',' : session('admin.line') . session('admin.id') . ',';
                    }

                    $params['password'] = $this->auth->getEncryptPassword($params['password'], $params['salt']);

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
                            'group_id' => AdminHelper::MASHANG_AUTH_GROUP_ID
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

        if ($row['pid'] != session('admin.id') && session('admin.type') != AdminHelper::ADMIN)
            $this->error('禁止跨级编辑');

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

                    if ($params['status'] == 'lock')
                        db()->name('admin')
                            ->where('line', 'like', '%,' . $ids . ',%')
                            ->update([
                                'status' => 'lock'
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

    /**
     * 变更积分
     * @param $ids
     * @return string|void
     * @throws \think\Exception
     */
    public function changeBalance($ids = null)
    {
        if (!$this->request->isPost())
            return $this->view->fetch();

        try {
            $this->token();
            $admin = db()->name('admin')
                ->where('id', $ids)
                ->find();

            if ($admin['pid'] != session('admin.id') && session('admin.type') != AdminHelper::ADMIN)
                throw new \Exception('不可以越级修改积分');

            AdminHelper::verifyGooleCode(input('row.google_auth_code'));

            $list[] = [
                'balance' => input('row.balance'),
                'memo' => input('row.memo'),
                'admin_id' => $ids
            ];

            if ($admin['pid'])
                $list[] = [
                    'balance' => -input('row.balance'),
                    'memo' => input('row.memo'),
                    'admin_id' => $admin['pid']
                ];


            CommonHelper::requestBackend($list, 'changeBalance');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success();
    }

}
