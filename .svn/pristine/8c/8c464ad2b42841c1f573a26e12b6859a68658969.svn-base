<?php

namespace Api\Controller;

use Clas\Goods;
use Clas\Favorite;
use Clas\Ad;
use Clas\Orderout;

class GoodsOutController extends CommonController
{


	public function __construct()
	{
		parent::__construct();
	}

	/**
	 *
	 * @param int $pid 必须  上级分类ID，默认为0获取顶级分类,-1是获取所有分类
	 * @return json|string
	 * @mgl
	 */
	public function category($data = null)
	{
		if (!$data)
		{

			$data = I();
		}
		$obj = new Goods($data);

		$res = $obj->category_list($data);
		jsonout($res[0], $res[1], $res[2]);
	}

	/**
	 * 获取三级分类
	 * @return array
	 * @mgl
	 */
	public function get_level3_cate()
	{
		$fall = M('goods_category')->where('level=3')->select();
		jsonout('商品3级分类', 0, $fall);
	}

	/**
	 *
	 * @param array $data 必须   type_id分类id id规格分类id
	 * @return json|string
	 * @mgl
	 */
	public function goodsSpec($data = null)
	{
		if (!$data)
		{

			$data = I();
		}
		$obj = new Goods($data);

		$res = $obj->goodsSpec($data);
		if (is_array($res))
		{

			jsonout($res[0], $res[1], $res[2]);
		}
		jsonout($res);
	}

	//根据一级分类获取二级分类
	public function category_two()
	{
		$id = I('id');
		$category = M('goods_category')->field('name,img')->find($id);

		$su_category = M('goods_category')->where('pid=' . $id)->getField('id,name,img');
		$all_category['img'] = $category['img'];
		$all_category['name'] = $category['name'];
		$all_category['sub'] = $su_category;
		if ($su_category)
		{
			jsonout('获取数据成功', 1, $all_category);
		}
		else
		{
			jsonout('子分类没有数据', 2, $all_category);
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
			jsonout("加载完成");
		}
		$numArr = $obj->getNums($where, $num);
		$res['list'] = $ok;
		$res['page'] = $page;
		$res['last_id'] = get_last_id($ok, 'id', $num);
		$res['max_page'] = $numArr['max_page'];
		foreach ($res['list'] as $k => $v)
		{
			$res['list'][$k]['thumb'] = full_pre_url($v['thumb']);
			$res['list'][$k]['images'] = json_decode($res['list'][$k]['images'], true);
			foreach ($res['list'][$k]['images'] as $kk => $vv)
			{

				$res['list'][$k]['images'][$kk] = full_pre_url($vv);

			}
			$res['list'][$k]['images'] = json_encode($res['list'][$k]['images']);
		}
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
		$product_type = $data['product_type'];

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
		if ($product_type)
		{
			$where['product_type'] = $product_type;
		}
        if(!I('is_show',0)){
            $where['is_on_sale'] = 1;
        }

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
			if ($sell_sort == 'desc')
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
	 * 商品详情
	 * @param int $id 商品id必须
	 * @return json|string
	 * @mgl
	 */
	public function detail($data = null)
	{

	    if (!$data)
		{
			$data = I();
		}
		$obj = new Goods($data);
		$res = $obj->detail($data);

		if (!is_array($res))
		{
			jsonout($res);
		}

		$res[2]['goods']['thumb'] = full_pre_url($res[2]['goods']['thumb']);
		$res[2]['goods']['content'] = htmlspecialchars_decode($res[2]['goods']['content']);

		foreach ($res[2]['images'] as $k => $v)
		{
			$res[2]['images'][$k] = full_pre_url($v);
		}
		// 成交记录是否输出
		$transaction_record = array();
		$is_transaction_record = get_config('is_transaction_record');//控制显示成交记录 1是显示 0是关闭
		if ($is_transaction_record)
		{
			$orderOut = new Orderout();
			$transaction_record = $orderOut->get_transaction_record($data['id']);
		}
		$res[2]['transaction_record'] = $transaction_record;
		$res[2]['is_transaction_record'] = $is_transaction_record;
		if (is_array($res))
		{
			jsonout($res[0], $res[1], $res[2]);
		}
		jsonout($res);
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

		$obj = new Goods($data);
		$content = $obj->goods_detail($data);

		$this->assign('content', htmlspecialchars_decode($content));
		$this->display('Index/goods_detail');
	}

	function goodsGroup($data = null)
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
	 *
	 *商品评论列表
	 * @param int $goods_id 可选
	 * @param int $user_id 可选
	 * @param int $page 当前页
	 * @param int $page_size 每页显示数量
	 * @param int $source 来源，0商品详情，1个人中心，2商家界面
	 * @return json|string
	 * @mgl
	 */
	function goodsComment($data = null)
	{

		if (!$data)
		{
			$data = I();
		}
		if ($data['source'] < 1 || !$data['source'])
		{
			unset($data['user_id']);
		}

		$obj = new Goods($data);
		$a = $obj->goods_comment($data);
		if (is_array($a))
		{
			jsonout($a[0], $a[1], $a[2]);
		}
		jsonout($a);

	}


	/*
	 * 获取广告
	 * @yh
	 * */
	function getAd($position, $row = 0)
	{
		$newAd = array();
		if ($row)
		{
			$ad = M('ad')->where(array('position' => $position))->order('sort desc')->limit($row)->select();
			foreach ($ad as $v)
			{
				$newAd[] = array(
					'ad_id' => $v['id'],
					'photo' => $v['photo'],
					'module' => $v['module'],
					'mod_id' => $v['mod_id'],
					'api_url' => $v['api_url'],
					'link' => $v['link']
				);
			}
		}
		else
		{
			$ad = M('ad')->where(array('position' => $position))->find();
			$newAd = array(
				'ad_id' => $ad['id'],
				'photo' => $ad['photo'],
				'module' => $ad['module'],
				'mod_id' => $ad['mod_id'],
				'api_url' => $ad['api_url'],
				'link' => $ad['link']
			);
		}
		return $newAd;
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

	// 获取商品信息
	function getGoods($data = null)
	{
		if (!$data)
		{
			$data = I();
		}
		$Mg = M('goods');
		if (empty($data['goods_id']))
		{
			return false;
		}
		$goods = $Mg->find($data);

		jsonout('商品信息', 0, $goods);
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
	 * 获取团购商品列表
	 * 同时返回商品对应的分类列表
	 * @zd
	 */
	public function groupList()
	{

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
		jsonout("团购列表", 0, $res);
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
	 * 获取秒杀商品列表
	 * @shy
	 */
	public function hourList()
	{
		$goods = new Goods();
		$time = I("time", strtotime(date("Y-m-d H", time()) . ":00:00"));//范围时间
		$cat_id = I("cat_id", 0);    // 分类ID
		$last_id = I("last_id", 0);
		$page = I("page", 1);
		$paging['page'] = $page;
		$paging['last_id'] = $last_id;
		$num = 10;
		$order = "a.sort desc";
		$rulewhere["a.isdel"] = 0;
		$rulewhere["a.is_on_sale"] = 1;
		$rulewhere["a.is_hour"] = 1;
		$rulewhere["b.type"] = 0;
		// $rulewhere["b.start_time"] = array('elt', $time);
		// $rulewhere["b.end_time"] = array('egt', $time);
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
		}
		$res['list'] = $list;
		$res['page'] = $page;
		$res['last_id'] = get_last_id($list, 'id', $num);
		jsonout("秒杀列表", 0, $res);
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
	 * 商品海报
	 */
	public function goods_poster()
	{

		$goods_id = I("goods_id", 0);    // 商品ID
		$price = I("price", 0);
		$user_id = I("user_id", 0);
		$goods_kind = I("goods_kind", 0);    // 商品打开来源，0普通商品，1拼团，2整点秒杀，3限时购


		$goods = M('goods')->find($goods_id);
		if ($goods && $goods['is_on_sale'] == 1 && $goods['isdel'] == 0)
		{
			Vendor('phpqrcode.phpqrcode');
			$url = 'http://' . $_SERVER['HTTP_HOST'] . "/index.php/M/Goods/detail?id=" . $goods_id . '&user_id=' . $user_id . '&goods_kind=' . $goods_kind;
			$url = urldecode($url);
			$uuid = uniqid($goods_id);
			$filename = 'upload/goods/qrcode/' . $uuid . $goods_id . '.png';

			//使用方法-------------------------------------------------
			$gData = [
				'pic' => $goods['thumb'],//商品图片
				'title' => $goods['name'],
				'price' => $price,
				'logo' => GC('logo'),
			];
			//输出到图片
			if ($user_id)
			{
				$goods_poster = M('goods_poster')->where('user_id=' . $user_id . ' and goods_id=' . $goods_id)->find();
				if (!$goods_poster)
				{
					$object = new \QRcode();
					// // ob_clean();//这个一定要加上，清除缓冲区
					$object->png($url, $filename, 'L', 6, 2);
					$qrcode = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $filename;

					$this->createSharePng($gData, $qrcode, 'upload/goods/code/' . $uuid . '_' . $goods_id . '_' . $user_id . '.jpg');
					$date['goods_id'] = $goods_id;
					$date['user_id'] = $user_id;
					$date['time'] = time();
					$date['qrcode'] = 'upload/goods/code/' . $uuid . '_' . $goods_id . '_' . $user_id . '.jpg';
					M('goods_poster')->add($date);
					$qrcode = $date['qrcode'];
				}
				else
				{
					$qrcode = $goods_poster['qrcode'];
				}
			}
			else
			{
				$goods_poster = M('goods_poster')->where('user_id=0 and goods_id=' . $goods_id)->find();
				if (!$goods_poster)
				{
					$object = new \QRcode();
					// // ob_clean();//这个一定要加上，清除缓冲区
					$object->png($url, $filename, 'L', 6, 2);
					$qrcode = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $filename;

					$this->createSharePng($gData, $qrcode, 'upload/goods/code/' . $uuid . '_' . $goods_id . '_' . $user_id . '.jpg');
					$date['goods_id'] = $goods_id;
					$date['user_id'] = $user_id;
					$date['time'] = time();
					$date['qrcode'] = 'upload/goods/code/' . $uuid . '_' . $goods_id . '_' . $user_id . '.jpg';
					M('goods_poster')->add($date);
					$qrcode = $date['qrcode'];
				}
				else
				{
					$qrcode = $goods_poster['qrcode'];
				}
			}

			jsonout("图片", 0, $qrcode);

		}
		else
		{
			jsonout("商品不存在或已下架", 1);
		}
	}


	/**
	 * 分享图片生成
	 * @param $gData  商品数据，array
	 * @param $codeName 二维码图片
	 * @param $fileName string 保存文件名,默认空则直接输入图片
	 */
	public function createSharePng($gData, $codeName, $fileName = '')
	{
		//创建画布
		$im = imagecreatetruecolor(618, 1000);

		//填充画布背景色
		$color = imagecolorallocate($im, 255, 255, 255);
		imagefill($im, 0, 0, $color);

		$white = imagecolorallocate($im, 226, 226, 226);//设置绘制图像的线的颜色
		imagerectangle($im, 93.5, 120, 522, 780, $white);//绘制一个长方形

		//字体文件
		$font_file = 'Public/font/HKJGH-Light.ttf';
		$font_file_bold = 'Public/font/HKJGH-Light.ttf';

		//设定字体的颜色
		$font_color_1 = ImageColorAllocate($im, 140, 140, 140);
		$font_color_2 = ImageColorAllocate($im, 28, 28, 28);
		$font_color_3 = ImageColorAllocate($im, 129, 129, 129);
		$font_color_red = ImageColorAllocate($im, 217, 45, 32);

		$fang_bg_color = ImageColorAllocate($im, 254, 216, 217);

		//Logo 顶部的logo
		list($l_w, $l_h) = getimagesize('http://' . $_SERVER['HTTP_HOST'] . $gData['logo']);
		$logoImg = @imagecreatefrompng('http://' . $_SERVER['HTTP_HOST'] . $gData['logo']);
		imagecopyresized($im, $logoImg, 274, 28, 0, 0, 70, 70, $l_w, $l_h);

		//温馨提示 
		//imagettftext($im, 14,0, 100, 130, $font_color_1 ,$font_file, '温馨提示：喜欢长按图片识别二维码即可前往购买');

		//商品图片  第2,3个参数是x y的位置，第7,8个参数是商品图片的大小
		list($g_w, $g_h) = getimagesize($gData['pic']);
		$goodImg = $this->createImageFromFile($gData['pic']);
		imagecopyresized($im, $goodImg, 93.5, 120, 0, 0, 430, 430, $g_w, $g_h);

		//二维码
		list($code_w, $code_h) = getimagesize($codeName);
		$codeImg = $this->createImageFromFile($codeName);
		imagecopyresized($im, $codeImg, 93.5, 820, 0, 0, 170, 170, $code_w, $code_h);

		//商品描述
		$dstY = 618;
		$theTitle = $this->cn_row_substr($gData['title'], 1, 12);

		$fontBox1 = imagettfbbox(20, 0, $font_file, $theTitle[1]);//文字水平居中实质
		$dstX1 = ceil(($dstY - $fontBox1[2] + 70) / 2);

		$fontBox2 = imagettfbbox(20, 0, $font_file, $theTitle[2]);//文字水平居中实质
		$dstX2 = ceil(($dstY - $fontBox2[2] + 60) / 2);
		imagettftext($im, 14, 0, $dstX1, 610, $font_color_2, $font_file, $theTitle[1]);
		imagettftext($im, 14, 0, $dstX2, 640, $font_color_2, $font_file, $theTitle[2]);
		imagettftext($im, 14, 0, 240, 730, $font_color_red, $font_file_bold, "￥");
		imagettftext($im, 28, 0, 260, 730, $font_color_red, $font_file_bold, $gData["price"]);


		$Title = $this->cn_row_substr('长按图片识别二维码查看商品详情', 2, 9);
		imagettftext($im, 14, 0, 300, 900, $font_color_2, $font_file, $Title[1]);
		imagettftext($im, 14, 0, 300, 930, $font_color_2, $font_file, $Title[2]);

		//imagettftext($im, 14,0, 230, 900, $font_color_1 ,$font_file, '长按图片识别二维码查看商品详情');
		//输出图片
		if ($fileName)
		{
			imagepng($im, $fileName);
		}
		else
		{
			Header("Content-Type: image/png");
			imagepng($im);
		}
		//释放`空间
		imagedestroy($im);
		imagedestroy($goodImg);
		imagedestroy($codeImg);
	}

	/**
	 * 从图片文件创建Image资源
	 * @param $file 图片文件，支持url
	 * @return bool|resource    成功返回图片image资源，失败返回false
	 */
	public function createImageFromFile($file)
	{
		if (preg_match('/http(s)?:\/\//', $file))
		{
			$fileSuffix = $this->getNetworkImgType($file);
		}
		else
		{
			$fileSuffix = pathinfo($file, PATHINFO_EXTENSION);
		}

		if (!$fileSuffix)
		{
			return false;
		}

		switch ($fileSuffix)
		{
			case 'jpeg':
				$theImage = @imagecreatefromjpeg($file);
				break;
			case 'jpg':
				$theImage = @imagecreatefromjpeg($file);
				break;
			case 'png':
				$theImage = @imagecreatefrompng($file);
				break;
			case 'gif':
				$theImage = @imagecreatefromgif($file);
				break;
			default:
				$theImage = @imagecreatefromstring(file_get_contents($file));
				break;
		}

		return $theImage;
	}

	/**
	 * 获取网络图片类型
	 * @param $url  网络图片url,支持不带后缀名url
	 * @return bool
	 */
	public function getNetworkImgType($url)
	{
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $url); //设置需要获取的URL
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //支持https
		curl_exec($ch);//执行curl会话
		$http_code = curl_getinfo($ch);//获取curl连接资源句柄信息
		curl_close($ch);//关闭资源连接

		if ($http_code['http_code'] == 200)
		{
			$theImgType = explode('/', $http_code['content_type']);

			if ($theImgType[0] == 'image')
			{
				return $theImgType[1];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * 分行连续截取字符串
	 * @param $str  需要截取的字符串,UTF-8
	 * @param int $row 截取的行数
	 * @param int $number 每行截取的字数，中文长度
	 * @param bool $suffix 最后行是否添加‘...’后缀
	 * @return array    返回数组共$row个元素，下标1到$row
	 */
	public function cn_row_substr($str, $row = 1, $number = 10, $suffix = true)
	{
		$result = array();
		for ($r = 1; $r <= $row; $r++)
		{
			$result[$r] = '';
		}

		$str = trim($str);
		if (!$str)
		{
			return $result;
		}

		$theStrlen = strlen($str);

		//每行实际字节长度
		$oneRowNum = $number * 3;
		for ($r = 1; $r <= $row; $r++)
		{
			if ($r == $row and $theStrlen > $r * $oneRowNum and $suffix)
			{
				$result[$r] = $this->mg_cn_substr($str, $oneRowNum - 6, ($r - 1) * $oneRowNum) . '...';
			}
			else
			{
				$result[$r] = $this->mg_cn_substr($str, $oneRowNum, ($r - 1) * $oneRowNum);
			}
			if ($theStrlen < $r * $oneRowNum)
			{
				break;
			}
		}

		return $result;
	}

	/**
	 * 按字节截取utf-8字符串
	 * 识别汉字全角符号，全角中文3个字节，半角英文1个字节
	 * @param $str  需要切取的字符串
	 * @param $len  截取长度[字节]
	 * @param int $start 截取开始位置，默认0
	 * @return string
	 */
	public function mg_cn_substr($str, $len, $start = 0)
	{
		$q_str = '';
		$q_strlen = ($start + $len) > strlen($str) ? strlen($str) : ($start + $len);

		//如果start不为起始位置，若起始位置为乱码就按照UTF-8编码获取新start
		if ($start and json_encode(substr($str, $start, 1)) === false)
		{
			for ($a = 0; $a < 3; $a++)
			{
				$new_start = $start + $a;
				$m_str = substr($str, $new_start, 3);
				if (json_encode($m_str) !== false)
				{
					$start = $new_start;
					break;
				}
			}
		}
		//切取内容
		for ($i = $start; $i < $q_strlen; $i++)
		{
			//ord()函数取得substr()的第一个字符的ASCII码，如果大于0xa0的话则是中文字符
			if (ord(substr($str, $i, 1)) > 0xa0)
			{
				$q_str .= substr($str, $i, 3);
				$i += 2;
			}
			else
			{
				$q_str .= substr($str, $i, 1);
			}
		}
		return $q_str;
	}

	/**
	 * Notes:商品排行榜列表
	 * User: WangSong
	 * Date: 2020/7/8
	 * Time: 11:03
	 */
	public function ranking_list()
	{
		$data = I();
		$goods = new Goods();
		$list = $goods->ranking_list($data['shop_id']);
		if (!$list)
		{
			jsonout('暂无数据');
		}
		else
		{
			jsonout('获取成功', 0, $list);
		}
	}


	/**
	 * 积分商城的商品列表
	 * 排序 (销量 价格 默认)
	 * @author  lty
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
		jsonout("商品列表", 0, $ok);
	}


}