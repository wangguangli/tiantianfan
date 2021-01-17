<?php
namespace Api\Controller;
use Clas\User;

class LiveinController extends CommonController
{

	/**
	 * 更新直播状态
	 * @param   int $roomid 	直播间ID
	 * @param   int $user_id 	用户ID
	 * @return  json
	 * @zd
	 */
	public function updateStatus()
	{
		$userId = I("user_id", 0);
		$roomid = I("roomid", 0);
		$live_status = I("live_status", 0);

		$data['live_status'] = $live_status;
		$data['live_status_cn'] = getLiveStatus($live_status);
		$where['roomid'] = $roomid;     // 101: 直播中, 102: 未开始, 105: 暂停中
		$rs = M("live_room")->where($where)->save($data);
		if ($rs !== false)
		{
			jsonout("更新成功", 0);
		}
		else
		{
			jsonout("更新失败", 0);
		}


	}
    
}