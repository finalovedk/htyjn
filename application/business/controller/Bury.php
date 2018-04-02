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
 * 公墓管理
 * Class Cemetery
 * @package app\admin\controller
 * @author ding <dingzhangze@163.com>
 * @date 2017/08/09
 */
class Bury extends BasicBusiness
{


    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->user = session('b_user');
        $this->uid = $this->user['create_by'];
    }
    /**
     * 指定当前数据表
     * @var string
     */
    protected $table = 'cemetery_bury';
    /**
     * 安葬列表
     */
    public function index()
    {
        $this->title = '安葬管理';

        // 获取到所有GET参数
        $get = $this->request->get();

        $db = Db::name($this->table)->where('create_by',$this->uid)->order('id desc');
        // 应用搜索条件
        foreach (['order_sn', 'cemetery_sn','member_name'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        // 实例化并显示
        $data = parent::_list($db, true, false);
        foreach ($data['list'] as $key => $list) {
            $data['list'][$key]['order_state'] = Db::name('business_finance')
                ->where('id',$list['finance_id'])->value('order_state');
            $data['list'][$key]['opera_name'] = Db::name('business_staff')->where('id', $list['opera'])->value('name');
            $data['list'][$key]['opera_phone'] = Db::name('business_staff')->where('id', $list['opera'])->value('phone');
        }
        return $this->fetch('', $data);
    }

    /**
     * 删除安葬订单
     */
    public function delBury()
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }

    /**
     * 订单详情
     * @return mixed
     */
    public function showBury()
    {

        $this->assign('title', '订单详情');
        $orderId = $this->request->param('id');
        //安葬数据
        $orderInfo = Db::name($this->table)->where('id', $orderId)->find();
        $opera = DB::table('tp_business_staff')->where('id',$orderInfo['opera'])->find();
        //墓穴订单数据
        $ceOrderInfo = DB::table('tp_cemetery_order')->where('order_sn',$orderInfo['order_sn'])->find();
        //墓穴类型（单/双）
        $type = DB::table('tp_cemetery_sn')->where('cemetery_sn',$orderInfo['cemetery_sn'])->value('type');
        //逝者数据
        $deaderInfo1 = DB::table('tp_storage_dead')->where('id',$ceOrderInfo['deader_id_1'])->find();
        if($type == 2){
            $deaderInfo2 = DB::table('tp_storage_dead')->where('id',$ceOrderInfo['deader_id_2'])->find();
            $this->assign('deaderInfo2', $deaderInfo2);
        }
        // print_r($opera);die;
        $this->assign('deaderInfo1', $deaderInfo1);
        $this->assign('opera', $opera);
        $this->assign('orderInfo', $orderInfo);
        $this->assign('ceOrderInfo', $ceOrderInfo);
        $this->assign('Type',$type);
        return $this->fetch('showbury');
    }
}
