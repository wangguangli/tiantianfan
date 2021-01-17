<?php
namespace Api\Controller;
use Clas\Agent;

class AgentoutController extends CommonController {

	public function __construct() {
		parent::__construct();
	}

	// 代理商列表
	public function AgentList($data=null)
	{
		if (!$data)
		{
			$data = I();
		}
		$Agentout = new Agent($data['agent_id']);
		$data = $Agentout->agentList($data);
		jsonout('代理商列表', 0, $data);
	}

	// 代理信息 -- 一个用户可能是多个区域的代理，那么把多个代理区域整合在一起输出
	public function agentInfo($data=null)
	{
		if (!$data)
		{
			$data = I();
		}
		$Agentout = new Agent($data['agent_id']);
		$data = $Agentout->agentInfo($data);
		if ($data) 
		{
			jsonout('代理商信息', 0, $data);
		}
		else
		{
			jsonout('代理商信息获取失败', 1);
		}
	}


}