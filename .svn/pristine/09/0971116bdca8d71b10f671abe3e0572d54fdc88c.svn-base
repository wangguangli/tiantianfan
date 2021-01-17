<?php

namespace M\Controller;
header("Content-type: text/html; charset=utf-8");

use Clas\Goods;
use Think\Controller;
use Clas\Shop;

class ShopController extends Controller
{
	public $user_id;

	public function __construct()
	{
		parent::__construct();
		$this->user_id = session('user_id');

	}

	public function index($data = null)
	{
		if (!$data)
		{
			$data = I();
		}

		$obj = new Shop(0, 'shop', $this->user_id);
		$ok = $obj->shopDetail($data);
		if (!is_array($ok))
		{
			$this->error($ok);
		}
		$data = $ok['result'];
		$this->display();
	}


	/**
	 *
	 */
	public function shopsave()
	{
		$ud = I();
		print_r($ud);
	}

	/**
	 * 商家列表
	 * 一般传入参数
	 * @param string $city 城市名
	 * @param int $city_id 城市ID
	 * @param int $longitude 经度
	 * @param int $latitude 维度
	 * @param int $industry_id 行业ID
	 * @param string $word 店铺名称查询
	 * @param int $page 第几页
	 * @param int $num 一页几个
	 * @param int $last_id 最后一个ID
	 * @return  json
	 * @wz
	 */
	public function shoplist()
	{

		$data = I();
	    if(!$data['order_by']){
	        $data['order_by'] = 1;
        }
		$this->assign('industry_id', $data['industry_id']);
		$this->assign('order_by', $data['order_by']);

		$this->display();


	}

	//商家详情
	public function shopDetail()
	{
		$data = I();
		$type = I('type', 1);
		if (!$data['shop_id'])
		{
			$this->error("参数异常，请重新打开");
		}
		if ($type == 1)
		{
			$obj = new Shop(0, 'shop', $this->user_id);
			$ok = $obj->shopDetail($data);
			if (!is_array($ok))
			{
				$this->error($ok);
			}
		}

		$page = I("page", 1);
		$num = 10;

		if ($type == 2 || $type == 1)
		{
			//查询商家商品
			$paging['page'] = $page;
			$obj = new Goods();
			$where['isdel'] = 0;
			$where['shop_id'] = $data['shop_id'];
			$ord = 'id DESC';
			$goodsList = $obj->goodsList($where, $ord, $num, $paging);
			$this->assign('list', $goodsList);
			if ($page == 1)
			{
				$numArr = $obj->getNums($where, $num);
				$max_page_goods = $numArr['max_page'];
				$this->assign('max_page_goods', $max_page_goods);
			}
		}
		if ($type == 3 || $type == 1)
		{
			//查询店铺的评论
			$commentData['page'] = $page;
			$commentData['page_size'] = $num;
			$commentData['shop_id'] = $data['shop_id'];
			$goods = new Goods();
			$rs = $goods->goods_comment($commentData);
			$comment = $rs[2];

			/*dump($comment, 1, 1, 0);
			die();*/

			$this->assign('comment', $comment);

			if ($page == 1)
			{
				$commentWhere['shop_id'] = $data['shop_id'];
				$commentCount = M('goods_comment')->where($commentWhere)->count();
				$max_page_comment = ceil($commentCount / $num);
				$this->assign('max_page_comment', $max_page_comment);
			}
		}
		$this->assign('data', $ok[2]);
		$this->assign('type', $type);
		$this->assign('shop_id', $data['shop_id']);
		$this->assign('user_id', $this->user_id);

		if ($type == 1)
		{
			$this->display();
		}
		elseif ($type == 2)
		{
			if ($page == 1)
			{
				$this->display();
			}
			else
			{
				$this->display('shop_goods_load_more');
			}
		}
		elseif ($type == 3)
		{
			if ($page == 1)
			{
				$this->display();
			}
			else
			{
				$this->display('shop_comment_load_more');
			}
		}
	}


	public function xieyi()
	{
		$arr = M('article')->where('title="商家注册协议"')->find();
		$this->assign('data', $arr);
		$this->display();
	}

	/**
	 * 商家服务费比例列表
	 * @return  json
	 * @zd
	 */
	public function feeList()
	{
		$list = M("config_shop_fee")->order("id asc")->select();
		if (!$list)
		{
			jsonout("暂无服务费列表", 1);
		}
		jsonout("服务费列表", 0, $list);
	}

	/**
	 * 商家评论
	 * 一般传入参数
	 * @param int $shop_id 商家ID
	 * @param int $page 第几页
	 * @param int $page_size 一页几个
	 * @return  json
	 * @wz
	 */
	public function shopComment($data = null)
	{

		if (!$data)
		{
			$data = I();
		}

		$data['goods_id'] = M('goods')->where("shop_id = {$data['shop_id']}")->getField("id", true);

		if ($data['goods_id'])
		{
			$goods = new Goods();
			$rs = $goods->goods_comment($data);
		}
		else
		{
			jsonout('暂无数据', 1);
		}
		jsonout($rs[0], $rs[1], $rs[2]);
	}

	/**
	 * Notes:输出线下门店
	 * User: WangSong
	 * Date: 2020/7/9
	 * Time: 17:49
	 */
	public function offline()
	{
		$data = I();
		$shop = new Shop($data['shop_id'], 'shop', $this->user_id);
		$list = $shop->get_shop_offline($data['shop_id']);
		$this->assign('list', $list);
		$this->display();
	}

}