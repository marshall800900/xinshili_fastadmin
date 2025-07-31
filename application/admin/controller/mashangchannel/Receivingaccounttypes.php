<?php

namespace app\admin\controller\mashangchannel;

use app\admin\common\library\AdminHelper;
use app\common\controller\Backend;
use think\exception\DbException;
use think\response\Json;

/**
 * 收款账号类型
 *
 * @icon fa fa-circle-o
 */
class Receivingaccounttypes extends Backend
{

    /**
     * Receivingaccounttypes模型对象
     * @var \app\admin\model\mashangchannel\Receivingaccounttypes
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\mashangchannel\Receivingaccounttypes;

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

        $where_extra = [];
        if (session('admin.type') != AdminHelper::ADMIN){
            $where_extra = [
                'code' => ['in', db()->name('mashang_product')->where('admin_id', session('admin.id'))->column('receiving_account_code')]
            ];
        }
        $list = $this->model
            ->where($where)
            ->where($where_extra)
            ->order($sort, $order)
            ->paginate($limit)
            ->each(function ($value){
                $where = [];
                if (session('admin.type') != AdminHelper::ADMIN)
                    $where['admin_id'] = session('admin.id');

               $info = db()->name('mashang_report')
                   ->field('
                    sum(create_order_amount) create_order_amount,
                    sum(success_order_amount) success_order_amount,
                    sum(from_create_order_amount) from_create_order_amount,
                    sum(from_success_order_amount) from_success_order_amount,
                    sum(team_create_order_amount) team_create_order_amount,
                    sum(team_success_order_amount) team_success_order_amount
                   ')
                   ->where($where)
                   ->where('receiving_account_code', $value['code'])
                   ->where('date_key', date('Y-m-d'))
                   ->group('date_key')
                   ->find();

                $value['create_order_amount'] = number_format($info['create_order_amount'] ?? 0, 2, '.', '');
                $value['success_order_amount'] = number_format($info['success_order_amount'] ?? 0, 2, '.', '');
                $value['from_create_order_amount'] = number_format($info['from_create_order_amount'] ?? 0, 2, '.', '');
                $value['from_success_order_amount'] = number_format($info['from_success_order_amount'] ?? 0, 2, '.', '');
                $value['team_create_order_amount'] = number_format($info['team_create_order_amount'] ?? 0, 2, '.', '');
                $value['team_success_order_amount'] = number_format($info['team_success_order_amount'] ?? 0, 2, '.', '');
            });
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

}
