<?php

namespace app\admin\common\library;
class AdminHelper
{
    const MASHANG = 'mashang';
    const MERCHANT = 'merchant';
    const ADMIN = 'admin';

    const MERCHANT_AUTH_GROUP_ID = 3;
    const MASHANG_AUTH_GROUP_ID = 4;

    /**
     * 验证谷歌
     * @param $google_code
     * @return true
     * @throws \Exception
     */
    public static function verifyGooleCode($google_code, $google_secret = '', $is_ret_fall = 0)
    {
        try {
            $google_obj = new GoogleAuthHelper();

            if (!$google_secret)
                $google_secret = session('admin.google_secret');

            $google_secret = DataEncryptHelper::decrypt($google_secret);

            if (!config('app_debug')){
                if (!$google_obj->verifyCode($google_secret, $google_code))
                    throw new \Exception('谷歌验证失败');
            }
        } catch (\Exception $e) {
            if ($is_ret_fall)
                return false;
            throw new \Exception($e->getMessage());
        }
        return true;
    }

    /**
     * 检测码商产品授权
     * @param $receiving_account_code
     * @return int
     */
    public static function checkAdminReceivingAccountAuth($receiving_account_code)
    {
        try {
            if (session('admin.type') != self::ADMIN) {
                $info = db()->name('mashang_product')
                    ->where('admin_id', session('admin.id'))
                    ->where('receiving_account_code', $receiving_account_code)
                    ->where('is_open', 1)
                    ->where('system_open', 1)
                    ->find();
                if (!$info)
                    throw new \Exception('未授权该产品');
            }
        } catch (\Exception $e) {
            return 0;
        }

        return 1;
    }
}