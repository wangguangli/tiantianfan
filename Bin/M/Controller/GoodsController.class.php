<?php

namespace M\Controller;
header("Content-type: text/html; charset=utf-8");

use Clas\Orderin;
use Clas\Orderout;
use Think\Controller;
use Clas\Goods;
use Clas\User;
use Clas\Ad;
use Clas\Jssdk;

class GoodsController extends Controller
{


	/**
	 * 根据分类查询该分类下的商品
	 */
	public function index()
	{
		$obj = new Goods();
		$cat_id = I("cat_id");
		$price_px = I("px", 0); //sell_count price
		$car_arr['id'] = $cat_id;
		$page = I('page', 1);

		$car_data = $obj->category_list($car_arr);
		$cat_name = $car_data[2][0]['name'];
		$cidArr = array();
		if ($cat_id)
		{
		    $cat_info = M('goods_category')->find($cat_id);
		    if($cat_info){
		        $this->assign('cat_name',$cat_info['name']);
            }else{
                $this->assign('cat_name','商品列表');
            }
			$childs = get_childs($cat_id);

			$c2a = reformat($childs);
			foreach ($c2a as $key => $val)
			{
				$cidArr[] = $val["id"];
			}
			$cidArr[] = $cat_id;
		}else{
            $this->assign('cat_name','商品列表');
        }

		if ($cidArr)
		{
			$where["cat_id"] = array("in", $cidArr);
		}
		$where["isdel"] = 0;
		$where["product_type"] = 0;
        $where['is_on_sale'] = 1;


		// 增加附属分类后的处理
		$wher_ext["cate_id"] = array("in", $cidArr);



		$extGoodsArr = M("goods_category_extra")->where($wher_ext)->getField("goods_id", true);


//		if ($extGoodsArr)
//		{
//			$map['is_on_sale'] = 1;
//			$map['id'] = array("in", $extGoodsArr);
//			$map['_logic'] = "OR";
//			$where['_complex'] = $map;
//		}

		switch ($price_px)
		{
			case "1":
				$ord = " sell_count desc";
				$ord1 = " sell_sort";
				break;
			case "2":
				$ord = " price asc";
				$ord1 = "price_sort";
				break;
			case "3":
				$ord = " price desc";
				$ord1 = " price_sort1";
				break;
			case "4":
				$ord = " price asc";
				$ord1 = " price_sort";
				break;
			default:
				$ord = " sort asc";
				$ord1 = " time_sort";
		}

		$num = 10;
		$paging['page'] = $page;


		$goods = $obj->goodsList($where, $ord, $num, $paging, $extGoodsArr);
		/*echo '<pre>';
		print_r($goods);exit();*/
		$count = count($goods);
		$goods = full_pre_url($goods, 'thumb');
		foreach ($goods as $k => $v)
		{
			$goods[$k]['images'] = json_decode($v['images']);
			foreach ($goods[$k]['images'] as $kk => $vv)
			{

				$goods[$k]['images'][$kk] = full_pre_url($vv);
			}
            $common_count = M('goods_comment')->where('goods_id='.$v['id'])->count();
            if($common_count > 999){
                $goods[$k]['common_count'] = '999+';
            }else{
                $goods[$k]['common_count'] = $common_count;
            }
		}
		$total = 0;
		$maxPage = 0;
		if ($page < 2)
		{
			$numArr = $obj->getNums($where, $num);
			$total = $numArr['total'];
			$maxPage = $numArr['max_page'];
		}

		$this->assign('goods', $goods);
		$this->assign('count', $count);
		$this->assign('cat_name', $cat_name);
		$this->assign('cat_id', $cat_id);
		$this->assign('total', $total);
		$this->assign('max_page', $maxPage);
		$this->assign('px', $price_px);
		$this->assign('ord', $ord1);
		$this->display('newgoodsindex');
//		if ($page > 1)
//		{
//			$this->display('index_more');
//		}
//		else
//		{
//			$this->display();
//		}

	}


	// 商品信息
	public function detail()
	{
		$id = I("id", 0);                // 商品ID
		$user_id = session('user_id');    // 用户ID
		$share_uid = I("share_uid", 0);
		if ($share_uid)
		{
			session('share_uid', $share_uid);
		}
		$user_id = $user_id ? $user_id : 0;
		$goods_kind = I("goods_kind", 0);    // 商品打开来源，0普通商品，1拼团，2整点秒杀，3限时购
		if (!$id)
		{
			$this->error("参数异常，请重新打开");
		}
		$data["id"] = $id;
		$data["user_id"] = $user_id;
		$data["goods_kind"] = $goods_kind;
		$data['group_number'] = 3; //查询拼团条数
		$obj = new Goods($data);
		$res = $obj->detail($data);
		if (!is_array($res))
		{
			$this->error($res);
		}

		//商品优惠券
		$de_yhq = $this->couponList($user_id, $id);

		if (empty($res[2]["spec_list"]))
		{ //为空
			$spec = 0;
		}
		else
		{
			$spec = 1;
		}
		//是否收藏该商品
		$where['user_id'] = $user_id;
		$where['fav_id'] = $id;
		$fav = M('favorites')->where($where)->find();
		if ($fav)
		{
			$fav = 1;//以收藏
		}
		else
		{
			$fav = 0;//未收藏
		}

		if ($user_id)
		{
			$fzurl = $_SERVER['SERVER_NAME'] . __SELF__ . '&user_id=' . $user_id;
		}

		$res[2]['total_group_num'] = count($res[2]['group_list']); //总拼团人数
		$content = M("goods")->where("id=" . $id)->getField("content");
		$content = htmlspecialchars_decode($content);
		$video = $res[2]["goods"]['video'];
		$images = $res[2]["images"];
		if ($video)
		{
			array_unshift($images, $video);
			$this->assign("video", $video);
		}
		$share = array(
			'title' => $res[2]['goods']['name'],
			'url' => C('C_HTTP_HOST') . 'M/Goods/detail?id=' . $res[2]['goods']['id'].'&share_uid='.$user_id.'&goods_kind='.$goods_kind,
			'thumb' => $res[2]['goods']['thumb'],
			'desc' => $res[2]['goods']['subhead'],
		);


		$js = new Jssdk();
		$images = full_pre_url($images);
		foreach ($images as $k => $v)
		{
			$images[$k] = full_pre_url($v);
		}
		$res[2]['goods']['thumb'] = full_pre_url($res[2]['goods']['thumb']);

		// 如果是秒杀商品，看看是否在规定时间内？
		// zd.2020.2.14
		$hour_status = 1;   // 正常秒杀
		if ($goods_kind == 2 || $goods_kind == 3)
		{
			$hourInfo = M("goods_hour")->where("goods_id=" . $id)->find();
			if (!$hourInfo)
			{
				$this->error("秒杀期限已过，请选购其他商品");
			}
			// $hour_status：0没开始，1正常状态，2过期
			$hour_str = "立即购买";
			if ($hourInfo['start_time'] > time())
			{
				$hour_status = 0;   // 还没开始
				$hour_str = "秒杀未开始";
			}
			if ($hourInfo['end_time'] < time())
			{
				$hour_status = 2;   // 已过期
				$hour_str = "秒杀已结束";
			}

		}
		$transaction_record = array();
		$is_transaction_record = get_config('is_transaction_record');//控制显示成交记录 1是显示 0是关闭
		if ($is_transaction_record)
		{
			$orderOut = new Orderout();
			$transaction_record = $orderOut->get_transaction_record($id);
		}
        $score_pay_mode = get_config('score_pay_mode');//判断运费支付方式  积分支付还是现金支付
        if($res[2]["goods"]['product_type'] ==1 && $score_pay_mode == 1){
            $is_score_pay = 1;//运费积分支付
        }else{
            $is_score_pay = 0;//运费现金支付
        }


		$this->assign('share', $share);
		$this->assign('wxjssign', $js->getSign(1));//获取分享数据
		$this->assign("content", $content);
		$this->assign("goods", $res[2]["goods"]);
		$this->assign("fav", $fav);
		$this->assign("images", $images);
		$this->assign("de_yhq", $de_yhq);
		$this->assign("fzurl", $fzurl);
		$this->assign("pwd_url", $_SERVER['SERVER_NAME']);
		$this->assign("transaction_record", $transaction_record);
		$this->assign("is_transaction_record", $is_transaction_record);

		$this->assign("shop", $res[2]["shop"]);
		$this->assign("is_favorite", $res[2]["is_favorite"]);
		$this->assign("favorite_count", $res[2]["favorite_count"]);
		$this->assign("def_spec_price", $res[2]["def_spec_price"]);
		$this->assign("group_list", $res[2]["group_list"]);
		$this->assign("total_group_num", $res[2]['total_group_num']);
		$this->assign("spec_list", $res[2]["spec_list"]);
		$this->assign("spec", $spec);
		$this->assign("goods_category", $res[2]["goods_category"]);
		$this->assign("user_id", $user_id);
		$this->assign("goods_kind", $goods_kind);
		$this->assign("goods_id", $data['id']);
		$this->assign("hour_status", $hour_status);
		$this->assign("hour_str", $hour_str);
		$this->assign("recommend_goods", $res[2]["recommend_goods"]);
		$this->assign("user_browse", $res[2]["user_browse"]);
		$this->assign("ranking_list", $res[2]["ranking_list"]);
		$this->assign("goods_comment_total", $res[2]["goods_comment"]['total']);
		$this->assign("goods_comment_list", $res[2]["goods_comment"]['last']);
		$this->assign("is_score_pay", $is_score_pay);


		$icp = get_config('icp',true);
		$wxChat = get_config('wechat',true);
		$this->assign('icp',$icp);
		$this->assign('wxChat',$wxChat);

		//计算当前购物车的数量
        $cartWhere['user_id'] = session('user_id');
        $cartCount = M('cart')->where($cartWhere)->count();
        $this->assign('cartCount',$cartCount);
		if ($goods_kind == 2)
		{
			$this->display("detail_hour");
		}
		else if ($goods_kind == 1)
		{
			$this->display("detail_group");
		}
		else
		{
			$this->display();
		}
	}

	//加入购物车
	public function makeCart()
	{
		$goods = I("goods");
		$goods = htmlspecialchars_decode($goods);
		$goods = json_decode($goods, true);
		$id = session('user_id');
		$Orderin = new Orderin($id);
		$data = $Orderin->makeCart();

		if ($data == 1)
		{
			jsonout('操作购物车', 0, '操作成功');
		}
		else
		{
			jsonout('操作购物车', 1, $data);
		}
	}

	// 只展示商品详情
	public function content()
	{
		$id = I("id", 0);
		$data = M("goods")->where("id=" . $id)->field("content")->find();
		$this->assign("content", htmlspecialchars_decode($data["content"]));
		$this->display();
	}

	public function category()
	{
		$list = $this->category_list();

		$firstArr = reset($list);

		$ads = M("ad")->where("position='category_banner' and cate_id=" . $firstArr['id'])->order("id desc")->find();
		$this->assign('ads', $ads);
		$this->assign('list', $list);
		$this->assign('first', $firstArr);
		$this->assign('menu_category', 'cut');
		$this->display();
	}


	public function category_list()
	{
		$all = M('goods_category')->getField('id,name,pid,img');
		$tree = array();
		foreach ($all as $k => $v)
		{
			if ($v['pid'])
			{
				$tree[$v['pid']]['sub'][$k] = $v['name'];
				$tree[$v['pid']]['imgs'][$k] = $v['img'];
				$tree[$v['pid']]['ids'][$k] = $v['id'];
			}
			else
			{
				$tree[$k]['name'] = $v['name'];
				$tree[$k]['img'] = $v['img'];
				$tree[$k]['id'] = $v['id'];
				$tree[$k]['sub'] = array();
			}
		}
		return $tree;
	}

	/**
	 * 首页商品名的搜索
	 */
	public function goodssearch()
	{

	    redirect('/index.php/M/Index/new_search');
	    exit;
		$arr = I();
		$page = I("page", 1);
		$content = $arr['content'];
		if ($content)
		{
			$where['name'] = array('like', "%" . $content . "%");
			$where['product_type'] = 0;
			$where['is_on_sale'] = 1;
			$where['isdel'] = 0;
			$where['is_group'] = 0;
			$where['is_hour'] = 0;

			$num = 10;
			$limit = (($page - 1) * $num) . ',' . $num;
			$count = M('goods')->where($where)->count();
			$max_page = ceil($count / $num);
			$goods = M('goods')->where($where)->order('id DESC')->limit($limit)->select();
		}else{
            $where['product_type'] = 0;
            $where['is_on_sale'] = 1;
            $where['isdel'] = 0;
            $where['tags'] = '热销';
            $where['is_group'] = 0;
            $where['is_hour'] = 0;
            $num = 10;
            $limit = (($page - 1) * $num) . ',' . $num;
            $count = M('goods')->where($where)->count();
            $max_page = ceil($count / $num);
            $goods = M('goods')->where($where)->order('id DESC')->limit($limit)->select();
        }
		foreach ($goods as $k => $v){
            $common_count = M('goods_comment')->where('goods_id='.$v['id'])->count();
            if($common_count > 999){
                $goods[$k]['common_count'] = '999+';
            }else{
                $goods[$k]['common_count'] = $common_count;
            }
        }
		$this->assign('content', $content);
		$this->assign('max_page', $max_page);
		$this->assign('goods', $goods);
		if ($page > 1)
		{
			$this->display("index_more");
		}
		else
		{
			$this->display();
		}


	}

	/**
	 * 商品评论 查看更多评论
	 */
	public function evaluate()
	{
		$id = I("goods_id");
		$where['goods_id'] = $id;
		$comment = M('goods_comment')->where($where)->order('id DESC')->select();
		if ($comment)
		{
			foreach ($comment as $k => $v)
			{
				$userInfo = M('user')->find($v['user_id']);
//				$comment[$k]['user_name'] = $userInfo['nickname'];
				//名称加密显示

                $comment[$k]['user_name'] = get_user_info_beauty($userInfo, 2, 2);

                $comment[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
                if($userInfo['headimgurl']){
                    $comment[$k]['headimage'] = $userInfo['headimgurl'];
                }else{
                    $comment[$k]['headimage'] = '/Public/images/profile.png';
                }
				$comment[$k]['image'] = explode(',', $v['images']);
			}
		}
		$this->assign('comment', $comment);
		$this->display();
	}

	/**
	 * 商品分享到朋友圈
	 */
	public function share()
	{
		$this->error('本功能正在开发中');
	}

	/**
	 * 团购列表及上方分类和广告
	 */
	public function goodsgroup()
	{
		$n = 0;
		$category_list = array();
		$cateList = M("goods")->where("is_group=1 and is_on_sale=1 and isdel=0")->group("cat_id")->field("cat_id")->select();
		foreach ($cateList as $key2 => $value2)
		{
			$catName = M("goods_category")->where("id=" . $value2["cat_id"])->getField("name");
			if ($catName)
			{
				$category_list[$n]["cat_id"] = $value2["cat_id"];
				$category_list[$n]["name"] = $catName;
			}

			$n++;
		}
		$position = "ad_group";
		$row = 1;
		$banner = get_ad($position, $row);
		$category_list = array_values($category_list);
		$res["banner"] = $banner;
		$res["catelist"] = $category_list;
		$goods = new Goods();
		$cat_id = I("cat_id", 0);    // 分类ID
		$last_id = I("last_id", 0);
		$page = I("page", 1);
		$paging['page'] = $page;
		$paging['last_id'] = $last_id;
		$num = 10;
		$order = "sort asc, id desc";
		if ($cat_id)
		{
			$where["cat_id"] = $cat_id;
		}
		$where["isdel"] = 0;
		$where["is_on_sale"] = 1;
		$where["is_group"] = 1;
		$list = $goods->goodsList($where, $order, $num, $paging);
		if (!$list)
		{
			jsonout("没有更多数据");
		}
		foreach ($list as $key => &$value)
		{
			$groupInfo = M("goods_group")->where("goods_id=" . $value["id"])->field("id,goods_id", true)->find();
			if (!$groupInfo || !$groupInfo['is_open'])
			{
				unset($list[$key]);
			}
			else
			{
				$group_price = M("goods_spec_price")->where("goods_id=" . $value["id"])->order("group_price asc")->getField("group_price");
				$groupInfo["group_price"] = $group_price;
				$group_num = M("order_goods")->where("order_status>0 and goods_id=" . $value["id"])->sum("goods_num");
				$groupInfo["group_num"] = $group_num ? $group_num : 0;
				$value = array_merge($value, $groupInfo);
			}
		}
		$list = array_values($list);
		$numArr = $goods->getNums($where, $num);
		$res['list'] = $list;
		$res['page'] = $page;
		$res['last_id'] = get_last_id($list, 'id', $num);
		$res['max_page'] = $numArr['max_page'];
		$res = json_encode($res);
		$this->assign('res', $res);
		$this->display();
	}

	/**
	 * 判断该拼团商品能否单独购买
	 */
	public function goodsIsPay()
	{
		$array = I();
		$goods_id = $array['goods_id'];
		$where['goods_id'] = $goods_id;
		$res = M('goods_group')->where($where)->find();
		if (!$res)
		{
			//该商品不是拼团商品，可以单独购买
			jsonout('可以购买', 0, null);
		}
		else
		{
			//判断是否设置了可以单独购买
			if ($res['buy_alone'] == 1)
			{
				jsonout('可以购买', 0, null);
			}
			else
			{
				jsonout('该商品不支持单独购买', -1, null);
			}
		}
	}

	/**
	 * 判断参与的团是否是由自己发起的
	 * 判断自己是否参与这个团了
	 */
	public function isMyGroupOrder()
	{
		$array = I();
		$user_id = session('user_id');
		$where['user_id'] = $user_id;
		$where['group_order_id'] = $array['order_id'];
		$res = M('order')->where($where)->find();
		if ($res['user_id'])
		{
			jsonout('你已经参与该拼团订单', -1, null);
		}
		else
		{
			jsonout('可以参与', 0, null);
		}
	}

	//根据一级分类获取二级分类
	public function getAllCategoryTwo($id)
	{
		if ($id == 0)
		{
			$id = I('id');
		}
		$category = M('goods_category')->field('name,img')->find($id);
		$su_category = M('goods_category')->where('pid=' . $id)->getField('id,name,img');
		foreach ($su_category as $key => $value)
		{
			$su_category[$key]['children'] = $this->getAllChildrenCate($value['id']);
		}
		if ($su_category)
		{
			$category['children'] = $su_category;
		}

		// 获取广告位
		$ads = M("ad")->where("position='category_banner' and cate_id=" . $id)->order("id desc")->find();
		if ($ads)
		{
			$category['ads'] = $ads;
		}
		else
		{
			$category['ads'] = array();
		}

		if ($su_category)
		{
			jsonout('获取数据成功', 1, $category);
		}
		else
		{
			jsonout('子分类没有数据', 2, $category);
		}
	}

	//递归查询回去所有子级分类
	public function getAllChildrenCate($id)
	{
		$category = M('goods_category')->field('name,img')->find($id);
		$su_category = M('goods_category')->where('pid=' . $id)->getField('id,name,img');
		foreach ($su_category as $key => $value)
		{
			$su_category[$key]['children'] = $this->getAllChildrenCate($value['id']);
		}
		return $su_category;
	}

	/**
	 * 输出规格对应的商品价格
	 * @param $data  key规格子项id以下划线组成的字符串  goods_id商品id
	 * @return json
	 * @mgl
	 */
	function get_spec_goods_price($data = null)
	{
		if (!$data)
		{
			$data = I();
		}
		$obj = new Goods($data);
		$a = $obj->get_spec_goods_price($data);
		if (is_array($a))
		{
			jsonout($a[0], $a[1], $a[2]);
		}
		jsonout($a);
	}

	/**
	 * 商品列表
	 * 以下都可根据条件筛选商品
	 * @param int $cat_id 分类id
	 * @param int $hot_id 1.首页精品推荐
	 * @param string $hot_name 根据hot名显示
	 * @param int $last_id 上一页最后一条id
	 * @param string $word 搜索内容(首页搜索框走这个接口)
	 * @param string $time_sort 时间, desc从大到小,asc从小到大
	 * @param string $sell_sort 销量，desc从大到小,asc从小到大
	 * @param string $price_sort 价格，desc从大到小,asc从小到大
	 * @param int $shop_id 商家id
	 * @return  json
	 * 目前只做基础的处理
	 * @yh
	 */
	public function goodsList($data = null)
	{

		$last_id = I("last_id", 0);
		$page = I("page", 1);
		$paging['page'] = $page;
		$paging['last_id'] = $last_id;
		$num = 10;

		$obj = new Goods();
		$where['isdel'] = 0;
		$where = $this->getGoodsQuery();
		$ord = $this->getGoodsSort();
		$ok = $obj->goodsList($where, $ord, $num, $paging);

		if (!$ok)
		{
			jsonout("暂无数据");
		}
		$numArr = $obj->getNums($where, $num);
		$res['list'] = $ok;
		$res['page'] = $page;
		$res['last_id'] = get_last_id($ok, 'id', $num);
		$res['max_page'] = $numArr['max_page'];
		jsonout("商品列表", 0, $res);
	}

	/**
	 * 筛选商品列表
	 * 以下都可根据条件筛选商品
	 * @param int $cat_id 分类id
	 * @param int $hot_id 1.正品热卖 2.热销商品 3.精品推荐 4.新品上市 5.猜你喜欢
	 * @param string $hot_name 根据hot名显示
	 * @param int $last_id 上一页最后一条id
	 * @param string $word 搜索内容(首页搜索框走这个接口)
	 * @param int $shop_id 商家ID
	 * @return  json
	 * @yh
	 */
	private function getGoodsQuery($data = null)
	{
		if (!$data)
		{
			$data = I();
		}
		$cat_id = $data['cat_id'];
		$hot_id = $data['hot_id'];
		$last_id = $data['last_id'];
		$word = $data['word'];
		$shop_id = $data['shop_id'];

		// 找到所有下级ID
		$cidArr = array();
		if ($cat_id)
		{
			$childs = get_childs($cat_id);
			$c2a = reformat($childs);
			foreach ($c2a as $key => $val)
			{
				$cidArr[] = $val["id"];
			}
			$cidArr[] = $cat_id;

		}


		if ($last_id > 0)
		{
			$where['id'] = array('lt', $last_id);
		}

		if ($word)
		{
			$where['name'] = array('like', "%{$word}%");
		}

		if ($hot_id)
		{
			$where['hot_id'] = $hot_id;
		}
		if ($cat_id && $cidArr)
		{
			$where['cat_id'] = array("in", $cidArr);
		}
		if ($shop_id)
		{
			$where['shop_id'] = $shop_id;
		}

		$where['is_on_sale'] = 1;
		$where['isdel'] = 0;
		return $where;
	}

	/**
	 * 按照条件排序商品列表
	 * @param string $time_sort 时间, desc从大到小,asc从小到大
	 * @param string $sell_sort 销量，desc从大到小,asc从小到大
	 * @param string $price_sort 价格，desc从大到小,asc从小到大
	 * @return  json
	 * @yh
	 */
	private function getGoodsSort($data = null)
	{
		if (!$data)
		{
			$data = I();
		}
		$time_sort = $data['time_sort'];
		$sell_sort = $data['sell_sort'];
		$price_sort = $data['price_sort'];

		$sort = 'id desc';
		if ($time_sort)
		{
			if ($time_sort == 'desc')
			{
				$sort = 'time desc';
			}
			else
			{
				$sort = 'time asc';
			}
		}

		if ($sell_sort)
		{
			if ($time_sort == 'desc')
			{
				$sort = 'sell_count desc';
			}
			else
			{
				$sort = 'sell_count asc';
			}
		}

		if ($price_sort)
		{
			if ($price_sort == 'desc')
			{
				$sort = 'price desc';
			}
			else
			{
				$sort = 'price asc';
			}
		}
		return $sort;
	}

	/**
	 * 拼团商品详情
	 * @param int $id 商品id必须
	 * @return json|string
	 * @shy
	 */
	public function group_detail($data = null)
	{
		if (!$data)
		{
			$data = I();
		}

		$obj = new Goods($data);
		$res = $obj->group_detail($data);

		if (is_array($res))
		{
			jsonout($res[0], $res[1], $res[2]);
		}
		jsonout($res);

	}

	function goodsGroupApi($data = null)
	{
		if (!$data)
		{
			$data = I();
		}
		$obj = new Goods($data);
		$list = $obj->unGroupList($data['goods_id'], 15);
		jsonout('正在拼团', 0, $list);

	}

	/**
	 * 获取团购商品列表
	 * 同时返回商品对应的分类列表
	 * @zd
	 */
	public function groupCate()
	{
		// 分类列表
		$n = 0;
		$category_list = array();

		$cateList = M("goods")->where("is_group=1 and is_on_sale=1 and isdel=0")->group("cat_id")->field("cat_id")->select();
		foreach ($cateList as $key2 => $value2)
		{
			$catName = M("goods_category")->where("id=" . $value2["cat_id"])->getField("name");
			if ($catName)
			{
				$category_list[$n]["cat_id"] = $value2["cat_id"];
				$category_list[$n]["name"] = $catName;
			}

			$n++;
		}
		$data["position"] = "ad_group";
		$data["row"] = 1;
		$obj = new Ad();
		$result = $obj->adList($data);
		$category_list = array_values($category_list);
		$res["banner"] = $result[2];
		$res["catelist"] = $category_list;
		jsonout("团购分类及广告", 0, $res);
	}

	/**
	 * 获取秒杀商品列表
	 * @shy
	 */
	public function hourList()
	{
		$goods = new Goods();
		$time = I("time", strtotime(date("Y-m-d H", time()) . ":00:00"));//范围时间
		$cat_id = I("cat_id", 0);    // 分类ID
		$page = I("page", 1);
		$paging['page'] = $page;
		$num = 10;
		$order = "a.sort desc";
		$rulewhere["a.isdel"] = 0;
		$rulewhere["a.is_on_sale"] = 1;
		$rulewhere["a.is_hour"] = 1;
		$rulewhere["b.type"] = 0;
		// $rulewhere["b.start_time"] = array('elt', $time);
		// $rulewhere["b.end_time"] = array('egt', $time);
		$hourStart = strtotime(date("Y-m-d H:00:00"));
		$hourEnd = $time + 3600;  // 当前小时结束
		$rulewhere["_string"] = " b.end_time>={$time} and ( b.start_time <= {$time}  or ( b.start_time >= {$time} and b.start_time < {$hourEnd} ) ) ";

		$list = $goods->goodsListRule($rulewhere, $order, $num, $paging);
		if (!$list)
		{
			jsonout("没有更多数据");
		}
		foreach ($list as $key => &$value)
		{
			$list[$key]['percentage'] = ceil($list[$key]['sell_count'] / $list[$key]['store_count'] * 100);
			$priceInfo = M("goods_spec_price")->where("goods_id=" . $value["id"])->order("hour_price asc")->find();
			$list[$key]['hour_price'] = $priceInfo['hour_price'] ? $priceInfo['hour_price'] : 0;
			if ($time > time())
			{
				$list[$key]['btn_str'] = "即将开始";
			}
			elseif ($time < $hourStart)
			{
				$list[$key]['btn_str'] = "已过期";
			}
			else
			{
				$list[$key]['btn_str'] = "马上抢";
			}
		}
		$res['list'] = $list;
		$res['page'] = $page;
		$res['last_id'] = get_last_id($list, 'id', $num);
		jsonout("秒杀列表", 0, $res);
	}

	/**
	 * 获取秒杀商品列表
	 * 同时返回商品对应的时间列表
	 * @shy
	 */
	public function hourCate()
	{
		$timeList = array();
		$wholetime = strtotime(date("Y-m-d H", time()) . ":00:00"); //当前整点时间
		for ($i = 0; $i < 5; $i++)
		{
			if (empty($timeList))
			{
				$timeList[$i]['cat_id'] = date("H", $wholetime) . ':00';
				$timeList[$i]['name'] = "抢购中";
				$timeList[$i]['time'] = $wholetime;
			}
			else
			{
				$wholetime = $wholetime + 3600;
				$timeList[$i]['cat_id'] = date("H", $wholetime) . ':00';
				$timeList[$i]['name'] = "即将开始";
				$timeList[$i]['time'] = $wholetime;
			}
		}
		$data["position"] = "ad_hour";
		$data["row"] = 1;
		$obj = new Ad();
		$result = $obj->adList($data);
		$res["banner"] = $result[2];
		$res["catelist"] = $timeList;
		jsonout("秒杀时间分类及广告", 0, $res);
	}

	/**
	 * 获取限时购商品列表
	 * @shy
	 */
	public function timeList()
	{
		$goods = new Goods();
		$time = I("time", strtotime(date("Y-m-d H", time()) . ":00:00"));//范围时间
		$cat_id = I("cat_id", 0);    // 分类ID
		$last_id = I("last_id", 0);
		$page = I("page", 1);
		$paging['page'] = $page;
		$paging['last_id'] = $last_id;
		$num = 1;
		$where['name'] = 'limited_time_size';
		$size = M('config_project')->where($where)->field('val')->find()['val'];
		$endtime = $time + ($size * 3600);
		$order = "a.sort desc";
		$rulewhere["a.isdel"] = 0;
		$rulewhere["a.is_on_sale"] = 1;
		$rulewhere["a.is_time"] = 1;
		$rulewhere["b.type"] = 1; //0 秒杀 1-限时购
		$rulewhere["b.start_time"] = array('egt', $time);
		$rulewhere["b.end_time"] = array('lt', $endtime);
		$list = $goods->goodsListRule($rulewhere, $order, $num, $paging);
		if (!$list)
		{
			jsonout("没有更多数据");
		}
		foreach ($list as $key => &$value)
		{
			$list[$key]['percentage'] = ceil($list[$key]['sell_count'] / $list[$key]['store_count'] * 100);
			$priceInfo = M("goods_spec_price")->where("goods_id=" . $value["id"])->order("time_price asc")->find();
			$list[$key]['time_price'] = $priceInfo['time_price'] ? $priceInfo['time_price'] : 0;
		}
		$numArr = $goods->getNums($where, $num);
		$res['list'] = $list;
		$res['page'] = $page;
		$res['last_id'] = get_last_id($list, 'id', $num);
		$res['max_page'] = $numArr['max_page'];
		jsonout("限时购列表", 0, $res);
	}

	/**
	 * 获取限时购商品列表
	 * 同时返回商品对应的时间列表
	 * @shy
	 */
	public function timeCate()
	{
		$where['name'] = 'limited_time_size';
		$size = M('config_project')->where($where)->field('val')->find()['val'];
		$timeList = array();
		$wholetime = strtotime(date("Y-m-d H", time()) . ":00:00"); //当前整点时间
		for ($i = 0; $i < 5; $i++)
		{
			if (empty($timeList))
			{
				$timeList[$i]['cat_id'] = date("H", $wholetime) . ':00';
				$timeList[$i]['name'] = "抢购中";
				$timeList[$i]['time'] = $wholetime;
			}
			else
			{
				$wholetime = $wholetime + ($size * 3600);
				$timeList[$i]['cat_id'] = date("H", $wholetime) . ':00';
				$timeList[$i]['name'] = "即将开始";
				$timeList[$i]['time'] = $wholetime;
			}
		}
		$data["position"] = "ad_time";
		$data["row"] = 1;
		$obj = new Ad();
		$result = $obj->adList($data);
		$category_list = array_values($category_list);
		$res["banner"] = $result[2];
		$res["catelist"] = $timeList;
		jsonout("限时购时间分类及广告", 0, $res);
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
	 * Notes:商品排行列表
	 * User: WangSong
	 * Date: 2020/7/8
	 * Time: 11:40
	 */
	public function ranking_list()
	{
		$data = I();
		$goods = new Goods();
		$list = $goods->ranking_list($data['shop_id']);
		$this->assign('list', $list);
		$this->display();
	}


	/**
	 * ajax查询商品商品交易明细
	 * 分页展示
	 * @author lty
	 */
	public function goodsPayList()
	{
		$orderOut = new Orderout();
		$id = I('id');
		$transaction_record = $orderOut->get_transaction_record($id);
		$page = I('page');
		if ($transaction_record)
		{
			$list['list'] = $transaction_record;
			$list['page'] = $page;
			jsonout('查询成功', 0, $list);
		}
		else
		{
			jsonout('暂无数据', 1, null);
		}

	}


	/**
	 * 积分商城的商品列表
	 * @author lty
	 */
	public function waitGoodsList()
	{

		$page = I('page', 1);
		$type = I('type', 0);//0默认id排序   1销量  2价格
		if ($type == 0)
		{
			$ord = 'id DESC';
		}
		elseif ($type == 1)
		{
			$ord = 'sell_count ASC';
		}
		elseif ($type == 2)
		{
			$ord = 'price ASC';
		}
		$where['isdel'] = 0;
		$where['product_type'] = 1;
		$where['is_on_sale'] = 1;
		$data['number'] = 10;
		$data['type'] = $type;
		$data['page'] = $page;
		$obj = new Goods();
		$ok = $obj->newGoodsList($where, $data, $ord);
		foreach ($ok['list'] as $k => $v)
		{
			$ok['list'][$k]['thumb'] = full_pre_url($v['thumb']);
			$ok['list'][$k]['images'] = json_decode($ok['list'][$k]['images'], true);
			foreach ($ok['list'][$k]['images'] as $kk => $vv)
			{
				$ok['list'][$k]['images'][$kk] = full_pre_url($vv);
			}
			$ok['list'][$k]['images'] = json_encode($ok['list'][$k]['images']);
		}
		$this->assign('list', $ok['list']);
		$this->assign('type', $type);
		$this->assign('max_page', $ok['max_page']);
		$this->assign('page', $page);

		if ($page > 1)
		{
			$this->display('Goods/waitGoodsList_more');
		}
		else
		{
			$this->display();
		}

	}

}