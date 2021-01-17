<?php

namespace M\Controller;
header("Content-type: text/html; charset=utf-8");

use Clas\Shop;
use Think\Controller;
use Clas\Agent;
use Clas\User;

class AgentController extends CommonController
{

    protected $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->user_id = session('user_id');
    }

    /**
     * 注册成为代理商
     */
    public function addAgent($data = null)
    {
        if (!$data) {
            $data = I();
        }
        $obj = new Agent($data['shop_id']);
        $ok = $obj->add($data);
        if (is_numeric($ok)) {
            jsonout("申请已提交，请等待审核", 0);
        } else {
            jsonout($ok);
        }
    }

    /**
     * 代理商信息
     */
    public function index($data = null)
    {
        if (!$data) {
            $data = I();
        }
        $Agentout = new Agent($data);
        $data = $Agentout->agentInfo($data);
        $where['id'] = $data['user_id'];
        $user = get_user_info($where);
        $this->assign('agentInfo', $data);
        $this->assign('user', $user);
        $this->display();
    }

    /**
     * 提现  moneyOutNotice
     */
    public function moneyout($data = null)
    {

        $user = M('user')->find($this->user_id);

        if (IS_POST) {
            $data = I();
            $data['user_id'] = $this->user_id;
            $obj = new User($this->user_id);
            $ok = $obj->Tixian($data);
            if (is_array($ok)) {
                jsonout($ok[0], $ok[1], $ok[2]);
            }
            jsonout($ok);
        }
        $bank_id = I("bank_id", 0);
        $bank = M('user_bank')->find($bank_id);
        $agent = M('agent')->where('user_id=' . $this->user_id)->find();
        if (empty($user['pay_password'])) {
            $is_psw = 0;
        } else {
            $is_psw = 1;
        }

        $this->assign('user_id', $this->user_id);
        $this->assign('bank', $bank);
        $this->assign('is_psw', $is_psw);
        $this->assign('user', $user);
        $this->assign('agent', $agent);
        $this->display();;

    }


    //代理信息
    public function info()
    {
        $agent = M('agent')->where('user_id=' . $this->user_id)->find();
        $feeInfo = get_config_shop_fee($agent['shop_fee_id']);
        $agent["shop_fee_name"] = "服务费" . $feeInfo['percent1'] . "%";
        $this->assign('agent', $agent);
        $this->display();
    }

    // 额度转换
    public function swap()
    {
        if (IS_POST) {

            $money = I("money", 0);
            if ($money <= 0) {
                $this->error("请输入转换额度");
            }

            $where['user_id'] = $this->user_id;
            $shopInfo = get_user_info($where, '', '', 3);
            if (!$shopInfo || $shopInfo['status'] != 1) {
                $this->error("代理信息异常，请重新登录");
            }
            if ($shopInfo['money'] <= 0) {
                $this->error("账户余额不足");
            }
            if ($shopInfo['money'] < $money) {
                $this->error("账户额度小于转换额度，请修改后转换");
            }
            $x_shop = get_config_project("x_agent"); //
            if ($x_shop <= 0) {
                $this->error("转换比例设置异常，请联系客服");
            }
            // 减少账户余额，增加余额额度（此额度要缩小相应的比例）
            $shop_money = round($money / $x_shop, 2);
            if ($shop_money <= 0) {
                $this->error("转换后额度<=0，请重新转换");
            }
            $model = M();
            $model->startTrans();

            $rs1 = change_money_log($this->user_id, 1, "money", 0, $money, 10, 6, "账户余额转换余额", 2, 3, 1);
            $rs2 = change_money_log($this->user_id, 1, "agent_money", 0, $shop_money, 3, 6, "账户余额转换到余额", 1, 3, 1);

            if ($rs1 && $rs2) {
                $model->commit();
                $this->success("转换完成", 0);
            } else {
                $model->rollback();
                $this->error("转换失败，请重新转换");
            }
        } else {
            $this->display();
        }
    }

    //申请代理
    public function applyagent()
    {
        if (IS_POST) {
            $data = I();

            $obj = new Agent($data['shop_id']);
            $ok = $obj->add($data);
            if (is_numeric($ok)) {
                $this->success("申请已提交，请等待审核");
            } else {
                $this->error($ok);
            }
        } else {
            $user_id = $this->user_id;
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }  //申请代理

    public function xieyi()
    {
        $arr = M('article')->where('title="代理商注册协议"')->find();
        $this->assign('data', $arr);
        $this->display();
    }

    /**
     * Notes:旗下商家
     * User: WangSong
     * Date: 2020/8/6
     * Time: 15:39
     */
    public function shops(){
        $data = I();
        $page = I('page',1);
        $paging['page'] = $page;
        $data['user_id'] = session('user_id');
        $Agentout = new Agent($data);
        $agent = $Agentout->agentInfo($data);
        if($agent['district_id']){
            $da['city_id'] = $agent['district_id'];
        }elseif ($agent['city_id']){
            $da['city_id'] = $agent['city_id'];
        }else{
            $da['city_id'] = $agent['province_id'];
        }

        $shop = new Shop(0, 'shop', $data['user_id']);
        $shop_list = $shop->shop($da,10,$paging);
        $where['city_id|district_id'] = $da['city_id'];
        if ($page < 2)
        {
            $numArr = $shop->getNums($where,10);
        }
        $this->assign('shop_list',$shop_list);
        $this->assign('max_page',$numArr['max_page']);
        $this->assign('total',$numArr['total']);
        if ($page > 1)
        {
            $this->display('shops_load_more');
        }
        else
        {
            $this->display();
        }
    }
}