<?php
namespace Api\Controller;
use Clas\User;
class FavoritesoutController extends CommonController {

	/**
	 * 收藏列表
	 * @param   int      	$user_id   	用户ID
	 * @param   int      	$type   	收藏类型 1商品 2商家
	 * @return  string
	 * @zd
	 */
	public function favorites()
	{
		$obj = new User();
		$result = $obj->favorites();
		if ($result['err'] == 0 && !$result['result'])
		{
			jsonout('暂无收藏记录');
		}
		jsonout($result['msg'], $result['err'], $result['result']);
	}
	
}