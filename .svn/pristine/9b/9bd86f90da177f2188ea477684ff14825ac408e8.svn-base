<?php
namespace Api\Controller;
use Clas\Agent;

class AgentinController extends CommonController {

	public function __construct() {
		parent::__construct();
	}

	// 添加
	public function addAgent($data=null)
	{
		if (!$data)
		{
			$data = I();
		}
		$obj = new Agent($data['shop_id']);
		$ok = $obj->add($data);
		if (is_numeric($ok)) 
		{
			jsonout("申请已提交，请等待审核", 0);
		}
		else
		{
			jsonout($ok);
		}
		
	}

	// 删除
	public function delAgent($data=null){
		if (!$data)
		{
			$data = I();
		}
		$obj = new Agent($data['shop_id']);
		$ok = $obj->delAgent($data);
		jsonout($ok);
	}

	/**
	 * 代理 账户余额 转换到 余额
	 * 按比例处理
	 * @param int       $user_id        代理UID
	 * @param int       $money          要转换的额度
	 * @return  json
	 * @zd
	 */
	public function m2am () 
	{
		$money = I("money", 0);
		if ($money <= 0) 
		{
			jsonout("请输入转换额度");
		}

		$where['user_id'] = $this->user_id;
		$shopInfo = get_user_info($where, '', '', 3);
		if (!$shopInfo || $shopInfo['status'] != 1) 
		{
			jsonout("代理信息异常，请重新登录");
		}
		if ($shopInfo['money'] <= 0) 
		{
			jsonout("账户余额不足");
		}
		if ($shopInfo['money'] < $money) 
		{
			jsonout("账户额度小于转换额度，请修改后转换");
		}
		$x_shop = get_config_project("x_agent"); // 
		if ($x_shop <= 0) 
		{
			jsonout("转换比例设置异常，请联系客服");
		}
		// 减少账户余额，增加余额额度（此额度要缩小相应的比例）
		$shop_money = round($money/$x_shop, 2);
		if ($shop_money <= 0) 
		{
			jsonout("转换后额度<=0，请重新转换");
		}
		$model = M();
		$model->startTrans();

		$rs1 = change_money_log($this->user_id, 1, "money", 0, $money, 10, 6, "账户余额转换余额", 2, 3, 1);
		$rs2 = change_money_log($this->user_id, 1, "agent_money", 0, $shop_money, 3, 6, "账户余额转换到余额", 1, 3, 1);
		
		if ($rs1 && $rs2) 
		{
			$model->commit();
			jsonout("转换完成", 0);
		}
		else
		{
			$model->rollback();
			jsonout("转换失败，请重新转换");
		}
	}

	



}