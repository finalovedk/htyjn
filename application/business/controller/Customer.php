<?php
// +----------------------------------------------------------------------
// | Ht.Memorial
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2017 http://www.yn123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ding <dingzhangze@163.com>
// +----------------------------------------------------------------------

namespace app\business\controller;

use think\Db;
use app\business\service\DataService;
use app\business\service\ToolsService;
/**
 * 客户管理
 * Class Cemetery
 * @package app\admin\controller
 * @author ding <dingzhangze@163.com>
 * @date 2017/06/01
 */
class Customer extends BasicBusiness
{

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->user = session('b_user');
        $this->uid=$this->user['create_by'];
    }

    /**
     * 指定当前数据表
     * @var string
     */
    protected $table = 'Customer';
    protected $tableVisit='CustomerVisit';

    /*
     * 客户列表
     */
    public function index()
    {
        // 设置页面标题
        $this->title = '客户管理';
        // 获取到所有GET参数
        $get = $this->request->get();
        // 实例Query对象
        $map=[
            'is_deleted'=>0,
            'create_by'=>$this->uid
        ];
        $db = Db::name($this->table)->where($map);
        // 应用搜索条件
        foreach (['username', 'phone'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        // 实例化并显示
        parent::_list($db);
    }

    /*
     * 客户添加
     */
    public function add()
    {

        return $this->_form($this->table, 'form');
    }

    protected function _add_form_filter(&$data)
    {

        if ($this->request->isPost()) {

            $data['password'] = admin_md5($this->request->param('password'));

//            if (isset($data['id'])) {
//                unset($data['username']);
//            } elseif (Db::name($this->table)->where('username', $data['username'])->find()) {
//                $this->error('用户账号已经存在，请使用其它账号！');
//            }
        }
    }

    /**
     * 客户编辑
     */
    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 用户密码修改
     */
    public function pass()
    {
        if ($this->request->isGet()) {
            $this->assign('verify', false);
            return $this->_form($this->table, 'pass');
        }
        $data = $this->request->post();
        if ($data['password'] !== $data['repassword']) {
            $this->error('两次输入的密码不一致！');
        }
//        if (DataService::save($this->table, ['id' => $data['id'], 'password' => md5($data['password'])], 'id')) {
        // 密码加密规则增强
        if (DataService::save($this->table, ['id' => $data['id'], 'password' => admin_md5($data['password'])])) {
            $this->success('密码修改成功，下次请使用新密码登录！', '');
        }
        $this->error('密码修改失败，请稍候再试！');
    }


    /**
     * 删除用户
     */
    public function del() {
        if (DataService::update($this->table)) {
            $this->success("用户删除成功！", '');
        }
        $this->error("用户删除失败，请稍候再试！");
    }

    /**
     * 用户禁用
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("用户禁用成功！", '');
        }
        $this->error("用户禁用失败，请稍候再试！");
    }

    /**
     * 用户启用
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("用户启用成功！", '');
        }
        $this->error("用户启用失败，请稍候再试！");
    }

    /*
     * 添加客户回访
     */
    public function addvisit()
    {
        $retitle=Db::name('cemetery_title')->where('create_by',$this->uid)->select();
        $this->assign('retitle',$retitle);
        return $this->_form($this->table, 'addvisit');
    }

    protected function _addvisit_form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            $vo['uid']=$vo['id'];
            unset($vo['id']);
            $res=Db::name($this->tableVisit)->insert($vo);
            $res !== false ? $this->success('恭喜，保存成功哦！', url('/').'business.html#'.url('customer/index').'?spm='.$this->spm) : $this->error('保存失败，请稍候再试！');
        }
    }
    /*
     * 客户回访
     */
    public function cvisit()
    {
        // 设置页面标题
        $this->title = '客户回访';
        // 获取到所有GET参数
        $get = $this->request->get();
        $map=[
            'is_deleted'=>0,
            'uid'=>$get['id']
        ];
        // 实例Query对象
        $db = Db::name($this->tableVisit)->where($map);
        // 应用搜索条件
//        foreach (['username', 'phone'] as $key) {
//            if (isset($get[$key]) && $get[$key] !== '') {
//                $db->where($key, 'like', "%{$get[$key]}%");
//            }
//        }
        // 实例化并显示
        parent::_list($db);
    }

    /**
     * 删除回访记录
     */
    public function delvisit() {
        if (DataService::update($this->tableVisit)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }

    /**
     * 编辑回访记录
     */
    public function editvisit()
    {
        return $this->_form($this->tableVisit, 'addvisit');
    }
}
