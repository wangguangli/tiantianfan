<?php
namespace M\Controller;
header("Content-type: text/html; charset=utf-8"); // tp输出的编码不是utf8，模板编码是。
use Think\Controller;
class CommonController extends Controller
{
    public function __construct(){
        parent::__construct();
        $fname = ACTION_NAME;//方法名
        $array  = array('refund_log', 'alipay');//不用继承的方法名数组
        $this->user_id = session('user_id');
        if(!in_array($fname,$array))
        {
            if (!$this->user_id)
            {
                $url = U('Index/login');
                redirect($url);
            }

        }
    }
}