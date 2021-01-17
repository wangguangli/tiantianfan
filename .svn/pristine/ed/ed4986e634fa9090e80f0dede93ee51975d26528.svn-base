<?php
namespace Clas;
class Address {

	//添加地址
	public function add_address($data){
		$user_id=$data['user_id'];
		if(empty($user_id)){
		    $user_id = session('user_id');
        }
		if(!$user_id){
			return array('请先登录', 1,'');
		}
		$consignee=$data['consignee'];
		$phone=$data['phone'];
		$country=$data['country'];
		$province=$data['province'];
		$city=$data['city'];
		$district=$data['district'];
		$twon=$data['twon'];
		$address=$data['address'];
		$zipcode=$data['zipcode'];
		$is_default=$data['is_default'];
		$is_default = $is_default ? $is_default : 0;

		if(empty($consignee)){
			return array('收货人不能为空', 1,'');
		}
		if(empty($phone)){
			  return array('联系方式不能为空', 1,'');
		}
		if(empty($province)){
			 return array('请选择正确的省份', 1,'');
		}
		if(empty($city)){
			  return array('请选择正确的市', 1,'');
		}
		if(empty($district)){
			  return array('请选择正确的县区', 1,'');
		}
		if(empty($address)){
			  return array('详细地址不能为空', 1,'');
		}
		$data['user_id']=$user_id;
		$data['consignee']=$consignee;
		$data['phone'] = $phone;
		$data['province']=$province;
		$data['country']=$country;
		$data['city']=$city;
		$data['district']=$district;
		$data['twon']=$twon?$twon:0;
		$data['address']=$address;
		$data['zipcode']=$zipcode;
		if($is_default == 1){
			M('user_address')->where('user_id='.$user_id)->setField('is_default',intval(0)); 
			$data['is_default']=$is_default;
		}else{
			$data['is_default']=intval(0);
		}
		$result =M('user_address')->add($data);
		if ($result) {
			return array('添加地址成功', 0,$result);
		} else {
			return array('添加地址失败', 1);
		}
	}
	   //修改地址
	public function edit_address($data){
		$address_id=$data['address_id'];
		$user_id = isset($data['user_id']) ? $data['user_id'] : session("user_id");
		$consignee=$data['consignee'];
		$phone=$data['phone'];
		$country=$data['country'];
		$province=$data['province'];
		$city=$data['city'];
		$district=$data['district'];
		$twon=$data['twon'];
		$address=$data['address'];
		$zipcode=$data['zipcode'];
		$is_default=$data['is_default'];
		$is_default = $is_default ? $is_default : 0;
		if(empty($consignee)){
		   return '收货人不能为空';
		}
		if(empty($phone)){
			return '联系方式不能为空';
		}
		if(empty($province)){
		   return '请选择正确的省份';
		}
		if(empty($city)){
			return '请选择正确的市';
		}
		if(empty($district)){
			return '请选择正确的县区';
		}
		if(empty($address)){
			return '详细地址不能为空';
		}
		

		$data['user_id']=$user_id;
		$data['consignee']=$consignee;
		$data['phone'] = $phone;
		$data['province']=$province;
		$data['country']=$country?$country:'国家';
		$data['city']=$city;
		$data['twon']=$twon?$twon:0;
		$data['district']=$district;
		$data['address']=$address;
		$data['zipcode']=$zipcode;
		if($is_default == 1){
			M('user_address')->where('user_id='.$user_id)->setField('is_default',intval(0));        
			$data['is_default']=$is_default;
		}else{
			$data['is_default']=intval(0);
		}

		$result =M('user_address')->where('user_id='.$user_id.' and id='.$address_id)->save($data);
		if ($result !== flase) {
			return array('操作成功', 0,$address_id);
		} else {
			return array('操作失败', 1);
		}
	

	}
	//删除地址
	public function del_address($data){
		$user_id=$data['user_id'];
		if(!$user_id){
			return array('请先登录', 1,'');
		}
		$address_id = $data['address_id'];
		if(!$address_id){
			return array('主要参数错误', 0);
		}
		$result =M('user_address')->where('id='.$address_id)->setField('isdel',1);
		if($result !== flase){
			return array('删除成功', 0);
		}else{
			return array('删除失败，请重试', 1);
		}
	 }

	// 地址列表
	public function address($data){

		$user_id=$data['user_id'];
        if(empty($user_id)){
            $user_id = session('user_id');
        }
		$where['user_id'] = $user_id;
		$where['isdel'] = 0;
		$result=M('user_address')->where($where)->order('is_default desc')->select();

		$region = get_region_list();
		foreach($result as $k=>$v){
			$result[$k]['province_name'] =  $region[$v['province']]['name'];
			$result[$k]['city_name'] =  $region[$v['city']]['name'];
			$result[$k]['district_name'] =  $region[$v['district']]['name'];
		}

		if($result){
			return array('地址列表', 0,$result);
		}else{

			return array('暂无地址', 1);
		}
	
	}
	
	public function province(){
		$sheng = M('region')->where('level = 1')->field('id,name')->select();
		return array('省', 0,$sheng);
	}
	
	public function  city(){
		$id=I("id");
		$type=I("type", 0); //默认输出单纯的数组，输出带有option的
		$sheng = M('region')->where('level = 2 and parent_id = '.$id)->field('id,name')->select();
		if ($type == 1) 
		{
			foreach ($sheng as $key => $value) 
			{
				$html.= "<option value='".$value['id']."'>".$value['name']."</option>";
			}
			$sheng = $html;
		}
		return array('市', 0,$sheng);
	}
	
	public function  district(){
		$id=I("id");
		 $type=I("type", 0); //默认输出单纯的数组，输出带有option的
		$sheng = M('region')->where('level = 3 and parent_id = '.$id)->field('id,name')->select();
		if ($type == 1) 
		{
			foreach ($sheng as $key => $value) 
			{
				$html.= "<option value='".$value['id']."'>".$value['name']."</option>";
			}
			$sheng = $html;
		}
		return array('县', 0,$sheng);
	}
    /**
     * Notes: 获取地址详情
     * User: wangsong
     * Date: 2019/10/22
     * Time: 11:04
     */
    public function get_address_detail($address_id,$user_id){
		$where['id'] = $address_id;
		$where['user_id'] = $user_id;
        $user_address = get_address($where, 'user_address');
        if(!$user_address){
            return array('该地址不存在', 1);
        }
        return $user_address;
    }
}