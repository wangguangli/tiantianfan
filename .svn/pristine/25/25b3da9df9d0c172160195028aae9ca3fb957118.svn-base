<?php
namespace Api\Controller;
use Clas\Ad;
class AdinController extends CommonController {

	/**添加广告
	 * @param   string      $description 位置描述
	 * @param   string      $position 	 位置关键字
	 * @param   string      $width 		 宽度
	 * @param   string      $height   	 高度
	 * @param   string      $title   	 标题
	 * @param   string      $content   	 图片描述 
	 * @param   string      $link   	 链接
	 * @param   string      $photo   	 图片
	 * @param   string      $module   	 模块
	 * @return  string      
	 * @szl
	 */
	public function addAd($data=null)
	{
		if(!$data){
			$data = I();
		}
		$obj = new Ad();
		$result = $obj->addAd($data);
		jsonout($result[0],$result[1]);
	}
	/**添加广告位置
	 * @param   string      $description 位置描述
	 * @param   string      $position 	 位置关键字
	 * @param   string      $width 		 宽度
	 * @param   string      $height   	 高度
	 * @return  string      
	 * @szl
	 */
	public function addPosition($data=null)
	{
		
		if(!$data){
			$data = I();
		}
		$obj = new Ad();
		$result = $obj->addPosition($data);
		jsonout($result[0],$result[1]);
	}

}