<?php

namespace app\admin\controller;

use app\admin\common\library\AdminHelper;
use app\admin\common\library\OrderHelper;
use app\common\controller\Backend;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Getselectpage extends Backend
{
    protected $noNeedRight = [
        'getReceivingAccountTypes'
    ];

    /**
     * 获取收款账号类型
     * @return \think\response\Json
     * @throws \Exception
     */
    public function getReceivingAccountTypes()
    {
        try {
            $is_json = input('is_json', 1);
            $key_words = input('q_word/a')[0] ?? '';
            $search_value = input('searchValue');
            $code = input('code');
            $where = [];
            if (!empty($key_words)) {
                $where['name'] = ['like', '%' . $key_words . '%'];
            }

            if (!empty($search_value) || $code) {
                $where['code'] = $search_value ? $search_value : $code;
            }

            if (session('admin.type') != AdminHelper::ADMIN)
                $where['code'] = [
                    'in',
                    db()->name('mashang_product')
                        ->where('admin_id', session('admin.id'))
                        ->where('is_open', '1')
                        ->where('system_open', '1')
                        ->column('receiving_account_code')
                ];

            $list = db()->name('receiving_account_types')
                ->field('code id, code value, name')
                ->where($where)
                ->select();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $is_json ?
            json([
                'list' => $list,
                'total' => count($list)
            ]) :
            $this->success('', '', $list);
    }

    /**
     * 获取充值账号
     * @return \support\Response|\think\response\Json|null
     * @throws \Exception
     */
    public function getReceivingAccounts()
    {
        try {
            $is_json = input('is_json', 1);
            $key_words = input('q_word/a')[0] ?? '';
            $search_value = input('searchValue');
            $where = [];
            if (!empty($key_words)) {
                $where['charge_account_name'] = ['like', '%' . $key_words . '%'];
            }

            if (!empty($search_value)) {
                $where['receiving_account_code'] = $search_value;
            }

            $list = db()->name('receiving_account')
                ->field('id, id value, charge_account_name name')
                ->where('is_open', 1)
                ->where('system_open', 1)
                ->where('is_del', 0)
                ->where($where)
                ->select();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $is_json ?
            json([
                'list' => $list,
                'total' => count($list)
            ]) :
            $this->success('', '', $list);
    }

    /**
     * 获取支付产品
     * @return \support\Response|\think\response\Json|null
     * @throws \Exception
     */
    public function getPayProduct()
    {
        try {
            $is_json = input('is_json', 1);
            $key_words = input('q_word/a')[0] ?? '';
            $search_value = input('searchValue');
            $where = [];
            if (!empty($key_words)) {
                $where['product_name'] = ['like', '%' . $key_words . '%'];
            }

            if (!empty($search_value)) {
                $where['product_code'] = $search_value;
            }

            $list = db()->name('pay_product')
                ->field('product_code id, product_code value, product_name name ')
//                ->where('is_open', 1)
                ->where($where)
                ->select();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        if ($is_json == 1)
            return json([
                'list' => $list,
                'total' => count($list)
            ]);

        if ($is_json == 2)
            return json($list);

        $this->success('', '', $list);
    }

    /**
     * 获取支付产品
     * @return \support\Response|\think\response\Json|null
     * @throws \Exception
     */
    public function getPayChannel()
    {
        try {
            $is_json = input('is_json', 1);
            $key_words = input('q_word/a')[0] ?? '';
            $search_value = input('searchValue');
            $where = [];
            if (!empty($key_words)) {
                $where['name'] = ['like', '%' . $key_words . '%'];
            }

            if (!empty($search_value)) {
                $where['id'] = $search_value;
            }

            $list = db()->name('pay_channel')
                ->field('id, id value, name ')
//                ->where('is_open', 1)
                ->where($where)
                ->select();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        if ($is_json == 1)
            return json([
                'list' => $list,
                'total' => count($list)
            ]);

        if ($is_json == 2)
            return json($list);

        $this->success('', '', $list);
    }

    /**
     * 获取订单状态
     * @return \support\Response|\think\response\Json|void
     */
    public function getPayOrderStatus()
    {
        $is_json = input('is_json', 1);

        $list = [
//            [
//                'id' => OrderHelper::ORDER_TYPE_DEFAULT,
//                'name' => '创建订单'
//            ],
            [
                'id' => OrderHelper::ORDER_TYPE_WAIT_PAY,
                'name' => '等待支付'
            ],
            [
                'id' => OrderHelper::ORDER_TYPE_PAY_SUCCESS,
                'name' => '支付成功'
            ],
            [
                'id' => OrderHelper::ORDER_TYPE_REFUND_ING,
                'name' => '退款中'
            ],
            [
                'id' => OrderHelper::ORDER_TYPE_REFUND_SUCCESS,
                'name' => '已退款'
            ],
            [
                'id' => OrderHelper::ORDER_TYPE_TIME_OUT,
                'name' => '订单超时'
            ],
        ];
        if ($is_json == 1)
            return json([
                'list' => $list,
                'total' => count($list)
            ]);

        if ($is_json == 2)
            return json($list);

        $this->success('', '', $list);
    }

}