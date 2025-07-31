<?php

namespace app\admin\common\library;

class CommonHelper
{
    /**
     * 生成俩位小数点数字
     * @param $value
     * @return string
     */
    public static function buildFloatValue($value)
    {
        if ($value === null || empty($value))
            $value = 0;


        $value = number_format($value, 2, '.', '');

        return $value;
    }

    /**
     * 请求中间件
     * @param $data
     * @param $method
     * @return mixed
     * @throws \Exception
     */
    public static function requestBackend($data, $method)
    {
        try {
            $content = DataEncryptHelper::encrypt($data);
            $request_url = config('site.admin_api_url') . '?content=' . urlencode($content) . '&method=' . $method;

            $result_json = file_get_contents($request_url);
            $result_array = json_decode($result_json, true);

            if (!isset($result_array['code']) || $result_array['code'] != 0)
                throw new \Exception($result_array['msg'] ?? '请求失败');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $result_array['data'];
    }

    /**
     * curl请求
     * @param $url
     * @param $data
     * @param array $header
     * @param string $method
     * @param int $verify_ssl
     * @param string $proxy_ip
     * @return bool|string
     */
    public static function curlRequest($url, $data, $header = [], $method = 'post', $verify_ssl = 0, $proxy_ip = '', $out_time = 30)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $out_time);

        if (!$verify_ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        //post请求
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);            //使用post请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  //提交数据
        }

        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        if ($proxy_ip) {
            if (is_array($proxy_ip)){
                curl_setopt($ch, CURLOPT_PROXY, $proxy_ip['proxy_ip']);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_ip['proxy_auth']);
            }else{
                curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
            }
        }

        $result = curl_exec($ch); //得到返回值
        $err_msg = curl_error($ch);

        curl_close($ch);          //关闭
        unset($ch);
        return $result ? $result : $err_msg;
    }
}