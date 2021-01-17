<?php
namespace Api\Controller;
use Clas\User;
class FavoritesinController extends CommonController {

	/**
	 * 添加收藏（商品或商家）
	 * @param   int 	$user_id 	分页数量
	 * @param   int 	$fav_id 	商品ID或商家UID
	 * @param   int 	$type 		1商品 2商家
	 * @return  array 				成功或失败
	 * @zd
	 */
	public function addFavorites()
	{
		$obj = new User();
		$data = I();
		$result = $obj->addFavorites($data);
		jsonout($result[0],$result[1]);
	}

	/**
	 * 删除收藏
	 * @param   int      	$user_id   	用户ID
	 * @param   int      	$type   	收藏类型 1商品 2商家
	 * @param   int      	$source   	来源，1商品界面，2商家界面，3收藏列表
	 * @param   int      	$id 		当source=1时，ID是商品ID，当source=2时，ID是商家user_id，当source=3时，ID是本记录的ID
	 * @return  string
	 * @zd
	 */
	public function deleteFavorites()
	{
		$obj = new User();
		$result = $obj->delFavorites();
		jsonout($result[0],$result[1]);
	}

}