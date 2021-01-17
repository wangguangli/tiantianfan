<?php
namespace Api\Controller;
use Clas\Account;

class AccountinController extends CommonController {
	

	/**添加银行卡
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
	public function addBank($data=null)
	{
		if(!$data){
			$data = I();
		}
		$obj = new Account();
		$result = $obj->add_bank($data);
		jsonout($result[0], $result[1], $result[2]);
	}	
	/**删除银行卡
	 *
	 * @param   int      	$user_id   	用户ID
	 * @return  string
	 * @zd
	 */

	public function deleteBank($data=null)
	{
		if(!$data){
			$data = I();
		}
		$obj = new Account();
		$result = $obj->delete_bank($data);
	    jsonout($result[0], $result[1], $result[2]);
	}
	
}