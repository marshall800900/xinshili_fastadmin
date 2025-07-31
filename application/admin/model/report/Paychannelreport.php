<?php

namespace app\admin\model\report;

use think\Model;


class Paychannelreport extends Model
{

    

    

    // 表名
    protected $name = 'pay_channel_report';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'device_text'
    ];
    

    
    public function getDeviceList()
    {
        return ['android' => __('Android'), 'iphone' => __('Iphone'), 'pc' => __('Pc'), 'other' => __('Other')];
    }


    public function getDeviceTextAttr($value, $data)
    {
        $value = $value ?: ($data['device'] ?? '');
        $list = $this->getDeviceList();
        return $list[$value] ?? '';
    }




}
