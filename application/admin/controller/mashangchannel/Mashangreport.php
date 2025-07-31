<?php

namespace app\admin\controller\mashangchannel;

use app\admin\common\library\AdminHelper;
use app\admin\common\library\CommonHelper;
use app\common\controller\Backend;
use think\exception\DbException;
use think\response\Json;

/**
 * 账户报管理
 *
 * @icon fa fa-circle-o
 */
class Mashangreport extends Backend
{

    /**
     * Mashangreport模型对象
     * @var \app\admin\model\mashangchannel\Mashangreport
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\mashangchannel\Mashangreport;

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
        $receiving_account_code = input('receiving_account_code', '');
        if (!$receiving_account_code)
            $this->error('参数错误');

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

        $where_extra = [
            'receiving_account_code' => $receiving_account_code
        ];
        if (session('admin.type') != AdminHelper::ADMIN){
            if (input('is_from') == 1){
                $where_extra = [
                    'pid' => session('admin.id')
                ];
            }else{
                $where_extra = [
                    'admin_id' => session('admin.id')
                ];
            }
        }

        $count_info = $this->model
            ->field('
                sum(create_order_number) create_order_number,
                sum(create_order_amount) create_order_amount,
                sum(success_order_number) success_order_number,
                sum(success_order_amount) success_order_amount,
                sum(from_success_order_number) from_success_order_number,
                sum(from_success_order_amount) from_success_order_amount,
                sum(team_success_order_number) team_success_order_number,
                sum(team_success_order_amount) team_success_order_amount,
                sum(rate_amount) rate_amount,
                sum(from_rate_amount) from_rate_amount,
                sum(team_rate_amount) team_rate_amount
            ')
            ->where($where)
            ->where($where_extra)
            ->find();

        $count_info['create_order_number'] = $count_info['create_order_number'] ?? 0;
        $count_info['success_order_number'] = $count_info['success_order_number'] ?? 0;
        $count_info['from_success_order_number'] = $count_info['from_success_order_number'] ?? 0;
        $count_info['team_success_order_number'] = $count_info['team_success_order_number'] ?? 0;
        $count_info['create_order_amount'] = CommonHelper::buildFloatValue($count_info['create_order_amount'] ?? 0);
        $count_info['success_order_amount'] = CommonHelper::buildFloatValue($count_info['success_order_amount'] ?? 0);
        $count_info['from_success_order_amount'] = CommonHelper::buildFloatValue($count_info['from_success_order_amount'] ?? 0);
        $count_info['team_success_order_amount'] = CommonHelper::buildFloatValue($count_info['team_success_order_amount'] ?? 0);
        $count_info['rate_amount'] = CommonHelper::buildFloatValue($count_info['rate_amount'] ?? 0);
        $count_info['from_rate_amount'] = CommonHelper::buildFloatValue($count_info['from_rate_amount'] ?? 0);
        $count_info['team_rate_amount'] = CommonHelper::buildFloatValue($count_info['team_rate_amount'] ?? 0);

        $list = $this->model
            ->field('
                date_key,receiving_account_code,pid,admin_id,
                sum(create_order_number) create_order_number,
                sum(create_order_amount) create_order_amount,
                sum(success_order_number) success_order_number,
                sum(success_order_amount) success_order_amount,
                sum(from_success_order_number) from_success_order_number,
                sum(from_success_order_amount) from_success_order_amount,
                sum(team_success_order_number) team_success_order_number,
                sum(team_success_order_amount) team_success_order_amount,
                sum(rate_amount) rate_amount,
                sum(from_rate_amount) from_rate_amount,
                sum(team_rate_amount) team_rate_amount
            ')
            ->where($where)
            ->where($where_extra)
            ->order($sort, $order)
            ->group('date_key,receiving_account_code,pid,admin_id')
            ->paginate($limit)
            ->each(function ($value){
                $value['admin_id'] = db()->name('admin')
                    ->where('id', $value['admin_id'])
                    ->value('username');
                $value['pid'] = db()->name('admin')
                    ->where('id', $value['pid'])
                    ->value('username');
            });
        $result = ['total' => $list->total(), 'rows' => $list->items(), 'counts' => $count_info];
        return json($result);
    }

}
