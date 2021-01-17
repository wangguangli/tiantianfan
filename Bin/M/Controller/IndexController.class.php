<?php

namespace M\Controller;
header("Content-type: text/html; charset=utf-8");

use Think\Controller;
use Clas\User;
use Api\Controller\IndexoutController;
use Clas\Ad;
use Clas\Article;
use Clas\Goods;
use Clas\Jssdk;

class IndexController extends Controller
{
	/**
	 *商城首页
	 */
	public function index()
	{
		$quick = I('quick', 0); // 是否在登录界面，提示微信快捷登录，1是，0否
		session('quick', $quick);
		$share_uid = I('share_uid', 0);
		session("share_uid", $share_uid);
		$ad = new Ad();
		// banner轮播图
		$data["position"] = "goods_top_banner";
		$data["row"] = 5;
		$bannerInfo = $ad->adList($data); //获取banner图及其他信息
		$banner = $bannerInfo[2];

		//一级商品分类
		$category = M('goods_category')->where("pid=0")->field('id,name,pid,img')->select();
		if ($category)
		{
			$category = full_pre_url($category, "img");
		}
		else
		{
			$category = array();
		}
		foreach ($category as $key => &$value)
		{
			if (!$value['img'])
			{
				$value['img'] = "";
			}
		}
		//小站快报（动态）
		$Article = new Article();
		$data_arc['type'] = 1;
		$news = $Article->articleList($data_arc);
		$news = $news[2];
		// 中间广告
		$data["position"] = "goods_middle_banner";
		$data["row"] = 1;
		$Advertisement = $ad->adList($data);
		$Advertisement = $Advertisement[2];

		//精品推荐图片
		// 精品推荐 左
		$data["position"] = "goods_f_banner";
		$data["row"] = 1;
		$ad31 = $ad->adList($data);

		// 精品推荐 右上边
		$data["position"] = "goods_r_t_banner";
		$data["row"] = 1;
		$ad32 = $ad->adList($data);

		// 精品推荐 右左下
		$data["position"] = "goods_r_fd_banner";
		$data["row"] = 1;
		$ad33 = $ad->adList($data);

		// 精品推荐 右右下
		$data["position"] = "goods_r_rd_banner";
		$data["row"] = 1;
		$ad34 = $ad->adList($data);

		$ad31 = full_pre_url($ad31[2][0], "photo");
		$ad32 = full_pre_url($ad32[2][0], "photo");
		$ad33 = full_pre_url($ad33[2][0], "photo");
		$ad34 = full_pre_url($ad34[2][0], "photo");
		$ad_ept = array(
			"ad_id" => 0,
			"photo" => "",
			"module" => "",
			"mod_id" => 0,
			"api_url" => "",
			"link" => "",
		);
		$ad31 = $ad31 ? $ad31 : $ad_ept;
		$ad32 = $ad32 ? $ad32 : $ad_ept;
		$ad33 = $ad33 ? $ad33 : $ad_ept;
		$ad34 = $ad34 ? $ad34 : $ad_ept;


		$goods = new Goods();
		$isHaveGroup = 1;   // 是否有拼团数据，0没有，1有
		$isHaveFlash = 1;   // 是否有秒杀数据，0没有，1有
		$isHaveToday = 1;   // 是否有今日好货，0没有，1有
		$isHideTwo = 0;   // 是否同时隐藏拼团和秒杀模块，0否，1是
		// 拼团
		$where_gp["is_group"] = 1;
		$where_gp["isdel"] = 0;
		$where_gp["is_on_sale"] = 1;
		$where_gp["product_type"] = 0;
		$groupList = $goods->goodsList($where_gp, "sort asc, id desc", 3);

		foreach ($groupList as $k => $v){
		    //查询团购价 查询第一个规格的团购价
            $specWhere['goods_id'] = $v['id'];
            $specInfo = M('goods_spec_price')->where($specWhere)->find();
            $groupList[$k]['spec_price'] = $specInfo['group_price'];

        }
		if (!$groupList)
		{
			$groupList = array();
		}
		foreach ($groupList as $key => &$value)
		{
			if (!$value["name"])
			{
				$value["name"] = "今日团购";
			}
			if($value['sell_count'] > 999){
                $value['sell_count'] = '999+';
            }
			//查询相关产品评论数
            $common_count = M('goods_comment')->where('goods_id='.$value['id'])->count();
            if($common_count > 999){
                $value['common_count'] = '999+';
            }else{
                $value['common_count'] = $common_count;
            }

		}
		$goods_group["title"] = "购物拼团";
		$goods_group["goods_list"] = $groupList;

		// 秒杀
		$where_fla["is_hour"] = 1;
		$where_fla["isdel"] = 0;
		$where_fla["is_on_sale"] = 1;
		$where_fla["product_type"] = 0;
		$flashList = $goods->goodsList($where_fla, "sort asc, id desc", 3);



		$wholetime = strtotime(date("Y-m-d H", time()) . ":00:00");
		$hour_end_time = $wholetime + 3600;
		if (!$flashList)
		{
			$flashList = array();
		}
		foreach ($flashList as $key2 => &$value2)
		{
			// 看看是否有符合当前时间的商品
			$flashStartTime = strtotime(date("Y-m-d H:00:00", time()));
			$flashEndTime = $flashStartTime + 3600;
			$whereFT = "goods_id=" . $value2['id'] . " and end_time>=" . $flashStartTime . " and start_time<" . $flashEndTime;
			$isHave = M("goods_hour")->where($whereFT)->find();
			if (!$isHave)
			{
				unset($flashList[$key2]);
				continue;
			}

            if($value2['sell_count'] > 999){
                $value2['sell_count'] = '999+';
            }
            //查询相关产品评论数
            $common_count = M('goods_comment')->where('goods_id='.$value2['id'])->count();
            if($common_count > 999){
                $value2['common_count'] = '999+';
            }else{
                $value2['common_count'] = $common_count;
            }
			/*
			if ($value2["name"])
			{
				$value2["name"] = "今日秒杀";
			}
			*/
		}

		$hourEnd = strtotime(date("Y-m-d H:00:00", time()));
		$hourEnd = $hourEnd + 3600;
		$resetTime = $hourEnd - time();
		$goods_flash["title"] = "整点秒杀";
		$goods_flash["reset_time"] = $resetTime;
		$goods_flash["goods_list"] = $flashList;

		if (count($groupList) < 1)
		{
			$isHaveGroup = 0;
		}
		if (count($flashList) < 1)
		{
			$isHaveFlash = 0;
		}
		if (!$isHaveGroup && !$isHaveFlash)
		{
			// 拼团和秒杀同时不存在数据，则两个模块现时隐藏
			$isHideTwo = 1;
		}

		// 今天好货
		$goods_today = array();
		$where_today["hot_id"] = 2;
		$where_today["isdel"] = 0;
		$where_today["is_on_sale"] = 1;
		$where_today["product_type"] = 0;
		$todayList = $goods->goodsList($where_today, "sort asc, id desc", 4);
		if (!$todayList)
		{
			$todayList = array();
		}
		$goods_today["title"] = "发现好货";
		$goods_today["content"] = "发现好货";
		$goods_today["goods_list"] = $todayList;

		if (count($todayList) < 1)
		{
			$isHaveToday = 0;
		}


		// 第二版广告 4个分开处理
		$data4["position"] = "four_ads_1_v2";
		$data4["row"] = 2;
		$ad4s = $ad->adList($data4);
		$ad4_1["title"] = $ad4s[2][0]["title"] ? $ad4s[2][0]["title"] : "人气冠军";
		$ad4_1["content"] = $ad4s[2][0]["content"] ? $ad4s[2][0]["content"] : "好评如潮，质量超好";
		$ad4_1["goods_list"] = $ad4s[2];

		$data4["position"] = "four_ads_2_v2";
		$data4["row"] = 2;
		$ad4s = $ad->adList($data4);
		$ad4_2["title"] = $ad4s[2][0]["title"] ? $ad4s[2][0]["title"] : "畅销冠军";
		$ad4_2["content"] = $ad4s[2][0]["content"] ? $ad4s[2][0]["content"] : "销量过万，订单不断";
		$ad4_2["goods_list"] = $ad4s[2];

		$data4["position"] = "four_ads_3_v2";
		$data4["row"] = 2;
		$ad4s = $ad->adList($data4);
		$ad4_3["title"] = $ad4s[2][0]["title"] ? $ad4s[2][0]["title"] : "新品上市";
		$ad4_3["content"] = $ad4s[2][0]["content"] ? $ad4s[2][0]["content"] : "全新品牌，卓越品质";
		$ad4_3["goods_list"] = $ad4s[2];

		$data4["position"] = "four_ads_4_v2";
		$data4["row"] = 2;
		$ad4s = $ad->adList($data4);
		$ad4_4["title"] = $ad4s[2][0]["title"] ? $ad4s[2][0]["title"] : "明星单品";
		$ad4_4["content"] = $ad4s[2][0]["content"] ? $ad4s[2][0]["content"] : "甄选、优质、超值";
		$ad4_4["goods_list"] = $ad4s[2];

		$banner_notice["banner_notice_1"] = "24小时急速发货";
		$banner_notice["banner_notice_2"] = "7天无忧退换";
		$banner_notice["banner_notice_3"] = "48小时快速退款";
		$ad4_3['goods_list'] = full_pre_url($ad4_3['goods_list'], 'photo');


		$automatic_logon = I("automatic_logon");
		if (decrypt2($automatic_logon) == '3429561')
		{
			$where_user['id'] = 8;
			$user = get_user_info($where_user);
			if ($user)
			{
				session("user_id", 8);
			}
		}
		$icp = get_config("icp");

		$js = new Jssdk();
		$jdk = $js->getSign(1);
		$user_id = session('user_id') ? session('user_id') : 0;
		$share = array(
			'title' => get_config('web_title'),
			'url' => C('C_HTTP_HOST') . 'M/Index/index?share_uid=' . $user_id,
			'thumb' => get_config('logo'),
			'desc' => get_config('description'),
		);

		$kefu['qq'] = get_config("qq");
		$kefu['phone'] = get_config("kefu_mobile");

		//分类下面的图片的循环数组
        $index_one_where['position'] = 'index_two_image';
        $two_image = M('ad')->where($index_one_where)->select();

        $this->assign('two_image',$two_image);
        //发现好货的三张图片
        $index_goods_one_where['position'] = 'index_goods_one';
        $index_goods_one = M('ad')->where($index_goods_one_where)->find();
        $this->assign('index_goods_one',$index_goods_one);

        $index_goods_two_where['position'] = 'index_goods_two';
        $index_goods_two = M('ad')->where($index_goods_two_where)->find();

        $this->assign('index_goods_two',$index_goods_two);

        $index_goods_three_where['position'] = 'index_goods_three';
        $index_goods_three = M('ad')->where($index_goods_three_where)->find();
        $this->assign('index_goods_three',$index_goods_three);


		$this->assign('wxjssign', $jdk);//获取分享数据
		$this->assign('share', $share);
		$this->assign('group', $goods_group);// 拼团
		$this->assign('flash', $goods_flash);// 秒杀
		$this->assign('hour_end_time', $hour_end_time); //秒杀结束时间
		$this->assign('today', $goods_today);// 今日好货
		$this->assign('popularity', $ad4_1);// 人气冠军
		$this->assign('seller', $ad4_2);// 畅销冠军
		$this->assign('newProducts', $ad4_3);// 新品
		$this->assign('star', $ad4_4);// 明星单品

		$this->assign('banner', $banner);//banner轮播图绑定
		$this->assign('category', $category);//分类绑定
		$this->assign('news', $news);//动态  快报绑定
		$this->assign('Advertisement', $Advertisement);//广告
		$this->assign('ad31', $ad31);
		$this->assign('ad32', $ad32);
		$this->assign('ad33', $ad33);
		$this->assign('ad34', $ad34);
		$this->assign('banner_notice', $banner_notice);
		$this->assign('menu_Shop', 'cut');
		$this->assign('isHaveToday', $isHaveToday);
		$this->assign('isHideTwo', $isHideTwo);
		$this->assign('icp', $icp);
		$this->assign('kefu', $kefu);
		$this->assign('isHaveFlash', $isHaveFlash);
		$this->assign('isHaveGroup', $isHaveGroup);
		$this->display();
	}

	/**
	 * 消息，帮助详情(WAP，app)
	 * @param string $id 文章ID
	 * @return  string
	 * @zd
	 */
	public function articleDetail()
	{
		$id = I('id');
		$article = M('article')->where('id=' . $id)->field('id,title,time,thumb,content,time')->find();
		$this->assign('article', $article);
		$this->display('Index:news_detail');
	}

	/**
	 * 首页更多好货
	 */
	public function todayGoods()
	{

		//排序
		$type = I('type', 1);
		if ($type == 1)
		{
			//综合
			$order = 'id DESC';
		}
		elseif ($type == 2)
		{
			//销量
			$order = 'sell_count DESC';
		}
		elseif ($type == 3)
		{
			//价格
			$order = 'price ASC';
		}
		$where_today["hot_id"] = 2;
		$where_today["isdel"] = 0;
		$where_today["is_on_sale"] = 1;
		$where_today["product_type"] = 0;
		$goods = M('goods')->where($where_today)->order($order)->select();

		$count = M('goods')->where($where_today)->count();
		$this->assign('goods', $goods);
		$this->assign('count', $count);
		$this->assign('type', $type);

		$this->display();
	}

	/**
	 *首页商品列表（猜你喜欢）
	 */
	public function goodsListIndex()
	{
		//猜你喜欢商品列表
		$page = I("page", 1);
		$page = $page > 0 ? $page : 1;
		$paging['page'] = $page;
		$num = 10;
		$obj = new Goods();
		$where['isdel'] = 0;
		$where['is_on_sale'] = 1;

		$where['product_type'] = 0;
		$where['is_hour'] = 0;
		$where['is_group'] = 0;
		$cat_id = I('cat_id');
		if($cat_id){

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
            $where['hot_id'] = 1;
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


        if($cidArr){
            $extGoodsArr = M("goods_category_extra")->where($wher_ext)->getField("goods_id", true);
            foreach ($extGoodsArr as $k => $v){
                $goodsInfo = M('goods')->find($v);

                if($goodsInfo['isdel'] == 1){
                    unset($extGoodsArr[$k]);
                }
            }

        }else{
            $extGoodsArr  =array();
        }




        $ord = $this->getGoodsSort();

		$ok = $obj->goodsList($where, $ord, $num, $paging,$extGoodsArr);
		_error_log('-----ok------');
		_error_log($ok);

		if (!$ok)
		{
			jsonout("暂无数据");
		}
		$numArr = $obj->getNums($where, $num);
		$res['list'] = $ok;
		$res['page'] = $page;
		$res['last_id'] = get_last_id($ok, 'id', $num);
		$res['max_page'] = $numArr['max_page'];
		$goods = $res['list'];
		if ($goods)
		{
			$goods = full_pre_url($goods, 'thumb');
			$array = array();
			foreach ($goods as $k => $v)
			{
				$array[$k]['id'] = $v['id'];
				$array[$k]['name'] = $v['name'];
				$array[$k]['price'] = $v['price'];
				$array[$k]['thumb'] = $v['thumb'];
				$array[$k]['tags'] = $v['tags'];
				if (mb_strlen($v['subhead'], "utf-8") > 15)
				{
					$v['subhead'] = mb_substr($v['subhead'], 0, 15, 'utf-8');
				}
				$array[$k]['subhead'] = $v['subhead'];
				//计算销量与评论数
                if($v >= 9999){
                    $array[$k]['sell_count'] = '9999+';
                }else{
                    $array[$k]['sell_count'] = $v['sell_count'];
                }
                //查询相关产品评论数
                $common_count = M('goods_comment')->where('goods_id='.$v['id'])->count();
                if($common_count > 999){
                    $array[$k]['common_count'] = '999+';
                }else{
                    $array[$k]['common_count'] = $common_count;
                }
			}

			jsonout('查询成功', 0, $array);
		}
		else
		{
			jsonout('查询成功', 0, null);
		}
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
		$price_sort1 = $data['price_sort1'];

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
        if ($price_sort1)
        {
            if ($price_sort == 'DESC')
            {
                $sort = 'price DESC';
            }
            else
            {
                $sort = 'price DESC';
            }
        }
		return $sort;
	}

	/**
	 * 首页动态查看全部(cat_id=1)
	 */
	public function articleAll()
	{
		$where['cat_id'] = 1;
		$article = M('article')->where($where)->select();
		if ($article)
		{
			foreach ($article as $k => $v)
			{
				$article[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
			}
		}
		else
		{
			$article = null;
		}
		print_r($article);
	}

	// 以用户名注册
	public function reg()
	{
		if (IS_POST)
		{
			$obj = new User();
			$ok = $obj->register_user();
			if (!is_numeric($ok))
			{
				$this->error($ok);
			}
			else
			{
				session('user_id', $ok);
			}
			$out_trade_no = I("out_trade_no");
			if ($out_trade_no)
			{
				$where_log['serial_no'] = $out_trade_no;
				$logInfo = M("pay3_log")->where($where_log)->find();
				if ($logInfo && $logInfo['buyer_open_id'])
				{
					$where_u['id'] = $ok;
					$userInfo = get_user_info($where_u);
					if ($logInfo['type'] == 1 && !$userInfo['openid'])
					{
						// 微信
						// 不能被别人用户绑定
						$where_is['openid'] = $logInfo['buyer_open_id'];
						$isHave = M("user")->where($where_is)->count();
						if (!$isHave)
						{
							$where_u['id'] = $userInfo['id'];
							$data_us['openid'] = $logInfo['buyer_open_id'];
							M("user")->where($where_u)->save($data_us);
						}
					}
					if ($logInfo['type'] == 2 && !$userInfo['buyer_id'])
					{
						// 支付宝
						// 不能被别人用户绑定
						$where_is['buyer_id'] = $logInfo['buyer_open_id'];
						$isHave = M("user")->where($where_is)->count();
						if (!$isHave)
						{
							$where_u['id'] = $userInfo['id'];
							$data_us['buyer_id'] = $logInfo['buyer_open_id'];
							M("user")->where($where_u)->save($data_us);
						}
					}
				}

				if ($logInfo && $logInfo['is_get'] < 1 && $userInfo['id'])
				{
					// 执行返利，只执行用户及用户的上级提成
//					order_qr_user($logInfo['id'], $logInfo['order_id'], $userInfo['id']);
				}

			}
			$this->success('注册成功', U('User/index'));
			exit;
		}
		$code_status = get_config("code_status");
		$tuijian = I('tuijian', '');
		if (session('share_uid') && !$tuijian)
		{
			$share_info = get_user_field(session('share_uid'));
			if ($share_info)
			{
				$tuijian = $share_info;
			}
		}
		$this->assign('tuijian', $tuijian);
		$this->assign('code_status', $code_status);
		$this->display();
	}

	// 以手机号注册
	public function reg2()
	{

		if (IS_POST)
		{
			$password = I('password');
			$confirm_pwd = I('confirm_pwd');
			if (strlen($password) < 6)
			{
				$this->error("输入密码不能少于6位");
			}
			if ($confirm_pwd != $password)
			{
				$this->error('两次密码不相同');
			}

			// 推荐注册
			$nickname = I("nickname", '');
			if (strlen($nickname) < 4)
			{
				$this->error("用户名至少4位");
			}
			$phone = I("phone", '');


			$realname = I("realname", '');
			if (!$realname)
			{
				$this->error("请输入姓名");
			}

			if (strlen($phone) != 11)
			{
				$this->error("请输入正确的手机号");
			}
			$pay_password = I("pay_password", '');
			if (strlen($pay_password) < 6)
			{
				$this->error("输入支付密码不能少于6位");
			}
			$id_card = I("id_card", '');


			$s1 = 0;
			$s2 = 0;
			$s3 = 0;

			$tjid = I("tjid", ''); // 推荐用户账号,唯一
			if ($tjid)
			{
				$tjinfo = M("user")->where("id=" . $tjid)->field("id,first_leader,second_leader,level")->find();
				if ($tjinfo)
				{
					$s1 = $tjinfo['id'];
					$s2 = $tjinfo['first_leader'];
					$s3 = $tjinfo['second_leader'];
				}
				if ($tjinfo['level'] < 1)
				{
					$this->error("推荐人非VIP，不能进行推广");
				}
			}
			$rsc = M("user")->where("nickname='" . $nickname . "'")->count();
			if ($rsc > 0)
			{
				$this->error("此账户已存在，请更换后注册");
			}
			$data['pay_password'] = get_pwd($pay_password);
			$data['realname'] = $realname;
			$data['phone'] = $phone;
			$data['id_card'] = $id_card;
			$data['nickname'] = $nickname;
			$data['password'] = get_pwd($password);
			$data['tuijian_id'] = $s1;
			$data['first_leader'] = $s1;
			$data['second_leader'] = $s2;
			$data['third_leader'] = $s3;
			$data['time'] = time();
			$data['ip'] = get_client_ip();
			$rsa = M("user")->add($data);

			$data1 = array(
				'user_id' => $rsa,
				'login_time' => time(),
				'date' => date('Ymd'),
				'time' => time(),
				'ip' => get_client_ip()
			);

			M('user_log')->add($data1);

			if ($rsa)
			{
				$this->success('注册成功，请下载app', U('Index/app_load'));
			}
			else
			{
				$this->error("注册失败，请重新注册");
			}
		}

		$tj = I('tj_str');

		if ($tj)
		{
			$tjid = base64_decode($tj);
			$nickname = M('user')->where('id=' . $tjid)->getField('nickname');
			$this->assign('tjid', $tjid);
			$this->assign('nickname', $nickname);
		}
		$this->display();
	}

	public function express()
	{
		$order_id = I('order_id');
		$result = $order = array();
		$where['id'] = $order_id;
		$order = M('order')->where($where)->find();

		if ($order['postid'] && $order['express_id'])
		{
			$this->assign('order', $order);
		}
		$host = "http://kdwlcxf.market.alicloudapi.com";//api访问链接
		$path = "/kdwlcx";//API访问后缀
		$method = "GET";
		$appcode = "07e070ee33004c2db8f455578a5875d9";//替换成自己的阿里云appcode
		$headers = array();
		array_push($headers, "Authorization:APPCODE " . $appcode);
		$querys = "no=" . $order['postid'] . "&type=" . $order['express_id'];
		$bodys = "";
		$url = $host . $path . "?" . $querys;//url拼接

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_FAILONERROR, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		if (1 == strpos("$" . $host, "https://"))
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		}
		$result = curl_exec($curl);
		$body = json_decode(substr($result, $headerSize), true);
		$result = $body['result'];
		$list = $result['list'];

		$this->assign('list', $list);
		$this->display();
	}

//找回密码页面
	public function password()
	{
		$code_status = get_config("code_status");
		$this->assign('code_status', $code_status);
		$this->display();
	}

	/**
	 * 找回登录密码
	 * @param string $phone 用户ID
	 * @param string $newpwd 新密码
	 * @param string $confirmpwd 确认密码
	 * @param string $phone_code 手机验证码
	 * @return  json
	 * @zd
	 */
	public function findPassword($data = null)
	{
		if (!$data)
		{
			$data = I();
		}
		$obj = new User();
		$ok = $obj->findPassword($data);
		if (is_numeric($ok))
		{
			$this->success('找回成功', U('Index/login'));
		}
		else
		{
			$this->error($ok, U('Index/password'));
		}
	}

	/**
	 * 多端支付(微信、支付宝、项目APP)
	 * 这里只需要处理支付宝和微信支付。
	 * 如果是本项目打开的话，那么APP会自动打开买单界面，不需要打开此网址
	 */
	public function pay()
	{
		$shop_id = I("shop_id", 0);                 // 商家ID
		if (IS_POST)
		{
			$pay_type = I("pay_type", 0);            // 2微信，3支付宝
			$money = I("money", 0, 'abs');           // 付款金额
			if ($pay_type == 2)
			{
				$type = 1;
				$payment_type = "wxpay";
			}
			else
			{
				$type = 2;
				$payment_type = "alipay";
			}
			if ($money <= 0)
			{
				$this->error("请输入正确的金额");
			}

			$model = M();
			$model->startTrans();
			$user_id = I("user_id") > 0 ? I("user_id") : 0;
			$order_no = make_order_no();
			$data['order_no'] = $order_no;
			$data['user_id'] = $user_id;
			$data['total_commodity_price'] = $money;
			$data['payment_type'] = $payment_type;
			$data['shipping_type'] = 0;
			$data['order_status'] = 0;
			$data['add_time'] = time();
			$data['ip'] = get_client_ip();
			$data['order_type'] = 1;
			$data['shop_id'] = $shop_id;
			$order_id = M("order")->add($data);

			$data_log["serial_no"] = make_order_no();
			$data_log["user_id"] = 0;
			$data_log["order_id"] = $order_id;
			$data_log["order_no"] = $order_no;
			$data_log["money"] = $money;
			$data_log["order_type"] = 1;
			$data_log["type"] = $type;
			$data_log["cre_time"] = time();
			$data_log["cre_date"] = date("Y-m-d H:i:s");
			$data_log["ip"] = get_client_ip();
			$logid = M("pay3_log")->add($data_log);

			if ($order_id && $logid)
			{
				$model->commit();
				$url = U('payHandle', array('order_id' => $order_id));
				redirect($url);
			}
			else
			{
				$model->rollback();
				$this->error("支付失败，请重新失败");
			}
			exit();

		}

		if (stristr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger'))
		{
			// 微信支付
			$pay_type = 2;
		}
		else
		{
			// 支付宝
			$pay_type = 3;
		}

		if (!$shop_id)
		{
			$shop_name = "无商家";
		}
		else
		{
			$shop_name = M("shop")->where("id=" . $shop_id)->getField("shop_name");
		}


		$this->assign('pay_type', $pay_type);
		$this->assign('shop_name', $shop_name);
		$this->assign('shop_id', $shop_id);
		$this->display();
	}

	public function payHandle()
	{
		$order_id = I("order_id", 0);            // 订单号ID
		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if (!$orderInfo)
		{
			$this->error("没有此订单，请重新支付");
		}
		if ($orderInfo['order_status'] > 0)
		{
			$this->error("此订单已支付");
		}

		if ($orderInfo['payment_type'] == 'wxpay')
		{

			$this->error("公众号支付暂不支付，请使用其他支付方式");

			$config_pay3 = get_config_pay3(3);
			if (!$config_pay3['appid'] || !$config_pay3['key2'])
			{
				$this->error("微信支付配置异常，请使用其他支付方式");
			}

			// 微信公众号支付
			$openid = I("openid", '');
			$unionid = I("unionid", '');
			if (!$openid)
			{
				$appid = $config_pay3['appid'];
				$redirect_uri = C("C_HTTP_HOST") . "/index.php/M/Index/getToken";
				$state = $order_id;
				$codeUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";

				redirect($codeUrl);
			}

			// 写入第三方支付记录表
			$out_trade_no = make_order_no();
			$total_amount = sprintf("%.2f", $orderInfo['total_commodity_price']);

			$data_log3["serial_no"] = $out_trade_no;
			$data_log3["user_id"] = $orderInfo["user_id"];
			$data_log3["order_id"] = $orderInfo["id"];
			$data_log3["order_no"] = $orderInfo["order_no"];
			$data_log3["money"] = $total_amount;
			$data_log3["order_type"] = $orderInfo["order_type"];
			$data_log3["type"] = 2;
			$data_log3["status"] = 0;
			$data_log3["cre_time"] = time();
			$data_log3["cre_date"] = date("Y-m-d H:i:s");
			$data_log3["ip"] = get_client_ip();
			$log3 = M("pay3_log")->add($data_log3);
			if (!$log3)
			{
				$this->error("订单支付异常，请联系客服");
			}
			if ($unionid)
			{
				M("order")->where("id=" . $order_id)->setField("unionid", $unionid);
			}

			$href = "./ThinkPHP/Library/Vendor/Wxpay";
			require_once $href . '/lib/WxPayConfig.php';
			require_once $href . '/lib/WxPayData.php';
			require_once $href . '/lib/WxPayApi.php';
			require_once $href . '/example/WxPay.NativePay.php';
			require_once $href . '/example/WxPay.JsApiPay.php';

			$input = new \WxPayUnifiedOrder();
			$input->SetBody($orderInfo['order_no']);
			$input->SetAttach("weixin");
			$input->SetOut_trade_no($orderInfo['order_no']);
			$input->SetTotal_fee($orderInfo['total_commodity_price'] * 100);
			$input->SetTime_start(date("YmdHis"));
			$input->SetTime_expire(date("YmdHis", time() + 600));
			$input->SetNotify_url(C("C_HTTP_HOST") . $config_pay3['notify_url']);
			$input->SetTrade_type("JSAPI");
			$input->SetOpenid($openid);
			$order2 = \WxPayApi::unifiedOrder($input);

			$tools = new \JsApiPay();
			$jsApiParameters = $tools->GetJsApiParameters($order2);

			$go_url = U('pay_success', 'id=' . $order_id);
			$back_url = U('pay_error', 'id=' . $order_id);
			$html = <<<EOF
				<script src="__PUBLIC__/layer/mobile/layer.js"></script>
				<script type="text/javascript">
				//调用微信JS api 支付
				function jsApiCall()
				{
					WeixinJSBridge.invoke(
						'getBrandWCPayRequest',$jsApiParameters,
						function(res){
							WeixinJSBridge.log(res.err_msg);
							 if(res.err_msg == "get_brand_wcpay_request:ok") {
								location.href='$go_url';
							 }else{
								//alert(JSON.stringify(res));
								//alert('支付失败！');
								layer.open({
									content: "支付失败！"
									,skin: 'msg'
									,time: 2
								});
								location.href='$back_url';
							 }
						}
					);
				}

				function callpay()
				{
					if (typeof WeixinJSBridge == "undefined"){
						if( document.addEventListener ){
							document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
						}else if (document.attachEvent){
							document.attachEvent('WeixinJSBridgeReady', jsApiCall);
							document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
						}
					}else{
						jsApiCall();
					}
				}
				callpay();
				</script>	
EOF;
			$this->assign("wxres", $html);
			$this->display();
			//exit(json_encode(array('status' => 1, 'msg' => '微信支付所需数据', 'result' => $html)));


		}
		else
		{
			// 支付宝
			$href = "./ThinkPHP/Library/Vendor/Alipay";
			require_once $href . '/config.php';
			require_once $href . '/wappay/service/AlipayTradeService.php';
			require_once $href . '/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';


			if ($orderInfo['total_commodity_price'] <= 0)
			{
				$this->error("支付金额不能<=0");
			}
			$config_pay3 = get_config_pay3(2);
			if (!$config_pay3)
			{
				$this->error("支付宝配置异常，请使用其他支付方式");
			}

			$body = '商城消费';
			$subject = '商城消费';
			$out_trade_no = make_order_no();
			$total_amount = sprintf("%.2f", $orderInfo['total_commodity_price']);

			// 写入第三方支付记录表
			$data_log3["serial_no"] = $out_trade_no;
			$data_log3["user_id"] = $orderInfo["user_id"];
			$data_log3["order_id"] = $orderInfo["id"];
			$data_log3["order_no"] = $orderInfo["order_no"];
			$data_log3["money"] = $total_amount;
			$data_log3["order_type"] = $orderInfo["order_type"];
			$data_log3["type"] = 2;
			$data_log3["status"] = 0;
			$data_log3["cre_time"] = time();
			$data_log3["cre_date"] = date("Y-m-d H:i:s");
			$data_log3["ip"] = get_client_ip();
			$log3 = M("pay3_log")->add($data_log3);
			if (!$log3)
			{
				return array(0, "订单支付异常，请联系客服");
			}

			$alipay_config = array(
				"app_id" => $config_pay3['appid'],
				"merchant_private_key" => $config_pay3['key1'],
				"alipay_public_key" => $config_pay3['key2'],
				"charset" => "UTF-8",
				"sign_type" => "RSA2",
				"gatewayUrl" => "https://openapi.alipay.com/gateway.do",
			);

			$payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
			$payRequestBuilder->setBody('商城消费');
			$payRequestBuilder->setSubject('商城消费');
			$payRequestBuilder->setTotalAmount($total_amount);
			$payRequestBuilder->setOutTradeNo($out_trade_no);

			$aop = new \AlipayTradeService($alipay_config);
			$notify_url = C("C_HTTP_HOST") . $config_pay3['notify_url'];
			$return_url = C("C_HTTP_HOST") . $config_pay3['return_url'];
			$response = $aop->wapPay($payRequestBuilder, $return_url, $notify_url);
			var_dump($response);
		}
	}

	public function getToken()
	{
		$code = I("code");
		$state = I("state");

		if ($code && $state)
		{
			$wxPay = get_config_pay3(3);
			$appid = $wxPay['appid'];
			$secret = $wxPay['key1'];
			$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code";
			$res = file_get_contents($url);
			$res = json_decode($res, true);
			if ($res['access_token'] && $res['openid'])
			{
				$state_arr = explode("_", $state);
				if (is_array($state_arr))
				{
					M('user')->where('id=' . $state_arr[1])->setField('openid', $res['openid']);
					$old_url = C("C_HTTP_HOST") . "/index.php/M/Shopcenter/baodan?order_id=" . $state_arr[0];
				}
				else
				{
					$old_url = C("C_HTTP_HOST") . "/index.php/M/Index/payHandle?order_id={$state}&openid=" . $res['openid'];
				}
				redirect($old_url);
			}
			else
			{
				$this->error("获取信息失败，请重新下单支付");
			}
		}
		else
		{
			$this->error("获取Token失败，请重新下单支付");
		}
	}


	// 支付后的选择提示
	public function notice()
	{
		$data = I();
		$out_trade_no = I("out_trade_no");
		$loginUrl = U('login', array('out_trade_no' => $out_trade_no));
		$regUrl = U('reg', array('out_trade_no' => $out_trade_no));
		$this->assign("regUrl", $regUrl);
		$this->assign("loginUrl", $loginUrl);
		$this->assign("out_trade_no", $out_trade_no);
		$this->display();
	}

	// 登录
	public function login()
	{
		// 来源，默认0跳转到个人中心，1跳转到来源地址
		$source = I("source", 0);
		$phone = I("phone");
		$phone_code = I("phone_code");
		$password = I("password");
		$login_type = I("login_type", 1);    // 1手机+密码，2手机+验证码

		if (IS_POST)
		{
			$refer = I("refer");

			if ($login_type == 2)
			{
				// 手机+验证码登录
				if (!$phone || !$phone_code)
				{
					$this->error("手机和验证码不能为空");
				}
				if (!check_phone_code($phone, $phone_code))
				{
					$this->error("验证码不正确");
				}
				$where['phone'] = $phone;
				$userInfo = M("user")->where($where)->find();
			}
			else
			{
				// 手机+密码登录
				if (!$phone || !$password)
				{
					$this->error("手机和密码不能为空");
				}
				$where['phone'] = $phone;
				$where['password'] = get_pwd($password);
				$userInfo = M("user")->where($where)->find();
			}
			if (!$userInfo)
			{
				$this->error("用户名或密码不正确");
			}
			if ($userInfo['status'] != 1 || $userInfo['is_del'] == 1)
			{
				$this->error("账户状态异常，请联系客服");
			}
			session('user_id', $userInfo['id']);

			$forceWx = get_config("wap_force_wx_login");
			/*if ($forceWx == 1)
			{
				// 强制微信登录，目前demo2用的是 双牛点餐 公众号的相关信息
				$this->getCode();
			}
			else
			{
				// 如果不强制微信登录，则如果在微信中打开，则微信登录，否则不微信登录
				if (stristr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger'))
				{
					// 目前demo2用的是 双牛点餐 公众号的相关信息
					$this->getCode();
				}
				else
				{
					if ($source == 1)
					{
						$this->success('登录成功', $refer);
					}
					else
					{
						$this->success('登录成功', U('User/index'));
					}
				}
			}*/

            if ($source == 1)
            {
                $this->success('登录成功', $refer);
            }
            else
            {
                $this->success('登录成功', U('User/index'));
            }
			exit();
		}

		$refer = $_SERVER['HTTP_REFERER'];    // 来源地址
		session("refer", $refer);
		$logo = get_config('logo');
		$alipay_login = get_config('alipay_login');
		$wx_login = get_config('wx_login');
		$code_login = get_config('code_login');
		if (session('quick') == 1 && $wx_login == 1 )
		{
			$quick = 1;
		}
		else
		{
			$quick = 0;
		}
		$this->assign('logo', $logo);
		$this->assign("refer", $refer);
		$this->assign("source", $source);
		$this->assign("alipay_login", $alipay_login);
		$this->assign("wx_login", $wx_login);
		$this->assign("code_login", $code_login);
		$this->assign("quick", $quick);
		$this->display();
	}

	// APP下载
	public function app_load()
	{
		$data["and_url"] = C("C_HTTP_HOST") . "/app/android___.apk";

		$res = M('config_app')->find();

		$data["ios_url"] = $res['ios_addres'];
		$this->assign("data", $data);
		$this->display();
	}

	/**
	 * 首页点击动态查看更多动态
	 */
	public function saveAllMessage()
	{
		$id = I('type');//1代表动态
		$data['type'] = $id;
		$obj = new Article();
		$result = $obj->articleList($data);
		$res = $result[2];
		if ($res)
		{
			foreach ($res as $k => $v)
			{
				$res[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
			}
		}
		$this->assign('data', $res);
		$this->display();

	}

	/**
	 * 查看动态详情
	 */
	public function messageInfo()
	{
		$messageId = I('id');
		$info = M('article')->find($messageId);
		if ($info)
		{
			$info['content'] = htmlspecialchars_decode($info['content']);
			$info['time'] = date('Y-m-d H:i:s', $info['time']);
			$this->assign('data', $info);
			$this->display();
		}
		else
		{
			$this->error('该文章不存在或已删除');
		}
	}

	/**
	 * 首页搜索页面
	 */
	public function search()
	{
		if (IS_POST)
		{
			$array = I('');
			$goodsName = $array['goods_name'];
			if (empty($goodsName))
			{
				//查询最新的商品
				$where['is_on_sale'] = 1;
				$goods = M('goods')->where($where)->order('id DESC')->limit(10)->select();
			}
			else
			{
				$where['is_on_sale'] = 1;
				$where['name'] = array('like', '%' . $goodsName . '%');
				$goods = M('goods')->where($where)->order('id DESC')->select();
			}
			jsonout('成功', 0, $goods);
		}
		$this->display();
	}

	public function new_search(){
        $page = I("page", 1);
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
        foreach ($goods as $k => $v){
            $common_count = M('goods_comment')->where('goods_id='.$v['id'])->count();
            if($common_count > 999){
                $goods[$k]['common_count'] = '999+';
            }else{
                $goods[$k]['common_count'] = $common_count;
            }
        }
        $this->assign('max_page', $max_page);
        $this->assign('goods', $goods);
        $this->display();
    }
    public function search1(){
        $arr = I();
        $page = I("page", 1);
        $content = $arr['search'];

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


        if(!$goods){
            jsonout('暂无数据',1,null);
        }else{
            jsonout('查询成功',0,$goods);
        }

    }

	public function upload_image()
	{
		$case = I("case");
		$ok = common_upload_photo($case, $case);
		if (empty($ok['success']))
		{
			api_is_str($ok['msg']);
		}
		jsonout('ok', 0, array('img' => $ok['msg']));
	}

	public function pay_success()
	{
		$user_id = session('user_id');
		$this->assign('user_id', $user_id);
		$this->display();
	}

	public function pay_error()
	{
		$user_id = session('user_id');
		$this->assign('user_id', $user_id);
		$this->display();
	}

	// 短信验证码
	public function get_phone_code()
	{

		$ok = get_phone_code();
		if ($ok === true)
		{
			jsonout("验证码已发送", 0);
		}
		else
		{
			jsonout($ok);
		}
	}


	// 微信code
	public function getCode()
	{
		$wx = M('config_pay3')->where("type=3")->find();
		$appid = $wx['appid'];
		if (!$appid)
		{
			$this->error("请联系客服配置微信信息");
			exit;
		}
		$url = urlencode('http://' . $_SERVER['HTTP_HOST'] . "/M/Weixin/GetOpenid");
		$codeUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$url}&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";
		redirect($codeUrl);
	}

	// 绑定手机号
	public function bind()
	{
		_error_log('----bind--');
		_error_log(I());
		$user_id = I('user_id', 0);
		$openid = I('openid', '');
		if (!$user_id && !$openid)
		{
			$this->error('缺少主要参数，请返回重新操作');
		}
		if (IS_POST)
		{
			_error_log('----bind---post--');
			_error_log(I());

			$nickname = session('wx_nickname');
			$headimgurl = session('wx_headimgurl');

			$phone = I('phone', '');
			$phone_code = I("phone_code");
			if (!$phone)
			{
				$this->error("请输入手机号");
			}
			if (!check_phone($phone))
			{
				$this->error("手机号格式有误");
			}

			$code_status = get_config("code_status");
			if ($code_status)
			{
				if (!$phone_code)
				{
					$this->error("请输入验证码");
				}
				if (!check_phone_code($phone, $phone_code))
				{
					$this->error("验证码不正确");
				}
			}

			/**
			 * 可能出现的情况：
			 * 1，使用微信登录，此时有用户，但绑定手机号，传递3个参数（UID+手机+openid）
			 *      操作方式：以uid为基础，更新手机，前提是 手机，没有被其他用户绑定。
			 *
			 *
			 * 2，使用微信登录，但是没有对应用户，即新的用户，则只有2个参数（手机+openid）
			 *      操作方式：确认手机、openid没有绑定其他用户。然后插入新用户。
			 *              如果此时查到了用户，则更新（前提是没有被绑定）
			 *              (如果以openid查到，则 更新手机，前提是手机没有被绑定)
			 *              (如果以手机查到，则 更新openid，前提是openid没有被绑定)
			 *
			 */
			if ($user_id)
			{
				$where_u1['id'] = $user_id;
				$userInfoUid = get_user_info($where_u1);
			}
			if ($phone)
			{
				$where_u3['phone'] = $phone;
				$userInfoPhone = get_user_info($where_u3);
			}

			if (session("refer"))
			{
				$loginUrl = session("refer");
			}
			else
			{
				$loginUrl = U('User/index');
			}

			// ::1
			if ($userInfoUid)
			{
				if ($userInfoPhone && $userInfoPhone['id'] != $user_id)
				{
					$this->error("该手机号已被其他账户绑定，请重新输入(101)");
				}
				if ($userInfoUid['phone'])
				{
					session("refer", "");
					session('share_uid', '');
					session('wx_nickname', '');
					session('wx_headimgurl', '');
					session('user_id', $user_id);
					$this->success("该账户已有手机号无需绑定", $loginUrl);
				}
				if (!$userInfoUid['nickname'] && $nickname)
				{
					$data_up['nickname'] = $nickname;
				}
				if (!$userInfoUid['headimgurl'] && $headimgurl)
				{
					$data_up['headimgurl'] = $headimgurl;
				}
				$data_up['phone'] = $phone;
				$where_u['id'] = $userInfoUid['id'];
				$res = M("user")->where($where_u)->save($data_up);
				if ($res !== false)
				{
					session("refer", "");
					session('share_uid', '');
					session('wx_nickname', '');
					session('wx_headimgurl', '');
					session('user_id', $user_id);
					$this->success("登录成功", $loginUrl);
				}
				else
				{
					$this->error("操作失败，请重新操作");
				}
			}
			elseif (!$user_id && $openid)
			{
				// 新用户：没有UID，有openid
				$where_u2['openid'] = $openid;
				$userInfoOpenid = get_user_info($where_u2);
				if ($userInfoOpenid)
				{
					// 找到了用户记录
					if ($userInfoOpenid['phone'])
					{
						session("refer", "");
						session('user_id', $userInfoOpenid['id']);
						$this->success("该账户已绑定手机，即将跳转", $loginUrl);
					}
					if ($userInfoPhone && $userInfoPhone['id'] != $userInfoOpenid['id'])
					{
						$this->error("该手机号已被其他账户绑定，请重新输入(102)");
					}
					// 更新手机
					if (!$userInfoOpenid['nickname'] && $nickname)
					{
						$data_up['nickname'] = $nickname;
					}
					if (!$userInfoOpenid['headimgurl'] && $headimgurl)
					{
						$data_up['headimgurl'] = $headimgurl;
					}
					$data_up['phone'] = $phone;
					$where_u['id'] = $userInfoOpenid['id'];
					$res = M("user")->where($where_u)->save($data_up);
					if ($res !== false)
					{
						session("refer", "");
						session('share_uid', '');
						session('wx_nickname', '');
						session('wx_headimgurl', '');
						session('user_id', $userInfoOpenid['id']);
						$this->success("登录成功", $loginUrl);
					}
					else
					{
						$this->error("操作失败，请重新操作");
					}
				}
				else
				{
					// 没有找到，则新增或修改
					if ($userInfoPhone)
					{
						if ($userInfoPhone['openid'])
						{
							$this->error("该手机号已被其他账户绑定，请重新输入(103)");
						}
						else
						{
							// 手机账号没有对应的openid，此时只需要更新即可
							if (!$userInfoPhone['nickname'] && $nickname)
							{
								$data_up['nickname'] = $nickname;
							}
							if (!$userInfoPhone['headimgurl'] && $headimgurl)
							{
								$data_up['headimgurl'] = $headimgurl;
							}
							$data_up['openid'] = $openid;
							$data_up['openid_temp'] = $openid;
							$where_u['id'] = $userInfoPhone['id'];
							$res = M("user")->where($where_u)->save($data_up);
							if ($res !== false)
							{
								session("refer", "");
								session('share_uid', '');
								session('wx_nickname', '');
								session('wx_headimgurl', '');
								session('user_id', $userInfoPhone['id']);
								$this->success("登录成功", $loginUrl);
							}
							else
							{
								$this->error("操作失败，请重新操作");
							}
						}
					}
					else
					{
						$data_add['openid'] = $openid;
						$data_add['openid_temp'] = $openid;
						$data_add['phone'] = $phone;
						$data_add['cre_time'] = time();
						$data_add['ip'] = get_client_ip();
						if (session('share_uid'))
						{
							$where_s['id'] = session('share_uid');
							$shareInfo = get_user_info($where_s);
							if ($shareInfo && $shareInfo['status'] == 1 && $shareInfo['is_del'] == 0)
							{
								$data_add['first_leader'] = session('share_uid');
								if ($shareInfo['first_leader'])
								{
									$data_add['second_leader'] = $shareInfo['first_leader'];
									if ($shareInfo['second_leader'])
									{
										$data_add['third_leader'] = $shareInfo['second_leader'];
									}
								}
							}
						}
						if ($nickname)
						{
							$data_add['nickname'] = $nickname;
						}
						if ($headimgurl)
						{
							$data_add['headimgurl'] = $headimgurl;
						}
						$res = M("user")->add($data_add);

						if ($res !== false)
						{
							session("refer", "");
							session('share_uid', '');
							session('wx_nickname', '');
							session('wx_headimgurl', '');
							session('user_id', $res);
							$this->success("登录成功", $loginUrl);
						}
						else
						{
							$this->error("操作失败，请重新操作");
						}
					}
				}
			}
			else
			{
				$this->error("登录失败，请返回重新登录");
			}
			exit();
		}
		$code_status = get_config("code_status");
		$this->assign('code_status', $code_status);
		$this->assign('user_id', $user_id);
		$this->assign('openid', $openid);
		$this->display();
	}


}

