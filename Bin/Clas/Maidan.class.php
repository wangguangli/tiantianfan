<?php
namespace Clas;
class Maidan {
	
	private $_user_id;
    private $_shop_id;
    
    public function __construct($shop_id, $user_id){
		if (func_num_args() < 2) {throw new \Exception('缺少参数<br>Missing argument!');}
		$this->_user_id = $user_id;
        $this->_shop_id = $shop_id;
	}


    //创建订单
    public function create ($rangli_id) {
        $money = I('money', 0, 'abs');
        if ($money <= 0) {
            return '金额数非法';
        }

        $pay_type = I('pay_type');
        $order_no = get_order_no('maidan');
        
        $data = array(
            'order_no' => $order_no,
            'user_id' => $this->_user_id,
            'shop_id' => $this->_shop_id,
            'money' => $money,
            'actual_payment' => $money,
			'rangli_id' => $rangli_id,
            'type' => $pay_type,
            'time' => time(),
            'ip' => get_client_ip()
        );
        
        $id = M('maidan')->add($data);
        
        if ($pay_type == 'money') {
            $ok = $this->account_pay($id, $money, $order_no);
            return $ok;
        }
        
        return $id; // TODO 返回微信支付的签名数据
    }

    //余额（或当面(现金)）支付
    private function account_pay ($order_id, $money, $order_no = '') {
        $user = M('user')->field('pay_password,money')->find($this->_user_id);
        $err = check_pay_password($this->_user_id, I('pay_password'), $user['pay_password']);
        if ($err) {
            return $err;
        }
        
        if ($user['money'] < $money) {
            return '你的余额不足';
        }
        
        M('user')->where('id=' . $this->_user_id)->setDec('money', $money);

        $log = array(
            'user_id' => $this->_user_id,
            'amount' => -$money,
            'type' => 'money',
            'deal_type' => 'maidan',
            'pay' => '余额支付',
            'order_no' => $order_no,
            'text' => '在' . get_shop_name($this->_shop_id) . '买单'
        );
		add_account_log($log, 1);
        
        $this->order_success($order_id, 'account');
        return 'ok';
    }

    public function pay () {
        $id = I('order_id');
        $md = M('maidan')->find($id);
        if (empty($md)) {
            return '非法操作';
        }
        if ($md['order_status']) {
            return '不能重复操作';
        }
        
        $money = $md['money']; // TODO  需验证第三方支付
        
        $log = array(
            'user_id' => $md['user_id'],
            'amount' => -$money,
            'deal_type' => 'maidan',
            'pay' => '微信支付',
            'order_no' => $md['order_no'],
            'text' => '在' . get_shop_name($md['shop_id']) . '买单'
        );
		add_account_log($log, 1);
        
        $this->order_success($id, 'weixin');
    }
    
    
    //订单成功(完成)
    private function order_success ($order_id, $pay_type) {

        M('maidan')->where('id=' . $order_id)->setField('order_status', 1);
        
        
        $md = M('maidan')->find($order_id);
		$this->_user_id = $md['user_id'];
        $this->_shop_id = $md['shop_id'];

        $rangli = get_rangli_config($md['rangli_id']);
        $deserve = $md['money'] - $md['money'] * $rangli['shop_rangli_ratio'];

        $data = array(
            'actual_payment' => $deserve,
            'payment_type' => $pay_type,
        );
        M('maidan')->where('id=' . $order_id)->save($data); //怕失败分开添加
        
        
        //把钱打给商家(和商城订单操作一样，所以使用商城订单的方法);
        Order::add_shop_money($md['money'], $deserve, $this->_shop_id, $md['order_no'], 'maidan');
        
        //推荐佣金
        tuijian_commission($this->_user_id, $this->_shop_id, $md['money'], $md['order_no'], 'maidan');

        //添加佣金
        agent_commission($this->_shop_id, $md['money'], $md['order_no']);
        
        
        //添加返利
        $obj = new Fanli($this->_shop_id, $this->_user_id);
        $obj->add($md['rangli_id'], $md['money'], 'maidan', $md['order_no']);
    }
    

    
	public function delete () {
		$id = I('maidan_id', 0, 'intval');
		$md = M('maidan')->find($id);
		
        if ($this->_user_id != $md['user_id'] && $md['shop_id'] != $this->_shop_id) {
            return '非法操作';
        }
        
        if (empty($this->_user_id)) { //如果是商家删除
            $this->_user_id = $md['user_id'];
        }
        
		
		if ($md['order_status']) {
			return '订单进行中不能删除';
		}
		
		M('maidan')->where('id=' . $id)->delete();
		
        //如果下单冻结了积分,在返还过去
        if ($md['point'] > 0) {
            M('user')->where('id=' . $this->_user_id)->setInc('shop_point', $md['point']);
        }
        
		return 'ok';
	}
    
    
}