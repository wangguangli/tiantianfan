<?php
namespace Api\Controller;
use Clas\Address;
use Clas\User;
class AddressinController extends CommonController {

	/**添加地址
	 *
	 * @param   int      	$user_id   	用户ID
	 * @param   string      $consignee 	收货人
	 * @param   string      $phone 	    手机号码
	 * @param   string      $zipcode 	邮政编码
	 * @param   string      $address   	 地址
	 * @return  string      $is_default   默认收货地址
	 * @return  string
	 * @lz
	 */
	public function addAddress($data=null)
	{
		if(!$data){
			$data = I();
		}
		$obj = new Address();
		$result = $obj->add_address($data);
	    jsonout($result[0], $result[1], $result[2]);
	}	
	/**修改地址
	 *
	 * @param   int      $user_id   	用户ID
	 * @return  string
	 * @zd
	 */
	public function editAddress($data=null)
	{
		if(!$data){
			$data = I();
		}

		$obj = new Address();
		$result = $obj->edit_address($data);
	   jsonout($result[0], $result[1], $result[2]);

	}	
	/**删除地址
	 *
	 * @param   int      	$user_id   	用户ID
	 * @return  string
	 * @zd
	 */

	public function delAddress($data=null)
	{
		if(!$data){
			$data = I();
		}
		$obj = new Address();
		$result = $obj->del_address($data);
	    jsonout($result[0], $result[1], $result[2]);
	}
	//省
	public function province()
	{
		$obj = new Address();
		$result = $obj->province();
	    jsonout($result[0], $result[1], $result[2]);
	}
	public function city()
	{
		$obj = new Address();
		$result = $obj->city();
	    jsonout($result[0], $result[1], $result[2]);
	}
	public function district()
	{
		$obj = new Address();
		$result = $obj->district();
	   jsonout($result[0], $result[1], $result[2]);
	}

}