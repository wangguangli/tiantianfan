<?php

namespace Clas;
class Favorite
{

	private $_user_id;

	public function __construct($user_id)
	{
		if (func_num_args() < 1)
		{
			throw new \Exception('缺少参数<br>Missing argument!');
		}
		$this->_user_id = $user_id;
	}

	public static function is_favorite($user_id, $fav_id, $type)
	{
		if (empty($user_id))
		{
			return false;
		}
		$where = array(
			'user_id' => $user_id,
			'fav_id' => $fav_id,
		);
		$c = M('favorites')->where($where)->count();
		if (empty($c))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	// 收藏数量，type 1商品，2商家
	public static function favorite_count($fav_id, $type)
	{
		$sum = M('favorites')->where(['fav_id' => $fav_id, 'type' => $type])->count();
		if (empty($sum))
		{
			return 0;
		}
		return $sum;
	}


}