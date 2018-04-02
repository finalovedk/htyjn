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

use app\admin\service\DataService;
use app\admin\service\ToolsService;
use think\Db;

/**
 * 图片管理
 * Class Article
 * @package app\business\controller
 * @author ding <dingzhangze@163.com>
 * @date 2017/7/25
 */
class Image extends BasicBusiness
{

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->user = session('b_user');
        $this->uid = $this->user['create_by'];
    }

    /**
     * 相册表
     * @var string
     */
    protected $table = 'grave_photo';

    /**
     * 相册管理
     */
    public function index()
    {
        $this->title='相册管理';
        $Map=[
            'create_by'=>$this->uid,
            'is_deleted'=>0
        ];
        $db = Db::name($this->table)->where($Map)->order('id asc');
        parent::_list($db, false);
    }

    /**
     * 添加相册
     */
    public function add()
    {
        $this->title='添加相册';
        // 获取陵园
        $menus = $this->_getGrave();
        $this->assign('menus', $menus);
        return $this->_form($this->table,'form');
    }

    /**
     * 获取陵园 辅助方法
     * @return array
     */
    private function _getGrave() {
        $map=[
            'pid'=>0,
            'is_deleted'=>0,
            'create_by'=>$this->uid
        ];
        $_menus = Db::name('CemeteryTomb')->where($map)->order('sort desc,id desc')->select();
        $menus = ToolsService::arr2table($_menus);
        foreach ($menus as $key => &$menu) {
            if (substr_count($menu['path'], '-') > 3) {
                unset($menus[$key]);
                continue;
            }
            if (isset($vo['pid'])) {
                $current_path = "-{$vo['pid']}-{$vo['id']}";
                if ($vo['pid'] !== '' && (stripos("{$menu['path']}-", "{$current_path}-") !== false || $menu['path'] === $current_path)) {
                    unset($menus[$key]);
                }
            }
        }

        return $menus;
    }


    /**
     * 编辑相册
     */
    public function edit()
    {
        $this->title='编辑相册';
        return $this->add();
    }

    /**
     * 相册删除
     */
    public function delPhoto()
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }

    /**
     * 相册照片列表
     */
    public function picture()
    {
        $list = Db::name('GravePhotoPic')->where('photo_id',$this->request->get('id'))->select();
        $html = '';
        foreach ($list as $key => $value){
            $html .= '<div id="img_'.$value['id'].'">';
            $html .= '<img data-tips-image src="'.$value['photo_picture_url'].'"/>';
            $html .= '<br><a href="JavaScript:void(0)" onclick="del_img('.$value['id'].')">删除</a>';
            $html .= ' </div>';
        }
        return $this->fetch('',['id'=>$this->request->get('id'),'html'=>$html]);
    }

    /**
     * 相册照片添加
     */
    public function add_photo()
    {
        $html = '';
        foreach (explode("|",input('value')) as $key => $value){
            if ($result = Db::name('GravePhotoPic')->insert(['photo_id'=>input('id'),'photo_picture_url'=>$value],$replace = false, $getLastInsID = true)) {
                $html .= '<div id="img_'.$result.'">';
                $html .= '<img data-tips-image src="'.$value.'"/>';
                $html .= '<br><a href="JavaScript:void(0)" onclick="del_img('.$result.')">删除</a>';
                $html .= ' </div>';
            }
        }
        if ($html){
            die($html);
        } else {
            die('0');
        }
    }

    /**
     * 相册照片删除
     */
    public function DelPicture()
    {
        $table = "HallPhotoPicture";
        if (DataService::update($table)) {
            die('删除成功！');
        }
        die("删除失败，请稍候再试！");
    }

    /**
     * 删除相册
     */
    public function del() {
        if (DataService::update($this->table)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }
}