<?php
namespace Clas;
class Message {
	

	private $_user_id;
	private $_role;
    private $_user;
    

	public function __construct($shop_id, $role, $user_id){
		if (func_num_args() < 2) {throw new \Exception('缺少参数<br>Missing argument!');}
		$this->_user_id = $user_id;
		$this->_role = $role;
        $this->_shop_id = $shop_id;
	}
   //消息中心
    public function News(){
        $id = I('id');
        $suser_id=I('user_id',0);
        $article = M('article')->where('cat_id = 0')->select();
        return 'ok';
    }
    //帮助中心
    public function Help(){
        $article = M('article')->where('cat_id = 0')->select();
        if(!$article){
             return '暂无数据';
        }
            return '获取消息数据成功';
    }
       
}