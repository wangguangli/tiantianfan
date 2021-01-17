<?php

namespace Api\Controller;

use Clas\Goods;

class ShopappController extends CommonController
{
	protected $shop_id = 0;
	protected $shopInfo = array();

	public function __construct()
	{
		parent::__construct();
		$this->shop_id = I("shop_id", 0);
		if ($this->shop_id)
		{
			$where['id'] = $this->shop_id;
			$shopInfo = get_user_info($where, "*", '', 2);
			$this->shopInfo = $shopInfo;
			if (!$this->shopInfo)
			{
				jsonout('没有找到此商家', 1);
			}
			if ($this->shopInfo['status'] != 1)
			{
				jsonout('此商家为非正常状态', 1);
			}
		}
		else
		{
			jsonout('请传入商家id', 1);
		}
	}

	/**
	 * Created by PhpStorm
	 * User: jinshibao
	 * Date: 2020/6/9
	 * Time: 11:09
	 * 商品添加规格 分类 模板
	 */
	public function goodsSpec()
	{
		$type_id = I('type_id', 0);
		if (!$type_id)
		{
			jsonout('请传入规格分类', 1);
		}
		$goods_type = M('goods_type')->find($type_id);

		if (!$goods_type)
		{
			jsonout('没有找到规格类型', 1);
		}
		$goods_id = I('goods_id', 0);
		$spec = M('goods_spec')->where('type_id=' . $goods_type['id'])->select();

		if ($spec)
		{
			$goods_type['spce'] = $spec;
			if ($goods_id)
			{
				$items_id = M('goods_spec_price')->where('goods_id = ' . $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
				$items_ids = explode('_', $items_id);
				foreach ($spec as $kk => $vv)
				{
					$item = M('goods_spec_item')->where('spec_id=' . $vv['id'])->select();
					foreach ($item as $kkk => $vvv)
					{
						if (in_array($vvv['id'], $items_ids))
						{
							$item[$kkk]['is_select'] = 1;
						}
						else
						{
							$item[$kkk]['is_select'] = 0;
						}
					}
					$goods_type['spce'][$kk]['item'] = $item;
				}
			}
			else
			{
				foreach ($spec as $kk => $vv)
				{
					$item = M('goods_spec_item')->where('spec_id=' . $vv['id'])->select();
					$goods_type['spce'][$kk]['item'] = $item;
				}
			}
		}
		jsonout('获取成功', 0, $goods_type);
	}

	/**
	 * Created by PhpStorm.
	 * User: jinshibao
	 * Date: 2020/6/13
	 * Time: 10:17
	 *点击规格项获取规格组合数据
	 */
	public function getSpec()
	{
		$data = I();
		$obj = new Goods();
		$result = $obj->getSpec($data);
		jsonout('获取成功', 0, $result);
	}

	/**
	 * Created by PhpStorm.
	 * User: jinshibao
	 * Date: 2020/6/13
	 * Time: 11:21
	 * 运费模板
	 */
	public function goodsType()
	{
		$goods_type = M('goods_type')->select();
		jsonout('获取成功', 0, $goods_type);
	}

	/**
	 * Created by PhpStorm.
	 * User: jinshibao
	 * Date: 2020/6/9
	 * Time: 14:06
	 *添加商品
	 */
	public function goodsAdd()
	{
		$data = I();
		$data['shop_id'] = $this->shop_id;
		$obj = new goods();

		$item = htmlspecialchars_decode($data['item']);
		$data['item'] = json_decode($item, true);
		if ($data['images'])
		{
			$data['images'] = explode('|', $data['images']);
		}
		$res = $obj->add($data);
		if (is_string($res))
		{
			jsonout($res, 1);
		}
		else
		{
			jsonout($res['msg'], $res['status']);
		}
	}

	/**
	 * Created by PhpStorm.
	 * User: jinshibao
	 * Date: 2020/6/6
	 * Time: 9:39
	 *商品详情
	 */
	public function goodsDetail()
	{
		$goods_id = I('goods_id', 0);
		if (!$goods_id)
		{
			jsonout('请传入商品id', 1);
		}
		$goods = M('goods')->find($goods_id);
		if ($goods['shop_id'] != $this->shop_id)
		{
			jsonout('店铺中没有对应的商品', 1);
		}
		$obj = new Goods();
		$result = $obj->app_goods_detail($goods);

		if (is_array($result))
		{
			jsonout('获取成功', 0, $result[2]);
		}
		else
		{
			jsonout($result, 1);
		}

	}

	/**
	 * Created by PhpStorm.
	 * User: jinshibao
	 * Date: 2020/6/13
	 * Time: 11:21
	 * 运费模板
	 */
	public function express()
	{
		$rule_list = M('express_rule')->select();
		jsonout('获取成功', 0, $rule_list);
	}

	/**
	 * Created by PhpStorm.
	 * User: jinshibao
	 * Date: 2020/6/9
	 * Time: 14:06
	 *
	 */
	public function goodsOnSale()
	{
		$is_on_sale = I('is_on_sale', 3);
		$goods_id = I('goods_id', 0);
		$shop_id = I('shop_id', 0);
		if ($is_on_sale == 3)
		{
			jsonout('请传入参数', 1);
		}
		if (!$goods_id)
		{
			jsonout('请传入商品id', 1);
		}
		$where['shop_id'] = $shop_id;
		$where['id'] = $goods_id;
		$goods = M('goods')->where($where)->find();
		if (!$goods)
		{
			jsonout('没有找到此商品', 1);
		}
		$res = M('goods')->where('id=' . $goods['id'])->setField('is_on_sale', $is_on_sale);
		if ($res !== false)
		{
			jsonout('操作成功', 0);
		}
		else
		{
			jsonout('操作失败，请重新操作', 1);
		}
	}

	/**
	 * Created by PhpStorm.
	 * User: jinshibao
	 * Date: 2020/6/9
	 * Time: 14:06
	 *删除商品
	 */
	public function delGoods()
	{
		$goods_id = I('goods_id', 0);
		$shop_id = I('shop_id');
		if (!$goods_id)
		{
			jsonout('请传入商品id', 1);
		}
		$where['shop_id'] = $shop_id;
		$where['id'] = $goods_id;
		$goods = M('goods')->where($where)->find();
		if (!$goods)
		{
			jsonout('没有找到此商品', 1);
		}
		$goods_isdel = M('goods')->where("shop_id=" . $shop_id . " and  id={$goods_id}")->setField('isdel', 1);
		if ($goods_isdel !== false)
		{
			M('cart')->where('goods_id=' . $goods_id)->delete(); //删除购物车中的商品
			jsonout('删除成功', 0);
		}
		else
		{
			jsonout('删除失败', 1);

		}
	}

	/**
	 * Created by PhpStorm.
	 * User: jinshibao
	 * Date: 2020/6/6
	 * Time: 8:55
	 * 店铺的商品列表
	 */
	public function goodsList()
	{
		$obj = new Goods();
		$page = I('page', 0);
		$page = $page >= 0 ? $page : 0;
		$where['shop_id'] = $this->shop_id;
		$paging['page'] = $page;
		$goodsList = $obj->goodsList($where, '', 10, $paging);
		$result['page'] = $page;
		$result['goodsList'] = $goodsList;
		if ($goodsList)
		{
			jsonout('获取成功', 0, $result);
		}
		else
		{
			jsonout('加载完成', 1);

		}
	}


	/**
	 * Title：商家添加规格名
	 * Note：
	 * User： zd
	 * Date： 2020-09-26 8:58
	 */
	public function add_goods_spec()
	{
		$name = I('name', '', 'trim');
		$sorting = I('sorting', 50);
		if (!$name)
		{
			jsonout('请输入规格名');
		}
		if (!$sorting)
		{
			$sorting = 50;
		}
		$where['name'] = $name;
		$where['shop_id'] = $this->shop_id;
		$isHave = M('goods_spec')->where($where)->find();
		if ($isHave)
		{
			jsonout('规格名已存在');
		}
		$where['order'] = $sorting;
		$res = M('goods_spec')->add($where);
		if ($res)
		{
			$spec['id'] = $res;
			$spec['type_id'] = 0;
			$spec['name'] = $name;
			$spec['order'] = $sorting;
			$spec['shop_id'] = $this->shop_id;
			jsonout('添加成功', 0, $spec);
		}
		else
		{
			jsonout('添加失败，请重新操作');
		}
	}

	/**
	 * Title：获取商家规格名列表
	 * Note：
	 * User： zd
	 * Date： 2020-09-26 8:58
	 */
	public function get_goods_spec()
	{
		$goods_id = I('goods_id', 0);
		$where['shop_id'] = $this->shop_id;
		$list = M('goods_spec')->where($where)->order('`order` asc')->select();
		if ($list)
		{
			$spec_list_sct = array();
			if ($goods_id)
			{
				$items_id = M('goods_spec_price')->where('goods_id = ' . $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
				$items_ids = explode('_', $items_id);
				$items_ids = array_unique($items_ids);
				// 通过规格值数组找出对应的规格名
				if ($items_ids)
				{
					$where_spt['id'] = array("in", $items_ids);
					$spec_list_sct = M('goods_spec_item')->where($where_spt)->getField("spec_id", true);

				}
				$spec_list_sct = array_unique($spec_list_sct);
			}
			foreach ($list as $key => &$value)
			{
				if (in_array($value['id'], $spec_list_sct))
				{
					$value['is_select'] = 1;
				}
				else
				{
					$value['is_select'] = 0;
				}
			}
			jsonout("规格名列表", 0, $list);
		}
		else
		{
			jsonout("暂无规格名列表", 1);
		}
	}


	/**
	 * Title：商家添加规格值
	 * Note：
	 * User： zd
	 * Date： 2020-09-26 8:58
	 */
	public function add_goods_spec_item()
	{
		$spec_id = I('spec_id', 0, 'trim');
		$item = I('item', '', 'trim');
		if (!$spec_id)
		{
			jsonout('请传入规格名ID');
		}
		if (!$item)
		{
			jsonout('请输入规格值');
		}
		$where_spec['id'] = $spec_id;
		$where_spec['shop_id'] = $this->shop_id;
		$haveSpec = M('goods_spec')->where($where_spec)->find();
		if (!$haveSpec)
		{
			jsonout("本商家没有对应的规格名", 1);
		}
		$where['spec_id'] = $spec_id;
		$where['item'] = $item;
		$isHave = M('goods_spec_item')->where($where)->find();
		if ($isHave)
		{
			jsonout('规格值已存在');
		}
		$res = M('goods_spec_item')->add($where);
		if ($res)
		{
			$spec['id'] = $res;
			$spec['item'] = $item;
			$spec['spec_id'] = $spec_id;
			jsonout('添加成功', 0, $spec);
		}
		else
		{
			jsonout('添加失败，请重新操作');
		}
	}


	/**
	 * Title：获取商家规格值
	 * Note：
	 * User： zd
	 * Date： 2020-09-26 8:58
	 */
	public function get_goods_spec_item()
	{
		$list = array();
		$goods_id = I('goods_id', 0, 'trim');
		$spec_id = I('spec_id', '', 'trim');
		$spec_id_arr = explode(',', $spec_id);

		if ($spec_id_arr)
		{
			$items_ids = array();
			if ($goods_id)
			{
				$items_id = M('goods_spec_price')->where('goods_id = ' . $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
				$items_ids = explode('_', $items_id);
				$items_ids = array_unique($items_ids);
			}
			foreach ($spec_id_arr as $key => $value)
			{
				$where['id'] = $value;
				$specInfo = M('goods_spec')->where($where)->find();
				if ($specInfo)
				{
					if ($specInfo['shop_id'] != $this->shop_id)
					{
						jsonout("其中的规格名与商家不对应", 1);
					}
					$where_item['spec_id'] = $value;
					$item_list = M('goods_spec_item')->where($where_item)->select();
					foreach ($item_list as $key2 => &$value2)
					{
						if (in_array($value2['id'], $items_ids))
						{
							$value2['is_select'] = 1;
						}
						else
						{
							$value2['is_select'] = 0;
						}
					}
					$specInfo['item'] = $item_list;
					$list[] = $specInfo;
				}
				else
				{
					jsonout("没有此规格名", 1);
				}
			}
		}
		if ($list)
		{
			jsonout("规格值列表", 0, $list);
		}
		else
		{
			jsonout("规格名下没有规格值列表", 1);
		}
	}

	/**
	 * Created by PhpStorm.
	 * User: jinshibao
	 * Date: 2020/6/9
	 * Time: 14:35
	 *商品分类
	 */
	public function goodsCategory1()
	{
		//$this->check_shop();

		$pid = I('pid', 0);
		$level = I('level', 1);
		if ($level == 1 && $pid == 0)
		{
			$category = M('goods_category')->where('pid=0 and level=1')->select();
		}
		else
		{
			$category = M('goods_category')->where('pid=' . $pid . ' and level=' . $level)->select();

		}

		if ($category)
		{
			jsonout('获取成功', 0, $category);
		}
		else
		{
			jsonout('获取失败', 1);

		}
	}

	public function goodsCategory($data = null){
        if (!$data)
        {

            $data = I();
        }
        $obj = new Goods($data);

        $res = $obj->category_list($data);
        jsonout($res[0], $res[1], $res[2]);
    }


}