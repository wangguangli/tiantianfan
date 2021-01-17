<?php

namespace Admin\Controller;

use Clas\Goods;
use Think\Page;

class GoodsController extends CommonController
{
	private $_obj;

	public function __construct()
	{
		parent::__construct();
		$this->_obj = new Goods(0, $this->_role, $this->_admin_id);
	}

	public function index()
	{
		$shopList = M("shop")->where("status=1")->field("id,shop_name")->select();
		$getData = I();
		if ($getData['cate_name'])
		{
			$where1['name'] = array('like', '%' . $getData['cate_name'] . '%');
			$cidArr = M('goods_category')->where($where1)->getField('id', true);
			if ($cidArr)
			{
				$where['cat_id'] = array("in", $cidArr);
			}
		}
		if ($getData['name'])
		{
			$where['name'] = array('like', '%' . $getData['name'] . '%');
		}
		if ($getData['cre_time'])
		{
			$getCreTime = get_time_style($getData['cre_time']);
			$where['time'] = array(array("gt", $getCreTime[0]), array("elt", $getCreTime[1]));
		}
		if ($getData['shop_id'])
		{
			$where['shop_id'] = $getData['shop_id'];
		}
		if ($getData['hot_id'])
		{
			$where['hot_id'] = array('eq', $getData['hot_id']);
		}
		$num = 10;
		$where['isdel'] = 0;
		$where['is_on_sale'] = 1;
		$count = M("goods")->where($where)->count();
		$page = new Page($count, $num);
		$show = $page->show();
		$list = M("goods")->where($where)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
		//获取特殊分类
		$specialCategory = M('goods_special_category')->select();
		$this->assign('special', $specialCategory);
		$this->assign("list", $list);
		$this->assign("show", $show);
		$this->assign("shop_list", $shopList);
		$this->display();
	}

	public function off_goods()
	{
		$last_id = I("last_id", 0);
		$page = I("page", 1);
		$paging['page'] = $page;
		$paging['last_id'] = $last_id;
		$num = 10;
		$word = I('word', '', 'trim');
		$type = I('type');

		if ($type == 0)
		{
			if (!empty($word))
			{
				$where['name'] = array('like', '%' . $word . '%');
			}
		}
		else
		{
			if ($type == 1)
			{
				$where1['name'] = array('like', '%' . $word . '%');
				$cat_id = M('goods_category')->where($where1)->getField('id');
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
				if ($cat_id && $cidArr)
				{
					$where['cat_id'] = array("in", $cidArr);
				}
			}
		}

		$where['isdel'] = 0;
		$where['is_on_sale'] = 0;

		$count = M('goods')->where($where)->count();
		$page = new Page($count, 10);
		$show = $page->show();
		$res = M('goods')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($res as $k => $v)
		{

			$res[$k]['shop_name'] = M('shop')->where('id=' . $v['shop_id'])->getField('shop_name');
			if (!$res[$k]['shop_name'])
			{
				$res[$k]['shop_name'] = '平台';
			}
			$res[$k]['cate_name'] = M('goods_category')->where('id=' . $v['cat_id'])->getField('name');
			$res[$k]['hot_name'] = M('goods_special_category')->where('id=' . $v['hot_id'])->getField('name');
		}

		$this->assign("show", $show);
		$this->assign("res", $res);
		$this->display();
	}

	public function del_goods()
	{
		$last_id = I("last_id", 0);
		$page = I("page", 1);
		$paging['page'] = $page;
		$paging['last_id'] = $last_id;
		$num = 10;
		$word = I('word', '', 'trim');
		$type = I('type');

		if ($type == 0)
		{
			if (!empty($word))
			{
				$where['name'] = array('like', '%' . $word . '%');
			}
		}
		else
		{
			if ($type == 1)
			{
				$where1['name'] = array('like', '%' . $word . '%');
				$cat_id = M('goods_category')->where($where1)->getField('id');
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
				if ($cat_id && $cidArr)
				{
					$where['cat_id'] = array("in", $cidArr);
				}
			}
		}

		$where['isdel'] = 1;

		$count = M('goods')->where($where)->count();
		$page = new Page($count, 10);
		$show = $page->show();
		$res = M('goods')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($res as $k => $v)
		{

			$res[$k]['shop_name'] = M('shop')->where('id=' . $v['shop_id'])->getField('shop_name');
			if (!$res[$k]['shop_name'])
			{
				$res[$k]['shop_name'] = '平台';
			}
			$res[$k]['cate_name'] = M('goods_category')->where('id=' . $v['cat_id'])->getField('name');
			$res[$k]['hot_name'] = M('goods_special_category')->where('id=' . $v['hot_id'])->getField('name');
		}
		$this->assign("show", $show);
		$this->assign("res", $res);
		$this->display();
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

	public function add()
	{
		if (IS_POST)
		{
			if (!$data)
			{
				$data = I();
			}
			$data['admin'] = 1;
			$obj = new Goods($data);
			$a = $obj->add($data);
			if ($a === true)
			{
				$this->success("操作成功");
			}
			else
			{
				$this->error($a);
			}
		}
		else
		{
			$goods_img = "/Public/images/img_place.png";    // 默认占位图
			$goods_img2 = "/Public/images/img_place.png";    // 默认占位图

			$id = I('id', 0, 'intval');

			if ($id)
			{
				$goods = M('goods')->find($id);
				if ($goods["images"])
				{
					$goods["images"] = json_decode($goods["images"], true);
				}
				if ($goods['thumb'])
				{
					$goods_img = $goods['thumb'];
				}
				if ($goods['video'])
				{
					$goods_video = $goods['video'];
				}
				$goods['service_show'] = json_decode($goods['service_show'], true);

				if ($goods['tags'] == '新品')
				{
					$goods['tags_type'] = 0;
				}
				elseif ($goods['tags'] == '热销')
				{
					$goods['tags_type'] = 1;
				}
				elseif ($goods['tags'] == '优质')
				{
					$goods['tags_type'] = 2;
				}

				// 找出第三级分类的全部同级 和 它的上级及全部同级，和 它的第一级
				if ($goods["cat_id"])
				{
					$cat3Info = M("goods_category")->where("id=" . $goods["cat_id"])->find();
					$cat3 = M("goods_category")->where("pid=" . $cat3Info["pid"])->select();    // 第三级同级
				}
				if ($cat3Info["pid"])
				{
					// 第二级及全部同级
					$cat2Info = M("goods_category")->where("id=" . $cat3Info["pid"])->find();
					$cat2 = M("goods_category")->where("pid=" . $cat2Info["pid"])->select();
				}
				$catinfo["c3"] = $goods["cat_id"];
				$catinfo["c2"] = $cat3Info["pid"];
				$catinfo["c1"] = $cat2Info["pid"];
				$catinfo["c3list"] = $cat3;
				$catinfo["c2list"] = $cat2;
				$spec_shop_id = $goods["shop_id"];
			}
			else
			{
				$goods = array();
				$spec_shop_id = 0;
			}
			$cat_list = M('goods_category')->where("pid = 0")->select(); // 已经改成联动菜单
			$this->assign("cat_list", $cat_list);
			$rule_list = M('express_rule')->where("id=parent_id")->select();
			$shop = M('shop')->where("status=1")->select();
			$this->assign("shop", $shop);
			$this->assign("rule_list", $rule_list);
			$this->assign("catinfo", $catinfo);

			$type_id = $goods["type_id"];
			$type_id = $type_id ? $type_id : 0;

			$goods["content"] = htmlspecialchars_decode($goods["content"]);

			$feelist = M("config_shop_fee")->order("id asc")->select();

			// 库存重新计算：只计算规格数量
			$goods["store_count"] = M("goods_spec_price")->where("goods_id=" . $id)->sum("store_count");

			// 规格类型
			$type_list = M("goods_type")->order("id asc")->select();

			// 团购参数设置
			$group = M("goods_group")->where("goods_id=" . $id)->find();
			// 秒杀参数设置
			$where['goods_id'] = $id;
			$where['type'] = 0;
			$hour = M("goods_hour")->where($where)->find();
			if ($hour)
			{
				$hour['start_time'] = date("Y-m-d H:i:s", $hour['start_time']);
				$hour['end_time'] = date("Y-m-d H:i:s", $hour['end_time']);
			}
			// 限时购参数设置
			$where['goods_id'] = $id;
			$where['type'] = 1;
			$time = M("goods_hour")->where($where)->find();
			if ($time)
			{
				$time['start_time'] = date("Y-m-d H:i:s", $time['start_time']);
				$time['end_time'] = date("Y-m-d H:i:s", $time['end_time']);
			}

			// 三级分类列表
			$cate3list = M("goods_category")->where("level=3")->order("id asc")->select();
			// 已关联的附属分类
			$extra_cate_list = M("goods_category_extra")->where("goods_id=" . $id)->select();
			foreach ($extra_cate_list as $ke => &$ve)
			{
				$cateName = M("goods_category")->where("id=" . $ve['cate_id'])->getField("name");
				$ve['name'] = $cateName;
			}

			// 查找所有自提点信息
			$picks = M('goods_picksite')->where('is_del=0')->select();
			// 已选的自提点
			$pickHave = explode(",", $goods['picksite']);
			$size = get_config('video');    // 商品视频上传最大值

			// 规格spec列表 @zd.2020.07.28
			if ($spec_shop_id > 0)
			{
				$where_spec['shop_id'] = array("in", array(0, $spec_shop_id));
			}
			else
			{
				$where_spec['shop_id'] = 0;
			}
			$spec_list_have = M("goods_spec")->order("`order` asc, id asc")->where($where_spec)->select();
			$spec_list_have_ids = M("goods_spec")->order("`order` asc, id asc")->where($where_spec)->getField("id", true);

			// 已选择的规格名和规格值
			// 最终输出：已选的规格名（分3个），已选的规格值（分3个数组 + 3个逗号的）
			// 规格列表（全部列表值），每一个选择的规格名对应的所有规格值
			$spec_list_select = M("goods_spec_price")->where("goods_id=" . $id)->select();
			$spec_item = array();
			foreach ($spec_list_select as $key => $value)
			{
				$v_key = explode("_", $value['key']);
				$spec_item = array_merge($spec_item, $v_key);
				$spec_item = array_unique($spec_item);
			}
			if ($spec_item)
			{
				$where_sp_item['id'] = array("in", $spec_item);
				$spec_ids = M("goods_spec_item")->where($where_sp_item)->select();
			}
			$sku_spec_1 = 0;            // 规格名（1）
			$sku_spec_2 = 0;
			$sku_spec_3 = 0;
			$sku_spec_list_1 = 0;       // 规格名（1）下的所有规格值（1）列表
			$sku_spec_list_2 = 0;
			$sku_spec_list_3 = 0;
			$sku_spec_item_1 = '';      // 规格名（1）选中的规格值（1）列表，逗号分隔
			$sku_spec_item_2 = '';
			$sku_spec_item_3 = '';
			$sku_spec_item_a_1 = array();// 规格名（1）选中的规格值（1）列表，数组
			$sku_spec_item_a_2 = array();
			$sku_spec_item_a_3 = array();
			$add_num = 1;

			foreach ($spec_ids as $key2 => $value2)
			{
				if (!$sku_spec_1 || $sku_spec_1 == $value2['spec_id'] )
				{
					$sku_spec_1 = $value2['spec_id'];
					$sku_spec_item_a_1[] = $value2['id'];
					if (!$sku_spec_list_1)
					{
						$sku_spec_list_1 = M("goods_spec_item")->where("spec_id=".$value2['spec_id'])->select();
					}
					$add_num = 2;
				}
				if ( (!$sku_spec_2 || $sku_spec_2 == $value2['spec_id'] ) && $sku_spec_1 && $sku_spec_1 != $value2['spec_id'])
				{
					$sku_spec_2 = $value2['spec_id'];
					$sku_spec_item_a_2[] = $value2['id'];
					if (!$sku_spec_list_2)
					{
						$sku_spec_list_2 = M("goods_spec_item")->where("spec_id=".$value2['spec_id'])->select();
					}
					$add_num = 3;
				}
				if ( (!$sku_spec_3 || $sku_spec_3 == $value2['spec_id'] ) && $sku_spec_1 && $sku_spec_1 != $value2['spec_id'] && $sku_spec_2 && $sku_spec_2 != $value2['spec_id'])
				{
					$sku_spec_3 = $value2['spec_id'];
					$sku_spec_item_a_3[] = $value2['id'];
					if (!$sku_spec_list_3)
					{
						$sku_spec_list_3 = M("goods_spec_item")->where("spec_id=".$value2['spec_id'])->select();
					}
					$add_num = 4;
				}
			}
			$sku_spec_item_1 = implode(",", $sku_spec_item_a_1);
			$sku_spec_item_2 = implode(",", $sku_spec_item_a_2);
			$sku_spec_item_3 = implode(",", $sku_spec_item_a_3);

			$this->assign("goods_img", $goods_img);
			$this->assign("goods_img2", $goods_img2);
			$this->assign("goods_video", $goods_video);
			$this->assign("goods", $goods);
			$this->assign("goods_id", $id);
			$this->assign("type_id", $type_id);
			$this->assign("show_list", show_list());
			$this->assign("feelist", $feelist);
			$this->assign("type_list", $type_list);
			$this->assign("group", $group);
			$this->assign("hour", $hour);
			$this->assign("time", $time);
			$this->assign("cate3list", $cate3list);
			$this->assign("extra_cate_list", $extra_cate_list);
			$this->assign("picks", $picks);
			$this->assign("pickHave", $pickHave);
			$this->assign("size", $size);

			$this->assign("spec_list_have", $spec_list_have);
			$this->assign("sku_spec_1", $sku_spec_1);
			$this->assign("sku_spec_2", $sku_spec_2);
			$this->assign("sku_spec_3", $sku_spec_3);
			$this->assign("sku_spec_list_1", $sku_spec_list_1);
			$this->assign("sku_spec_list_2", $sku_spec_list_2);
			$this->assign("sku_spec_list_3", $sku_spec_list_3);
			$this->assign("sku_spec_item_1", $sku_spec_item_1);
			$this->assign("sku_spec_item_2", $sku_spec_item_2);
			$this->assign("sku_spec_item_3", $sku_spec_item_3);
			$this->assign("sku_spec_item_a_1", $sku_spec_item_a_1);
			$this->assign("sku_spec_item_a_2", $sku_spec_item_a_2);
			$this->assign("sku_spec_item_a_3", $sku_spec_item_a_3);

			$this->assign("sku_spec_list_j_1", json_encode($sku_spec_list_1));
			$this->assign("sku_spec_list_j_2", json_encode($sku_spec_list_2));
			$this->assign("sku_spec_list_j_3", json_encode($sku_spec_list_3));
			$this->assign("add_num", $add_num);
			$this->assign("shop_id", $spec_shop_id);

			/*echo "<pre>";
			print_r($add_num);
			die();*/

			$this->display();
		}

	}

	public function add_goods_image()
	{
		$result = $this->_obj->addGoodsImage();
		echo $result;
	}

	public function delete_category($data = null)
	{
		if (!$data)
		{
			$data = I();
		}
		$a = $this->_obj->deleteCategory($data);
		if (is_array($a))
		{
			$this->success($a[0]);
		}
		else
		{
			$this->error($a);
		}
	}

	/**
	 * 规格模板
	 */
	function spec()
	{
		$type = I('type_id', 0);
		$this->assign('type_id', $type);
		$this->display();
	}

	public function ajax_spec_select()
	{
		$data = I();
		$result = $this->_obj->goodsSpec($data)[2];

		if ($data['goods_id'])
		{
			$items_id = M('goods_spec_price')->where('goods_id = ' . $data['goods_id'])->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
			$items_ids = explode('_', $items_id);
			$this->assign('items_ids', $items_ids);
		}


		$this->assign('list', $result);

		$this->assign('type_id', $data['type_id']);
		$this->display('ajax_spec_select');


	}

	/**
	 * 动态获取商品规格输入框 根据不同的数据返回不同的输入框
	 */
	public function ajaxGetSpecInput()
	{
		$data = I();
		$str = $this->_obj->getSpecInput($data);
		exit($str);
	}

	public function add_spec()
	{
		if (IS_POST)
		{
			$data = I();

			$ok = $this->_obj->addSpec($data);
			if (is_array($ok))
			{
				jsonout($ok[0], $ok[1], $ok[2]);
			}
			jsonout($ok);
		}

		$t = I('type_id');
		$spec_id = I('spec_id');
		if ($spec_id)
		{
			$spec_items = M('goods_spec_item')->where('spec_id=' . $spec_id)->getField('item', true);
			$goods_spec = M('goods_spec')->find($spec_id);

			$this->assign('spec_items', join(PHP_EOL, $spec_items));
			$this->assign('goods_spec', $goods_spec);
		}
		$this->assign('type_id', $t ? $t : $goods_spec['type_id']);
		$this->display();
	}

	public function update_spec()
	{
		if (IS_POST)
		{
			$data = I();
			$ok = $this->_obj->updateSpec($data);
			if (is_array($ok))
			{
				jsonout($ok[0], $ok[1], $ok[2]);
			}
			jsonout($ok);
		}

		$t = I('type_id');
		$spec_id = I('spec_id');
		if ($spec_id)
		{

			$spec_items = M('goods_spec_item')->where('spec_id=' . $spec_id)->getField('item', true);
			$goods_spec = M('goods_spec')->find($spec_id);
			$this->assign('spec_items', join(PHP_EOL, $spec_items));
			$this->assign('goods_spec', $goods_spec);
		}
		$this->assign('type_id', $t ? $t : $goods_spec['type_id']);
		$this->display();
	}

	public function delete_spec()
	{
		$obj = new Goods(0, $this->_role);
		$ok = $obj->deleteSpec();
		if ($ok)
		{
			$this->error($ok);
		}
		$this->success('ok');
	}

	/**
	 * 删除规格 后台专用
	 */
	public function ajax_delete_spec()
	{
		$obj = new Goods(0, $this->_role);
		$data = I();
		$ok = $obj->AjaxDeleteSpec($data);
		if (!is_array($ok))
		{
			$this->error($ok);
		}
		$this->success('ok');
	}

	public function category_list()
	{
//            $this->assign('list', category_list());

		$goods = new \Clas\Goods();

		$cates = $goods->category_list();

//        dump($cates);
		$this->assign('list', $cates[2]);
		$this->display();
	}

	public function add_category()
	{

		if (IS_POST)
		{
			$result = $this->_obj->editCategory(I());
			if (is_array($result))
			{
				$this->success($result[0], U('category_list'));
			}
			else
			{
				$this->error($result);
			}
		}
		else
		{
			$id = I('id', 0, 'intval');

			if ($id)
			{
				$data['id'] = $id;
				$cates = $this->_obj->category_list($data)[2];
				$this->assign('categorys', $cates);
				$this->assign('pid', $id);
			}

			$this->display();
		}
	}

	public function up_category()
	{

		if (IS_POST)
		{


			$result = $this->_obj->editCategory(I());

			if (is_array($result))
			{
				$this->success($result[0], U('category_list'));
			}
			else
			{
				$this->error($result);
			}
		}
		else
		{
			$id = I('id', 0, 'intval');
			if ($id)
			{
				$data['id'] = $id;
				$cates = $this->_obj->category_list($data)[2][0];

				$this->assign($cates);
				if ($cates['level'] > 0)
				{
					$level = $cates['level'] - 1;
					unset($data);
					$data['level'] = $level;
					$fcates = $this->_obj->category_list($data)[2];
					$this->assign('categorys', $fcates);
				}

			}

			$this->display();
		}
	}

	public function set_sort()
	{

		$page = page('goods', 'hot_id>0', '', 'hot_id asc,sort asc');

		$this->assign('page', $page['show']);
		$this->assign('list', $page['list']);
		$this->assign('category', get_category());
		$this->display();
	}

	public function sortAct()
	{
		$id = I('id', 0, 'intval');
		$val = I('val', 0, 'intval');
		M('goods')->where('id=' . $id)->setField('sort', $val);
	}

	public function is_on_sale()
	{
		$this->_obj->is_on_sale();
		$this->success('ok');
	}

	public function delete()
	{
		$id = I("id", 0);
		$rs = M("goods")->where("id=" . $id)->setField("isdel", 1);
		$rs = M("goods")->where("id=" . $id)->setField("is_on_sale", 0);
		if ($rs !== false)
		{
			$this->success('已删除');
		}
		else
		{
			$this->error('删除失败，请重新操作');
		}
	}

	public function pi_off()
	{
		$pi = I('pi');
		foreach ($pi as $value)
		{
			M('goods')->where('id=' . $value)->setField('is_on_sale', 0);
		}
		echo 1;
	}

	public function pi_on()
	{
		$pi = I('pi');
		foreach ($pi as $value)
		{
			M('goods')->where('id=' . $value)->setField('is_on_sale', 1);
		}
		echo 1;
	}

	public function pi_del()
	{
		$pi = I('pi');
		foreach ($pi as $value)
		{
			M("goods")->where("id=" . $value)->setField("isdel", 1);
			M('goods')->where('id=' . $value)->setField('is_on_sale', 0);
		}
		echo 1;
	}

	public function getCategory()
	{
		$parent_id = I('get.parent_id'); // 商品分类 父id
		$list = M('goods_category')->where("pid = $parent_id")->select();
		foreach ($list as $k => $v)
			$html .= "<option value='{$v['id']}'>{$v['name']}</option>";
		exit($html);
	}

	// 商品规格分类
	public function goods_type()
	{
		$list = M("goods_type")->order("id asc")->select();
		$this->assign("list", $list);
		$this->display();
	}

	// 商品规格分类修改/添加
	public function goods_type_edit()
	{
		$id = I("id", 0);
		if (IS_POST)
		{
			$name = I("name");
			if (!$name)
			{
				$this->error("名称不能为空");
			}
			if ($id)
			{
				$rs = M("goods_type")->where("id=" . $id)->setField("name", $name);
			}
			else
			{
				$data["name"] = $name;
				$rs = M("goods_type")->add($data);
			}
			if ($rs !== false)
			{
				$this->success("操作成功");
			}
			else
			{
				$this->error("操作失败，请重新操作");
			}
			exit();
		}

		if ($id)
		{
			$name = M("goods_type")->where("id=" . $id)->getField("name");
		}
		else
		{
			$name = "";
		}
		$this->assign("id", $id);
		$this->assign("name", $name);
		$this->display();
	}

	// 商品规格分类删除
	public function goods_type_del()
	{
		$id = I("id", 0);
		if (!$id)
		{
			$this->error("参数异常");
		}
		// 判断是否有相应分类下的规格
		$isHave = M("goods_spec")->where("type_id=" . $id)->count();
		if ($isHave > 0)
		{
			$this->error("此分类下有规格，请删除规格后再来操作");
		}

		$rs = M("goods_type")->where("id=" . $id)->delete();
		if ($rs !== false)
		{
			$this->success("删除成功");
		}
		else
		{
			$this->error("删除失败，请重新操作");
		}

	}

	// 运费管理
	public function express()
	{
		$list = M("express_rule")->where("id=parent_id")->select();
		$this->assign("list", $list);
		$this->display();
	}

	// 运费修改
	public function expressEdit()
	{
		$id = I("id", 0);
		if (IS_POST)
		{
			$name = I("name");
			$type = I("type");
			$the_first = I("the_first");
			$first_price = I("first_price");
			$the_second = I("the_second");
			$second_price = I("second_price");
			$city_num = I("city_num");
			$is_default = I("is_default", 0);
			$price = I("price", 0);

			if (!$city_num)
			{
				$this->error("请至少填写一个规则条目");
				exit();
			}

			if ($id)
			{
				M("express_rule")->where("id != " . $id . " and parent_id = " . $id)->delete();
			}

			if ($is_default)
			{
				// 如果此次修改是否默认的值，则先把全部数据重置为非默认
				M("express_rule")->where("id>0")->setField("is_default", 0);
			}

			$m = 0;
			foreach ($type as $key => $value)
			{
				$data = array();
				$data["name"] = $name;
				$data["type"] = $type[$key];
				$data["the_first"] = $the_first[$key];
				$data["first_price"] = $first_price[$key];
				$data["the_second"] = $the_second[$key];
				$data["second_price"] = $second_price[$key];
				$data["area"] = $city_num[$key];
				$data["is_default"] = $is_default;
				$data["parent_id"] = $id;
				if ($m < 1)
				{
					// 第一次执行，为了获取记录ID，后续使用
					if ($id)
					{
                        $data["price"] = $price;
						M("express_rule")->where("id=" . $id)->save($data);
					}
					else
					{
						$id = M("express_rule")->add($data);
						$data2["parent_id"] = $id;
						$data2["is_default"] = $is_default;
						$data2["price"] = $price;
						M("express_rule")->where("id=" . $id)->save($data2);
					}
				}
				else
				{
					M("express_rule")->add($data);
				}
				$m++;
			}
			$this->success("操作完成", U("express"));
			exit();
		}

		$pvc = M("region")->where("level=1")->order("id asc")->select();
		foreach ($pvc as $key => &$value)
		{
			$value["city"] = M("region")->where("parent_id=" . $value["id"])->order("id asc")->select();
		}
		$data = M("express_rule")->where("id=" . $id)->find();
		$list = M("express_rule")->where("parent_id=" . $id)->order("is_default desc,id asc")->select();
		$n = 0;
		foreach ($list as $key => &$value)
		{
			$str1 = "";
			$str2 = "";
			$str3 = "";
			if ($value["type"] == 1)
			{
				$str1 = "首重(克):{$value['the_first']}，首费(元):{$value['first_price']}，续重(克):{$value['the_second']}，续费(元):{$value['second_price']}";
			}
			else
			{
				$str1 = "首件(个):{$value['the_first']}，首费(元):{$value['first_price']}，续件(个):{$value['the_second']}，续费(元):{$value['second_price']}";
			}
			$where_r["id"] = array("in", $value["area"]);
			$str2 = M("region")->where($where_r)->getField("name", true);
			$str2 = implode(",", $str2);
			$str3 .= '<input type="hidden" name="the_first[' . $key . ']" value="' . $value['the_first'] . '">';
			$str3 .= '<input type="hidden" name="first_price[' . $key . ']" value="' . $value['first_price'] . '">';
			$str3 .= '<input type="hidden" name="the_second[' . $key . ']" value="' . $value['the_second'] . '">';
			$str3 .= '<input type="hidden" name="second_price[' . $key . ']" value="' . $value['second_price'] . '">';
			$str3 .= '<input type="hidden" name="type[' . $key . ']" value="' . $value['type'] . '">';
			$str3 .= '<input type="hidden" name="city_num[' . $key . ']" value="' . $value['area'] . '">';

			$value["str1"] = $str1;    // 首重/件xxx
			$value["str2"] = $str2;    // 城市
			$value["str3"] = $str3;    // input_hidden
			$n++;
		}
		$this->assign("list", $list);
		$this->assign("data", $data);
		$this->assign("pvc", $pvc);
		$this->assign("id", $id);
		$this->assign("n", $n);
		$this->display();
	}

	// 运费删除
	public function expressDel()
	{
		$id = I("id", 0);
		if (!$id)
		{
			$this->error("请传入正确的参数");
		}
		$rs = M("express_rule")->where("id=" . $id)->delete();
		if ($rs !== false)
		{
			$this->success("删除成功");
		}
		else
		{
			$this->error("删除失败，请重新操作");
		}

	}

	// 特殊分类
	public function special_category()
	{
		$list = M('goods_special_category')->order($order)->select();
		$this->assign('list', $list);
		$this->display();
	}

	public function special_category_upd()
	{
		if (IS_POST)
		{
			$id = I('id');
			$data = array(
				'name' => I('name'),
				'order_type' => I('order_type'),
				'edittime' => time()
			);
			if ($id)
			{
				M('goods_special_category')->where('id=' . $id)->save($data);
			}
			else
			{
				M('goods_special_category')->add($data);
			}
			$this->success("操作成功", U('Goods/special_category'));
		}
		else
		{

			$id = I('id', 0);
			$item = M("goods_special_category")->where("id=$id")->find();
			$type = order_type();
			$this->assign('type', $type);
			$this->assign('item', $item);
			$this->display('special_category_edit');

		}
	}

	public function special_category_del()
	{
		$id = I('id');
		M('goods_special_category')->where('id=' . $id)->delete();
		$this->success("操作成功", U('Goods/special_category'));
	}

	// 自提点列表
	public function picksite()
	{

		$title = I('title');

		if ($title)
		{
			$where['title'] = array('LIKE', '%' . $title . '%');
		}
		$name = I('name');
		if ($name)
		{
			$where['name'] = array('LIKE', '%' . $name . '%');
		}
		$phone = I('phone');
		if ($phone)
		{
			$where['phone'] = $phone;
		}
//        $address = I('address');
//        if($address){
//            $where['address'] = array('LIKE','%'.$address.'%');
//        }

		$week_day = array('周天', '周一', '周二', '周三', '周四', '周五', '周六');
		$list = M("goods_picksite")->where($where)->where("is_del=0")->order("id desc")->select();
		foreach ($list as $key => &$value)
		{
			if ($value['work_day'])
			{

			}
		}

		$this->assign("list", $list);
		$this->display();
	}

	// 自提点添加/修改
	public function picksiteEdit()
	{
		$id = I("id", 0);
		if (IS_POST)
		{
			$id = I("id", 0);
			$title = I("title", '');
			$name = I("name", '');
			$phone = I("phone", '');
			$province = I("province", 0);
			$city = I("city", 0);
			$district = I("district", 0);
			$address = I("address", '');
			$lat = I("lat", '');
			$lng = I("lng", '');
			$work_day = I("work_day", '');
			$work_time_start = I("work_time_start", '');
			$work_time_end = I("work_time_end", '');
			if (!$title || !$name || !$phone || !$province || !$city || !$district || !$address || !$work_day || !$work_time_start || !$work_time_end)
			{
				$this->error("所有数据均为必填项");
			}

			$work_day = implode(",", $work_day);

			$data['title'] = $title;
			$data['name'] = $name;
			$data['phone'] = $phone;
			$data['province'] = $province;
			$data['city'] = $city;
			$data['district'] = $district;
			$data['address'] = $address;
			$data['work_day'] = $work_day;
			$data['work_time_start'] = $work_time_start;
			$data['work_time_end'] = $work_time_end;
			$data['lat'] = $lat;
			$data['lng'] = $lng;
			$data['cre_time'] = time();
			if ($id)
			{

				$where['picksite_id'] = $id;
				$where['status'] = 0;
				$order = M('order_picksite')->where($where)->select();
				if ($order)
				{
					$this->success("该自提点存在未完成的订单，目前无法删除");
				}


				$rs = M("goods_picksite")->where("id=" . $id)->save($data);
			}
			else
			{
				$rs = M("goods_picksite")->add($data);
			}

			if ($rs !== false)
			{
				$this->success("操作完成", U('picksite'));
			}
			else
			{
				$this->error("操作失败，请重新操作");
			}
			exit();
		}
		$data = M("goods_picksite")->where("is_del=0 and id=" . $id)->find();
		$province = M('region')->where('level=1')->select();
		if ($data['province'])
		{
			$city = M('region')->where('parent_id=' . $data['province'])->select();
			if ($data['city'])
			{
				$district = M('region')->where('parent_id=' . $data['city'])->select();
			}

		}
		if ($data['work_time_start'])
		{
			$work_time_start = $data['work_time_start'];
		}
		else
		{
			$work_time_start = "";
		}
		if ($data['work_time_end'])
		{
			$work_time_end = $data['work_time_end'];
		}
		else
		{
			$work_time_end = "";
		}

		$data['work_day'] = explode(',', $data['work_day']);

		$this->assign("data", $data);
		$this->assign("province", $province);
		$this->assign("city", $city);
		$this->assign("district", $district);
		$this->assign("work_time_start", $work_time_start);
		$this->assign("work_time_end", $work_time_end);
		$this->display();
	}

	// 自提点添加/修改 @zd
	public function picksiteDel()
	{
		$id = I("id", 0);
		if (!$id)
		{
			$this->error("缺少主要参数");
		}
		//先判断改自提点绑定的订单是否都已经完成订单了
		$where['picksite_id'] = $id;
		$where['status'] = 0;
		$order = M('order_picksite')->where($where)->select();
		if ($order)
		{
			$this->success("该自提点存在未完成的订单，目前无法删除");
		}
		$rs = M("goods_picksite")->where("id=" . $id)->setField("is_del", 1);
		if ($rs !== false)
		{
			$this->success("删除成功");
		}
		else
		{
			$this->error("删除失败，请重新操作");
		}

	}

	/*
	 * 搜索规格
	 * @zd
	 * */
	public function searchType()
	{
		$word = I("word", '');
		$ul = "";
		if ($word)
		{
			$where['name'] = array("like", "%" . $word . "%");
			$list = M("goods_type")->where($where)->select();
			if ($list)
			{
				$ul .= "<option value='0'>请选择搜索结果</option>";
				foreach ($list as $key => $value)
				{
					$ul .= "<option value='" . $value['id'] . "'>" . $value['name'] . "</option>";
				}
			}
			else
			{
				$ul .= "<option value='0'>暂无相关规格，请重新搜索</option>";
			}
		}
		else
		{
			$list = M("goods_type")->select();
			if ($list)
			{
				$ul .= "<option value='0'>请选择规格类型</option>";
				foreach ($list as $key => $value)
				{
					$ul .= "<option value='" . $value['id'] . "'>" . $value['name'] . "</option>";
				}
			}
		}
		jsonout("规格列表", 0, $ul);
	}


	//商品设置规格奖励
	public function goods_gauge_reward()
	{

		if ($_POST)
		{
			$id = I('id');
			$data = I('');
			$goods_spec = M('goods_spec_price')->where('goods_id=' . $id)->field('id,key_name,price,purchase_price')->select();
			$time = time();
			$date = date('Y-m-d H:i:s', time());

			foreach ($goods_spec as $kk => $vv)
			{
				$goods_spec_award = M('goods_award')->where('goods_id=' . $id . ' and goods_spec_price_id =' . $vv['id'])->find();

				$da = array();

				if ($data['award_status'] == 'on')
				{
					$da['award_status'] = 1;
				}
				else
				{
					$da['award_status'] = 0;
				}
				$da['award_mode'] = $data['award_mode'];
				$da['self_money'] = $data['self_money'][$kk];
				$da['self_point'] = $data['self_point'][$kk];
				$da['first_money'] = $data['first_money'][$kk];
				$da['first_point'] = $data['first_point'][$kk];
				$da['second_money'] = $data['second_money'][$kk];
				$da['second_point'] = $data['second_point'][$kk];
				$da['third_money'] = $data['third_money'][$kk];
				$da['third_point'] = $data['third_point'][$kk];
				$da['self_money_repeat'] = $data['self_money_repeat'][$kk];
				$da['self_point_repeat'] = $data['self_point_repeat'][$kk];
				$da['first_money_repeat'] = $data['first_money_repeat'][$kk];
				$da['first_point_repeat'] = $data['first_point_repeat'][$kk];
				$da['second_money_repeat'] = $data['second_money_repeat'][$kk];
				$da['second_point_repeat'] = $data['second_point_repeat'][$kk];
				$da['third_money_repeat'] = $data['third_money_repeat'][$kk];
				$da['third_point_repeat'] = $data['third_point_repeat'][$kk];
				$da['up_time'] = $time;
				$da['up_date'] = $date;
				if ($goods_spec_award)
				{
					$res = M('goods_award')->where('goods_id=' . $id . ' and goods_spec_price_id =' . $vv['id'])->save($da);
				}
				else
				{
					$da['goods_id'] = $id;
					$da['goods_spec_price_id'] = $vv['id'];
					$res = M('goods_award')->add($da);
				}

			}
			if ($res)
			{
				$this->success('添加成功');
			}
			else
			{
				$this->error('添加失败');
			}
		}
		else
		{
			$id = I('id');
			$goods = M('goods')->where('id=' . $id)->field('id,name')->find();
			$goods_spec = M('goods_spec_price')->where('goods_id=' . $id)->field('id,key_name,price,purchase_price')->select();
			foreach ($goods_spec as $k => $v)
			{

				$goods_spec[$k]['award'] = M('goods_award')->where('goods_id=' . $id . ' and goods_spec_price_id =' . $v['id'])->find();

			}

			$this->assign('goods_spec', $goods_spec);
			$this->assign('goods', $goods);
			$this->display();
		}
	}

	// 彻底删除商品 -- 单个商品
	// @zd
	public function deleteThorough()
	{
		$id = I("id", 0);
		if (!$id)
		{
			$this->error('参数异常');
		}
		$rs = M("goods")->where("id=" . $id)->delete();
		M("goods_spec_price")->where("goods_id=" . $id)->delete();
		M("goods_group")->where("goods_id=" . $id)->delete();
		M("goods_hour")->where("goods_id=" . $id)->delete();
		M("goods_comment")->where("goods_id=" . $id)->delete();
		M("goods_category_extra")->where("goods_id=" . $id)->delete();
		if ($rs !== false)
		{
			$this->success('删除成功');
		}
		else
		{
			$this->error('删除失败，请重新操作');
		}
	}

	/*
	 * 批量删除，也可删除一个
	 *      批量删除参数：
	 *      ids: 如果是批量 则是数组，如果是单个 则是一个数字
	 *      type: 例 5_1，横线前面数字 代表删除的哪些内容，1=用户，2商家，3代理，4订单，5商品，6商品三级分类，7日志
	 *                   横线后面数字 代理删除类型，0逻辑删除，1彻底删除
	 *      isajax: 是否ajax操作，1是，0否，默认1
	 * @zd
	 * */
	public function deleteMuch()
	{
		$ids = I("ids", '');
		$type = I("type", '');
		$isajax = I("isajax", 1);
		if (!$ids)
		{
			if ($isajax == 1)
			{
				jsonout("请至少选择一个");
			}
			else
			{
				$this->error('请至少选择一个');
			}
		}

		$startPos = stripos($type, "_");
		if (!$type || $startPos === false)
		{
			if ($isajax == 1)
			{
				jsonout("请传入正确的操作类型");
			}
			else
			{
				$this->error('请传入正确的操作类型');
			}
		}
		$typeArr = explode("_", $type);
		if (!is_numeric($typeArr[0]) || !is_numeric($typeArr[1]) || $typeArr[0] <= 0 || $typeArr[1] < 0)
		{
			if ($isajax == 1)
			{
				jsonout("请传入正确的操作类型");
			}
			else
			{
				$this->error('请传入正确的操作类型');
			}
		}
		if ($typeArr[0] == 1)
		{
			// 删除用户
			$res = $this->deleteMuchUser($ids, $typeArr[1]);
		}
		elseif ($typeArr[0] == 2)
		{
			// 删除商家
			$res = $this->deleteMuchShop($ids, $typeArr[1]);
		}
		elseif ($typeArr[0] == 3)
		{
			// 删除代理
			$res = $this->deleteMuchAgent($ids, $typeArr[1]);
		}
		elseif ($typeArr[0] == 4)
		{
			// 删除订单
			$res = $this->deleteMuchOrder($ids, $typeArr[1]);
		}
		elseif ($typeArr[0] == 5)
		{
			// 删除商品
			$res = $this->deleteMuchGoods($ids, $typeArr[1]);
		}
		elseif ($typeArr[0] == 6)
		{
			// 删除商品三级分类
			$res = $this->deleteMuchCategory($ids, $typeArr[1]);
		}
		elseif ($typeArr[0] == 7)
		{
			// 删除日志
			$res = $this->deleteMuchLog($ids, $typeArr[1]);
		}
		else
		{
			$res = array(1, '暂无相关操作');
		}
		if ($isajax == 1)
		{
			jsonout($res[1], $res[0]);
		}
		else
		{
			if ($res[0] == 0)
			{
				$this->success($res[1]);
			}
			else
			{
				$this->error($res[1]);
			}
		}
	}

	// 删除用户
	// $ids  用户ID
	// $type  0逻辑删除，1彻底删除
	// @zd
	public function deleteMuchUser($ids = '', $type = 0)
	{
		if (!$ids || $type < 0)
		{
			return array(1, "参数错误");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		if ($type == 1)
		{
			// 彻底删除
			$where['id'] = array("in", $ids);
			$list = M("user")->where($where)->select();
			if ($list)
			{
				// 先写入删除日志表
				$data = array();
				foreach ($list as $key => $value)
				{
					$data[$key]['admin_id'] = session('admin_id');
					$data[$key]['cre_time'] = time();
					$data[$key]['ip'] = get_client_ip();
					$data[$key]['type'] = 1;
					$data[$key]['del_id'] = $value['id'];
					$data[$key]['detail_info'] = json_encode($value);
				}
				if ($data)
				{
					M("del_log")->addAll($data);
				}
			}
			$res = M("user")->where($where)->delete();
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
		else
		{
			// 逻辑删除
			$where['id'] = array("in", $ids);
			$res = M("user")->where($where)->setField("is_del", 1);
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
	}

	// 删除商家，只有彻底删除，没有逻辑删除，
	// 并且同时更改用户状态和删除相应的商品
	// $ids  商家ID
	// $type  0逻辑删除，1彻底删除
	// @zd
	public function deleteMuchShop($ids = '', $type = 0)
	{
		if (!$ids || $type < 0)
		{
			return array(1, "参数错误");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		if ($type >= 0)
		{
			// 彻底删除
			$where['id'] = array("in", $ids);
			$list = M("shop")->where($where)->select();
			if ($list)
			{
				// 先写入删除日志表
				$data = array();
				foreach ($list as $key => $value)
				{
					$data[$key]['admin_id'] = session('admin_id');
					$data[$key]['cre_time'] = time();
					$data[$key]['ip'] = get_client_ip();
					$data[$key]['type'] = 2;
					$data[$key]['del_id'] = $value['id'];
					$data[$key]['detail_info'] = json_encode($value);

					// 删除商家关联的相关商品
					M("user")->where("id=" . $value["user_id"])->setField("is_shop", 0);
					$goodsList = M("goods")->where("shop_id=" . $value["id"])->select();
					foreach ($goodsList as $key2 => $value2)
					{
						$ids[] = $value2['id'];
						$subInfo = M("goods_spec_price")->where("goods_id=" . $value2['id'])->select();
						$data2[$key]['admin_id'] = session('admin_id');
						$data2[$key]['cre_time'] = time();
						$data2[$key]['ip'] = get_client_ip();
						$data2[$key]['type'] = 5;
						$data2[$key]['del_id'] = $value2['id'];
						$data2[$key]['detail_info'] = json_encode($value2);
						$data2[$key]['detail_info_sub'] = json_encode($subInfo);
					}
					if ($data2)
					{
						M("del_log")->addAll($data2);
					}
					M("goods")->where("shop_id=" . $value["id"])->delete();
					if ($ids)
					{
						$where_og['goods_id'] = array("in", $ids);
						M("goods_spec_price")->where($where_og)->delete();
					}
					// 商家商品---end
				}
				if ($data)
				{
					M("del_log")->addAll($data);
				}
			}
			$res = M("shop")->where($where)->delete();
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
	}

	// 删除代理
	// $ids  代理ID
	// $type  0逻辑删除，1彻底删除
	// @zd
	public function deleteMuchAgent($ids = '', $type = 0)
	{
		if (!$ids || $type < 0)
		{
			return array(1, "参数错误");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		if ($type == 1)
		{
			// 彻底删除
			$where['id'] = array("in", $ids);
			$list = M("agent")->where($where)->select();
			if ($list)
			{
				// 先写入删除日志表
				$data = array();
				foreach ($list as $key => $value)
				{
					$rs3 = M("agent")->where("id !=" . $value['id'] . " and user_id=" . $value['user_id'])->count();
					if ($rs3 < 1)
					{
						M('user')->where('id=' . $value['user_id'])->setField("is_agent", 0);
					}
					$data[$key]['admin_id'] = session('admin_id');
					$data[$key]['cre_time'] = time();
					$data[$key]['ip'] = get_client_ip();
					$data[$key]['type'] = 3;
					$data[$key]['del_id'] = $value['id'];
					$data[$key]['detail_info'] = json_encode($value);
				}
				if ($data)
				{
					M("del_log")->addAll($data);
				}
			}
			$res = M("agent")->where($where)->delete();
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
		else
		{
			// 逻辑删除
			$where['id'] = array("in", $ids);
			$res = M("agent")->where($where)->setField("isdel", 1);
			$list = M("agent")->where($where)->select();
			if ($list)
			{
				foreach ($list as $key => $value)
				{
					$rs3 = M("agent")->where("id !=" . $value['id'] . " and user_id=" . $value['user_id'])->count();
					if ($rs3 < 1)
					{
						M('user')->where('id=' . $value['user_id'])->setField("is_agent", 0);
					}
				}
			}
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
	}

	// 删除订单
	// $ids  订单ID
	// $type  0逻辑删除，1彻底删除
	// @zd
	public function deleteMuchOrder($ids = '', $type = 0)
	{
		if (!$ids || $type < 0)
		{
			return array(1, "参数错误");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		if ($type == 1)
		{
			// 彻底删除
			$where['id'] = array("in", $ids);
			$list = M("order")->where($where)->select();
			if ($list)
			{
				// 先写入删除日志表
				$data = array();
				foreach ($list as $key => $value)
				{
					$subInfo = M("order_goods")->where("order_id=" . $value['id'])->select();
					$data[$key]['admin_id'] = session('admin_id');
					$data[$key]['cre_time'] = time();
					$data[$key]['ip'] = get_client_ip();
					$data[$key]['type'] = 4;
					$data[$key]['del_id'] = $value['id'];
					$data[$key]['detail_info'] = json_encode($value);
					$data[$key]['detail_info_sub'] = json_encode($subInfo);
				}
				if ($data)
				{
					M("del_log")->addAll($data);
				}
			}
			$res = M("order")->where($where)->delete();
			$where_og['order_id'] = array("in", $ids);
			M("order_goods")->where($where_og)->delete();   // 删除子订单
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
		else
		{
			// 逻辑删除
			$where['id'] = array("in", $ids);
			$res = M("order")->where($where)->setField("isdel", 1);
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
	}

	// 删除商品
	// $ids  商品ID
	// $type  0逻辑删除，1彻底删除
	// @zd
	public function deleteMuchGoods($ids = '', $type = 0)
	{
		if (!$ids || $type < 0)
		{
			return array(1, "参数错误");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		if ($type == 1)
		{
			// 彻底删除
			$where['id'] = array("in", $ids);
			$list = M("goods")->where($where)->select();
			if ($list)
			{
				// 先写入删除日志表
				$data = array();
				foreach ($list as $key => $value)
				{
					$subInfo = M("goods_spec_price")->where("goods_id=" . $value['id'])->select();
					$data[$key]['admin_id'] = session('admin_id');
					$data[$key]['cre_time'] = time();
					$data[$key]['ip'] = get_client_ip();
					$data[$key]['type'] = 5;
					$data[$key]['del_id'] = $value['id'];
					$data[$key]['detail_info'] = json_encode($value);
					$data[$key]['detail_info_sub'] = json_encode($subInfo);
				}
				if ($data)
				{
					M("del_log")->addAll($data);
				}
			}
			$res = M("goods")->where($where)->delete();
			$where_og['goods_id'] = array("in", $ids);
			M("goods_spec_price")->where($where_og)->delete();   // 删除子订单
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
		else
		{
			// 逻辑删除
			$where['id'] = array("in", $ids);
			$res = M("goods")->where($where)->setField("isdel", 1);
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
	}


	// 删除分类
	// $ids  分类ID
	// $type  0非强制删除，1强制删除
	// @zd
	public function deleteMuchCategory($ids = '', $type = 0)
	{
		if (!$ids || $type < 0)
		{
			return array(1, "参数错误");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		if ($type == 1)
		{
			// 1强制删除
			$where['id'] = array("in", $ids);
			$list = M("goods_category")->where($where)->select();
			if ($list)
			{
				// 先写入删除日志表
				$data = array();
				$subArr = array();
				foreach ($list as $key => $value)
				{
					$subInfo[0] = M("goods_category")->where("pid=" . $value['id'])->select();
					$subId = M("goods_category")->where("pid=" . $value['id'])->getField("id", true);
					if ($subId)
					{
						if ($subArr)
						{
							$subArr = array_merge($subArr, $subId);
						}
						else
						{
							$subArr = $subId;
						}
						$where2['pid'] = array("in", $subId);
						$subInfo[1] = M("goods_category")->where($where2)->select();
						$subId2 = M("goods_category")->where($where2)->getField("id", true);
						if ($subId2)
						{
							if ($subArr)
							{
								$subArr = array_merge($subArr, $subId2);
							}
							else
							{
								$subArr = $subId2;
							}
						}
					}
					$data[$key]['admin_id'] = session('admin_id');
					$data[$key]['cre_time'] = time();
					$data[$key]['ip'] = get_client_ip();
					$data[$key]['type'] = 6;
					$data[$key]['del_id'] = $value['id'];
					$data[$key]['detail_info'] = json_encode($value);
					$data[$key]['detail_info_sub'] = json_encode($subInfo);
				}
				if ($data)
				{
					M("del_log")->addAll($data);
				}
			}
			$res = M("goods_category")->where($where)->delete();
			if ($subArr)
			{
				$where_og['id'] = array("in", $subArr);
				M("goods_category")->where($where_og)->delete();   // 删除子类
			}
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
		else
		{
			// 0非强制删除
			$allId = array();
			$where['id'] = array("in", $ids);
			$idOne = M("goods_category")->where($where)->getField("id", true);
			if ($idOne)
			{
				$where2['pid'] = array("in", $idOne);
				$idTwo = M("goods_category")->where($where2)->getField("id", true);
				if ($idTwo)
				{
					$where2['pid'] = array("in", $idTwo);
					$idThree = M("goods_category")->where($where2)->getField("id", true);
					$allId = array_merge($idOne, $idTwo);
					if ($idThree)
					{
						$allId = array_merge($allId, $idThree);
					}
				}
				else
				{
					$allId = $idOne;
				}
			}
			if ($allId)
			{
				$where3['cat_id'] = array("in", $allId);
				$count = M('goods')->where($where3)->count();
				if ($count > 0)
				{
					return array(1, "分类下有商品，不可删除");
				}
				else
				{
					$res = M("goods_category")->where($where3)->delete();
					if ($res)
					{
						return array(0, "操作成功");
					}
					else
					{
						return array(1, "操作失败");
					}
				}
			}
			else
			{
				return array(1, "没有相应分类，请刷新后操作");
			}
		}
	}


	// 删除日志
	// $ids  日志ID
	// $type  0逻辑删除，1彻底删除   都是一样的删除
	// @zd
	public function deleteMuchLog($ids = '', $type = 0)
	{
		if (!$ids || $type < 0)
		{
			return array(1, "参数错误");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		if ($type >= 0)
		{
			// 彻底删除
			$where['id'] = array("in", $ids);
			$list = M("account_log")->where($where)->select();
			if ($list)
			{
				// 先写入删除日志表
				$data = array();
				foreach ($list as $key => $value)
				{
					$data[$key]['admin_id'] = session('admin_id');
					$data[$key]['cre_time'] = time();
					$data[$key]['ip'] = get_client_ip();
					$data[$key]['type'] = 7;
					$data[$key]['del_id'] = $value['id'];
					$data[$key]['detail_info'] = json_encode($value);
				}
				if ($data)
				{
					M("del_log")->addAll($data);
				}
			}
			$res = M("account_log")->where($where)->delete();
			if ($res)
			{
				return array(0, "操作成功");
			}
			else
			{
				return array(1, "操作失败");
			}
		}
		else
		{
			return array(1, "参数错误");
		}
	}


	/*
	 * 批量恢复，也可恢复一个，只能恢复逻辑删除的数据
	 *      批量恢复参数：
	 *      ids: 如果是批量 则是数组，如果是单个 则是一个数字
	 *      type: 代表恢复的哪些内容，1=用户，3代理，4订单，5商品  （6商品三级分类，7日志，2商家  这个没有逻辑删除）
	 *      isajax: 是否ajax操作，1是，0否，默认1
	 * @zd
	 * */
	public function recoverMuch()
	{
		$ids = I("ids", '');
		$type = I("type", '');
		$isajax = I("isajax", 1);
		if (!$ids)
		{
			if ($isajax == 1)
			{
				jsonout("请至少选择一个");
			}
			else
			{
				$this->error('请至少选择一个');
			}
		}

		if (!$type)
		{
			if ($isajax == 1)
			{
				jsonout("请传入正确的操作类型");
			}
			else
			{
				$this->error('请传入正确的操作类型');
			}
		}
		if ($type == 1)
		{
			// 删除用户
			$res = $this->recoverMuchUser($ids);
		}
		elseif ($type == 3)
		{
			// 删除代理
			$res = $this->recoverMuchAgent($ids);
		}
		elseif ($type == 4)
		{
			// 删除订单
			$res = $this->recoverMuchOrder($ids);
		}
		elseif ($type == 5)
		{
			// 删除商家
			$res = $this->recoverMuchGoods($ids);
		}
		else
		{
			$res = array(1, '暂无相关操作');
		}
		if ($isajax == 1)
		{
			jsonout($res[1], $res[0]);
		}
		else
		{
			if ($res[0] == 0)
			{
				$this->success($res[1]);
			}
			else
			{
				$this->error($res[1]);
			}
		}
	}

	// 恢复逻辑删除的用户
	// $ids  用户ID
	// @zd
	public function recoverMuchUser($ids = '')
	{
		if (!$ids)
		{
			return array(1, "请至少选择一项");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		// 恢复逻辑删除
		$where['id'] = array("in", $ids);
		$res = M("user")->where($where)->setField("is_del", 0);
		if ($res)
		{
			return array(0, "操作成功");
		}
		else
		{
			return array(1, "操作失败");
		}
	}

	// 恢复逻辑删除的代理
	// $ids  代理ID
	// @zd
	public function recoverMuchAgent($ids = '')
	{
		if (!$ids)
		{
			return array(1, "请至少选择一项");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		// 恢复逻辑删除
		$where['id'] = array("in", $ids);
		$res = M("agent")->where($where)->setField("isdel", 0);
		$list = M("agent")->where($where)->select();
		foreach ($list as $key => $value)
		{
			M('user')->where('id=' . $value['user_id'])->setField("is_agent", 1);
		}
		if ($res)
		{
			return array(0, "操作成功");
		}
		else
		{
			return array(1, "操作失败");
		}
	}

	// 恢复逻辑删除的订单
	// $ids  订单ID
	// @zd
	public function recoverMuchOrder($ids = '')
	{
		if (!$ids)
		{
			return array(1, "请至少选择一项");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		// 恢复逻辑删除
		$where['id'] = array("in", $ids);
		$res = M("order")->where($where)->setField("isdel", 0);
		if ($res)
		{
			return array(0, "操作成功");
		}
		else
		{
			return array(1, "操作失败");
		}
	}

	// 恢复逻辑删除的商品
	// $ids  商品ID
	// @zd
	public function recoverMuchGoods($ids = '')
	{
		if (!$ids)
		{
			return array(1, "请至少选择一项");
		}
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		// 恢复逻辑删除
		$where['id'] = array("in", $ids);
		$res = M("goods")->where($where)->setField("isdel", 0);
		if ($res)
		{
			return array(0, "操作成功");
		}
		else
		{
			return array(1, "操作失败");
		}
	}


	/**
	 * Title： 导出表格
	 * Note：
	 * User： zd
	 * Date： 2020/6/20 11:20
	 *
	 */
	public function export_excel()
	{
		$name = I("name", '');
		$cate_name = I("cate_name", '');
		$cre_time = I("cre_time", '');
		$shop_id = I("shop_id", '');
		$hot_id = I("hot_id", '');
		if ($name)
		{
			$where['name'] = array('like', '%' . $name . '%');
		}
		if ($cate_name)
		{
			$where1['name'] = array('like', '%' . $cate_name . '%');
			$cidArr = M('goods_category')->where($where1)->getField('id', true);
			if ($cidArr)
			{
				$where['cat_id'] = array("in", $cidArr);
			}
		}
		if ($cre_time)
		{
			$getCreTime = get_time_style($cre_time);
			$where['time'] = array(array("gt", $getCreTime[0]), array("elt", $getCreTime[1]));
		}
		if ($shop_id)
		{
			$where['shop_id'] = $shop_id;
		}
		if ($hot_id)
		{
			$where['shop_id'] = $hot_id;
		}
		$where['is_on_sale'] = 1;
		$where['isdel'] = 0;

		$tempID = 5;        // 导出模板ID
		$res = exportExcel($where, 'id desc', 0, $tempID, '', 0);
		if ($res['err'] == 0)
		{
			jsonout('数据导出', 0, $res['result']);
		}
		else
		{
			jsonout($res['msg'], 1);
		}
	}


	/**
	 * Title：新的规格处理
	 * Note：
	 * User： zd
	 * Date： 2020/7/29 12:14
	 *
	 * add_num      添加编号（1，2，3）
	 * spec_id      规格名ID
	 * shop_id      商家ID
	 * goods_id     商品ID
	 *
	 */
	public function new_spec_list()
	{
		$list = array();
		$itemlist = array();
		$add_num = I("add_num", 1);
		$shop_id = I("shop_id", 0);
		$goods_id = I("goods_id", 0);
		$spec_id = I("spec_id", 0);
		$spec_name = I("spec_name", '', 'trim');
		$spec_item_id = I("spec_item_id", '');
		$spec_item_name = I("spec_item_name", '');

		if ($shop_id > 0)
		{
			$where_spec['shop_id'] = $shop_id;
		}
		else
		{
			$where_spec['shop_id'] = 0;
		}
		$list = M("goods_spec")->order("`order` asc, id asc")->where($where_spec)->select();

		// 当$spec_id >0 时，表示原来有此规格名，直接取对应的规格值
		// 当$spec_id =0 时，表示没有此规格，那么先把此值增加到规格名列表中，然后输出相应的数据
		if ($spec_id > 0)
		{
			$spec_name = M("goods_spec")->where("id=" . $spec_id)->getField("name");
			$where['spec_id'] = $spec_id;
			$itemlist = M("goods_spec_item")->order("id asc")->where($where)->select();
		}
		else
		{
			if ($spec_name && strrpos($spec_name, '请选择') === false)
			{
				$where_spec['name'] = $spec_name;
				$isHave = M("goods_spec")->where($where_spec)->select();
				if (!$isHave)
				{
					$data_add['name'] = $spec_name;
					$data_add['order'] = 50;
					$data_add['shop_id'] = $shop_id;
					$spec_id = M("goods_spec")->add($data_add);
				}
				unset($where_spec['name']);
			}
			$list = M("goods_spec")->order("`order` asc, id asc")->where($where_spec)->select();
		}
		$result['list'] = $list;                        // 规格名列表
		$result['spec_id'] = $spec_id;                  // 规格名ID
		$result['spec_name'] = $spec_name;              // 规格名称
		$result['itemlist'] = $itemlist;                // 规格值列表
		$result['spec_item_id'] = $spec_item_id;        // 规格值ID
		$result['spec_item_name'] = $spec_item_name;    // 规格值名称
		jsonout("规格数据", 0, $result);
	}

	/**
	 * Title：新的规格处理
	 * Note：
	 * User： zd
	 * Date： 2020/7/29 12:14
	 *
	 * add_num      添加编号（1，2，3）
	 * spec_id      规格名ID
	 * shop_id      商家ID
	 * goods_id     商品ID
	 *
	 */
	public function new_spec_item_list()
	{
		$list = array();
		$itemlist = array();
		$spec_id = I("spec_id", 0);
		$spec_item_id = I("spec_item_id", 0);
		$spec_item_name = I("spec_item_name", '', 'trim');
		$filter_id = I("filter_id", '');

		if (!$spec_item_id && $spec_item_name && strrpos($spec_item_name, '请选择') === false)
		{
			if ($filter_id)
			{
				$where_gs['id'] = array("not in", $filter_id);
			}
			$where_gs['spec_id'] = $spec_id;
			$itemlist = M("goods_spec_item")->where($where_gs)->select();

			$data_add['item'] = $spec_item_name;
			$data_add['spec_id'] = $spec_id;
			$isHave = M("goods_spec_item")->where($data_add)->find();
			if ($isHave)
			{
				jsonout("已经添加了相同的规格值");
			}
			$spec_item_id = M("goods_spec_item")->add($data_add);
			$newItem['id'] = $spec_item_id;
			$newItem['spec_id'] = $spec_id;
			$newItem['item'] = $spec_item_name;
			$itemlist[] = $newItem;
		}
		$list = M("goods_spec_item")->where("spec_id=" . $spec_id)->select();
		$result['list'] = $list;
		$result['itemlist'] = $itemlist;
		$result['spec_id'] = $spec_id;
		$result['spec_item_id'] = $spec_item_id;
		$result['spec_item_name'] = $spec_item_name;
		jsonout("规格数据", 0, $result);
	}


}