<?php

namespace app\admin\model\mashangchannel;

use think\Model;


class Order extends Model
{

    

    

    // 表名
    protected $name = 'receiving_account_pay_url';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'status_text',
        'create_time_text',
        'order_expired_time_text',
        'expired_time_text',
        'create_report_status_text',
        'success_report_status_text',
        'refund_report_status_text',
        'receiving_account_create_report_text',
        'receiving_account_success_report_text'
    ];
    

    
    public function getTypeList()
    {
        return ['receiving' => __('Receiving'), 'shop' => __('Shop')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4')];
    }

    public function getCreateReportStatusList()
    {
        return ['0' => __('Create_report_status 0'), '1' => __('Create_report_status 1')];
    }

    public function getSuccessReportStatusList()
    {
        return ['0' => __('Success_report_status 0'), '1' => __('Success_report_status 1')];
    }

    public function getRefundReportStatusList()
    {
        return ['0' => __('Refund_report_status 0'), '1' => __('Refund_report_status 1')];
    }

    public function getReceivingAccountCreateReportList()
    {
        return ['0' => __('Receiving_account_create_report 0'), '1' => __('Receiving_account_create_report 1')];
    }

    public function getReceivingAccountSuccessReportList()
    {
        return ['0' => __('Receiving_account_success_report 0'), '1' => __('Receiving_account_success_report 1')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getOrderExpiredTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['order_expired_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getExpiredTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['expired_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCreateReportStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_report_status'] ?? '');
        $list = $this->getCreateReportStatusList();
        return $list[$value] ?? '';
    }


    public function getSuccessReportStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['success_report_status'] ?? '');
        $list = $this->getSuccessReportStatusList();
        return $list[$value] ?? '';
    }


    public function getRefundReportStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['refund_report_status'] ?? '');
        $list = $this->getRefundReportStatusList();
        return $list[$value] ?? '';
    }


    public function getReceivingAccountCreateReportTextAttr($value, $data)
    {
        $value = $value ?: ($data['receiving_account_create_report'] ?? '');
        $list = $this->getReceivingAccountCreateReportList();
        return $list[$value] ?? '';
    }


    public function getReceivingAccountSuccessReportTextAttr($value, $data)
    {
        $value = $value ?: ($data['receiving_account_success_report'] ?? '');
        $list = $this->getReceivingAccountSuccessReportList();
        return $list[$value] ?? '';
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setOrderExpiredTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setExpiredTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
