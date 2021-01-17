<?php

namespace Clas;

use Clas\Favorite;
use Clas\Orderout;
use Clas\Ad;
use Think\Page;

class Goods
{
	private $_shop_id;
	private $_thumb = '';
	private $_discount_json = '';


	// role: admin,user(shop),user_id(平台管理人员id或商家user_id)
	public function __construct($data = null)
	{

		if ($data['shop_id'] > 0)
		{
			$shop = M('shop')->where("user_id=" . $data['shop_id'])->find();
			if (!$shop)
			{
				return '商家不存在';
			}
		}

		$this->_shop_id = $data['shop_id'];
	}


	//计算商品佣金
	public static function get_goods_commission($goods_total, $commission)
	{

		if (empty($commission))
		{
			return false;
		}

		if (is_numeric($commission))
		{
			return $goods_total * $commission / 100;
		}
		else
		{
			$data = json_decode($commission, true);
			$arr = array();
			foreach ($data as $k => $v)
			{
				if (empty($v))
				{
					continue;
				}

				$total = $goods_total * $v / 100;
				$arr[$k] = $total;
			}
			return json_encode($arr);
		}
	}


	public static function get_goods_commission_text($str)
	{
		if (empty($str))
		{
			return '';
		}
		if (is_numeric($str))
		{
			return $str;
		}

		$arr = json_decode($str, true);
		$text = '';
		if (is_array($arr))
		{
			foreach ($arr as $k => $v)
			{
				if (empty($v))
				{
					continue;
				}

				if ($k == 'tj')
				{
					$text .= "推荐人:{$v} ";
				}
				else
				{
					$text .= get_user_level($k) . ":{$v} ";
				}
			}
		}
		return $text;
	}


	private function _check_point()
	{
		$price = I('price', 0, 'abs');
		$point_price = I('point', 0, 'abs');
		$is_point = I('is_point', 0, 'intval');

		if ($point_price <= 0)
		{
			return false;
		}
		if ($point_price > $price)
		{
			return '折扣金额不能大于价格';
		}

		$point = $point_price * 100;

		$account_payable = $price - $point_price;

		$arr = array(
			'price' => $price,
			'point_price' => $point_price,
			'point' => $point,
			'account_payable' => $account_payable, //应付款
			'price_text' => "￥{$account_payable}+积分{$point}",
			'is_point' => $is_point
		);
		$this->_discount_json = json_encode($arr);
		return false;
	}


	private function _check_fanli($price)
	{
		$id = I('rangli_id', 0, 'intval');
		$config = get_rangli_config($id);

		if ($config)
		{

			$config['gongxiangzhi'] = $price * $config['buy_fanli_ratio'];

			$this->_discount_json = json_encode(
				array(
					'rangli_id' => $id,
					'shop_rangli_ratio' => $config['shop_rangli_ratio'],
					'shop_rangli_percent' => $config['shop_rangli_percent'],
					'buy_fanli_ratio' => $config['buy_fanli_ratio'],
					'buy_fanli_percent' => $config['buy_fanli_percent'],
					'gongxiangzhi' => $price * $config['buy_fanli_ratio']
				)
			);
		}
	}

	public function add($data)
	{
		$goods_id = $data['goods_id'];
		$name = $data['name'];
		$price = $data['price'];
		$market_price = $data['market_price'];
		$hot_id = $data['hot_id'] ? $data['hot_id'] : 0;
		$sell_count = $data['sell_count'];
		$store_count = $data['store_count'];
		$thumb = $data['thumb'];
		$spec = $data['item'];
		$is_on_sale = $data['is_on_sale'];
		$images = $data['images'];
		$sort = $data['sort'];
		$shop_fee_id = $data['shop_fee_id'];
		$shop_id = $data['shop_id'] ? $data['shop_id'] : 0;    // shop_id
		$type_id = $data['type_id'] ? $data['type_id'] : 0;        // 所属规格类型
		$is_group = $data['is_group'] ? $data['is_group'] : 0;    // 1团购
		$is_hour = $data['is_hour'] ? $data['is_hour'] : 0;        // 2整点秒杀
		$is_time = $data['is_time'] ? $data['is_time'] : 0;        // 3限时购

		$service_fee_bfb = $data['service_fee_bfb'] ? $data['service_fee_bfb'] : 0;        // 服务费比例
		$still_fee_bfb = $data['still_fee_bfb'] ? $data['still_fee_bfb'] : 0;        //返还比例
		$is_coupon = $data['is_coupon'] ? $data['is_coupon'] : 0;        //是否使用代金券  1：是   0：否

		if ($data['tags_type'] == 0)
		{
			$tags = '新品';
		}
		elseif ($data['tags_type'] == 1)
		{
			$tags = '热销';
		}
		elseif ($data['tags_type'] == 2)
		{
			$tags = '优质';
		}
		$group = $data['group'];            // 团购参数数组
		$hour = $data['hour'];                // 秒杀参数数组
		$time = $data['time'];
		$video = $data['video'] ? $data['video'] : 0;// 视频
		$shipping_rule_id = $data['shipping_rule_id']; //运费模板
		$product_code = $data['product_code']; // 商品编码

		$extra_cate_list = $data['extra_cate_list'];    // 附属分类，新增到另外一个关联表，含ID=0：array(0 => '0', 1 => '62')
		$service_show[] = $data['service_show1'] ? $data['service_show1'] : 0;
		$service_show[] = $data['service_show2'] ? $data['service_show2'] : 0;
		$service_show[] = $data['service_show3'] ? $data['service_show3'] : 0;
		// 自提点处理
		$is_pick = I("is_pick", 0);    // 1可自提，0不可自提

		if ($is_pick == 1)
		{
			$picksiteWhere['is_del'] = 0;
			$picksiteList = M('goods_picksite')->where($picksiteWhere)->select();
			$pickArray = array();
			foreach ($picksiteList as $k => $v)
			{
				$pickArray[$k] = $v['id'];
			}
			$picksite = implode(",", $pickArray);
		}


		//不在进行判断
//		$picksite = I("picksite");    // 选择的自提点，数组，转为逗号分隔字符串
//		$picksite = implode(",", $picksite);
//		if ($is_pick && !$picksite)
//		{
//			return "请选择具体的自提点";
//		}

		if (!$type_id)
		{
			// return "请选择规格类型";
		}

		if ($shop_id && !$shop_fee_id)
		{
			$where_s['id'] = $shop_id;
			$shopInfo = get_user_info($where_s, '', '', 2);
			if ($shopInfo["shop_fee_id"])
			{
				$shop_fee_id = $shopInfo["shop_fee_id"];
			}
		}

		if (empty($market_price))
		{
			$market_price = $price;
		}
		if ($market_price < $price)
		{
			return '市场价不能小于本店价';
		}
		if (empty($name))
		{
			return '请输入商品名称';
		}
		if (empty($price))
		{
			return '请输入商品价格';
		}
		if (empty($thumb))
		{
			return '请上传商品图片';
		}

		if (!is_array($spec))
		{
			return '请选择商品规格';
		}

		if (!$shop_fee_id && $shop_id < 1)
		{
			// return '请选择服务费比例';
		}

		if (empty($shipping_rule_id))
		{
			return '请选择运费模板';
		}
		if (!$data['cat_id_3'])
		{
			return '请选择商品分类';
		}


        if ($data['product_type'] != 1) {
            if (!$service_fee_bfb || $service_fee_bfb <= 0) {
                return '请输入服务费比例';
            }
            if (!$still_fee_bfb || $still_fee_bfb <= 0) {
                return '请输入返还比例';
            }

        }


		if ($images)
		{
			$images = json_encode($images);
		}
		else
		{
			$images[0] = $thumb;
			$images = json_encode($images);
		}

		$sort = $sort ? $sort : 1;
		$shop_fee_id = $shop_fee_id ? $shop_fee_id : 0;

		$data_add = array(
			'name' => $name,
			'subhead' => $data['subhead'],
			'price' => $price,
			'market_price' => $market_price,
			'shipping_fee' => $data['shipping_fee'],
			'cat_id' => $data['cat_id_3'],
			'thumb' => $thumb,
			'sell_count' => $sell_count,
			'shop_id' => $shop_id,
			'hot_id' => $hot_id,
			'content' => htmlspecialchars_decode($data['content']),
			'images' => $images,
			'time' => time(),
			'ip' => get_client_ip(),
			'is_on_sale' => $is_on_sale,
			'sort' => $sort,
			'shop_fee_id' => $shop_fee_id,
			'type_id' => $type_id,
			'is_group' => $is_group,
			'is_hour' => $is_hour,
			'is_time' => $is_time,
			'video' => $video,
			'product_code' => $product_code,
			'shipping_rule_id' => $shipping_rule_id,
			'is_pick' => $is_pick,    // 是否可自提，1是，0否
			'picksite' => $picksite,    // 自提点
			'service_show' => json_encode($service_show),    // 服务显示
			'product_type' => $data['product_type'],
			'tags' => $tags,
			'is_shipping' => I('is_shipping', 0),//支持发货
			'service_fee_bfb' =>$service_fee_bfb,//服务费比例
			'still_fee_bfb' => $still_fee_bfb,//返还比例
			'is_coupon' => $is_coupon,//是否使用代金券  1：是   0：否
		);

		if ($store_count)
		{
			$data_add['store_count'] = $store_count;
		}

		if ($goods_id)
		{
			$goods = $this->get_goods($goods_id);
			if (!$goods)
			{
				return '未找到该商品';
			}
			$this->add_spec_goods_price($spec, $goods_id);
			$res = M('goods')->where('id=' . $goods_id)->save($data_add);
		}
		else
		{
			$res = $goods_id = M('goods')->add($data_add);
			$this->add_spec_goods_price($spec, $res);
		}

		// 团购处理
		if ($is_group)
		{
			// 设置团购属性
			$this->setGroupGoods($goods_id, 1, $group);
		}
		else
		{
			// 删除团购属性
			$this->setGroupGoods($goods_id, 0, $group);
		}
		//秒杀处理
		if ($is_hour)
		{
			$this->setHourGoods($goods_id, 1, $hour);
		}
		else
		{
			$this->setHourGoods($goods_id, 0, $hour);
		}
		//限时购处理
		if ($is_time)
		{
			$this->setTimeGoods($goods_id, 1, $time);
		}
		else
		{
			$this->setTimeGoods($goods_id, 0, $time);
		}

		// 附属分类处理，关联表
		$extra_cate_list = array_unique($extra_cate_list);
		$ki = 0;
		foreach ($extra_cate_list as $kce => $vce)
		{
			if ($vce > 0)
			{
				$extArr[$ki]['goods_id'] = $goods_id;
				$extArr[$ki]['cate_id'] = $vce;
				$extArr[$ki]['cre_time'] = time();
				$extArr[$ki]['cre_date'] = date("Y-m-d H:i:s");
				$ki++;
			}
		}
		if ($extArr)
		{
			M("goods_category_extra")->where("id>0 and goods_id=" . $goods_id)->delete();
			M("goods_category_extra")->addAll($extArr);
		}

		if ($res !== false)
		{
			if ($data['admin'])
			{
				return true;
			}
			else
			{
				jsonout("操作成功！", 0);
			}
		}
		else
		{
			return "操作失败，请重新操作";
		}
	}

	/**
	 * 商品团购处理
	 * goods_id 商品ID
	 * group    团购参数设置
	 */
	public function setGroupGoods($goods_id, $is_group, $group)
	{
		$db = M("goods_group");
		if (!$is_group)
		{
			$db->where("goods_id=" . $goods_id)->delete();
			return true;
		}
		$groupInfo = $db->where("goods_id=" . $goods_id)->find();
		$group['up_date'] = date("Y-m-d H:i:s");

		if (!$group['team_people_num'])
		{
			$group['team_people_num'] = 2;
		}
		if (!$group['team_time_limit'])
		{
			$group['team_time_limit'] = 24;
		}

		if ($groupInfo)
		{
			$db->where("goods_id=" . $goods_id)->save($group);
		}
		else
		{
			$group["goods_id"] = $goods_id;
			$db->add($group);
		}
		return true;
	}

	/**
	 * 商品秒杀处理
	 * goods_id 商品ID
	 * hour    秒杀参数设置
	 */
	public function setHourGoods($goods_id, $is_hour, $hour)
	{
		$db = M("goods_hour");
		$where['goods_id'] = $goods_id;
		$where['type'] = 0;
		if (!$is_hour)
		{
			$db->where($where)->delete();
			return true;
		}
		$hourInfo = $db->where($where)->find();
		$hour['up_date'] = date("Y-m-d H:i:s");
		$hour['start_time'] = strtotime($hour['start_time']);
		$hour['end_time'] = strtotime($hour['end_time']);
		if ($hourInfo)
		{
			$db->where($where)->save($hour);
		}
		else
		{
			$hour["goods_id"] = $goods_id;
			$hour["type"] = 0;
			$db->add($hour);
		}
		return true;
	}

	/**
	 * 商品限时购处理
	 * goods_id 商品ID
	 * time    限时购参数设置
	 */
	public function setTimeGoods($goods_id, $is_time, $time)
	{
		$db = M("goods_hour");
		$where['goods_id'] = $goods_id;
		$where['type'] = 1;
		if (!$is_time)
		{
			$db->where($where)->delete();
			return true;
		}
		$timeInfo = $db->where($where)->find();
		$time['up_date'] = date("Y-m-d H:i:s");
		$time['start_time'] = strtotime($time['start_time']);
		$time['end_time'] = strtotime($time['end_time']);
		if ($timeInfo)
		{
			$db->where($where)->save($time);
		}
		else
		{
			$time["goods_id"] = $goods_id;
			$time["type"] = 1;
			$db->add($time);
		}
		return true;
	}

	public function update()
	{
		$id = I('id', 0, 'intval');
		$name = I('name', '', 'trim');
		$price = I('price', 0, 'abs');
		$market_price = I('market_price', 0, 'abs');
		$x = I('x', 0, 'intval');

		$goods = M('goods')->find($id);

		if ($this->_shop_id && $goods['shop_id'] != $this->_shop_id)
		{
			return _dat('非法操作');
		}

		if ($market_price < $price)
		{
			return _dat('市场价不能小于本店价');
		}
		if (empty($name))
		{
			return _dat('请输入商品名称');
		}
		if (empty($price))
		{
			return _dat('请输入商品价格');
		}

		$this->_check_fanli($price);//检查让利

		$data = [
			'name' => $name,
			'price' => $price,
			'market_price' => $market_price,
			'shipping_fee' => I('shipping_fee', 0, 'abs,intval'),
			'cat_id' => I('cat_id', 0, 'abs,intval'),
			'hot_id' => I('hot_id', 0, 'intval')
		];

		if ($x > $goods['sell_count'] - $goods['x'])
		{
			$data['sell_count'] = $x;
			$data['x'] = $x - $goods['sell_count'] + $goods['x'];
		}


		$ok1 = $this->_check_goods_photo(true);
		if ($ok1)
		{
			return _dat($ok1);
		}
		if ($this->_thumb)
		{
			$data['thumb'] = $this->_thumb;
		}

		$data['content'] = I('content', '');
		$data['images'] = $this->getTempGoodsImages(I('images'), $goods['images']);
		$data['discount_json'] = $this->_discount_json;

		M('goods')->where('id=' . $id)->save($data);

		return _dat('修改完成', 0);
	}

	public function getTempGoodsImages($images, $old_images = '')
	{


		if (empty($images))
		{
			return '';
		}
		if ($images == $old_images)
		{
			return $images;
		}

		$arr = explode('|', $images);
		$new_arr = array();

		if ($old_images)
		{
			$old_arr = explode('|', $old_images);
			foreach ($old_arr as $o)
			{
				if (array_search($o, $arr) === false)
				{
					delete_abs_file($o);
				}
			}
		}

		foreach ($arr as $i)
		{
			if (strpos($i, 'temp') !== false)
			{
				$g = str_replace('temp', 'goods', $i);
				$path = dirname('.' . $g);
				if (!is_dir($path))
				{
					mkdir($path);
				}
				copy('.' . $i, '.' . $g);
				delete_abs_file($i);
				$new_arr[] = $g;
			}
			else
			{
				$new_arr[] = $i;
			}
		}

		return join('|', $new_arr);
	}


	public function addGoodsImage($goods_id = 0)
	{

		if ($goods_id)
		{

			$a = common_upload_photo('image', 'temp');
			if (empty($a['success']))
			{
				return $a['msg'];
			}

			$images = M('goods')->where('id=' . $goods_id)->getField('images');
			if ($images)
			{
				$images .= '|' . $a['path'];
			}
			else
			{
				$images = $a['path'];
			}

			M('goods')->where("id={$goods_id} and shop_id={$this->_shop_id}")->setField('images', $images);

			return 'ok';
		}

		//只在页面中调用
		$a = common_upload_photo('image', 'temp');
		if ($a['success'])
		{
			exit(json_encode(_dat($a['path'], 0)));
		}
		api_is_str($a['msg']);
	}


	public function is_on_sale()
	{
		$id = I('id', 0, 'intval');
		$issale = I('issale', 0, 'intval');

		M('goods')->where("id={$id} and shop_id={$this->_shop_id}")->setField('is_on_sale', $issale);

		return 'ok';
	}


	public function delete($data)
	{
		$id = $data['id'];
		$shop_id = $data['shop_id'];

		$goods = M('goods')->find($id);
		if (!$goods)
		{
			return '未找到商品';
		}

		if (!$shop_id)
		{
			$goods_isdel = M('goods')->where("id={$id}")->setField('isdel', 1);
		}
		else
		{
			$goods_isdel = M('goods')->where("shop_id=" . $shop_id . " and  id={$id}")->setField('isdel', 1);
		}


		//$this->deleteSpec($id);//删除商品的规格
		if ($goods_isdel !== false)
		{
			M('cart')->where('goods_id=' . $id)->delete(); //删除购物车中的商品
			return $goods_isdel;
		}
		else
		{
			return "操作失败，请重新操作";
		}


	}


	public function addComment($order_id, $goods_id)
	{
		$user_id = M('order')->where('id=' . $order_id)->getField('user_id');
		if ($user_id != $this->_user_id)
		{
			return '非法操作';
		}
		$c = M('order_goods')->where("order_id={$order_id} and goods_id={$goods_id}")->count();
		if (empty($c))
		{
			return '非法操作';
		}

		$c1 = M('goods_comment')->where("order_id={$order_id} and goods_id={$goods_id} and user_id={$this->_user_id}")->count();
		if ($c1)
		{
			return '你已经评论过了不能重复评论';
		}
	}

	/**
	 *
	 *商品规格
	 * @return json|string
	 * @mgl
	 */
	public function goodsSpec($data)
	{

		$w = array();
		if (!$data['type_id'])
		{
			$w['type_id'] = 0;
		}
		else
		{
			$w['type_id'] = $data['type_id'];
		}
		if ($data['id'])
		{
			$w['id'] = array('in', $data['id']);
		}

		$all = M('goods_spec')->where($w)->select();
		if (!$all)
		{
			return '规格不存在';

		}
		foreach ($all as &$gs)
		{
			$gs['goods_spec_item'] = M('goods_spec_item')->where('spec_id=' . $gs['id'])->select();
		}
		return array('商品规格', 0, $all);
	}


	/*
	 * [添加/删除 商品规格价格]
	 * 取出原规格的key，组合成数组。
	 * 再取出新规格的key，组合成数组
	 * 如果有新增，则加入。
	 * 如果有减少，则减掉。
	 * 如果没有变动，则不变
	 * @zd
	 * 2020.07.03 改
	*/
	public function add_spec_goods_price($spec, $goods_id)
	{
		$itemOld = M('goods_spec_price')->where('goods_id=' . $goods_id)->getField('key', true);
		$itemNew = array_keys($spec);

		if (!$itemOld && $itemNew)
		{
			$itemAdd = $itemNew;
		}
		if ($itemOld && !$itemNew)
		{
			$itemReduce = $itemOld;
		}
		if ($itemOld && $itemNew)
		{
			$itemReduce = array_diff($itemOld, $itemNew);     // 减少的规格
			$itemAdd = array_diff($itemNew, $itemOld);       // 增加的规格
			$itemIns = array_intersect_key($itemOld, $itemNew); // 共有的规格
		}
		if ($itemReduce)
		{
			foreach ($itemReduce as $key => $value)
			{
				$where_d = array();
				$where_d['goods_id'] = $goods_id;
				$where_d['key'] = $value;
				M('goods_spec_price')->where($where_d)->delete();
			}
			$del = 1;
		}
		else
		{
			$del = 1;
		}
		if ($itemIns)
		{
			foreach ($itemIns as $key3 => $value3)
			{
				$c3 = $spec[$value3];
				$t3['price'] = $c3['price'];
				$t3['purchase_price'] = $c3['purchase_price'];
				$t3['store_count'] = $c3['store_count'];
				$t3['integral'] = $c3['integral'];
				$t3['give_integral'] = $c3['give_integral'];
				$t3['group_price'] = $c3['group_price'];
				$t3['hour_price'] = $c3['hour_price'];
				$t3['time_price'] = $c3['time_price'];
				$t3['weight'] = $c3['weight'];
				$t3['img'] = $c3['img'];

				$where_t3['goods_id'] = $goods_id;
				$where_t3['key'] = $value3;
				M('goods_spec_price')->where($where_t3)->save($t3);
			}
			$save = 1;
		}
		else
		{
			$save = 1;
		}


		if ($itemAdd)
		{
			$arr = array();
			foreach ($itemAdd as $key2 => $value2)
			{
				$c = $spec[$value2];
				$t['key'] = $value2;
				$t['key_name'] = $c['key_name'];
				$t['price'] = $c['price'];
				$t['goods_id'] = $goods_id;
				$t['purchase_price'] = $c['purchase_price'];
				$t['store_count'] = $c['store_count'];
				$t['integral'] = $c['integral'];
				$t['give_integral'] = $c['give_integral'];
				$t['group_price'] = $c['group_price'];
				$t['hour_price'] = $c['hour_price'];
				$t['time_price'] = $c['time_price'];
				$t['weight'] = $c['weight'];
				$t['img'] = $c['img'];
				array_push($arr, $t);
			}
			if ($arr)
			{
				$add = M('goods_spec_price')->addAll($arr);
			}
			else
			{
				$add = 1;
			}
		}
		else
		{
			$add = 1;
		}
		if ($add !== false && $del !== false)
		{
			return true;
		}
		return false;
	}

	/**
	 * 输出规格对应的商品价格
	 * @param $data  key规格子项id以下划线组成的字符串  goods_id商品id
	 * @return array
	 * @mgl
	 */
	public function get_spec_goods_price($data)
	{

		$w['key'] = $data['key'];
		if (!$w['key'])
		{
			return '请输入规格子项';
		}
		$w['goods_id'] = $data['goods_id'];
		if (!$w['goods_id'])
		{
			return '请输入商品id';
		}
		$res = M('goods_spec_price')->where($w)->find();
		if ($res)
		{
			$img = full_pre_url($res['img']);
			$keyArr = explode("_", $res['key']);
			$keyName = "";
			foreach ($keyArr as $key2 => $value2)
			{
				$item = M("goods_spec_item")->where("id=" . $value2)->getField("item");
				$keyName = $keyName . $item . " ";
			}
			return array('操作成功', 0, array('price' => $res['price'], 'img' => $img, 'store_count' => $res['store_count'], 'group_price' => $res['group_price'], 'hour_price' => $res['hour_price'], 'time_price' => $res['time_price'], 'goods_id' => $data['goods_id'], 'key' => $data['key'], 'key_name' => $keyName));
		}
		else
		{
			$res = M('goods')->where('id=' . $data['goods_id'])->find();
			if ($res)
			{
				$img = full_pre_url($res['thumb']);
				return array('操作成功', 0, array('price' => $res['price'], 'img' => $img, 'store_count' => $res['store_count'], 'group_price' => 0, 'hour_price' => 0, 'time_price' => 0, 'goods_id' => $data['goods_id'], 'key' => '', 'key_name' => $keyName));
			}
			else
			{
				return array('操作成功', 0, array('price' => 0, 'img' => '', 'store_count' => 0, 'group_price' => 0, 'hour_price' => 0, 'time_price' => 0, 'goods_id' => 0, 'key' => '', 'key_name' => $keyName));
			}
		}

	}

	/**
	 * @param $ds
	 * @return array|string
	 *
	 */
	public function addSpec($ds)
	{
		$type_id = $ds['type_id'];
		$spec_id = $ds['spec_id'];
		$name = $ds['name'];
		$order = $ds['order'];
		$spec_item = explode(PHP_EOL, $ds['spec_item']);
		foreach ($spec_item as $key => $value)
		{
			$spec_item[$key] = trim($spec_item[$key]);
			$spec_item[$key] = str_replace(PHP_EOL, '', $spec_item[$key]);
			if (strlen($spec_item[$key]) < 1)
			{
				unset($spec_item[$key]);
			}

		}
		$spec_item = array_values($spec_item);

		if (!$type_id)
		{
			return '请选择类别';
		}
		if (!$name)
		{
			return '请输入规格名称';
		}

		if (!$spec_item)
		{
			return '请录入规格';
		}

		if ($spec_id)
		{
			M('goods_spec')->where('id=' . $spec_id)->save(
				array(
					'type_id' => $type_id,
					'name' => $name,
					'order' => $order,
				)
			);

			// 增加或删除时，看看是否有原规格，如果有 则保留原规格，并且不删除对应的规格价格。如果 没有原规格，则删除
			$where_have['spec_id'] = $spec_id;
			$itemOld = M("goods_spec_item")->where($where_have)->getField('item', true);  // 原规格数组

			$itemReduce = array_diff($itemOld, $spec_item);     // 减少的规格
			$itemAdd = array_diff($spec_item, $itemOld);       // 增加的规格
			$itemAdd = array_values($itemAdd);

			// 2020.02.04 删除规格时，如果该规格正在被使用，则提示不能删除
			foreach ($itemReduce as $key3 => $value3)
			{
				$where_del['spec_id'] = $spec_id;
				$where_del['item'] = $value3;
				$specFind = M('goods_spec_item')->where($where_del)->find();
				$itemDel[] = $specFind;   // 想要删除的规格ID
			}
			$noticeArr = array();
			foreach ($itemDel as $key4 => $value4)
			{
				$old_w['key'] = array('like', '%' . $value4['id'] . '%');
				$gsp = M('goods_spec_price')->where($old_w)->field('key,id')->select();
				if ($gsp)
				{
					foreach ($gsp as $c)
					{
						$exkey = explode('_', $c['key']);
						if (in_array($value4['id'], $exkey))
						{
							$noticeArr[] = $value4['item'];
						}
						else
						{
							$where_del2['id'] = $value4['id'];
							M('goods_spec_item')->where($where_del2)->delete();
						}
					}
				}
				else
				{
					$where_del2['id'] = $value4['id'];
					M('goods_spec_item')->where($where_del2)->delete();
				}

			}
			$noticeArr = array_unique($noticeArr);
			if ($noticeArr)
			{
				$noticeArr = implode("、", $noticeArr);
				$noticeStr = "【" . $noticeArr . "】以上规格不可删除或修改，因为有商品正在使用";
				return $noticeStr;
			}

			// 把准备删除的规格删除，同时把相应商品的规格价格也删除
			// 2020.02.04 下面两块代码注释：不再删除商品的相关规格，而是提示不能删除相关的规格
			// @zd
			/*
			----start-----
			foreach ($itemReduce as $key3=>$value3)
			{
				$where_del['spec_id'] = $spec_id;
				$where_del['item'] = $value3;
				$specFind = M('goods_spec_item')->where($where_del)->find();
				$itemDel[] = $specFind;
				M('goods_spec_item')->where($where_del)->delete();
			}
			foreach ($itemDel as $key4=>$value4)
			{
				$old_w['key'] = array('like', '%'.$value4['id'].'%');
				$gsp = M('goods_spec_price')->where($old_w)->field('key,id')->select();
				foreach ($gsp as $c)
				{
					$exkey = explode('_', $c['key']);
					if (in_array($value4['id'], $exkey))
					{
						M('goods_spec_price')->delete($c['id']);
					}
				}
			}
			----end-----
			*/

			// 先把新增的规格，增加上
			foreach ($itemAdd as $key2 => $value2)
			{
				$spm[$key2]['item'] = $value2;
				$spm[$key2]['spec_id'] = $spec_id;
			}
			if ($spm)
			{
				$resAdd = M('goods_spec_item')->addAll($spm);
			}
			else
			{
				$resAdd = true;
			}

			if ($resAdd)
			{
				return array('操作成功', 0);
			}
			else
			{
				return '操作失败';
			}

		}
		else
		{
			M()->startTrans();
			$id = M('goods_spec')->add(
				array(
					'type_id' => $type_id,
					'name' => $name,
					'order' => $order,
				)
			);

			$item = array();
			foreach ($spec_item as $k => $v)
			{
				$item[$k]['item'] = $v;
				$item[$k]['spec_id'] = $id;
			}
			$res = M('goods_spec_item')->addAll($item);
			if ($id && $res)
			{
				M()->commit();
				return array('操作成功', 0, $id);
			}
			M()->rollback();
			return '添加失败';
		}
	}

	/**
	 * @param $ds
	 * @return array|string
	 *
	 */
	public function updateSpec($ds)
	{

		$type_id = $ds['type_id'];
		$spec_id = $ds['spec_id'];
		$name = $ds['name'];
		$order = $ds['order'];
		$spec_item = explode(PHP_EOL, $ds['spec_item']);

		if (!$type_id)
		{
			return '请选择类别';
		}
		if (!$name)
		{
			return '请输入规格名称';
		}

		if (!$spec_item)
		{

			return '请请录入规格';
		}

		if ($spec_id)
		{
			M('goods_spec')->where('id=' . $spec_id)->save(
				array(
					'type_id' => $type_id,
					'name' => $name,
					'order' => $order,
				)
			);


			$old_s = M('goods_spec_item')->where('spec_id=' . $spec_id)->getField('id', true);
			$old_w['key'] = array('like', $old_s, 'or');
			$gsp = M('goods_spec_price')->where($old_w)->field('key,id')->select();
			M()->startTrans();
			foreach ($gsp as $c)
			{
				$tt = explode('_', $c['key']);
				foreach ($tt as $t)
				{
					if (in_array($t, $old_s))
					{

						M('goods_spec_price')->delete($c['id']);


					}
				}
				unset($tt);
			}
			unset($old_s);
			unset($old_w);
			unset($gsp);
			M('goods_spec_item')->where('spec_id=' . $spec_id)->delete();
			$item = array();
			foreach ($spec_item as $k => $v)
			{
				$item[$k]['item'] = $v;
				$item[$k]['spec_id'] = $spec_id;
			}
			$r = M('goods_spec_item')->addAll($item);
			if ($r)
			{
				M()->commit();
				return array('操作成功', 0);
			}
			M()->rollback();
			return '添加失败';


		}
		else
		{
			M()->startTrans();
			$id = M('goods_spec')->add(
				array(
					'type_id' => $type_id,
					'name' => $name,
					'order' => $order,
				)
			);

			$item = array();
			foreach ($spec_item as $k => $v)
			{
				$item[$k]['item'] = $v;
				$item[$k]['spec_id'] = $id;
			}
			$res = M('goods_spec_item')->addAll($item);
			if ($id && $res)
			{
				M()->commit();
				return array('操作成功', 0, $id);
			}
			M()->rollback();
			return '添加失败';
		}


	}

	/**
	 *
	 * @param int $id 必须    int 规格表id
	 * @return array
	 */
	public function deleteSpec($data)
	{

		$id = $data['id'];
		if (!$id)
		{
			return '请输入id';
		}

		$spec = M('goods_spec')->find($id);
		$old_s = M('goods_spec_item')->where('spec_id=' . $spec['id'])->getField('id', true);
		$old_w['key'] = array('like', $old_s, 'or');
		$gsp = M('goods_spec_price')->where($old_w)->field('key,id')->select();
		M()->startTrans();
		foreach ($gsp as $c)
		{
			$tt = explode('_', $c['key']);
			foreach ($tt as $t)
			{
				if (in_array($t, $old_s))
				{
					M('goods_spec_price')->delete($c['id']);
				}
			}
			unset($tt);
		}
		unset($old_s);
		unset($old_w);
		unset($gsp);
		M('goods_spec_item')->where('spec_id=' . $spec['id'])->delete();
		$s = M('goods_spec')->delete($id);

		if ($s)
		{
			M()->commit();
			return array('操作成功', 0);
		}
		M()->rollback();
		return '操作失败';
	}

	/**
	 *
	 * 后台专用
	 * @param int $id 必须    int 规格表id
	 * @param int $type 必须    int 类型id
	 * @return array
	 */
	public function AjaxDeleteSpec($data)
	{

		$id = $data['id'];
		if (!$id)
		{
			return '请输入id';
		}


		$w['key'] = array('like', "%$id%");
		$gsp = M('goods_spec_price')->where($w)->field('key,id')->select();
		unset($w);

		$f = false;
		foreach ($gsp as $c)
		{
			$tt = explode('_', $c['key']);
			if (in_array($tt, $id))
			{
				$f = true;
				break;
			}
			unset($tt);
			if ($f)
			{
				break;
			}
		}

		if ($f)
		{
			return '操作失败,存在该规格商品,不能删除';
		}
		M()->startTrans();
		$gsi = M('goods_spec_item')->where('id=' . $id)->find();
		M('goods_spec_item')->delete($id);
		$count = M('goods_spec_item')->where('spec_id=' . $gsi['spec_id'])->count();
		if ($count <= 0)
		{
			M('goods_spec')->delete($gsi['spec_id']);
		}
		M()->commit();
		return array('操作成功', 0);

	}

	/**
	 *
	 * @param int $pid 可选  int 父类id
	 * @param int $id 可选  int 类别表id
	 * @param string $name 可选  string 类别名称
	 * @param string $img 可选  string 类别图片
	 * @return string
	 * @mgl
	 */
	public function editCategory($ds)
	{
		$pid = I('pid', 0);
		$id = $ds['id'];
		$name = $ds['name'];
		if (empty($name))
		{
			return '分类名不能为空';
		}
		$sql['pid'] = $pid;
		$sql['name'] = $name;
		$c = M('goods_category')->where($sql)->count();
		if ($c && !$id)
		{
			return '分类名称重复';
		}
		unset($c);
		unset($sql);

		if (!$id)
		{
			$info = uploads("img", '', '', '', 0);
			if ($info[1] < 1 && $info)
			{
				$data['img'] = $info[2];
			}
			else
			{
				$data['img'] = C("C_DF_CATE");
			}

			if (!$pid)
			{
				$data = ['name' => $name, 'pid' => 0, 'level' => 1, 'img' => $info['2']];
			}
			else
			{
				$gc_pid = M('goods_category')->where('id=' . $pid)->find();
				if (!$gc_pid)
				{
					return '父级分类不存在！';
				}
				if ($gc_pid['level'] == 2)
				{
					if (!$_FILES["img"]["name"])
					{
						return '请上传图片';
					}
					unset($gc_pid);
					$data = ['name' => $name, 'pid' => $pid, 'level' => 3, 'img' => $info['2']];
				}
				else
				{
					$data = ['name' => $name, 'pid' => $pid, 'level' => 2, 'img' => $info['2']];
				}
			}
			$res = M('goods_category')->add($data);
			unset($data);
			if ($res)
			{

				return array('添加成功', 0);
			}
			return '添加失败！';
		}
		else
		{
			$data['name'] = $name;
			// $info = common_upload_photo('img', 'category');
			if ($_FILES["img"]["name"])
			{
				$info = uploads("img", '', '', '', 0);
				if ($info[1] < 1 && $info)
				{
					$data['img'] = $info[2];
				}
				else
				{
					$data['img'] = C("C_DF_CATE");
				}
			}
			$data['pid'] = $pid;

			$res = M('goods_category')->where('id=' . $id)->save($data);
			unset($data);
			if ($res !== false)
			{
				return array('修改成功', 0);
			}
			return '修改失败！';
		}
	}

	public function category_list($data)
	{

		if ($data['pid'])
		{
			$where['pid'] = $data['pid'];
		}
		if ($data['level'])
		{
			$where['level'] = $data['level'];
		}
		if ($data['id'])
		{
			$where['id'] = $data['id'];
		}
		if (!$where)
		{
			$where['pid'] = 0;
		}
		// 安卓版本 在分类界面做二级菜单悬浮，需要在三级菜单中增加二级菜单的名称
		$fall = M('goods_category')->where($where)->select();
		foreach ($fall as $k => $v)
		{
			// 如果是三级，直接查找二级名称
			if ($v['level'] == 3 && $v['pid'] > 0)
			{
				$upName = M("goods_category")->where("id=" . $v['pid'])->getField("name");
				$fall[$k]['up_name'] = $upName;
			}

			// 广告
			// 小程序单独请求二级分类时，也要输出广告 @zd.2020.08.04
			if ($v['level'] == 2)
			{
				$ad = M('ad')->field('id,photo,module,mod_id,api_url,link')->where('cate_id=' . $v['pid'])->find();
			}
			else
			{
				$ad = M('ad')->field('id,photo,module,mod_id,api_url,link')->where('cate_id=' . $v['id'])->find();
			}

			if ($ad)
			{
				if ($ad['photo'])
				{
					$fall[$k]['display'] = 1;
				}
				else
				{
					$fall[$k]['display'] = 0;
				}
			}
			else
			{
				$fall[$k]['display'] = 0;
			}
			// 这里可能是二级、或三级分类
			$fall[$k]['gc'] = M('goods_category')->where('pid=' . $v['id'])->select();
			foreach ($fall[$k]['gc'] as $kk => $vv)
			{
				$fall[$k]['gc'][0]['photo'] = $ad['photo'];
				$fall[$k]['gc'][0]['module'] = $ad['module'];
				$fall[$k]['gc'][0]['mod_id'] = $ad['mod_id'];
				$fall[$k]['gc'][0]['api_url'] = $ad['api_url'];
				$fall[$k]['gc'][0]['link'] = $ad['link'];
				$fall[$k]['gc'][$kk]['display'] = 0;
				$fall[$k]['gc'][0]['display'] = $fall[$k]['display'];

				// 如果是三级分类，则增加二级分类名
				if ($vv['level'] == 3)
				{
					$fall[$k]['gc'][$kk]['up_name'] = $v['name'];
				}

				// 这里可能是三级分类
				$list3 = M('goods_category')->where('pid=' . $vv['id'])->select();
				if ($vv['level'] == 2)
				{
					foreach ($list3 as $key3 => &$value3)
					{
						$value3['up_name'] = $vv['name'];
					}
				}
				$fall[$k]['gc'][$kk]['gc'] = $list3;
			}
		}
		return array('商品分类', 0, $fall);
	}

	/**
	 * 查询一个一级分类的所有子分类
	 * @param int pid  一级分类id
	 */
	public function category_one($pid, $target = array())
	{
		$where['pid'] = $pid;
		$category = M('goods_category')->where($where)->select();
		if ($category)
		{
			foreach ($category as $c)
			{
				$target[$c['id']] = $c;
				$target = $this->category_one($c['id'], $target);
			}
		}
		return $target;
	}

	/**
	 * @param int $id 可选  int 类别id
	 * @return string
	 * @mgl
	 */
	public function deleteCategory($data)
	{


		$c = M('goods_category')->where('pid=' . $data['id'])->count();
		if (!empty($c))
		{
			return '含有子类不能删除';
		}
		$cc = M('goods')->where('cat_id=' . $data['id'])->count();
		if (!empty($cc))
		{
			return '这个分类含有商品不能删除';
		}
		$res = M('goods_category')->where('id=' . $data['id'])->delete();
		if ($res)
		{
			return array('删除成功', 0);
		}
		return '删除失败,请确认类别id';

	}

	/**
	 * @param int $id 可选  int 评论id
	 * @return string
	 * @mgl
	 */
	public function delete_goods_comment($data)
	{


		$res = M('goods_comment')->where('id=' . $data['id'])->delete();
		if ($res)
		{
			return array('删除成功', 0);
		}
		return '删除失败,请确认id';

	}


	/**
	 * 商品详情
	 * @param int $id 必须
	 * @return array
	 * @mgl
	 */
	public function detail($data)
	{
		$goods_id = $data['id'];
		$user_id = $data['user_id'];
		$group_number = $data['group_number'] ? $data['group_number'] : 10;
		$goods_kind = $data['goods_kind'] ? $data['goods_kind'] : 0;    // 0常规商品，1团购，2整点秒杀，3限时购

		if (!$goods_id)
		{
			return '商品不存在或下架';
		}

		$order_out = new Orderout();
		$goods = $order_out->getGoods($goods_id);
		$goods['service_info'] = $this->get_service_info($goods_id);//获取服务信息
		$goods['service_name'] = '';//将服务内容拼接字符串输出
		if ($goods['service_info'])
		{
			for ($i = 0; $i < count($goods['service_info']); $i++)
			{
				if ($i == count($goods['service_info']) - 1)
				{
					$goods['service_name'] .= $goods['service_info'][$i]['title'];
				}
				else
				{
					$goods['service_name'] .= $goods['service_info'][$i]['title'] . ' • ';
				}

			}
		}
		if (empty($goods) || $goods['is_on_sale'] != 1)
		{
			return '商品不存在或下架';
		}

		if ($goods['images'])
		{
			$images = json_decode($goods['images']);
		}
		else
		{
			$images[] = $goods['thumb'];
		}

		$shippingRule = M("express_rule")->where("id=" . $goods['shipping_rule_id'])->find();
		if (!$shippingRule)
		{
			$shipping_fee = 0;
		}
		else
		{
			$shipping_fee = $shippingRule['first_price'];
		}
		$ranking_val = $this->calculation_goods_ranking($goods_id);//获取该商品排名
		//获取商品排行列表（显示三条）
		$ranking_list = $this->ranking_list($goods['shop_id'], 3);
		$data = array(
			'id' => $goods_id,
			'name' => $goods['name'],
			'subhead' => $goods['subhead'],
			'price' => $goods['price'],
			'market_price' => $goods['market_price'],
			'shipping_fee' => $shipping_fee,
			'thumb' => $goods['thumb'],
			'sell_count' => $goods['sell_count'],
			'store_count' => $goods['store_count'],
			'time' => $goods['time'],
			'cat_id' => $goods['cat_id'],
			'shop_id' => $goods['shop_id'],
			'group_number' => $group_number,
			'video' => $goods['video'],
			'service_info' => $goods['service_info'],
			'service_name' => $goods['service_name'],
			'content' => $goods['content'],
			'ranking_val' => $ranking_val,//排名
			'product_type' => $goods['product_type'],//排名

		);

		$where_s["id"] = $goods['shop_id'];
		$shop = get_user_info($where_s, "*", 0, 2);
		$is_offline_shop = 0;//线下门店关闭
		if ($shop)
		{
			// 有商户只获取商户信息
			$shop_name = $shop['shop_name'];
			$shop_phone = $shop['tel'] ? $shop['tel'] : "";
			$logo = $shop['thumb'];
			$qq = $shop['qq'];
			$fav_count = M('favorites')->where("fav_id={$goods['shop_id']} and type=2")->count();

			$shop_goods = M('goods')->field('id,thumb,name')->where('isdel=0 and is_on_sale=1 and shop_id=' . $goods_id['shop_id'])->order('id desc')->limit('0,6')->select();
			if ($user_id)
			{
				$is_fav_shop = M('favorites')->where("fav_id={$goods['shop_id']} and type=2 and user_id=" . $user_id)->count();
			}
			else
			{
				$is_fav_shop = 0;   // 0未收藏店铺，1收藏
			}
			$is_offline_shop = $shop['is_offline_shop'];//获取该商家的线下门店
		}
		else
		{
			$shop_name = '自营';
			$shop_phone = get_config('kefu_mobile');
			$logo = get_config("logo");
			$qq = get_config("qq");
			$fav_count = M('favorites')->where("type=2")->count();
			$shop_goods = M('goods')->field('id,thumb,name')->where('isdel=0 and is_on_sale=1 and shop_id=0')->limit('0,6')->select();
			$is_fav_shop = 0;   // 默认不收藏
		}

		$logo = full_pre_url($logo);
		$goods_count = M('goods')->where('shop_id=' . $goods['shop_id'])->count();

		$goods_ids = M('goods')->where('shop_id=' . $goods['shop_id'])->getField('id', true);

		$where['shop_id'] = $goods['shop_id'];
		$where['order_status'] = array("gt", 0);
		$sell_count = M('order_goods')->where($where)->sum('goods_num');
		$sell_count = $sell_count ? $sell_count : 0;

		unset($where);
		unset($goods_ids);

		$def_spec_price = M('goods_spec_price')->field('key,price,img,store_count,group_price,hour_price,time_price')->where('goods_id=' . $goods_id)->order("price asc")->find();
		if (!$def_spec_price)
		{
			$def_spec_price = array();
		}
		else
		{
			$data['price'] = $def_spec_price['price'];
			$def_spec_price['img'] = full_pre_url($def_spec_price['img']);
			// 输出中文规格名
			$keyArr = explode("_", $def_spec_price['key']);
			$keyStrDF = "";
			foreach ($keyArr as $keydf2 => $valuedf2)
			{
				$itemdf = M("goods_spec_item")->where("id=" . $valuedf2)->getField("item");
				$keyStrDF = $keyStr . $itemdf . " ";
			}
			$def_spec_price['key_name'] = $keyStrDF;
		}

		$Fav = new Favorite(0);

		// 根据商品附加分类，处理相应的输出
		// 团购：需要输出：团购信息+前10个还没拼团的订单，显示最先下单的
		$group_list = array();    // 拼团列表
		if ($goods_kind == 1)
		{
			$groupInfo = M("goods_group")->where("goods_id=" . $data["id"])->find();
			if (!$groupInfo)
			{
				$goods["is_group"] = 0;
			}
			$data["team_people_num"] = $groupInfo["team_people_num"] ? $groupInfo["team_people_num"] : 0;    // 拼团人数
			$group_price = M("goods_spec_price")->where("goods_id=" . $data["id"])->order("group_price asc")->getField("group_price");
			$data["group_price"] = $group_price;
			$data["diff_price"] = round($data["price"] - $group_price, 2);    // 差额
			$group_list = $this->unGroupList($data["id"], $data['group_number']);    // 未成功才列表
            foreach ($group_list as $k => $v){
                $str_1 = mb_substr($v['name'], 0, 3, 'utf-8');
                $str_2 = mb_substr($v['name'], -1, 1, 'utf-8');
                $group_list[$k]['name'] =$str_1 . '***' . $str_2;
            }
		}
		else
		{
			if ($goods_kind == 2)
			{
				$hour_list = M('goods_hour')->where("goods_id=$goods_id and type=0")->find();
				$priceInfo = M("goods_spec_price")->where("goods_id=" . $data["id"])->order("hour_price asc")->find();
				$data['hour_price'] = $priceInfo['hour_price'];
				$data['start_time'] = $hour_list['start_time'];
				$data['end_time'] = $hour_list['end_time'];
			}
			else
			{
				if ($goods_kind == 3)
				{
					$time_list = M('goods_hour')->where("goods_id=$goods_id and type=1")->find();
					$priceInfo = M("goods_spec_price")->where("goods_id=" . $data["id"])->order("time_price asc")->find();
					$data['time_price'] = $priceInfo['time_price'];
					$data['start_time'] = $time_list['start_time'];
					$data['end_time'] = $time_list['end_time'];
				}
			}
		}
		//商品浏览记录
		$user_browse = array();
		if ($user_id)
		{
			$footprint = array(
				'goods_kind' => $goods_kind,
				'goods_id' => $goods_id,
				'user_id' => $user_id,
				'cre_time' => time(),
			);
			M('goods_footprint')->add($footprint);
			//获取我的足迹（浏览记录）
			$user = new User();
			$where['f.user_id'] = $user_id;
			$user_browse = $user->footprint($where, 'f.id desc', 10);
			$user_browse = array_slice($user_browse, 0, 3);
		}
		// 倒计时，这里要修改 *********
		$data['last_time'] = 3600;

		// 为你推荐，获取同类目、同商家下的6条商品
		$recommend_goods = get_recommend_goods($goods_id);

		// 获取商品总评论，及最新的3条
		$goods_comment_total = M("goods_comment")->where("goods_id=" . $goods_id)->count();
		$where_gc['id'] = $goods_id;
		$where_gc['page'] = 1;
		$where_gc['page_size'] = 3;
		$order_gc = "id desc,service_attitude desc,commodity_quality desc";
		$goods_comment_last = $this->goods_comment($where_gc, $order_gc);
		if ($goods_comment_last[1] == 1)
		{
			$goods_comment_last = array();
		}
		else
		{
			$goods_comment_last = $goods_comment_last[2];
		}


		$goods_comment['total'] = $goods_comment_total;
		$goods_comment['last'] = $goods_comment_last;
		$goods_comment['last_total'] = count($goods_comment_last);

		$share['title'] = "我发现了一个很棒的商品，快来看看吧";
		$share['desc'] = $data['name'];
		$share['thumb'] = $data['thumb'];
		$share['share_url'] = C('C_HTTP_HOST') . '/index.php/M/Goods/detail?share_uid=' . $user_id . '&id=' . $goods_id;

		return array('商品详情', 0, array(
			'goods_kind' => $goods_kind,    // 0常规商品，1团购，2整点秒杀，3限时购
			'goods' => $data,
			'images' => $images ? $images : [],
			'goods_category' => M('goods_category')->find($goods['cat_id']),
			'spec_list' => $this->goods_spec_list($goods_id, 1),
			'def_spec_price' => $def_spec_price,
			'favorite_count' => $Fav->favorite_count($goods_id, 1),
			'is_favorite' => $Fav->is_favorite($user_id, $goods_id, 'goods'),
			'couponList' => $this->couponList($user_id, $goods_id),//商品优惠券
			'shop' => array(
				'shop_id' => $goods['shop_id'],
				'name' => $shop_name,
				'logo' => $logo,
				'phone' => $shop_phone,
				'goods_count' => $goods_count,
				'sell_count' => $sell_count,
				'like_count' => $fav_count,
				'qq' => $qq,
				'is_fav_shop' => $is_fav_shop,
				'is_offline_shop' => $is_offline_shop,//输出该商品是否开启线下门店0是未开启1是已开启
			),
			'shop_goods' => $shop_goods,
			'group_list' => $group_list,
			'recommend_goods' => $recommend_goods,
			'user_browse' => $user_browse,//会员浏览记录
			'ranking_list' => $ranking_list,//店铺商品热榜显示三条
			'goods_comment' => $goods_comment,//商品评论信息
			'share' => $share, // 分享信息
		)
		);
	}

	//优惠券列表
	public function couponList($user_id, $goods_id, $shop_id)
	{
		$where = "status= 1 and is_del=0 and type in (1,3) and money>0";
		if ($goods_id)
		{
			$where .= " and goods_id=" . $goods_id;
		}
		if ($shop_id)
		{
			$where .= " and shop_id=" . $shop_id;
		}
		$list = M("coupon")->where($where)->field("cre_date,cre_time,ip,status,is_del", true)->order("id desc")->select();
		foreach ($list as $key => &$value)
		{
			// 该用户是否领取过此优惠券
			if ($user_id)
			{
				$where_uc['cid'] = $value['id'];
				$where_uc['user_id'] = $user_id;
				$userCoupon = M("coupon_user")->where($where_uc)->find();
				if ($userCoupon)
				{
					$value['is_have'] = 1;    // 已领取过
				}
				else
				{
					$value['is_have'] = 0;    // 未领取过
				}
			}
			else
			{
				$value['is_have'] = 0;    // 未领取过
			}

			// 满减提示
			$value['conditions_cn'] = "满" . $value['conditions'] . "元可用";
			$shopName = "全平台通用优惠券";
			$couponType = "平台券";
			$newUser = "";            // 是否仅新用户可用 -- 即以前未使用过优惠券的
			$effectiveTime = "";    // 有效时间
			if ($value['shop_id'])
			{
				$shopName = M("shop")->where("id=" . $value['shop_id'])->getField("shop_name");
				$shopName = $shopName ? $shopName : "平台商家";
				$shopName = "仅限" . $shopName . "使用";
				$couponType = "商家券";
			}
			if ($value['goods_id'])
			{
				$shopName = M("goods")->where("id=" . $value['goods_id'])->getField("name");
				$shopName = $shopName ? $shopName : "指定商品";
				$shopName = "仅限" . $shopName . "使用";
				$couponType = "商品券";
			}
			$value['shop_name'] = $shopName;
			$value['coupon_type'] = $couponType;
			if ($value['to_new_user'])
			{
				$newUser = "[新用户]";
			}
			$value['to_new_user_cn'] = $newUser;

			if ($value['start_type'] == 1)
			{
				$effectiveTime = "领取后{$value['use_days']}天有效期";
			}
			else
			{
				$effectiveTime = date("Y.m.d", $value['use_start_time']) . " - " . date("Y.m.d", $value['use_end_time']);
			}
			$value['effective_time'] = $effectiveTime;

			// 是否被全部领取完了
			if ($value['send_num'] > $value['get_num'])
			{
				$value['sell_out'] = 0;    // 还没有被抢完
			}
			else
			{
				$value['sell_out'] = 1;    // 已被抢完
			}

		}
		if (!$list)
		{
			return $list;
		}
		else
		{
			return $list;
		}

	}

	/**
	 * 商家APP 编辑商品
	 * @param int $id 必须
	 * @return array
	 * @mgl
	 */
	public function app_goods_detail($data)
	{
		_error_log(1111);
		_error_log($data);
		$goods_id = $data['id'];
		$user_id = $data['user_id'];
		$group_number = $data['group_number'] ? $data['group_number'] : 10;
		$goods_kind = $data['goods_kind'] ? $data['goods_kind'] : 0;    // 0常规商品，1团购，2整点秒杀，3限时购

		if (!$goods_id)
		{
			return '商品不存在';
		}
		$order_out = new Orderout();
		$goods = $order_out->getGoods($goods_id);
		if (empty($goods))
		{
			return '商品不存在';
		}

		if ($goods['images'])
		{
			$images = json_decode($goods['images']);
		}
		else
		{
			$images[] = $goods['thumb'];
		}
		if ($goods['hot_id'] == 2)
		{
			$is_cart = 0;
		}
		else
		{
			$is_cart = 1;
		}
		$goods_type = M('goods_type')->field('id,name')->find($goods['type_id']);
		$data = array(
			'id' => $goods_id,
			'name' => $goods['name'],
			'subhead' => $goods['subhead'],
			'price' => $goods['price'],
			'market_price' => $goods['market_price'],
			'shipping_fee' => $goods['shipping_fee'],
			'thumb' => $goods['thumb'],
			'sell_count' => $goods['sell_count'],
			'store_count' => $goods['store_count'],
			'time' => $goods['time'],
			'cat_id' => $goods['cat_id'],
			'shop_id' => $goods['shop_id'],
			'group_number' => $group_number,
			'video' => $goods['video'],
			'sort' => $goods['sort'],
			'is_pick' => $goods['is_pick'],
			'is_shipping' => $goods['is_shipping'],
			'goods_type' => $goods_type ? $goods_type : (object)array(),
			'is_on_sale' => $goods['is_on_sale'],
			'shipping_rule' => M('express_rule')->field('id,name')->find($goods['shipping_rule_id']),
			'express' => M('express')->field('id,name')->find($goods['shipping_rule_id']),
			'content' => htmlspecialchars_decode($goods['content'])

		);

		$where_s["id"] = $goods['shop_id'];
		$shop = get_user_info($where_s, "*", 0, 2);

		if ($shop)
		{
			// 有商户只获取商户信息
			$shop_name = $shop['shop_name'];
			$shop_phone = $shop['tel'] ? $shop['tel'] : "";
			$logo = $shop['thumb'];
			$qq = $shop['qq'];
			$fav_count = M('favorites')->where("fav_id={$goods['shop_id']} and type=2")->count();
		}
		else
		{
			$shop_name = '自营';
			$shop_phone = get_config('kefu_mobile');
			$logo = get_config("logo", 1);
			$qq = get_config("qq");
			$fav_count = M('favorites')->where("type=2")->count();
		}

		$logo = full_pre_url($logo);
		$goods_count = M('goods')->where('shop_id=' . $goods['shop_id'])->count();

		$goods_ids = M('goods')->where('shop_id=' . $goods['shop_id'])->getField('id', true);

		$where['shop_id'] = $goods['shop_id'];
		$where['order_status'] = array("gt", 0);
		$sell_count = M('order_goods')->where($where)->sum('goods_num');
		$sell_count = $sell_count ? $sell_count : 0;

		unset($where);
		unset($goods_ids);

		$def_spec_price = M('goods_spec_price')->field('price,img,store_count')->where('goods_id=' . $goods_id)->find();
		if (!$def_spec_price)
		{
			$def_spec_price = array();
		}

		$Fav = new Favorite(0);

		// 根据商品附加分类，处理相应的输出
		// 团购：需要输出：团购信息+前10个还没拼团的订单，显示最先下单的
		$group_list = array();    // 拼团列表
		if ($goods_kind == 1)
		{
			$groupInfo = M("goods_group")->where("goods_id=" . $data["id"])->find();
			if (!$groupInfo)
			{
				$goods["is_group"] = 0;
			}
			$data["team_people_num"] = $groupInfo["team_people_num"] ? $groupInfo["team_people_num"] : 0;    // 拼团人数
			$group_price = M("goods_spec_price")->where("goods_id=" . $data["id"])->order("group_price asc")->getField("group_price");
			$data["group_price"] = $group_price;
			$data["diff_price"] = round($data["price"] - $group_price, 2);    // 差额
			$group_list = $this->unGroupList($data["id"], $data['group_number']);    // 未成功才列表
		}
		else
		{
			if ($goods_kind == 2)
			{
				$hour_list = M('goods_hour')->where("goods_id=$goods_id and type=0")->find();
				$priceInfo = M("goods_spec_price")->where("goods_id=" . $data["id"])->order("hour_price asc")->find();
				$data['hour_price'] = $priceInfo['hour_price'];
				$data['start_time'] = $hour_list['start_time'];
				$data['end_time'] = $hour_list['end_time'];
			}
			else
			{
				if ($goods_kind == 3)
				{
					$time_list = M('goods_hour')->where("goods_id=$goods_id and type=1")->find();
					$priceInfo = M("goods_spec_price")->where("goods_id=" . $data["id"])->order("time_price asc")->find();
					$data['time_price'] = $priceInfo['time_price'];
					$data['start_time'] = $time_list['start_time'];
					$data['end_time'] = $time_list['end_time'];
				}
			}
		}
		//商品浏览记录
//        if ($user_id) {
//            $footprint = array(
//                'goods_kind' => $goods_kind,
//                'goods_id' => $goods_id,
//                'user_id' => $user_id,
//                'cre_time' => time(),
//            );
//            M('goods_footprint')->add($footprint);
//        }
		$user_id = I('user_id');
		if ($user_id)
		{
			$phone = M('user')->where('id=' . $user_id)->getField('phone');
			$share_url = C('C_HTTP_HOST') . 'index.php/M/Goods/detail?share=1&phone=' . $phone . '&id=' . $goods_id;
		}
		else
		{
			$share_url = '';

		}
		if ($goods["cat_id"])
		{
			$cat3Info = M("goods_category")->field('id,name,pid')->where("id=" . $goods["cat_id"])->find();
			$cat3 = M("goods_category")->where("pid=" . $cat3Info["pid"])->select();    // 第三级同级
		}
		if ($cat3Info["pid"])
		{
			// 第二级及全部同级
			$cat2Info = M("goods_category")->field('id,name,pid')->where("id=" . $cat3Info["pid"])->find();
			$cat2 = M("goods_category")->where("pid=" . $cat2Info["pid"])->select();
		}
		if ($cat2Info["pid"])
		{
			// 第一级及全部同级
			$cat1Info = M("goods_category")->field('id,name,pid')->where("id=" . $cat2Info["pid"])->find();
			$cat1 = M("goods_category")->where("pid=" . $cat2Info["pid"])->select();
		}
//        $catinfo["c3"] = $goods["cat_id"];
//        $catinfo["c2"] = $cat3Info["pid"];
//        $catinfo["c1"] = $cat2Info["pid"];
		$catinfo[0] = $cat1Info;
		$catinfo[1] = $cat2Info;
		$catinfo[2] = $cat3Info;

		return array('商品详情', 0, array(
			'goods_kind' => $goods_kind,    // 0常规商品，1团购，2整点秒杀，3限时购
			'goods' => $data,
			'images' => $images ? $images : [],
			'goods_category' => $catinfo,
			'spec_list' => $this->goods_spec_list($goods_id, 1),
			'def_spec_price' => $def_spec_price,
			'favorite_count' => $Fav->favorite_count($goods_id, 1),
			'is_favorite' => $Fav->is_favorite($user_id, $goods_id, 'goods'),
			'shop' => array(
				'shop_id' => $goods['shop_id'],
				'name' => $shop_name,
				'logo' => $logo,
				'phone' => $shop_phone,
				'goods_count' => $goods_count,
				'sell_count' => $sell_count,
				'like_count' => $fav_count,
				'qq' => $qq,
			),
			'group_list' => $group_list,
			'is_cart' => $is_cart,
			'share_url' => $share_url
		)
		);
	}

	/**
	 * 商品详情展示页面
	 * @param int $goods_id 商品id必须
	 * @mgl
	 */
	public function goods_detail($data = null)
	{
		if (!$data)
		{
			$data = I();
		}

		$content = M('goods')->where('id=' . $data['goods_id'])->getField('content');

		return $content;
	}

	/**
	 *
	 * 获取商品规格
	 * @param $goods_id 商品id
	 * @param $choose    是否已被选择，1是，0否
	 * @return array  规格数组
	 */
	private function goods_spec_list($goods_id, $choose = 0)
	{
		$all = M('goods_spec_price')->field('key')->where('goods_id=' . $goods_id)->find();

		$arr = array();
		$chooseArr = array();

		foreach ($all as $v)
		{
			$g = explode('_', $v);
			$w['id'] = array('in', $g);
			$res = M('goods_spec_item')->where($w)->group('spec_id')->getField('spec_id', true);
			$arr = array_merge($arr, $res);
		}
		array_unique($arr);

		$chooseItem = M('goods_spec_price')->where('goods_id=' . $goods_id)->getField("key", true);

		$chooseItem = implode("_", $chooseItem);
		$chooseArr = explode("_", $chooseItem);
		array_unique($chooseArr);

		if ($arr)
		{
			$w['id'] = array('in', $arr);
			$all = M('goods_spec')->where($w)->select();
			if (!$all)
			{
				return '规格不存在';
			}
			foreach ($all as &$gs)
			{
				$where_item["spec_id"] = $gs['id'];

				if ($choose && $chooseArr)
				{
					$where_item["id"] = array("in", $chooseArr);
				}
				else
				{
					// $gs['goods_spec_item'] = M('goods_spec_item')->where('spec_id=' . $gs['id'])->select();
				}

				$gs['goods_spec_item'] = M('goods_spec_item')->where($where_item)->select();

			}
			return $all;
		}
		return [];

	}

	/**
	 * 获取商店
	 * @param $s  商店id单个或集合
	 * @return array  返回商家信息
	 */
	private
	function get_shop($s)
	{
		if (empty($s))
		{
			return '';
		}
		if (is_array($s))
		{
			$list = M('shop')->where('id in(' . join(',', $s) . ')')->select();
			$shop = map($list, false);
		}
		else
		{
			$shop = M('shop')->find($s);
		}
		return $shop;
	}


	/**
	 *
	 *商品评论列表
	 * @param int $goods_id 必须
	 * @param int $user_id_eva 可选，查看本人的评价
	 * @param int $page 当前页
	 * @param int $page_size 每页显示数量
	 * @return json|string
	 * @mgl
	 */
	function goods_comment($data, $order = 'id desc')
	{
		$page = $data['page'];
		$page_size = $data['page_size'];

		if ($data['id'])
		{
			$where['goods_id'] = array('in', $data['id']);
		}
		if ($data['user_id_eva'])
		{
			$where['user_id'] = $data['user_id_eva'];
		}
		if (isset($data['shop_id']))
		{
			$where['shop_id'] = $data['shop_id'];
		}
		if (!$order)
		{
			$order = "id desc";
		}


		$goods_comment = M('goods_comment')->where($where)->page($page, $page_size)->order($order)->select();

		foreach ($goods_comment as &$v)
		{
			if ($v["images"])
			{
				$images = json_decode(htmlspecialchars_decode($v["images"]), true);

			}
			else
			{
				$images = array();
			}
			$v['images'] = $images;
			if ($v["time"])
			{
				$time = date("Y-m-d", $v['time']);

			}
			else
			{
				$time = date("Y-m-d");
			}
			$v["time"] = $time;
			$order = M('order')->where('id=' . $v['order_id'])->field("id,order_no,shop_note")->find();
			if ($order)
			{
				$v['order'] = $order;
				$v['spec_name'] = M('order_goods')->where('order_id=' . $order['id'])->getField('goods_spec_price_name', true);
			}
			else
			{
				$order["id"] = "0";
				$order["order_no"] = "";
				$order["shop_note"] = "";
				$v['order'] = $order;
				$v['spec_name'] = "";
			}
			$where_u['id'] = $v['user_id'];
			$field = "id,phone,realname,level,username,nickname,headimgurl";
			$userInfo = get_user_info($where_u, $field);
			if ($userInfo)
			{
				$userInfo['headimgurl'] = get_user_info_beauty($userInfo, 3, 0);
				$v['headimgurl'] = $userInfo['headimgurl'];
				if ($v['is_hide_name'])
				{
					$userInfo['phone'] = get_user_info_beauty($userInfo, 1, 2);
					$userInfo['nickname'] = get_user_info_beauty($userInfo, 2, 2);
					$v['user'] = $userInfo;
					$v['nickname'] = $userInfo['nickname'];
				}
				else
				{
					$v['user'] = $userInfo;
					$v['nickname'] = $userInfo['nickname'];
				}
			}
			else
			{

				$v['headimgurl']   = '/Public/images/profile.png';
				$v['nickname'] = '';
			}

		}
		if ($goods_comment)
		{
			return array('商品评论', 0, $goods_comment);
		}
		else
		{
			return array('加载完成', 1);
		}

	}


//得到商品信息
	function get_goods($id)
	{
		$Mg = M('goods');
		if (empty($id))
		{
			return false;
		}
		if (is_array($id))
		{
			$str = 'id,name,price,market_price,shipping_fee,thumb,store_count,shop_id,is_on_sale,is_check';
			$goods = $Mg->where('id in(' . join(',', $id) . ')')->getField($str);
		}
		else
		{
			$goods = $Mg->find($id);
		}
		return $goods;
	}


	// $extCateArr 附属分类
	public function goodsList($where, $order = "id desc", $num = 10, $paging = array(), $extGoodsArr)
	{
		// 搜索条件
		if (!isset($where["isdel"]))
		{
			$where["isdel"] = 0;
		}

		$num = $num <= 0 ? 10 : $num;
		if (!$paging)
		{
			$paging['page'] = 1;
			$paging['last_id'] = 0;
		}
		// 如果 last_id 和 page 都 <=0 即表示刚开始获取数据
		// 如果 last_id 和 page 都 >0，则以 last_id 为先
		if ($paging['last_id'] > 0)
		{
			$where['id'] = array("lt", $paging['last_id']);
			$limit = $num;
		}
		else
		{
			$paging['page'] = $paging['page'] <= 1 ? 1 : $paging['page'];
			$start = ($paging['page'] - 1) * $num;
			$limit = $start . "," . $num;
		}


//		$page = I('page',1);
//		$limit = ($page - 1)*6;
		if (!$order)
		{
			$order = "sort asc";
		}

		$arr = I();
		if ($arr['type'] == 0)
		{
			//商品名
			if (!empty($arr['content']))
			{
				$where['name'] = array('like', '%' . $arr['content'] . '%');
			}
		}
		else
		{
			if ($arr['type'] == 1)
			{
				//分类
				if (!empty($arr['content']))
				{
					$name['name'] = $arr['content'];
					$category = M('goods_category')->where($name)->find();
					if ($category)
					{
						$where['cat_id'] = $category['id'];
					}
				}
			}
		}
		if ($extGoodsArr)
		{
		    $where1['id'] = array('in', $extGoodsArr);
		    $where1['is_on_sale'] = array('eq', 1);
			//$map['id'] = array('in', $extGoodsArr);
			$map['_logic'] = "or";
			$map['_complex'] = $where;
			$map[] = $where1;
		}
		else
		{
			$map = $where;
		}


		$list = M("goods")->where($map)->limit($limit)->field("content", true)->order($order)->select();
		//print_r(M()->getLastSql());exit();
		if ($list)
		{

			foreach ($list as $key => &$value)
			{
				if ($value['is_group'] > 0)
				{
					$group_price = M("goods_spec_price")->where("goods_id=" . $value["id"])->order("group_price asc")->getField("group_price");
					$value["group_price"] = $group_price ? $group_price : 0;
				}
				if ($value['is_hour'] > 0)
				{
					$hour_price = M("goods_spec_price")->where("goods_id=" . $value["id"])->order("hour_price asc")->getField("hour_price");
					$value['hour_price'] = $hour_price ? $hour_price : 0;
				}
				// if ($value['isdel'])//注释说明：放开，后台删除商品列表无数据
				// {
				// unset($list[$key]);
				// }
				$value['shop_name'] = getShopName($value['shop_id']);
				$value['cate_name'] = getCategoryName($value['cat_id'], false);
				$value['hot_name'] = getGoodsHot($value['hot_id']);
				//$value['status_name'] = user_status_name($value['status']);
			}
			$list = array_values($list);

			return $list;
		}
		else
		{
			return array();
		}
	}

	public function getNums($where, $num)
	{
		$num = $num >= 1 ? $num : 10;
		$total = M("goods")->where($where)->count();

		$total = $total ? $total : 0;
		$max_page = ceil($total / $num);
		$data['total'] = $total;
		$data['max_page'] = $max_page;
		return $data;
	}

	/**
	 * 获取 规格的 笛卡尔积
	 * @param $goods_id 商品 id
	 * @param $spec_arr 笛卡尔积
	 * @return string 返回表格字符串
	 */
	public function getSpecInput($data = null)
	{
		$data['goods_id'] = $data['goods_id'] ? $data['goods_id'] : 0;
		$spec_arr = html_entity_decode($data['spec_arr']);
		$spec_arr = json_decode($spec_arr, true);
		$spec_arr_k = array_keys($spec_arr);

		$spec_arr_k[] = 0;
		$w['id'] = array('in', $spec_arr_k);

		$spec = M('goods_spec')->where($w)->getField('id,name');
		unset($w);
		$spec_arr_v = array();
		foreach ($spec_arr as &$v)
		{
			if (!is_array($v))
			{
				array_push($spec_arr_v, $v);
				continue;
			}
			$spec_arr_v = array_merge($spec_arr_v, $v);
		}
		$spec_arr_v[] = 0;
		$w['id'] = array('in', $spec_arr_v);

		$specItem = M('goods_spec_item')->where($w)->getField('id,item,spec_id');//规格项

		unset($w);
		unset($spec_arr_v);
		$keySpecGoodsPrice = M('goods_spec_price')->where('goods_id = ' . $data['goods_id'])->getField('key,key_name,price,purchase_price,store_count,give_integral,img,integral,group_price,hour_price,time_price,weight');//规格项

		foreach ($spec_arr as $k => $item)
		{
			if (!is_array($item))
			{
				$spec_arr[$k] = array($item);
			}
		}

		foreach ($spec_arr as $k => &$item)
		{
			if (count($item) <= 0)
			{
				unset($spec_arr[$k]);

			}
		}
		$res_arr = array_values($spec_arr);

		if (count($res_arr) > 1)
		{
			$spec_dkr = $this->CartesianProduct($res_arr);
			unset($res_arr);
			unset($spec_arr);
		}
		else
		{
			$spec_dkr = $res_arr[0];
			unset($res_arr);
			unset($spec_arr);
		}


		$str = "<table class='table table-bordered' id='spec_input_tab'>";
		$str .= "<tr>";
		// 显示第一行的数据
		foreach ($spec as $k => $v)
		{
			$str .= " <td><b>$v</b></td>";
		}
		$str .= "<td><b>售价</b></td>
			   <td><b>成本价</b></td>
			   <td><b>团购价</b></td>
			   <td><b>整点秒杀</b></td>
			   <td style='display: none;'><b>限时购</b></td>
			   <td><b>库存</b></td>
			   <td><b>重量(克)</b></td>
			   <td><b>图片</b></td>
			 </tr>";
		// 显示第二行开始
		foreach ($spec_dkr as $k => $v)
		{
			$str .= "<tr>";
			$v = explode(',', $v);

			foreach ($v as $k2 => $v2)
			{
				$str .= "<td>" . $specItem[$v2]['item'] . "</td>";
				$item_key_name[$v2] = $spec[$specItem[$v2]['spec_id']] . ':' . $specItem[$v2]['item'];
			}

			ksort($item_key_name);

			$item_key = implode('_', array_keys($item_key_name));
			$item_name = implode(' ', $item_key_name);

			unset($item_key_name);
			$keySpecGoodsPrice[$item_key]['price'] ? false : $keySpecGoodsPrice[$item_key]['price'] = 0; // 售价价格默认为0
			$keySpecGoodsPrice[$item_key]['purchase_price'] ? false : $keySpecGoodsPrice[$item_key]['purchase_price'] = 0; // 商品进价价格默认为0

			//团购价格
			$keySpecGoodsPrice[$item_key]['group_price'] ? false : $keySpecGoodsPrice[$item_key]['group_price'] = 0;

			//秒杀价
			$keySpecGoodsPrice[$item_key]['hour_price'] ? false : $keySpecGoodsPrice[$item_key]['hour_price'] = 0;
			$keySpecGoodsPrice[$item_key]['time_price'] ? false : $keySpecGoodsPrice[$item_key]['time_price'] = 0;
			$keySpecGoodsPrice[$item_key]['store_count'] ? false : $keySpecGoodsPrice[$item_key]['store_count'] = 0; //库存默认为0
			$keySpecGoodsPrice[$item_key]['weight'] ? false : $keySpecGoodsPrice[$item_key]['weight'] = 0; //重量

			$str .= "<td><input required class='input_spec_item' name='item[$item_key][price]' value='{$keySpecGoodsPrice[$item_key]['price']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' /></td>";
			$str .= "<td><input required class='input_spec_item' name='item[$item_key][purchase_price]' value='{$keySpecGoodsPrice[$item_key]['purchase_price']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";
			$str .= "<td><input class='input_spec_item' name='item[$item_key][group_price]' value='{$keySpecGoodsPrice[$item_key]['group_price']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";
			$str .= "<td><input class='input_spec_item' name='item[$item_key][hour_price]' value='{$keySpecGoodsPrice[$item_key]['hour_price']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";
			/*$str .= "<td><input class='input_spec_item' name='item[$item_key][time_price]' value='{$keySpecGoodsPrice[$item_key]['time_price']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";*/
			$str .= "<td><input required class='input_spec_item' name='item[$item_key][store_count]' value='{$keySpecGoodsPrice[$item_key]['store_count']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";
			$str .= "<td><input class='input_spec_item' name='item[$item_key][weight]' value='{$keySpecGoodsPrice[$item_key]['weight']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";

			if ($keySpecGoodsPrice[$item_key]['img'])
			{
				$imgurl = $keySpecGoodsPrice[$item_key]['img'];
			}
			else
			{
				$imgurl = "/Public/images/add-button.jpg";
			}
			// $str .= "<td><img width='35' height='35' src='$imgurl' id='item[{$item_key}][img]' class='spec_img' id='spec_img{$k}' h='IMG{$k}'/>";

			$str .= "<td style='text-align:center;'><p><img width='35' height='35' src='$imgurl' class='spec_{$k}' /></p><div class='layui-btn' style='height:25px;line-height:25px;padding:0 5px; position:relative; overflow:hidden' ><i class='layui-icon'>&#xe67c;</i>";

			$str .= "<input type='file' class='spec_img_file' data-number='{$k}' id='spec_img{$k}' name='headimg' style='opacity: 0;z-index: 99; position: absolute; left: 0; top:0' height:30px; width:41px/>";
			$str .= "<input type='hidden' name='item[{$item_key}][img]' value='$imgurl' class='spec_img{$k}' />";

			$str .= "<input type='hidden' name='item[$item_key][key_name]' value='$item_name' /> </div></td>";


			$str .= "</tr>";
		}
		$str .= "</table>";
		return $str;
	}

	/**
	 * 获取 规格的 笛卡尔积
	 * @param $goods_id 商品 id
	 * @param $spec_arr 笛卡尔积
	 * @return string 返回表格字符串
	 */
	public function getSpec($data = null)
	{
		$data['goods_id'] = $data['goods_id'] ? $data['goods_id'] : 0;
		$spec_arr = html_entity_decode($data['spec_arr']);
		$spec_arr = json_decode($spec_arr, true);
		$spec_arr_k = array_keys($spec_arr);

		$spec_arr_k[] = 0;

		$w['id'] = array('in', $spec_arr_k);

		$spec = M('goods_spec')->where($w)->getField('id,name');
		unset($w);
		$spec_arr_v = array();
		foreach ($spec_arr as &$v)
		{
			if (!is_array($v))
			{
				array_push($spec_arr_v, $v);
				continue;
			}
			$spec_arr_v = array_merge($spec_arr_v, $v);
		}
		$spec_arr_v[] = 0;
		$w['id'] = array('in', $spec_arr_v);

		$specItem = M('goods_spec_item')->where($w)->getField('id,item,spec_id');//规格项

		unset($w);
		unset($spec_arr_v);
		$keySpecGoodsPrice = M('goods_spec_price')->where('goods_id = ' . $data['goods_id'])->getField('key,key_name,price,purchase_price,store_count,give_integral,img,integral,group_price,hour_price,time_price,weight');//规格项

		foreach ($spec_arr as $k => $item)
		{
			if (!is_array($item))
			{
				$spec_arr[$k] = array($item);
			}
		}

		foreach ($spec_arr as $k => &$item)
		{
			if (count($item) <= 0)
			{
				unset($spec_arr[$k]);

			}
		}

		$res_arr = array_values($spec_arr);
		if (count($res_arr) > 1)
		{
			$spec_dkr = $this->CartesianProduct($res_arr);
			unset($res_arr);
			unset($spec_arr);
		}
		else
		{
			$spec_dkr = $res_arr[0];
			unset($res_arr);
			unset($spec_arr);
		}

		foreach ($spec_dkr as $k => $v)
		{
			$v = explode(',', $v);

			foreach ($v as $k2 => $v2)
			{
				$item_key_name[$v2] = $spec[$specItem[$v2]['spec_id']] . ':' . $specItem[$v2]['item'];
				$item_val_name[$v2] = $specItem[$v2]['item'];
			}
			ksort($item_key_name);
			$item_key = implode('_', array_keys($item_key_name));
			$item_name = implode(' ', $item_key_name);
			$item_name_one = implode(' ', $item_val_name);

			unset($item_key_name);
			unset($item_val_name);

			$keySpecGoodsPrice_res[$k]['price'] = $keySpecGoodsPrice[$item_key]['price'] ? $keySpecGoodsPrice[$item_key]['price'] : ''; // 售价价格默认为0
			$keySpecGoodsPrice_res[$k]['purchase_price'] = $keySpecGoodsPrice[$item_key]['purchase_price'] ? $keySpecGoodsPrice[$item_key]['purchase_price'] : ''; // 商品进价价格默认为0

			//团购价格
			$keySpecGoodsPrice_res[$k]['group_price'] = $keySpecGoodsPrice[$item_key]['group_price'] ? $keySpecGoodsPrice[$item_key]['group_price'] : '';

			//秒杀价
			$keySpecGoodsPrice_res[$k]['hour_price'] = $keySpecGoodsPrice[$item_key]['hour_price'] ? $keySpecGoodsPrice[$item_key]['hour_price'] : '';
			$keySpecGoodsPrice_res[$k]['time_price'] = $keySpecGoodsPrice[$item_key]['time_price'] ? $keySpecGoodsPrice[$item_key]['time_price'] : '';
			$keySpecGoodsPrice_res[$k]['store_count'] = $keySpecGoodsPrice[$item_key]['store_count'] ? $keySpecGoodsPrice[$item_key]['store_count'] : ''; //库存默认为0
			$keySpecGoodsPrice_res[$k]['weight'] = $keySpecGoodsPrice[$item_key]['weight'] ? $keySpecGoodsPrice[$item_key]['weight'] : ''; //重量
			$keySpecGoodsPrice_res[$k]['img'] = $keySpecGoodsPrice[$item_key]['img'] ? $keySpecGoodsPrice[$item_key]['img'] : '';
			$keySpecGoodsPrice_res[$k]['name'] = $item_name; //规格名称
			$keySpecGoodsPrice_res[$k]['name1'] = $item_name_one; //规格名称
			$keySpecGoodsPrice_res[$k]['key'] = $item_key;
		}
		return $keySpecGoodsPrice_res;
	}

	function CartesianProduct($sets)
	{
		// 保存结果
		$result = array();

		// 循环遍历集合数据
		for ($i = 0, $count = count($sets); $i < $count - 1; $i++)
		{
			// 初始化
			if ($i == 0)
			{
				$result = $sets[$i];
			}
			// 保存临时数据
			$tmp = array();

			// 结果与下一个集合计算笛卡尔积
			foreach ($result as $res)
			{
				foreach ($sets[$i + 1] as $set)
				{
					$tmp[] = $res . ',' . $set;
				}
			}
			// 将笛卡尔积写入结果
			$result = $tmp;
		}
		return $result;
	}


	// 获取某个商品拼团未完成的订单-部分数量，输出相关的信息
	public function unGroupList($goods_id = 0, $group_number = 10)
	{
		if (!$goods_id)
		{
			return array();
		}
		$where_gp = "isdel=0 and order_type=5 and order_status=1 and group_leader=1 and group_people_num>group_people_num_have and goods_id=" . $goods_id;
		$field = "id as order_id,user_id,group_people_num,group_people_num_have,group_time_end";
		$group_list = M("order")->where($where_gp)->field($field)->order("group_time_end desc, id asc")->limit(0, $group_number)->select();
		$group_list = $group_list ? $group_list : array();
		foreach ($group_list as $key2 => &$value2)
		{
			$value2["group_time_end_dis"] = date("H:i:s", $value2["group_time_end"]);
			$endTime = $value2["group_time_end"];
			$endTime = $endTime - time();
			if ($endTime < 60)
			{
				// 剩余一分钟的不再显示
				unset($group_list[$key2]);
			}
			$value2["end_time"] = $endTime;

			$where_gpu["id"] = $value2["user_id"];
			$field_gp = "headimgurl,phone,realname,nickname,level";
			$gpUser = get_user_info($where_gpu, $field_gp);
			$gpName = $gpUser["nickname"] ? $gpUser["nickname"] : $gpUser["realname"];
			$gpName = $gpUser["realname"] ? $gpUser["realname"] : $gpUser["phone"];
			$value2["name"] = $gpName;
			$value2["headimgurl"] = full_pre_url($gpUser["headimgurl"]);
			$value2["diff_num"] = $value2["group_people_num"] - $value2["group_people_num_have"];
		}
		$group_list = array_values($group_list);
		return $group_list;
	}


	/**
	 * 拼团商品详情
	 * @param int $id 必须
	 * @return array
	 * @mgl
	 */
	public function group_detail($data)
	{
		$goods_id = $data['id'];
		$user_id = $data['user_id'];
		$goods_kind = $data['goods_kind'] ? $data['goods_kind'] : 0;    // 0常规商品，1团购，2整点秒杀，3限时购
		// 根据商品附加分类，处理相应的输出
		// 团购：需要输出：团购信息+前10个还没拼团的订单，显示最先下单的
		$group_list = array();    // 拼团列表
		if ($goods_kind == 1)
		{
			$group_list = $this->unGroupList($data["id"]);    // 未成功才列表
		}
        foreach ($group_list as $k => $v){
            $str_1 = mb_substr($v['name'], 0, 3, 'utf-8');
            $str_2 = mb_substr($v['name'], -1, 1, 'utf-8');
            $group_list[$k]['name'] =$str_1 . '***' . $str_2;
        }
		return array('商品详情', 0, array(
			'goods_kind' => $goods_kind,    // 0常规商品，1团购，2整点秒杀，3限时购
			'goods' => $data,
			'group_list' => $group_list,
		)
		);
	}

	/**
	 * 秒杀、限时购商品
	 */
	public function goodsListRule($where, $order = "id desc", $num = 10, $paging = array())
	{
		// 搜索条件
		if (!isset($where["isdel"]))
		{
			$where["isdel"] = 0;
		}

		$num = $num <= 0 ? 10 : $num;
		if (!$paging)
		{
			$paging['page'] = 1;
			$paging['last_id'] = 0;
		}
		// 如果 last_id 和 page 都 <=0 即表示刚开始获取数据
		// 如果 last_id 和 page 都 >0，则以 last_id 为先
		if ($paging['last_id'] > 0)
		{
			$where['id'] = array("lt", $paging['last_id']);
			$limit = $num;
		}
		else
		{
			$paging['page'] = $paging['page'] <= 1 ? 1 : $paging['page'];
			$start = ($paging['page'] - 1) * $num;
			$limit = $start . "," . $num;
		}
		if (!$order)
		{
			$order = "sort asc";
		}


		$arr = I();
		if ($arr['type'] == 0)
		{
			//商品名
			if (!empty($arr['content']))
			{
				$where['name'] = array('like', '%' . $arr['content'] . '%');
			}
		}
		else
		{
			if ($arr['type'] == 1)
			{
				//分类
				if (!empty($arr['content']))
				{
					$name['name'] = $arr['content'];
					$category = M('goods_category')->where($name)->find();
					if ($category)
					{
						$where['cat_id'] = $category['id'];
					}
				}
			}
		}
		$list = M('goods as a')->join('sns_goods_hour as b on a.id=b.goods_id')->where($where)->limit($limit)->order($order)->select();
		if ($list)
		{
			foreach ($list as $key => &$value)
			{
				unset($value['content']);
				$value['id'] = $value['goods_id'];
				$value['shop_name'] = getShopName($value['shop_id']);
				$value['cate_name'] = getCategoryName($value['cat_id'], false);
				$value['hot_name'] = getGoodsHot($value['hot_id']);
				//$value['status_name'] = user_status_name($value['status']);
			}
			return $list;
		}
		else
		{
			return array();
		}
	}

	/**
	 * Notes:商品详情界面商品信息
	 * User: WangSong
	 * Date: 2020/2/21
	 * Time: 9:09
	 */
	public function goods_detail_goods($shop_id)
	{
		$user_id = session("user_id");
		if ($user_id)
		{
			$where = "f.user_id=" . $user_id . " and type=0";
		}
		else
		{
			$where = "type=0";
		}
		$data["sell_goods"] = M("goods")->where("shop_id=" . $shop_id)->order("sell_count desc")->limit(3)->select();
		$data["look_goods"] = M("goods_footprint as f")->join("sns_goods as g on f.goods_id = g.id ")
			->where($where)->field("g.*")->limit(3)->select();
		$data["follow_goods"] = M("goods")->where("shop_id=" . $shop_id)->order("hits desc")->limit(3)->select();
		return $data;
	}

	/**
	 * Notes:店铺模板商品列表
	 * User: WangSong
	 * Date: 2020/2/24
	 * Time: 9:36
	 * @param $shop_id
	 * @return mixed
	 */
	public function shop_goods_list_pc($shop_id)
	{
		$goods_list["data1"] = M("goods")->where("shop_id=" . $shop_id . " and hot_id=3")->order("sort desc")->limit(2)->select();
		$goods_list["data2"] = M("goods")->where("shop_id=" . $shop_id . " and hot_id=3")->order("sort desc")->limit(2, 6)->select();
		return $goods_list;
	}

	/**
	 * Notes:获取商家商品分类
	 * User: WangSong
	 * Date: 2020/2/24
	 * Time: 9:58
	 * @param $shop_id
	 * @return array
	 */
	public function shop_goods_cate()
	{
		$new_cagegory[0]['id'] = 0;
		$new_cagegory[0]['name'] = '所有产品';
		$goods_cate_all = M("goods_category")->where("pid=0")->select();
		foreach ($goods_cate_all as $k => $v)
		{
			$new_cagegory[$k + 1]['id'] = $v["id"];
			$new_cagegory[$k + 1]["name"] = $v["name"];
			$goods_category_son = M("goods_category")->where("pid=" . $v["id"])->field("id,name")->select();
			$new_cagegory[$k + 1]["son_list"] = $goods_category_son;
		}
		return $new_cagegory;
	}

	/**
	 * @param $goods_id
	 * @return array
	 * Notes:获取服务内容
	 * User: WangSong
	 * Date: 2020/7/4
	 * Time: 15:49
	 */
	public function get_service_info($goods_id)
	{
		$array = array();
		$name = "";
		$goods = M('goods')->where('id=' . $goods_id)->find();
		if ($goods['service_show'])
		{
			$service_show = json_decode($goods['service_show'], true);
			$a = 0;
			for ($i = 0; $i < count($service_show); $i++)
			{
				if ($service_show[$i] == 1)
				{
					if ($i == 0)
					{
						$service_description = M('service_description')->where('id=1')->find();
						$name .= $service_description['name'];
						$array[$a]['title'] = $service_description['name'];
						$array[$a]['content'] = $service_description['info'];
					}
					elseif ($i == 1)
					{
						$type = get_config_project('profit_space');
						$service_description = M('service_description')->where('type=' . $type)->find();
						$name .= $service_description['name'];
						$array[$a]['title'] = $service_description['name'];
						$array[$a]['content'] = $service_description['info'];
					}
					elseif ($i == 2)
					{
						if ($goods['shipping_type'] == 2)
						{
							$array[$a]['title'] = "门店自提";
							$array[$a]['content'] = "支持门店自提，本商品免运费";
						}
						else
						{
							$array[$a]['title'] = "快递配送";
							$expreess_rule = M('express_rule')->where('id=' . $goods['shipping_rule_id'])->find();
							if (!$expreess_rule)
							{
								$array[$a]['content'] = "支持快递配送，本商品免运费";
							}
							else
							{
								if ($expreess_rule['type'] == 1)
								{
									$type = $expreess_rule['the_first'] . 'g';
								}
								else
								{
									$type = $expreess_rule['the_first'] . '件';
								}
								if ($expreess_rule['second_price'] == 0)
								{
									if ($expreess_rule['first_price'] == 0)
									{
										$array[$a]['content'] = "支持快递配送，本商品免运费";
									}
									else
									{
										$array[$a]['content'] = "支持快递配送，本商品运费" . $expreess_rule['first_price'] . '元';
									}
								}
								else
								{
									$array[$a]['content'] = "支持快递配送，本商品最低运费" . $expreess_rule['first_price'] . '元/' . $type;
								}
							}
						}
						//print_r($array);exit();
					}
					$a++;
				}
			}
		}
		return $array;
	}

	/**
	 * @param $goods_id
	 * @return mixed
	 * Notes:计算该商品销量排名
	 * User: WangSong
	 * Date: 2020/7/8
	 * Time: 10:27
	 */
	public function calculation_goods_ranking($goods_id)
	{
		$goods = M('goods')->where('id=' . $goods_id)->find();
		$ranking_val = 0;
		$all_goods = M('goods')->where('shop_id=' . $goods['shop_id'] . ' and isdel = 0')->field('id,sell_count')->order('sell_count desc')->limit(20)->select();
		foreach ($all_goods as $k => $v)
		{
			if ($goods_id == $v['id'])
			{
				$ranking_val = $k + 1;
			}
		}
		return $ranking_val;
	}

	/**
	 * @param $shop_id
	 * @param $num
	 * Notes:获取商品排行榜列表
	 * User: WangSong
	 * Date: 2020/7/8
	 * Time: 11:10
	 * @return mixed
	 */
	public function ranking_list($shop_id, $num)
	{
		if (!$num)
		{
			$num = 20;
		}
		$where['shop_id'] = $shop_id;
		$where['isdel'] = 0;
		$where['is_on_sale'] = 1;
		$goods = M('goods')->where($where)->field('id,name,price,thumb,sell_count')->order('sell_count desc')->limit($num)->select();
		foreach ($goods as $key => &$value)
		{
			$value['thumb'] = full_pre_url($value['thumb']);
			//计算评论数
            //查询相关产品评论数
            $common_count = M('goods_comment')->where('goods_id='.$value['id'])->count();
            $value['common_count'] = $common_count;

		}
		return $goods;
	}

	/**
	 * 简单查询商品列表
	 * @author lty
	 */
	public function newGoodsList($where, $data, $order)
	{

		$data['page'] = ($data['page'] - 1) * $data['number'];
		$list = M('goods')->where($where)->limit($data['page'], $data['number'])->order($order)->select();
		//计算max_page
		$count = M('goods')->where($where)->count();
		$max_page = ceil($count / $data['number']);
		return array(
			'list' => $list,
			'page' => $data['page'],
			'type' => $data['type'],
			'max_page' => $max_page,
		);
	}

	/**
	 * Notes:设置商品上下架
	 * User: WangSong
	 * Date: 2020/8/7
	 * Time: 9:10
	 */
	public function goods_is_on_sale()
	{
		$goods_id = I('goods_id');
		if (!$goods_id)
		{
			return '该商品不存在';
		}
		$is_on_sale = I('is_on_sale', 1);
		$re = M('goods')->where('id=' . $goods_id)->setField('is_on_sale', $is_on_sale);
		if ($re)
		{
			return $re;
		}
		else
		{
			return '操作失败';
		}
	}
}

