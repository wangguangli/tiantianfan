<?php

namespace Clas;
class ExpressApi
{

	//public $session_id;
	public $amount;
	public $pwd;
	public $redirectUri;
	public $url;
	public $appkey;

	public function __construct()
	{
		//$this->session_id = session_id();
		//define('SESSION_ID',$this->session_id);
		$data = M('express_print_server')->where('is_start=1')->find();
		$this->amount = $data['amount'];
		$this->pwd = $data['pwd'];

		$this->appkey = $data['appkey'];
//        $this->url = 'http://v91.fengsutb.com/freeprint/platform/submitTrades.action';
		$this->url = $data['url'];
		$this->inquire_url = $data['inquire_url'];
	}

	/**
	 * @param $arr   订单号 order_no  int    收货人name  varchar     province 省 int
	 *                市    city     int     区县 distirct   int         街道 town
	 *                地址   address varchar  手机 phone     varchar      电话 tel varchar
	 *                 商品数量  goods_num  int  商品名称 goods_name varchar   备注  remark text
	 *
	 */
	public function express($arr)
	{
		$data['account'] = array(
			'account' => $this->amount,
			'pwd' => $this->pwd,
			'appkey' => $this->appkey,
		);
		$new_arr = array();
		foreach ($arr as $k => $v)
		{
			$new_arr[$k] = array(
				'tid' => $v['order_no'],
				'name' => $v['consignee'],
				'province' => get_region_name($v['province']),
				'city' => get_region_name($v['city']),
				'area' => get_region_name($v['district']),
				'town' => '',
				'address' => $v['address'],
				'mobile' => $v['phone'],
				'phone' => $v['tel'],
				'remark' => '',

			);

			foreach ($v['order_goods'] as $k1 => $v1)
			{
				$new_arr[$k]['orderBeans'][$k1] = array(
					'tid' => $v['order_no'],
					'oid' => $v1['id'],
					'num' => $v1['goods_num'],
					'name' => $v1['goods_name'],
					'property' => $v1['goods_spec_price_name']
				);
			}

		}

		$data['trades'] = $new_arr;
		$data_json = json_encode($data);
		$res = $this->http_curl($this->url, $data_json);
		return $res;
	}

	/**
	 * 查询接口
	 */
	public function inquire($arr)
	{
		$data['account'] = array(
			'account' => $this->amount,
			'pwd' => $this->pwd,
			'appkey' => $this->appkey,
		);
		$new_arr = array();
		foreach ($arr as $k => $v)
		{
			$new_arr[$k] = array(
				'tid' => $v,
			);
		}
		$data['trades'] = $new_arr;
		$data_json = json_encode($data);
		$res = $this->http_curl($this->inquire_url, $data_json);
		return $res;
	}

	/**
	 * post 请求
	 * @param $url
	 * @param string $postData
	 * @param string $res
	 * @return bool|string
	 */
	public
	function http_curl($url, $postData = '', $res = 'json')
	{
		//1.初始化curl
		$ch = curl_init();
		//2.设置curl的参数
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$param['param'] = $postData;

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

		//3.采集
		$output = curl_exec($ch);
		//4.关闭
		curl_close($ch);
		if ($res == 'json')
		{
			if (curl_error($ch))
			{
				//请求失败，返回错误信息
				return curl_error($ch);
			}
			else
			{
				//请求成功，返回信息
				return $output;
			}
		}
	}


}