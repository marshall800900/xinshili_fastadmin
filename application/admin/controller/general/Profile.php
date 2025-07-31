<?php

namespace app\admin\controller\general;

use app\admin\common\library\AdminHelper;
use app\admin\common\library\DataEncryptHelper;
use app\admin\common\library\GoogleAuthHelper;
use app\admin\model\Admin;
use app\common\controller\Backend;
use fast\Random;
use think\Session;
use think\Validate;

/**
 * 个人配置
 *
 * @icon fa fa-user
 */
class Profile extends Backend
{

    protected $searchFields = 'id,title';

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            $this->model = model('AdminLog');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->where('admin_id', $this->auth->id)
                ->order($sort, $order)
                ->paginate($limit);

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        $is_bind_google = empty(session('admin.google_secret')) ? 0 : 1;
        $this->view->assign('is_bind_google', $is_bind_google);
        $this->view->assign('is_merchant', session('admin.type') == AdminHelper::MERCHANT ? 1 : 0);

        return $this->view->fetch();
    }

    public function apiDoc()
    {
        try {
//            if (!session('admin.google_secret'))
//                throw new \Exception('请先绑定谷歌');

            $product_list = db()->name('merchant_pay_product')
                ->field('product_code, rate')
                ->where('merchant_id', session('admin.id'))
                ->where('is_open', '1')
                ->select();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->view->assign('md5_key', DataEncryptHelper::decrypt(session('admin.md5_key')));
        $this->view->assign('merchant_id', session('admin.id'));
        $this->view->assign('api_url', config('site.api_url'));
        $this->view->assign('product_list', $product_list);
        return $this->view->fetch();
    }

    /**
     * 绑定谷歌
     * @return string
     * @throws \think\Exception
     */
    public function bindGoogle()
    {
        if (session('admin.google_secret'))
            $this->error('已绑定谷歌');

        $google_obj = new GoogleAuthHelper();
        if ($this->request->isPost()) {
            $this->token();
            $params = input('row/a');
            if (!$google_obj->verifyCode($params['google_secret'], $params['google_auth_code']))
                $this->error('谷歌验证码验证失败');

            $google_secret = DataEncryptHelper::encrypt($params['google_secret']);

            db()->name('admin')
                ->where('id', session('admin.id'))
                ->update([
                    'google_secret' => $google_secret
                ]);
            Session::set("admin.google_secret", $google_secret);

            return $this->success();
        }
        $google_secret = $google_obj->createSecret(32);
        $qr_code_url = $google_obj->getQRcodeContent(config('site.name') . '-' . session('admin.username'), $google_secret); //第一个参数是"标识",第二个参数为"安全密匙SecretKey" 生成二维码信息
        $this->view->assign([
            'qr_code_url' => $qr_code_url,
            'google_secret' => $google_secret
        ]);
        return $this->view->fetch();
    }

    /**
     * 更新个人信息
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            $params = array_filter(array_intersect_key(
                $params,
                array_flip(array('email', 'nickname', 'password', 'avatar'))
            ));
            unset($v);
            if (!Validate::is($params['email'], "email")) {
                $this->error(__("Please input correct email"));
            }
            if (isset($params['password'])) {
                if (!Validate::is($params['password'], "/^[\S]{6,30}$/")) {
                    $this->error(__("Please input correct password"));
                }
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
            }
            $exist = Admin::where('email', $params['email'])->where('id', '<>', $this->auth->id)->find();
            if ($exist) {
                $this->error(__("Email already exists"));
            }
            if ($params) {
                $admin = Admin::get($this->auth->id);
                $admin->save($params);
                //因为个人资料面板读取的Session显示，修改自己资料后同时更新Session
                Session::set("admin", $admin->toArray());
                Session::set("admin.safecode", $this->auth->getEncryptSafecode($admin));
                $this->success();
            }
            $this->error();
        }
        return;
    }
}
