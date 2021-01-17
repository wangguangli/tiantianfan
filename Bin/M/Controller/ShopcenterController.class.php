<?php

namespace M\Controller;
header("Content-type: text/html; charset=utf-8");

use Clas\Goods;
use Think\Controller;
use Clas\User;
use Clas\Shop;
use Clas\Orderin;

class ShopcenterController extends CommonController
{
	protected $shop_id;

	public function __construct()
	{
		parent::__construct();
		if (I("shop_id"))
		{
			session("shop_id", I("shop_id"));
		}
		$this->shop_id = session("shop_id");
	}

	// 商家中心
	public function index()
	{
		// 商家列表点击进来
		$obj = new Shop(0, 'shop', $this->user_id);
		$data['shop_id'] = $this->shop_id;
		$ok = $obj->shopDetail($data);
		if (!is_array($ok))
		{
			$this->error($ok);
		}
		$where['id'] = $this->user_id;
		$user = get_user_info($where);
		$this->assign('user', $user);
		$this->assign('data', $ok[2]['shop']);
		$this->display();
	}


	// 订单列表
	public function orders()
	{
		$user_id = $this->user_id;    // 商家UID
		$obj = new Shop($this->shop_id, 'shop', $user_id);
        $order_status = I("order_status", -1);
        $page = I('page',1);
        $paging['page'] = $page;
        if ($order_status >= 0 && is_numeric($order_status))
        {
            $data['order_status'] = $order_status;
        }

		$data['shop_id'] = $this->shop_id;
		$data['page'] = $page;
		$num = 10;
		$ok = $obj->get_shopOrderList($data);

		/*foreach ($ok as $k=>$v){
		    $ok[$k]['order_status_name'] = get_order_status($v['order_status']);
        }*/
        $max_page = $ok['max_page'];
		$res['list'] = $ok;
		$res['last_id'] = get_last_id($ok, 'id', $num);
		$res['max_page'] = $max_page;
		$this->assign('data', $res);
		$this->assign('list', $ok['list']);
		$this->assign('order_status', $order_status);
		$this->assign('max_page', $max_page);
		if($page < 2){
            $this->display();
        }else{
            $this->display('orders_more');
        }

	}

	// 提现
	public function moneyout()
	{
		$user = M('user')->find($this->user_id);
		if (IS_POST)
		{
			$data = I();
			$data["user_id"] = $this->user_id;
//			$data['shop_id'] = $this->shop_id;
			$obj = new User($this->user_id);
			$ok = $obj->Tixian($data);
			if (is_array($ok))
			{
				jsonout($ok[0], $ok[1], $ok[2]);
			}
			jsonout($ok);
		}


		//还是查询用户的
		$bank_id = I("bank_id", 0);
		$bank = M('user_bank')->find($bank_id);

//		$shop = M('shop')->where('user_id=' . $this->user_id)->find();
		if (empty($user['pay_password']))
		{
			$is_psw = 0;
		}
		else
		{
			$is_psw = 1;
		}
//		$this->assign('shop_id', $this->shop_id);
		$this->assign('user_id', $this->user_id);
		$this->assign('bank', $bank);
		$this->assign('is_psw', $is_psw);
		$this->assign('user', $user);
//		$this->assign('shop', $shop);
		$this->display();;

	}

	//店铺信息
	public function shop()
	{

		$where_u["id"] = $this->user_id;
		$user = get_user_info($where_u);
        $where_s["id"] = $this->shop_id;

        if(session('applyShop')){
            $shop = session('applyShop');
        }else{
            $shop = get_user_info($where_s, '', '', 2);
        }
        $shop_fee = get_config_shop_fee($shop['shop_fee_id']);
        $shop_fee = '服务费'.$shop_fee['percent1'].'%，返还客户'.$shop_fee['percent2'].'%，返还商家'.$shop_fee['percent3'].'%';

        session('applyShop',null);
        if(!is_numeric($shop['province_id'])){
            $province_id = 0;
        }else{
            $province_id = $shop['province_id'];
        }
        if(!is_numeric($shop['city_id'])){
            $city_id = 0;
        }else{
            $city_id = $shop['city_id'];
        }
        if(!is_numeric($shop['district_id'])){
            $district_id = 0;
        }else{
            $district_id = $shop['district_id'];
        }


		$industry = M('industry')->select();
		if (IS_GET)
		{
			$data = I();
			if ($data)
			{
				$shop['city'] = $data['city'];
				$shop['longitude]'] = $data['longitude]'];
				$shop['latitude'] = $data['latitude'];
				$shop['address'] = $data['address'];
			}
		}
		if ($shop['list_img'])
		{
			$list_img = json_decode($shop['list_img']);
            $shop['list_img'] = implode('|',$list_img);
		}
        if($shop['images']){
            $list_img = explode('|',$shop['images']);
        }
       // print_r($list_img);exit();
		$this->assign('list_img', $list_img);
		$this->assign('data', $shop);
		$this->assign('user', $user);
		$this->assign('industry', $industry);
		$this->assign('user_id', $this->user_id);
        $this->assign('province_id', $province_id);
        $this->assign('city_id', $city_id);
        $this->assign('district_id', $district_id);
        $this->assign('shop_fee', $shop_fee);
		$this->display();
	}

	//申请商家
	public function applyshop()
	{   $shop = session('applyShop');
        session('applyShop',null);

		if (IS_POST)
		{
			$obj = new Shop(0, 'shop', $this->user_id);
			$ok = $obj->add();
			if (!is_numeric($ok))
			{
				$this->error($ok);
			}
			$this->success('提交成功请等待审核', U('user/index'));
			exit();
		}
		if (IS_GET)
		{

		    if(!$shop['shop_id']){
		        $shop['id'] = 0;
            }
			$data = I();
		    if($shop['images']){
		        $shop['new_images'] = explode('|',$shop['images']);
            }
			$shop['city'] = $data['city'];
			$shop['longitude'] = $data['longitude'];
			$shop['latitude'] = $data['latitude'];
			$shop['address'] = $data['address'];

            $list = M("config_shop_fee")->order("id asc")->select();
            foreach ($list as $k=>&$v){
                $v['name'] = '服务费'.$v['percent1'].'%，返还客户'.$v['percent2'].'%，返还商家'.$v['percent3'].'%';
            }
		}
		else
		{
			$shop['address'] = 0;
		}
        if(!is_numeric($shop['province_id'])){
            $province_id = 0;
        }else{
            $province_id = $shop['province_id'];
        }
        if(!is_numeric($shop['city_id'])){
            $city_id = 0;
        }else{
            $city_id = $shop['city_id'];
        }
        if(!is_numeric($shop['district_id'])){
            $district_id = 0;
        }else{
            $district_id = $shop['district_id'];
        }
		$industry = M('industry')->select();
		$this->assign('industry', $industry);
		$this->assign('data', $shop);
		$this->assign('user_id', 0);
		$this->assign('shop_fee', $list);
		$this->assign('province_id', $province_id);
		$this->assign('city_id', $city_id);
		$this->assign('district_id', $district_id);
		$this->display();
	}

	//编辑店铺信息
	public function editShop()
	{
		$id = I('id');
		$obj = new Shop($id, 'shop', $this->user_id);
		$ok = $obj->updateInfo();
		if ($ok != 'ok')
		{
			$this->error($ok);
		}
		else
		{
			$this->success('修改成功');
		}
	}

	public function location()
	{
	   session('applyShop',I());//将上个界面填写的数据存放session
		if (IS_GET)
		{
			$id = I('shop_id', 0);
			if ($id)
			{
				$shop = M('shop')->find($id);
			}
			else
			{
				$shop['longitude'] = '116.593919';
				$shop['latitude'] = '35.423191';
			}
			$this->assign('shop', $shop);
		}

		$this->display();
	}

	// 基本信息
	public function info()
	{
		$this->display();
	}

	// 报单
	public function baodan()
	{
		if (IS_POST)
		{
			$order_id = I("order_id", 0, 'intval');
			if (!$order_id)
			{
				$phone = I("phone", 0);
				$shop_id = I("shop_id", 0);
				$money = I("money", 0);
				$pay_type = I("pay_type", '');
				$type = I("type", 1);

				if (!$phone || !$shop_id)
				{
					jsonout('缺少主要参数', 1);
				}
				if (!$money || $money <= 0)
				{
					jsonout('消费金额必须大于0', 1);
				}
				if (!$pay_type)
				{
					jsonout('请选择正确的支付方式', 1);
				}

				$where_u['phone'] = $phone;
				$userInfo = get_user_info($where_u);
				if (!$userInfo || $userInfo['status'] != 1)
				{
					jsonout('用户账号异常，请联系客服', 1);
				}

				// 商家报单/代理报单时，根据商家/代理的服务费比例*消费金额 == 支付金额
				if ($type < 1 || $type > 2)
				{
					jsonout('操作有误，请重新登录', 1);
				}
				if ($type == 2)
				{
					// 代理UID
					$user_id = get_user_field($shop_id, "user_id", 0, 3);
				}
				else
				{
					// 商家UID
					$user_id = get_user_field($shop_id, "user_id", 0, 2);
				}
				if ($userInfo['id'] == $user_id)
				{
					jsonout('不可给自己报单', 1);
				}

				if ($type == 1)
				{
					$utype = 2;
					$order_type = 2;
				}
				elseif ($type == 2)
				{
					$utype = 3;
					$order_type = 4;
				}
				$where_sd["id"] = $shop_id;
				$info = get_user_info($where_sd, '', '', $utype);
				if (!$info || $info['status'] != 1)
				{
					jsonout('本账号异常，请联系客服', 1);
				}

				if (!$info['shop_fee_id'])
				{
					jsonout('服务费比例异常，请联系客服', 1);
				}
				$feeInfo = get_config_shop_fee($info['shop_fee_id']);
				if (!$feeInfo)
				{
					jsonout('服务费比例异常，请联系客服', 1);
				}

				$total_commodity_price = round($feeInfo['percent1'] / 100 * $money, 2);
				if ($total_commodity_price <= 0)
				{
					jsonout('上交服务费太低，不可报单', 1);
				}

				$order_no = make_order_no();
				$data["order_no"] = $order_no;
				$data["user_id"] = $userInfo['id'];
				$data["total_commodity_price"] = $total_commodity_price;
				$data["shop_note"] = $money;    // （在报单时，此值表示用户消费总额）
				$data["payment_type"] = $pay_type;
				$data["shipping_type"] = 0;
				$data["add_time"] = time();
				$data["ip"] = get_client_ip();
				$data["order_type"] = $order_type;
				$data["shop_id"] = $shop_id;
				$order_id = M("order")->add($data);
				if (!$order_id)
				{
					jsonout('订单创建失败，请重新买单', 1);
				}
			}

			// 开始支付
			$payData['order_id'] = $order_id;
			$payData['pay_type'] = $pay_type;

			$Orderin = new Orderin($user_id);
			$data = $Orderin->payOrder($payData);
			if ($data[0] < 1)
			{
				// 成功 _ 正常
				$pay_type = I("pay_type");
				if ($pay_type == "yepay")
				{
					$notice = array('notice' => '账户支付成功');
					jsonout('支付成功', 0, $notice);
				}
				elseif ($pay_type == "wxpay")
				{
					// 微信支付
					$notice = array('notice' => '拉起APP进行支付', 'wx_url' => $data[1]);
					jsonout('进行APP支付', 0, $notice);
				}
				elseif ($pay_type == "alipay")
				{
					// 支付宝支付
					$notice = array('notice' => '拉起APP进行支付', 'ali_url' => $data[1]);
					jsonout('进行APP支付', 0, $notice);
				}
				elseif ($pay_type == "wxmp")
				{
					// 支付宝支付

					$notice = array('notice' => '拉起微信进行支付', 'wx_url' => $data[1]);
					jsonout('进行微信支付', 0, $notice);
				}
				elseif ($pay_type == "aliwap")
				{
					// 支付宝支付
					//$notice = array('notice'=> '拉起支付宝进行支付', 'ali_url'=>$data[1] );
					//jsonout('进行微信支付', 0, $notice);
					var_dump($data[1]);
				}
				else
				{
					jsonout('请选择正确的支付方式', 1);
				}
			}
			else
			{
				jsonout($data[1], 1);
			}

		}
		else
		{
			$where_u["id"] = $this->user_id;
			$user = get_user_info($where_u,'*',1);
			if (empty($user['pay_password']))
			{
				$is_psw = 0;
			}
			else
			{
				$is_psw = 1;
			}
			$where['user_id'] = $this->user_id;
			$shop = get_user_info($where, 0, 0, 2);
			$this->assign('is_psw', $is_psw);
			$this->assign('shop_id', $shop['id']);
			$this->display();
		}

	}

	public function code()
	{
        $shop_id = session('shop_id');
        $shop = M('shop')->where('id='.$shop_id)->find();
        if($shop['shop_qrcode']){
            $this->assign('shop_qrcode',C("C_HTTP_HOST").$shop['shop_qrcode']);
        }else{
            header("Content-Type: text/html; charset=UTF-8");
            Vendor('phpqrcode.phpqrcode');
            $qr_path = "./upload/";
            if(!file_exists($qr_path.'shop_qrcode/')){
                mkdir($qr_path.'shop_qrcode/', 0700,true);//判断保存目录是否存在，不存在自动生成文件目录
            }

            $url = C("C_HTTP_HOST") . U('Index/pay') . '?shop_id=' . $shop_id;
            $url = urldecode($url);
            $time = time();
            $filename = 'upload/shop_qrcode/' . $shop_id . '_' . $time . '.png';
            $object = new \QRcode();
            // // ob_clean();//这个一定要加上，清除缓冲区
            $object->png($url, $filename, 'Q', '6', '2');
            imagepng($url, $filename);   // 生成图片之后，保存
            M('shop')->where('id='.$shop_id)->setField('shop_qrcode','/'.$filename);
            $this->assign('shop_qrcode',C("C_HTTP_HOST").'/'.$filename);
        }
		$this->display();
	}

	// 额度转换
	public function swap()
	{
		if (IS_POST)
		{
			$money = I("money", 0);
			if ($money <= 0)
			{
				$this->error('请输入转换金额');
			}

			$where['id'] = $this->shop_id;
			$shopInfo = get_user_info($where, '', '', 2);
			if (!$shopInfo || $shopInfo['status'] != 1)
			{
				$this->error("商家信息异常，请重新登录");
			}
			if ($shopInfo['money'] <= 0)
			{
				$this->error("账户余额不足");
			}
			if ($shopInfo['money'] < $money)
			{
				$this->error("账户额度小于转换额度，请修改后转换");
			}
			$x_shop = get_config_project("x_shop");    //
			if ($x_shop <= 0)
			{
				$this->error("转换比例设置异常，请联系客服");
			}
			// 减少账户余额，增加交易账户额度（此额度要缩小相应的比例）
			$shop_money = round($money / $x_shop, 2);
			if ($shop_money <= 0)
			{
				$this->error("转换后额度<=0，请重新转换");
			}
			$model = M();
			$model->startTrans();
			$rs1 = change_money_log($shopInfo['user_id'], 1, "money", 0, $money, 7, 6, "账户余额转换交易账户", 2, 2, 1);
			$rs2 = change_money_log($shopInfo['user_id'], 1, "shop_money", 0, $shop_money, 2, 6, "账户余额转换到交易账户", 1, 2, 1);

			if ($rs1 && $rs2)
			{
				$model->commit();
				$this->success("转换完成", U('index'));

			}
			else
			{
				$model->rollback();
				$this->error("转换失败，请重新转换");
			}
		}
		else
		{
			$this->display();
		}
	}


	// 旗下商家
	public function shops()
	{
		$this->display();
	}

	//获取地区
	function get_region_list()
	{
		if (!F('region_list'))
		{
			$region_list = M('region')->where('level<4')->getField('id,name,parent_id');
			F('region_list', $region_list);
		}
		return $region_list ? $region_list : F('region_list');
	}

	//返回数据
	public function get_city_json()
	{
		$list = get_region_list();
		$data = array();
		foreach ($list as $k => $v)
		{
			$data[$v['parent_id']][$v['id']] = $v['name'];
		}
		echo 'var region_list = ' . json_encode($data);
	}

	/**
	 * 图片上传
	 * @param string      handlename    控件名称
	 * @return  json
	 * @zd
	 */
	public function uploads()
	{
		$video = M('config')->where('name="video"')->getField('val');

		$video1 = ($video + '5') . "M";
		$video2 = ($video + '') . "M";


		@ini_set('post_max_size', $video1);
		@ini_set('upload_max_filesize', $video2);

		$handlename = I("handlename");

		if (!$handlename)
		{
			jsonout("缺少主要参数");
		}
		$video = I('video');

		if ($video)
		{
			$size = M('config')->where('name = "video"')->getField('val');

			$size = $size * 1024 * 1024;
			$is_video = 1;
			$rs = uploads($handlename, '', $size, '', '', $is_video);
			jsonout("视频上传", $rs[1], $rs[2]);
		}
		else
		{
			$rs = uploads($handlename);
			jsonout("图片上传", $rs[1], $rs[2]);
		}
	}

    /**
     * Notes:商家商品管理
     * User: WangSong
     * Date: 2020/8/6
     * Time: 17:25
     */
	public function management(){
        $shop_id = session('shop_id');
        $shop = new Goods();
        $ord = 'id desc';
        $where['shop_id'] = array('eq',$shop_id);
        $where['is_del'] = array('eq',0);
        $page = I("page", 1);
        $paging['page'] = $page;
        $num = 10;
        $list = $shop->goodsList($where, $ord, $num, $paging);
        $max_page = 1;
        $total = 0;
        if($page < 2){
            $numArr = $shop->getNums($where, $num);
            $max_page = $numArr['max_page'];
            $total = $numArr['total'];
        }
        $this->assign('list',$list);
        $this->assign('max_page',$max_page);
        $this->assign('total',$total);
        $this->assign('shop_id',$shop_id);
        if($page < 2){
            $this->display();
        }else{
            $this->display('management_more');
        }
    }

    /**
     * Notes:订单发货
     * User: WangSong
     * Date: 2020/8/7
     * Time: 14:06
     */
    public function delivery(){
        $order_id = I('order_id');
        $shop_id = $this->shop_id;
        $order_goods = M('order_goods')->where('order_id='.$order_id.' and shop_id='.$shop_id.' and order_status=1')->select();
        $order = M('order')->where('id='.$order_id)->find();
        $express = get_express();
        $this->assign('order',$order);
        $this->assign('order_goods',$order_goods);
        $this->assign('express',$express);
        $this->assign('shop_id',$shop_id);
        $this->display();
    }

    /**
     * Notes:订单详情
     * User: WangSong
     * Date: 2020/8/7
     * Time: 14:07
     */
    public function order_detail(){
        $shop = new Shop(0,'shop',session('user_id'));
        $re = $shop->shop_order_detail();
        $this->assign('order',$re['order']);
        $this->assign('order_goods',$re['order_goods']);
        $this->display();
    }
    public function addItem(){
        $user_id = session('user_id');
        $data = I();
        $this->assign('data',$data);
        $this->assign('user',$user_id);
        $this->display();
    }
    public function addImg(){
        $user_id = session('user_id');
        $this->display();
    }
    public function addSpecs(){
        $user_id = session('user_id');
        $this->display();
    }
    public function addDetails(){
        $user_id = session('user_id');
        $this->display();
    }
    public function addText(){
        $user_id = session('user_id');
        $this->display();
    }
}