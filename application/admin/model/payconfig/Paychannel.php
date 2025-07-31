<?php

namespace app\admin\model\payconfig;

use think\Model;


class Paychannel extends Model
{

    

    

    // 表名
    protected $name = 'pay_channel';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'pay_type_text',
        'get_pay_url_type_text',
        'amount_type_text',
        'is_open_text',
        'create_time_text',
        'update_time_text'
    ];
    

    
    public function getPayTypeList()
    {
        return ['wechat' => __('Wechat'), 'wechat_qrcode' => __('Wechat_qrcode'), 'qq' => __('Qq'), 'alipay' => __('Alipay'), 'unionqr' => __('Unionqr'), 'yunshanfuqr' => __('Yunshanfuqr'), 'other' => __('Other')];
    }

    public function getGetPayUrlTypeList()
    {
        return ['0' => __('Get_pay_url_type 0'), '1' => __('Get_pay_url_type 1')];
    }

    public function getAmountTypeList()
    {
        return ['1' => __('Amount_type 1'), '2' => __('Amount_type 2'), '3' => __('Amount_type 3'), '4' => __('Amount_type 4'), '5' => __('Amount_type 5')];
    }

    public function getIsOpenList()
    {
        return ['0' => __('Is_open 0'), '1' => __('Is_open 1')];
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['pay_type'] ?? '');
        $list = $this->getPayTypeList();
        return $list[$value] ?? '';
    }


    public function getGetPayUrlTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['get_pay_url_type'] ?? '');
        $list = $this->getGetPayUrlTypeList();
        return $list[$value] ?? '';
    }


    public function getAmountTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['amount_type'] ?? '');
        $list = $this->getAmountTypeList();
        return $list[$value] ?? '';
    }


    public function getIsOpenTextAttr($value, $data)
    {
        $value = $value ?: ($data['is_open'] ?? '');
        $list = $this->getIsOpenList();
        return $list[$value] ?? '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['update_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
