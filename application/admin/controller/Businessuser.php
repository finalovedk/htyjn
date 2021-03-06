<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\service\DataService;
use think\Db;

/**
 * 系统用户管理控制器
 * Class BusinessUser
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:12
 */
class Businessuser extends BasicAdmin {

    /**
     * 指定当前数据表
     * @var string
     */
    protected $table = 'BusinessCompany';

    /**
     * 用户列表
     */
    public function index() {
        // 设置页面标题
        $this->title = '公墓系统商家管理';
        // 获取到所有GET参数
        $get = $this->request->get();
        // 实例Query对象
        $db = Db::name($this->table)->where('is_deleted', '0');
        // 应用搜索条件
        foreach (['username', 'phone'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }

        // 分类名称
        $data = parent::_list($db, true, false);
        foreach ($data['list'] as $key => $list) {
            $data['list'][$key]['cemetery_name'] = Db::name('cemetery_tomb')->where(['create_by'=> $list['id'],'pid'=>0])->value('name');
        }
        // 获取商品分类
        return $this->fetch('', $data);
    }

    /**
     * 授权管理
     * @return array|string
     */
    public function auth()
    {
        return $this->_form($this->table, 'auth');
    }

    /**
     * 授权管理表单数据默认处理
     * @param array $data
     */
    public function _auth_form_filter(&$data)
    {
        if ($this->request->isPost()) {
            Db::name($this->table)->update($data);
           Db::name('BusinessStaff')->where('create_by',$data['id'])->update(['authorize'=>$data['authorize']]);
        }
    }

    /**
     * 用户添加
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 用户添加表单数据默认处理
     * @param array $data
     */
    public function _add_form_filter(&$data) {
        if ($this->request->isPost()) {
            $result=input('post.');
            if ($data['password'] !== $data['repassword']) {
                $this->error('两次输入的密码不一致！');
            }
            unset($result['repassword']);
            $result['password'] = admin_md5($data['password']);
            $ret=Db::name($this->table)->insertGetId($result);
            unset($result['unit_name'],$result['address'],$result['repassword']);
            $result['create_by']=$ret;
            $res=Db::name('BusinessStaff')->insert($result);
            $res &&$ret!== false ? $this->success('恭喜，保存成功哦！', url('/').'admin.html#'.url('businessuser/index').'?spm='.$this->spm) : $this->error('保存失败，请稍候再试！');
        }
    }

    /**
     * 用户编辑
     */
    public function edit() {
        return $this->_form($this->table, 'form');
    }

    /**
     * 用户密码修改
     */
    public function pass() {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        if ($this->request->isGet()) {
            $this->assign('verify', false);
            return $this->_form($this->table, 'pass');
        }
        $data = $this->request->post();
        if ($data['password'] !== $data['repassword']) {
            $this->error('两次输入的密码不一致！');
        }
        if (DataService::save($this->table, ['id' => $data['id'], 'password' => admin_md5($data['password'])])) {
            $this->success('密码修改成功，下次请使用新密码登录！', '');
        }
        $this->error('密码修改失败，请稍候再试！');
    }

    /**
     * 表单数据默认处理
     * @param array $data
     */
    public function _form_filter(&$data) {
        if ($this->request->isPost()) {
            if (isset($data['authorize']) && is_array($data['authorize'])) {
                $data['authorize'] = join(',', $data['authorize']);
            }
            if (isset($data['id'])) {
                unset($data['username']);
            } elseif (Db::name($this->table)->where('username', $data['username'])->find()) {
                $this->error('用户账号已经存在，请使用其它账号！');
            }
        } else {
            $data['authorize'] = explode(',', isset($data['authorize']) ? $data['authorize'] : '');
            $this->assign('authorizes', Db::name('BusinessAuth')->where('create_by',0)->select());
        }
    }

    /**
     * 删除用户
     */
    public function del() {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止删除！');
        }
        if (DataService::update($this->table)) {
            $this->success("用户删除成功！", '');
        }
        $this->error("用户删除失败，请稍候再试！");
    }

    /**
     * 用户禁用
     */
    public function forbid() {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        if (DataService::update($this->table)) {
            $this->success("用户禁用成功！", '');
        }
        $this->error("用户禁用失败，请稍候再试！");
    }

    /**
     * 用户启用
     */
    public function resume() {
        if (DataService::update($this->table)) {
            $this->success("用户启用成功！", '');
        }
        $this->error("用户启用失败，请稍候再试！");
    }

}
