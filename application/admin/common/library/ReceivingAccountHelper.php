<?php

namespace app\admin\common\library;


use think\exception\ValidateException;

class ReceivingAccountHelper
{

    public static function fengzhangguiCheck($params)
    {
        try {
            $list = [];
            foreach ($params as $key => $val) {
                if (isset($val['checkbox'])) {
                    $params[$key]['cookie'] = base64_decode($val['cookie']);
                    $list[] = [
                        'cookie' => base64_decode($val['cookie']),
                        'charge_account' => $val['charge_account'],
                        'charge_account_name' => $val['charge_account_name'],
                        'proxy_ip' => $params['proxy_ip'],
                        'receiving_account_code' => $params['receiving_account_code'],
                        'admin_id' => $params['admin_id'],
                    ];
                }
            }

            if (count($list) < 1)
                throw new \Exception('请选择门店');
        } catch (\Exception $e) {
            throw new ValidateException($e->getMessage());
        }

        return [
            'adds' => true,
            'list' => $list
        ];
    }

    /**
     * 丰掌柜登录
     * @param $params
     * @return array
     * @throws \Exception
     */
    public static function fengzhangguiLogin($params)
    {
        try {
            $request_url = 'http://206.238.178.200:7979/login';
            $request_params = [
                'userName' => $params['username'],
                'passWord' => $params['password'],
                'ip' => $params['proxy_ip'],
            ];

            $result_json = CommonHelper::curlRequest($request_url, json_encode($request_params, JSON_UNESCAPED_UNICODE), ['Content-Type:application/json']);
//            $result_json = '{"code":"200","errmsg":"null","data":{"msg":"登入成功","BodyID":"1255A8A0AC194454BEC74BD862403A4A","MerchantID":"1255A8A0AC194454BEC74BD862403A4A"}}';
            $result_array = json_decode($result_json, true);
            LogHelper::write([$request_url, $request_params, $result_json, $result_array], '', 'request_login');
            if (!isset($result_array['code']) || $result_array['code'] != 200)
                throw new \Exception($result_array['error'] ?? '登陆失败');

            $request_store_url = 'http://206.238.178.200:7979/queryStore';
            $request_store_params = [
                'ip' => $params['proxy_ip'],
                'MerchantID' => $result_array['data']['MerchantID']
            ];
            $result_store_json = CommonHelper::curlRequest($request_store_url, json_encode($request_store_params, JSON_UNESCAPED_UNICODE), ['Content-Type:application/json']);
//            $result_store_json = '{"code":"200","errmsg":"null","data":[{"StoreID":"6DA1231B98A84985A7B4B3A4F05D7C1E","StoreName":"福安市辉简信息科技有限公司_门店","MerchantName":"福安市辉简信息科技有限公司","MerchantCode":"110350904620001","StoreCode":"10001"},{"StoreID":"7321DBD9FCB64A0991789DF1A3F0DAA5","StoreName":"厦门思明区北门7号","MerchantName":"福安市辉简信息科技有限公司","MerchantCode":"110350904620001","StoreCode":"10002"},{"StoreID":"6E7E267298014F47B1065626F0627664","StoreName":"厦门思明区北门8号","MerchantName":"福安市辉简信息科技有限公司","MerchantCode":"110350904620001","StoreCode":"10003"},{"StoreID":"A530A685F50D4D9599689FD3859E358F","StoreName":"北京市朝阳区北大街156号","MerchantName":"福安市辉简信息科技有限公司","MerchantCode":"110350904620001","StoreCode":"10004"}]}';
            $result_store_array = json_decode($result_store_json, true);
            LogHelper::write([$request_store_url, $request_store_params, $result_store_json, $result_store_array], '', 'request_store');
            if (!isset($result_store_array['code']) || $result_store_array['code'] != 200)
                throw new \Exception($result_store_array['error'] ?? '获取门店列表失败');

            if (!isset($result_store_array['data']) || count($result_store_array['data']) < 1)
                throw new \Exception('未获取到门店列表');

            $store_list = [];
            foreach ($result_store_array['data'] as $val) {
                $store_list[] = [
                    'charge_account' => $val['MerchantName'],
                    'charge_account_name' => $val['StoreName'],
                    'cookie' => base64_encode(json_encode([
                        'BodyID' => $result_array['data']['BodyID'],
                        'MerchantID' => $result_array['data']['MerchantID'],
                        'StoreID' => $val['StoreID'],
                        'MerchantCode' => $val['MerchantCode'],
                    ], JSON_UNESCAPED_UNICODE))
                ];
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $store_list;
    }

    public static function tiktokCheck($params)
    {
        return $params;
    }

    public static function jintiaoCheck($params)
    {
        if (!isset($params['id'])) {
            $result = CommonHelper::requestBackend([
                'redis_key' => 'jintiao_' . $params['charge_account']
            ], 'getCache');
            if (!$result[0])
                throw new ValidateException('请先扫码授权');

            $params['extra_params'] = $result[0];
        }
        return $params;
    }

    /**
     * ck检测
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public static function dytbCheck($params)
    {
        try {
            if (!is_array($params['area']))
                $params['area'] = json_decode($params['area'], true);

            $params = ProxyIpHelper::getProxyIp($params);
            $proxy_ip = explode(':', $params['proxy_ip']);

            $request_url = 'http://44.210.149.175:8090/recharge_external_user_info_cache';

            $request_header = [
                'Content-Type: application/x-www-form-urlencoded',
                'ip:' . $proxy_ip[0],
                'port:' . $proxy_ip[1],
                'username:' . 'd4222495829',
                'password:' . 'l4ce3iha'
            ];

            if (!isset($params['extra_params']) || !$params['extra_params']) {
                $system_extra_params_list = db()->name('pay_order')
                    ->where('user_device', 'iphone')
                    ->orderRaw('rand()')
                    ->limit(5)
                    ->column('system_extra_params');

                if (count($system_extra_params_list) > 0) {
                    $key = rand(0, count($system_extra_params_list) - 1);
                    $system_extra_params = $system_extra_params_list[$key];
                } else {
                    $system_extra_params = '{"screen_params":{"width":412,"height":915,"availWidth":412,"availHeight":915,"colorDepth":24,"pixelRatio":2.625},"navigator_params":{"userAgent":"Mozilla\/5.0 (Linux; Android 14; V2244A; wv) AppleWebKit\/537.36 (KHTML, like Gecko) Version\/4.0 Chrome\/123.0.6312.118 Mobile Safari\/537.36 VivoBrowser\/24.0.21.0","platform":"Linux aarch64"},"window_params":{"innerWidth":412,"innerHeight":821,"outerWidth":412,"outerHeight":821,"screenX":0,"screenY":0,"pageYOffset":0}}';
                }
            } else {
                $system_extra_params = $params['extra_params'];
            }

            $system_extra_params = json_decode($system_extra_params, true);

            $request_data = [
                'price' => 100,
                'screenParams' => json_encode($system_extra_params['screen_params'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT),
                'navigatorParams' => json_encode($system_extra_params['navigator_params'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT),
                'windowParams' => json_encode($system_extra_params['window_params'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT),
                'cookie' => $params['cookie']
            ];


            $result_json = CommonHelper::curlRequest($request_url, http_build_query($request_data), $request_header);
            LogHelper::write([http_build_query($request_data), $request_data, $request_header, $request_url, $result_json]);

            if (strstr($result_json, '请登录后进入直播间')) {
                if (isset($params['id'])) {
                    db()->name('receiving_account')
                        ->where('id', $params['id'])
                        ->update([
                            'is_open' => 0,
                            'create_fail_msg' => '请登录后进入直播间'
                        ]);
                }
                throw new \Exception('请登录后进入直播间');
            }

            if (!strstr($result_json, '登录账号发生变化')) {
                if (strstr($result_json, 'milliseconds') || strstr($result_json, 'BrotliDecompress') || strstr($result_json, 'Request failed'))
                    ProxyIpHelper::unsetProxyIp($params);

                $result_array = json_decode($result_json, true);
                if (!isset($result_array['code']) || $result_array['code'] != 0)
                    throw new \Exception('ck异常请重试');

                if (!isset($result_array['data']['data']['user_info'][0]['short_id']) || !$result_array['data']['data']['user_info'][0]['short_id'])
                    throw new \Exception('ck异常请重试');
            }

            $params['area'] = json_encode($params['area'], JSON_UNESCAPED_UNICODE);
            $params['charge_account'] = $result_array['data']['data']['user_info'][0]['short_id'];
            $params['charge_account_name'] = $result_array['data']['data']['user_info'][0]['nick_name'];
        } catch (\Exception $e) {
            throw new ValidateException($e->getMessage());
        }
        return $params;
    }

    /**
     * ck检测
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public static function douyinCheck($params)
    {
        try {
            if (!is_array($params['area']))
                $params['area'] = json_decode($params['area'], true);

            $params = ProxyIpHelper::getProxyIp($params);
            $proxy_ip = explode(':', $params['proxy_ip']);

            $request_url = 'http://44.210.149.175:8090/recharge_external_user_info_cache';

            $request_header = [
                'Content-Type: application/x-www-form-urlencoded',
                'ip:' . $proxy_ip[0],
                'port:' . $proxy_ip[1],
                'username:' . 'd4222495829',
                'password:' . 'l4ce3iha'
            ];

            if (!isset($params['extra_params']) || !$params['extra_params']) {
                $system_extra_params_list = db()->name('pay_order')
                    ->where('user_device', 'iphone')
                    ->orderRaw('rand()')
                    ->limit(5)
                    ->column('system_extra_params');

                if (count($system_extra_params_list) > 0) {
                    $key = rand(0, count($system_extra_params_list) - 1);
                    $system_extra_params = $system_extra_params_list[$key];
                } else {
                    $system_extra_params = '{"screen_params":{"width":412,"height":915,"availWidth":412,"availHeight":915,"colorDepth":24,"pixelRatio":2.625},"navigator_params":{"userAgent":"Mozilla\/5.0 (Linux; Android 14; V2244A; wv) AppleWebKit\/537.36 (KHTML, like Gecko) Version\/4.0 Chrome\/123.0.6312.118 Mobile Safari\/537.36 VivoBrowser\/24.0.21.0","platform":"Linux aarch64"},"window_params":{"innerWidth":412,"innerHeight":821,"outerWidth":412,"outerHeight":821,"screenX":0,"screenY":0,"pageYOffset":0}}';
                }
            } else {
                $system_extra_params = $params['extra_params'];
            }

            $system_extra_params = json_decode($system_extra_params, true);

            $request_data = [
                'price' => 100,
                'screenParams' => json_encode($system_extra_params['screen_params'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT),
                'navigatorParams' => json_encode($system_extra_params['navigator_params'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT),
                'windowParams' => json_encode($system_extra_params['window_params'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT),
                'cookie' => $params['cookie']
            ];


            $result_json = CommonHelper::curlRequest($request_url, http_build_query($request_data), $request_header);
            LogHelper::write([http_build_query($request_data), $request_data, $request_header, $request_url, $result_json]);

            if (strstr($result_json, '请登录后进入直播间')) {
                if (isset($params['id'])) {
                    db()->name('receiving_account')
                        ->where('id', $params['id'])
                        ->update([
                            'is_open' => 0,
                            'create_fail_msg' => '请登录后进入直播间'
                        ]);
                }
                throw new \Exception('请登录后进入直播间');
            }

            if (!strstr($result_json, '登录账号发生变化')) {
                if (strstr($result_json, 'milliseconds') || strstr($result_json, 'BrotliDecompress') || strstr($result_json, 'Request failed'))
                    ProxyIpHelper::unsetProxyIp($params);

                $result_array = json_decode($result_json, true);
                if (!isset($result_array['code']) || $result_array['code'] != 0)
                    throw new \Exception('ck异常请重试');

                if (!isset($result_array['data']['data']['user_info'][0]['short_id']) || !$result_array['data']['data']['user_info'][0]['short_id'])
                    throw new \Exception('ck异常请重试');
            }

            $params['area'] = json_encode($params['area'], JSON_UNESCAPED_UNICODE);
            $params['charge_account'] = $result_array['data']['data']['user_info'][0]['short_id'];
            $params['charge_account_name'] = $result_array['data']['data']['user_info'][0]['nick_name'];
        } catch (\Exception $e) {
            throw new ValidateException($e->getMessage());
        }
        return $params;
    }

    /**
     * 纷享生活发送验证码
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public static function fxshSendSmsCode($params)
    {
        try {
            $params['proxy_ip'] = json_decode($params['proxy_ip'], true);
            if (!isset($params['proxy_ip']['proxy_auth']) || !isset($params['proxy_ip']['proxy_ip']))
                throw new \Exception('代理IP格式错误');

            if (count($params['proxy_ip']['proxy_auth']) != 2){
                $params['proxy_ip']['proxy_auth'] = explode(':', $params['proxy_ip']['proxy_auth']);
                if (count($params['proxy_ip']['proxy_auth']) != 2)
                    throw new \Exception('代理IP格式错误');
            }

            $api_url = db()->name('pay_api')
                ->where('api_code', $params['receiving_account_code'])
                ->value('api_url');

            $request_url = $api_url . 'sendCode';
            $request_data = [
                'phone' => $params['charge_account'],
                'ip' => $params['proxy_ip']['proxy_ip'],
                'proxyUser' => $params['proxy_ip']['proxy_auth'][0],
                'proxyPass' => $params['proxy_ip']['proxy_auth'][1],
            ];

            $result_json = CommonHelper::curlRequest($request_url, json_encode($request_data, JSON_UNESCAPED_UNICODE), ['Content-Type:application/json']);
            $result_array = json_decode($result_json, true);
            LogHelper::write([$request_url, $request_data, $result_json, $result_array]);

            if (!isset($result_array['code']) || $result_array['code'] != 200 || !isset($result_array['data']['deviceNo']))
                throw new \Exception($result_array['errmsg'] ?? '获取验证码失败');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result_array['data']['deviceNo'];
    }

    /**
     * 纷享生活查询余额
     * @param $params
     * @return string
     */
    public static function fxshQueryBalance($params){
        try{
            $params['proxy_ip'] = json_decode($params['proxy_ip'], true);
            $params['cookie'] = json_decode($params['cookie'], true);

            $api_url = db()->name('pay_api')
                ->where('api_code', $params['receiving_account_code'])
                ->value('api_url');

            $request_url = $api_url . 'getyur';
            $request_data = [
                'ck' => $params['cookie']['ck'],
                'deviceNo' => $params['cookie']['deviceNo'],
                'ip' => $params['proxy_ip']['proxy_ip'],
                'proxyUser' => $params['proxy_ip']['proxy_auth'][0],
                'proxyPass' => $params['proxy_ip']['proxy_auth'][1],
            ];

            $result_json = CommonHelper::curlRequest($request_url, json_encode($request_data, JSON_UNESCAPED_UNICODE), ['Content-Type:application/json']);
            $result_array = json_decode($result_json, true);
            LogHelper::write([$request_url, $request_data, $result_json, $result_array]);

            if (!isset($result_array['code']) || $result_array['code'] != 200 || !isset($result_array['data']['dataJson']))
                throw new \Exception($result_array['errmsg'] ?? '登录失败');

            if (!isset($result_array['data']['dataJson']['availableAmount']))
                throw new \Exception('查询失败');

            db()->name('receiving_account')
                ->where('id', $params['id'])
                ->update([
                    'balance' => $result_array['data']['dataJson']['availableAmount']
                ]);
        }catch (\Exception $e){
            throw new ValidateException($e->getMessage());
        }
        return '余额：' . $result_array['data']['dataJson']['availableAmount'] ;
    }

    /**
     * 纷享生活登录
     * @param $params
     * @return array
     * @throws \Exception
     */
    public static function fxshCheck($params){
        try{
            if (isset($params['id']) && empty($params['sms_code'])){
                if ($params['pay_password']){
                    $cookie = db()->name('receiving_account')
                        ->where('id', $params['id'])
                        ->value('cookie');
                    $cookie = json_decode($cookie, true);
                    $cookie['pay_password'] = $params['pay_password'];
                    $params['cookie'] = json_encode($cookie, JSON_UNESCAPED_UNICODE);
                }

                unset($params['sms_code'], $params['pay_password']);
                $charge_account_info = $params;
            }else{
                $params['proxy_ip'] = json_decode($params['proxy_ip'], true);
                if (!isset($params['proxy_ip']['proxy_auth']) || !isset($params['proxy_ip']['proxy_ip']))
                    throw new \Exception('代理IP格式错误');

                if (count($params['proxy_ip']['proxy_auth']) != 2){
                    $params['proxy_ip']['proxy_auth'] = explode(':', $params['proxy_ip']['proxy_auth']);
                    if (count($params['proxy_ip']['proxy_auth']) != 2)
                        throw new \Exception('代理IP格式错误');
                }

                $api_url = db()->name('pay_api')
                    ->where('api_code', $params['receiving_account_code'])
                    ->value('api_url');

                $request_url = $api_url . 'login';
                $request_data = [
                    'phone' => $params['charge_account'],
                    'phonecode' => $params['sms_code'],
                    'deviceNo' => $params['extra_params'],
                    'ip' => $params['proxy_ip']['proxy_ip'],
                    'proxyUser' => $params['proxy_ip']['proxy_auth'][0],
                    'proxyPass' => $params['proxy_ip']['proxy_auth'][1],
                ];

                $result_json = CommonHelper::curlRequest($request_url, json_encode($request_data, JSON_UNESCAPED_UNICODE), ['Content-Type:application/json']);
                $result_array = json_decode($result_json, true);
                LogHelper::write([$request_url, $request_data, $result_json, $result_array]);

                if (!isset($result_array['code']) || $result_array['code'] != 200 || !isset($result_array['data']['dataJson']))
                    throw new \Exception($result_array['errmsg'] ?? '登录失败');

                $cookie = $result_array['data']['dataJson'];
                $cookie['ck'] =$result_array['data']['ck'];
                $cookie['deviceNo'] =$params['extra_params'];
                $cookie['pay_password'] = $params['pay_password'];
                $charge_account_info = [
                    'receiving_account_code' => $params['receiving_account_code'],
                    'charge_account' => $params['charge_account'],
                    'proxy_ip' => json_encode($params['proxy_ip'], JSON_UNESCAPED_UNICODE),
                    'charge_account_name' => $params['charge_account'],
                    'charge_amount' => 88888888,
                    'cookie' => json_encode($cookie, JSON_UNESCAPED_UNICODE)
                ];
            }

            $charge_account_info['is_open'] = '1';
            $charge_account_info['create_fail_msg'] = '';
        }catch (\Exception $e){
            throw new ValidateException($e->getMessage());
        }
        return $charge_account_info;
    }

    /**
     * 查询余额
     * @param $params
     * @return void
     * @throws \Exception
     */
    public static function fxshGetBalance($params){
        try{
            $params['proxy_ip'] = json_decode($params['proxy_ip'], true);
            $params['cookie'] = json_decode($params['cookie'], true);

            $api_url = db()->name('pay_api')
                ->where('api_code', $params['receiving_account_code'])
                ->value('api_url');

            $request_url = $api_url . 'getyur';
            $request_data = [
                'ck' => $params['cookie']['ck'],
                'deviceNo' => $params['cookie']['deviceNo'],
                'ip' => $params['proxy_ip']['proxy_ip'],
                'proxyUser' => $params['proxy_ip']['proxy_auth'][0],
                'proxyPass' => $params['proxy_ip']['proxy_auth'][1],
            ];

            $result_json = CommonHelper::curlRequest($request_url, json_encode($request_data, JSON_UNESCAPED_UNICODE), ['Content-Type:application/json']);
            $result_array = json_decode($result_json, true);
            LogHelper::write([$request_url, $request_data, $result_json, $result_array]);

            if (!isset($result_array['code']) || $result_array['code'] != 200 || !isset($result_array['data']['dataJson']))
                throw new \Exception($result_array['errmsg'] ?? '登录失败');

            if (!isset($result_array['data']['dataJson']['availableAmount']))
                throw new \Exception('查询失败');

            db()->name('receiving_account')
                ->where('id', $params['id'])
                ->update([
                    'balance' => $result_array['data']['dataJson']['availableAmount']
                ]);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

        return ['balance' => $result_array['data']['dataJson']['availableAmount']];
    }

    public static function fxshQueryInfo($params){
        try{
            $api_url = db()->name('pay_api')
                ->where('api_code', $params['receiving_account_code'])
                ->value('api_url');

            $info = db()->name('receiving_account')
                ->field('id, receiving_account_code, cookie, proxy_ip, proxy_ip_invalid_time, id,area, extra_params')
                ->where('receiving_account_code', $params['receiving_account_code'])
                ->where('is_open', '1')
                ->where('is_del', '0')
                ->where('id', '<>', $params['id'])
                ->find();
            if (!$info)
                throw new \Exception('查询异常');
            $info['proxy_ip'] = json_decode($info['proxy_ip'], true);
            $info['cookie'] = json_decode($info['cookie'], true);


            $request_url = $api_url . 'queryUser';
            $request_data = [
                'ck' => $info['cookie']['ck'],
                'deviceNo' => $info['cookie']['deviceNo'],
                'ip' => $info['proxy_ip']['proxy_ip'],
                'proxyUser' => $info['proxy_ip']['proxy_auth'][0],
                'proxyPass' => $info['proxy_ip']['proxy_auth'][1],
                'keyword' => $params['charge_account']
            ];

            $result_json = CommonHelper::curlRequest($request_url, json_encode($request_data, JSON_UNESCAPED_UNICODE), ['Content-Type:application/json']);
            $result_array = json_decode($result_json, true);
            LogHelper::write([$request_url, $request_data, $result_json, $result_array]);

            if (!isset($result_array['code']) || $result_array['code'] != 200 || !isset($result_array['data']['dataJson']))
                throw new \Exception($result_array['errmsg'] ?? '登录失败');

            $params['cookie'] = json_decode($params['cookie'], true);
            $params['cookie']['user_info'] = $result_array['data']['dataJson']['userList'][0];
            db()->name('receiving_account')
                ->where('id', $params['id'])
                ->update([
                    'charge_account_name' => $result_array['data']['dataJson']['userList'][0]['userNo'],
                    'cookie' => json_encode($params['cookie'], JSON_UNESCAPED_UNICODE)
                ]);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
        return true;
    }

    public static function fxshTransfer($params, $extra_params){
        try {
            $params['proxy_ip'] = json_decode($params['proxy_ip'], true);
            $params['cookie'] = json_decode($params['cookie'], true);

            $api_url = db()->name('pay_api')
                ->where('api_code', $params['receiving_account_code'])
                ->value('api_url');

            $public_params = [
                'ck' => $params['cookie']['ck'],
                'deviceNo' => $params['cookie']['deviceNo'],
                'ip' => $params['proxy_ip']['proxy_ip'],
                'proxyUser' => $params['proxy_ip']['proxy_auth'][0],
                'proxyPass' => $params['proxy_ip']['proxy_auth'][1],
            ];

            $request_prams = array_merge($public_params, [
                'keyword' => $extra_params['user_no']
            ]);
            $result_json = CommonHelper::curlRequest($api_url.'queryUser', json_encode($request_prams, JSON_UNESCAPED_UNICODE), ['Content-Type:application/json']);
            $result_array = json_decode($result_json, true);
            LogHelper::write([$request_prams, $result_json, $result_array], '', 'queryUser');
            if (!isset($result_array['code']) || $result_array['code'] != 200 || !isset($result_array['data']['dataJson']))
                throw new \Exception($result_array['errmsg'] ?? '查询用户名失败');

            $request_prams = array_merge($public_params, [
                'amount' => intval($extra_params['amount'] * 100),
                'UserNo' => $result_array['data']['dataJson']['userList'][0]['userNo']
            ]);
            $result_json = CommonHelper::curlRequest($api_url.'pay1', json_encode($request_prams, JSON_UNESCAPED_UNICODE), ['Content-Type:application/json']);
            $result_array = json_decode($result_json, true);
            LogHelper::write([$request_prams, $result_json, $result_array], '', 'pay1');
            if (!isset($result_array['code']) || $result_array['code'] != 200 || !isset($result_array['data']['dataJson']))
                throw new \Exception($result_array['errmsg'] ?? '发起转账失败');

            $request_prams = array_merge($public_params, [
               'orderNo' => $result_array['data']['dataJson']['orderNo'],
                'payPwd' => $params['cookie']['pay_password']
            ]);
            $result_json = CommonHelper::curlRequest($api_url.'confirm', json_encode($request_prams, JSON_UNESCAPED_UNICODE), ['Content-Type:application/json']);
            $result_array = json_decode($result_json, true);
            LogHelper::write([$request_prams, $result_json, $result_array], '', 'confirm');
            if (!isset($result_array['code']) || $result_array['code'] != 200 || !isset($result_array['data']['dataJson']))
                throw new \Exception($result_array['errmsg'] ?? '发起转账失败');

            if (!isset($result_array['data']['dataJson']['type']) || $result_array['data']['dataJson']['type'] != null){
                if (isset($result_array['data']['dataJson']['riskMsg'])){
                    throw new \Exception($result_array['data']['dataJson']['riskMsg'] . '_' . $result_array['data']['dataJson']['type']);
                }
                throw new \Exception('发起转账失败');
            }

//            {"ck":"","amount":"","deviceNo":"","UserNo":"","ip":"","proxyUser":"","proxyPass":"."}

            dump($result_array);die;
        }catch (\Exception $e){
            throw new ValidateException($e->getMessage());
        }
    }
}