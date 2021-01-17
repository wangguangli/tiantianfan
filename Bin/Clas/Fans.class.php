<?php
namespace Clas;
class Fans {

	//粉丝列表
    public function fanList($user_id){
        if(empty($user_id)){
            $user_id = I('user_id');
            if(!$user_id){
                return array('请先登陆', 1);
            }
        }
		$type = I('type');
		if($type==1){
			$fans = M('user')->where('is_del=0 and first_leader='.$user_id)->field('id,headimgurl,phone,cre_time,nickname,realname')->select();
		}
		if($type==2){
			$fans = M('user')->where('is_del=0 and second_leader='.$user_id)->field('id,headimgurl,phone,cre_time,nickname,realname')->select();
		}
		if($type==3){
			$fans = M('user')->where('is_del=0 and third_leader='.$user_id)->field('id,headimgurl,phone,cre_time,nickname,realname')->select();
		}
		if ($fans) 
		{
			$msg = "粉丝列表";
			$err = 0;
			foreach ($fans as $key => &$value) 
			{
				$value["headimgurl"] = full_pre_url($value["headimgurl"]);
				if (!$value['realname'])
				{
					$value['realname'] = "未实名";
				}
			}
		}
		else
		{
			$msg = "暂无数据";
			$err = 1;
			$fans = array();
		}
		return array($msg, $err, $fans);
    }
  
       
}