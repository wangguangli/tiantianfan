<?php
namespace Clas;
class Fanli {
	
	private $_user_id;
    private $_shop_id;
    

    public function __construct($shop_id, $user_id){
		if (func_num_args() < 2) {throw new \Exception('缺少参数<br>Missing argument!');}
		$this->_user_id = $user_id;
        $this->_shop_id = $shop_id;
	}
    
    
    public function add ($rangli_id, $money, $type, $order_no, $goods_id = 0) {
        
        if (empty($rangli_id) || empty($money)) {
            return false;
        }
        
        $ymw = get_year_month_week();
        $config = get_rangli_config($rangli_id);
        
        $shop_rangli_money = $money * $config['shop_rangli_ratio'];
        $fanli_money = $money * $config['buy_fanli_ratio'];
        
        $log = array(
            'user_id' => $this->_user_id,
            'shop_id' => $this->_shop_id,
            'type' => $type,
            'sum_money' => $money,
            'shop_rangli_money' => $shop_rangli_money,
            'buy_fanli_money' => $fanli_money,
            'shop_rangli_percent' => $config['shop_rangli_percent'],
            'buy_fanli_percent' => $config['buy_fanli_percent'],
            'rangli_id' => $rangli_id,
            'date' => date('ymd'),
            'year' => $ymw['year'],
            'month' => $ymw['month'],
            'week' => $ymw['week'],
            'time' => time(),
            'order_no' => $order_no,
            'goods_id' => $goods_id
        );
        M('rangli_log')->add($log);
        
        M('shop')->where('id=' . $this->_shop_id)->setInc('shop_rangli_money', $shop_rangli_money);
        
        
        
        $sql = "update __TABLE__ set sum_buy=sum_buy+{$money},sum_rangli=sum_rangli+{$fanli_money},wait_rangli=wait_rangli+{$fanli_money} where id={$this->_user_id}";
		M('user')->execute($sql);
        
        
        
        /*
        $data = M('rangli')->where('user_id=' . $this->_user_id)->find();
        if ($data) {
            $data1 = array(
                'sum_money' => $data['sum_money'] + $money,
                'fanli_money' => $data['fanli_money'] + $fanli_money,
                'wait_money' => $data['wait_money'] + $fanli_money
            );
        } else {
            $data1 = array(
                'sum_money' => $money,
                'fanli_money' => $fanli_money,
                'wait_money' => $fanli_money
            );
        }
        M('rangli')->where('id=' . $data['id'])->save($data1);
         * 
         */
    }
    
    
    public function addMall($id, $order_no) {
        $goods = M('order_goods')->where('order_id=' . $id)->select();
        
        foreach ($goods as $g) {
            $discount_json = M('goods')->where('id=' . $g['goods_id'])->getField('discount_json');
            if (empty($discount_json)) {
                continue;
            }
            $this->add(get_json_field($discount_json, 'rangli_id'), $g['goods_total'], 'mall', $order_no, $g['goods_id']);
        }
        
    }
    
    
    public static function send_rangli () {
        $where['date'] = date('Ymd');
        $data = M('rangli_date')->where($where)->find();
        if (empty($data['gongxiang_count']) || $data['is_send']) {
            return false;
        }

        $user = M('user')->field('id,money,sum_buy,sum_rangli,yiderangli,wait_rangli')->where('wait_rangli>0')->select();
        
        $sum_money = 0;
        $actual_money = 0;
        
        
        foreach ($user as $u) {
            $arr = self::user_rangli($data['gongxiang_ratio'], $data['gongxiangzhi'], $data['gongxiang_count'], $u);
            if ($arr) {
                $sum_money += $arr[0];
                $actual_money += $arr[1];
            }
            
        }
        
        $data1 = array(
            'actual_count' => count($user),
            'sum_money' => $sum_money,
            'actual_money' => $actual_money,
            'is_send' => 1
        );
        M('rangli_date')->where('id=' . $data['id'])->save($data1);
    }
    
    
    
    public static function user_rangli ($ratio, $sum_gx, $gx_count, $user) {
        $user_id = $user['id'];
        $sum_buy = $user['sum_buy'];
        $yifan = $user['yiderangli'];
        $wait_money = $user['wait_rangli'];
        
        $x = $sum_buy * $ratio * $sum_gx / $gx_count;
		
        $today_moeny = min($x, $wait_money);
        if ($today_moeny <= 0) {
			return false;
		}

        
        $data = array(
            'money' => $user['money'] + $today_moeny,
            'yiderangli' => $yifan + $today_moeny,
            'wait_rangli' => $wait_money - $today_moeny
        );
        M('user')->where('id=' . $user_id)->save($data);
        
		$log = array(
			'user_id' => $user_id,
			'amount' => $today_moeny,
			'type' => 'money',
			'deal_type' => 'rangli',
			'text' => '让利'
		);
        add_account_log($log, 1);
        
        return array($x, $today_moeny);
    }
    
    
    
}