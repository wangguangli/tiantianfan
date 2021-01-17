<?php
namespace Api\Controller;
use Clas\Account;

class AccountoutController extends CommonController {
	/**
	 * 银行卡列表
	 * @return  string               直接输入字段的值 
	 * @author  Lz
	 */
	public function bankList($data=null) {
		if(!$data){
			$data = I();
		}
		$obj = new Account();
		$result = $obj->bank_list($data);
		jsonout($result[0], $result[1], $result[2]);
	
	}

    /**
     * Notes:获取银行卡详情
     * User: WangSong
     * Date: 2020/8/1
     * Time: 8:45
     */
	public function get_bank_detail(){
	    $account = new Account();
	    $re = $account->get_bank_detail();
	    if(is_array($re)){
	        jsonout('获取成功',0,$re);
        }else{
	        jsonout($re,1);
        }
    }
	
}