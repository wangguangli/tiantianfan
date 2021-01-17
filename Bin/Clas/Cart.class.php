<?php
namespace Clas;
class Cart {
	
	private $_user_id;
	private $_role;
	
	
	// role: admin,user
	public function __construct($user_id, $role){
		if (func_num_args() < 2) {exit('缺少参数<br>Missing argument!');}
        
		$this->_user_id = $user_id;
		$this->_role = $role;
	}
    
    
	public function add(){
		
		$goods_id = I('goods_id', 0, 'intval');
        $goods_num = I('goods_num', 0, 'intval');
        $goods_spec_ids = I('goods_spec_ids');

        //是否购物车里操作数量,负数是减少0是清空,如果正数比原来的小也算是相应的减少
        $is_cart = I('iscart', 0, 'intval'); 

        $goods = M('goods')->find($goods_id);

        if (empty($goods) || empty($goods['is_on_sale'])) {
            return _dat('商品不存在或下架');
        }
        if (is_nums_str($goods_spec_ids) == -1) {
            return _dat('非法操作[cas]');
        }
        $c = M('cart')->where('user_id=' . $this->_user_id)->count();
        if ($c > 200) {
            return _dat('购物车里的数量太多请删除一部分在添加');
        }
        
        // TODO 可追加一条根据规格来查看购物车商品是不是存在
        $cart = M('cart')->field('id,goods_num')->where(['user_id' => $this->_user_id, 'goods_id' => $goods_id])->find();
        if (empty($cart)) {
            
            if ($goods_num < 1) {
                return _dat('请选择商品数量');
            }
            
            $data = [
                'user_id' => $this->_user_id,
                'goods_id' => $goods_id,
                'goods_num' => $goods_num,
                'goods_spec' => get_goods_spec($goods_spec_ids),
                'goods_spec_ids' => $goods_spec_ids,
                'shop_id' => $goods['shop_id'],
                'time' => time(),
                'ip' => get_client_ip()
            ];
            M('cart')->add($data);
            
            return _dat('', 0, ['goods_id' => $goods_id, 'goods_num' => $goods_num]);
        }
        
        if ($is_cart) {

            // if ($goods_num != 0) {
                // $goods_num += $cart['goods_num'];
            // }

            if ($goods_num > 0) {
                M('cart')->where('id=' . $cart['id'])->setField('goods_num', $goods_num);
            } else {
                M('cart')->where('id=' . $cart['id'])->delete();
            }
            
        } else {
            if ($goods_num < 1) {
                return _dat('请选择商品数量');
            }
            $goods_num += $cart['goods_num'];
            M('cart')->where('id=' . $cart['id'])->setField('goods_num', $goods_num);
        }
        return _dat('', 0, ['goods_id' => $goods_id, 'goods_num' => $goods_num]);
	}
	
    
    public function getAll () {
        $where = request_last_id();
        $where['user_id'] = $this->_user_id;
        $list = flow_page('cart', $where);

        if(empty($list)) {
            return null;
        }
        
        $goods = get_array_column($list, 'goods_id', 'get_goods');
        $shop = get_array_column($list, 'shop_id', 'get_shop_name');
        
        $newList = array();
        foreach ($list as $v) {
            $g = $goods[$v['goods_id']];
            $_goods = array(
                'goods_id' => $g['id'],
                'goods_name' => $g['name'],
                'thumb' => $g['thumb'],
                'goods_price' => $g['price'],
                'goods_num' => $v['goods_num'],
                'market_price' => $g['market_price'],
                'goods_spec' => $g['goods_spec'],
                'is_on_sale' => $g['is_on_sale'],
                'cart_id' => $v['id']
            );
            
            $sid = $v['shop_id'];
            if ($newList[$sid]) {
                $newList[$sid]['goods'][] = $_goods;
            } else {
                $newList[$sid] = array(
                    'shop_id' => $sid,
                    'shop_name' => $sid ? $shop[$sid] : '自营店',
                    'shop_photo' => '',
                    'goods' => array($_goods)
                );
            }
        }
        
        return array(
            'list' => array_values($newList),
            'last_id' => get_last_id($list)
        );
    }
    
}