<?php
namespace Api\Controller;
use Clas\Ad;
use Clas\User;

class AdoutController extends CommonController {
	
	/**广告列表
	 * @param   string      $position 	广告位置关键字
	 * @param   string      $row 	    查询几条
	 * @szl
	 */
	public function adList($data=null)
	{
		
		if(!$data){
			$data = I();
		}
		$obj = new Ad();
		$result = $obj->adList($data);
	    jsonout($result[0],$result[1],$result[2]);
	}
	/**广告位置列表
	 * @szl
	 */
	public function positionList($data=null)
	{
		if(!$data){
			$data = I();
		}
		$obj = new Ad();
		$result = $obj->positionList($data);
	    jsonout($result[0],$result[1],$result[2]);
	}
		
	
}