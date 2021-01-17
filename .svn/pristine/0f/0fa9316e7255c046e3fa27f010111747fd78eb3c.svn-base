<?php
namespace Api\Controller;
use Clas\Message;
use Clas\User;
class MessageController extends CommonController {
 

   public function Message()
	{
		$obj = new Message();
		$result = $obj->News();
	    if($result) {
			jsonout($result,1);
		}else{
			jsonout('列表',0);
		}
	}	
	 public function Help()
	{
		$obj = new Message();
		$result = $obj->Help();
	    if($result) {
			jsonout($result,1);
		}else{
			jsonout('获取消息数据成功',0);
		}
	}	

}