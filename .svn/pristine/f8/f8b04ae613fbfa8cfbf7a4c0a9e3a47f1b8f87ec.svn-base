<?php
namespace Api\Controller;
use Clas\User;

class LiveoutController extends CommonController
{

	/**
	 * 获取微信直播的直播列表
	 * @param   int $user_id 	用户ID
	 * @return  json
	 * @zd
	 */
	public function index()
	{
		$userId = I("user_id", 0);
		$force = I("force", 0);     // 是否强制刷新，1是，0否
		if ($force == 0)
		{
			// 因为有每天限额500次的更新，所以默认每次获取数据的时间间隔是5分钟。可以强制更新
			// 如果不强制更新，那么看看最后更新时间是不是1分钟之前
			// 如果是，则可以更新，如果不是，则读取原有数据。
			$liveInfo = M("live_room")->where("is_del=0")->order("get_time desc")->find();
			if (time()-$liveInfo['get_time'] < 100 && $liveInfo)
			{
				$where_live['is_del'] = 0;
				// $where_live['live_status'] = array("in", array(101,102,105));     // 101: 直播中, 102: 未开始, 105: 暂停中
				$list = M("live_room")->where($where_live)->order("id desc")->select();
				if (!$list)
				{
					jsonout("加载完成", 0);
				}
				else
				{
					jsonout("直播列表", 0, $list);
				}
			}
		}

		// 这里是强制更新流程
		$token = $this->getToken();
		if (!$token)
		{
			jsonout("Token获取失败，请联系客服");
		}
		$url = "https://api.weixin.qq.com/wxa/business/getliveinfo?access_token=".$token;
		$para['start'] = 0;
		$para['limit'] = 100;
		$para = json_encode($para);
		$list = httpRequest($url, "post", $para);
		$list = json_decode($list, true);
		if ($list['errcode'] !=0)
		{
			jsonout($list['errmsg']);
		}
		$list = $list['room_info'];
		foreach ($list as $key=>$value)
		{
			$data = array();
			$data['name'] = $value['name'];
			$data['roomid'] = $value['roomid'];
			$data['cover_img'] = $value['cover_img'];
			$data['live_status'] = $value['live_status'];
			$data['live_status_cn'] = getLiveStatus($value['live_status']);
			$data['anchor_name'] = $value['anchor_name'];
			$data['share_img'] = $value['share_img'];
			$data['start_time'] = $value['start_time'];
			$data['end_time'] = $value['end_time'];
			$data['goods'] = json_encode($value['goods']);

			if ($value['roomid'])
			{
				$have = M("live_room")->where("roomid=".$value['roomid'])->find();
				if ($have)
				{
					$data['up_time'] = time();
					M("live_room")->where("roomid=".$value['roomid'])->save($data);
				}
				else
				{
					$data['cre_time'] = time();
					M("live_room")->add($data);
				}
			}
			else
			{
				$data['cre_time'] = time();
				M("live_room")->add($data);
			}
		}
		$dataGTM['get_time'] = time();
		M("live_room")->where("id>0")->save($dataGTM);  // 统一更新获取时间
		$where_live['is_del'] = 0;
		// $where_live['live_status'] = array("in", array(101,102,105));     // 101: 直播中, 102: 未开始, 105: 暂停中
		$list = M("live_room")->where($where_live)->order("start_time desc")->select();
		if (!$list)
		{
			jsonout("暂无直播信息", 0);
		}
		else
		{
			jsonout("直播列表", 0, $list);
		}


	}

	/**
	 * 获取小程序调用凭据Token
	 * @param   int $user_id 	用户ID
	 * @return  json
	 * @zd
	 */
	public function getToken()
	{
		$configInfo = get_config_pay3(4);
		if (!$configInfo['appid'] || !$configInfo['key2'])
		{
			jsonout('小程序配置异常，请联系客服处理', 1);
		}
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$configInfo['appid']."&secret=".$configInfo['key2'];
		$tokenInfo = file_get_contents($url);
		$tokenInfo = json_decode($tokenInfo, true);
		if ($tokenInfo['errcode'] && $tokenInfo['errcode'] != 0)
		{
			return "";
		}
		$token = $tokenInfo['access_token'];
		return $token;

	}
    
}