<?php
namespace Api\Controller;
use Clas\Fans;
class FansoutController extends CommonController {

	/**添加广告
	 * @param   int      	$type 		位置描述
	 * @param   string      $user_id 	用户ID
	 * @return  string      
	 * @zd
	 */
	public function fanList()
	{
		$obj = new Fans();
		$result = $obj->fanList();
		jsonout($result[0],$result[1],$result[2]);
	}	

}