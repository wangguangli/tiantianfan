<?php
namespace Clas;
class Account {
	
	/**
	 * 添加银行卡
	 * @param   string      $name           持卡人 
	 * @param   string  	$cart_no        银行卡号
	 * @param   int  		$bank_name      银行名
	 * @return  string 		$bank_address	银行名开户地址
	 * @author  Lz
	 */
	public function add_bank($data) {
		$user_id=$data['user_id'];
		if(!$user_id){
	
			return array('请先登录', 1,'');
		}
		$name = $data['name'];
		$cart_no = $data['cart_no'];
		$bank_name = $data['bank_name'];
		$type = $data['type'];
		$bank_address = $data['bank_address'];
        $bank_id = $data['id'];
		// 持卡人与平台用户实名认证人不统一,,,,是否需要验证？？？
		if (empty($name)) {
			return array('请输入持卡人', 1,'');
		}
		if (empty($cart_no)) {
			 return array('请输入卡号', 1,'');
		}
		if (empty($bank_name)) {
			 return array('请输入银行名', 1,'');
		}
		$c = M('user_bank')->where('cart_no="'. $cart_no . '"')->count();
		if(!$data["id"]){
            if($c){
                return array('该银行卡已在平台记录，如不是本人绑定请联系管理员', 1);
            }
        }

		$type = $type ? $type : 0;
		$data = array(
			'bank_name' => $bank_name,
			'user_id' => $user_id,
			'name' => $name,
			'cart_no' => $cart_no,
			'bank_address' =>$bank_address,
			'type' => $type,
			'add_time' => time()
		);

		if ($bank_id)
		{
			M('user_bank')->where("id=".$bank_id)->save($data);
		}
		else
		{
			$bank_id = M('user_bank')->add($data);
		}
		$result = M('user_bank')->where("id=".$bank_id)->find();		

		if ($result) {
			return array('操作成功', 0, $result);
		} else {

			return array('操作失败，请重新操作', 1);
		}
	}
	/**
	 * 删除银行卡
	 * @param   string      $bank_id          持卡人 
	 * @author  Lz
	 */
	public function delete_bank($data){
		$user_id=$data['user_id'];
		if(!$user_id){
	
			return array('请先登录', 1,'');
		}
		$bank_id = $data['bank_id'];
		if(!$bank_id){
			 return array('缺少银行卡id', 1,'');
		}
		$result= M('user_bank')->where('id='.$bank_id)->delete();
		if($result){
		   return array('删除成功', 0,$result);
	   } else {
		   return array('操作失败，请重新操作', 1);
	  
	}
}    
	//银行卡列表
	public function bank_list($data) {
		$user_id=$data['user_id'];
	   if(!$user_id){
			return array('请先登录', 1,'');
		} 
		$result=M('user_bank')->where("user_id=$user_id")->select();
		if($result == null){
		  return array('您没有添加银行卡', 1,'');  
		}
		return array('银行卡列表', 0,$result);


	  
	}

    /**
     * Notes:获取银行卡详情
     * User: WangSong
     * Date: 2020/8/1
     * Time: 8:46
     */
	public function get_bank_detail(){
	    $data = I();
	    if(!$data['bank_id']){
	        return '该银行卡信息不存在';
        }
	    $bank = M('user_bank')->where('id='.$data['bank_id'])->find();
	    if($bank){
	        return $bank;
        }else{
	        return '该银行卡信息不存在';
        }
    }
}