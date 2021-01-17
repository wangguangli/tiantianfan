<?php

namespace M\Controller;
header("Content-type: text/html; charset=utf-8");

use Think\Controller;

class WeixinController extends Controller
{
	//获取access_token


	protected $wxconfig = array();

	public function __construct()
	{
		parent::__construct();
		$this->wxconfig = M('config_pay3')->where("type=3")->find();
	}

	// 网页授权登录获取 OpendId $Url返回的地址
	public function GetOpenid()
	{
		//通过code获得openid
		$code = I("code");
		if (!$code)
		{
			//触发微信返回code码
			$baseUrl = urlencode($this->get_url());
			$url = $this->__CreateOauthUrlForCode($baseUrl); // 获取 code地址
			Header("Location: $url"); // 跳转到微信授权页面 需要用户确认登录的页面
			exit();
		}
		else
		{
			// 上面跳转, 这里跳了回来
			//获取code码，以获取openid
			$data = $this->getOpenidFromMp($code);
			$data2 = $this->GetUserInfo($data['access_token'], $data['openid']);//获取微信openidId的返回值

			// 到这里的，有可能是登录，也有可能是只为了获取openid用于支付，所以要做个判断
			// 获得了openid，此时要考虑是否登录过了，如果登录了，则把用户对应的openid更新。
			// 如果没登录，则也找找是否有对应的用户
			//      如果有，则缓存用户信息； 如果没有，则看看(配置)是否让用户输入手机号进行注册。
			// 都要 更新临时openid
			if (session('user_id'))
			{
				$where_u['id'] = session('user_id');
				$data_u['openid'] = $data['openid'];
				$data_u['openid_temp'] = $data['openid'];
				M("user")->where($where_u)->save($data_u);

				if (session("refer"))
				{
					$loginUrl = session("refer");
					session("refer", "");
				}
				else
				{
					$loginUrl = U('User/index');
				}
				redirect($loginUrl);
				exit;
			}
			else
			{
				$wx_login_bind_phone = get_config("wx_login_bind_phone");
				$where_u['openid'] = $data['openid'];
				$userInfo = M("user")->where($where_u)->find();

				if ($userInfo)
				{
					// 有用户，没手机号，要强制绑定手机号
					if ($wx_login_bind_phone == 1 && !$userInfo['phone'])
					{
						session('wx_nickname', $data2['nickname']);
						session('wx_headimgurl', $data2['headimgurl']);
						$bindUrl = U('Index/bind', array('user_id' => $userInfo['id'], 'openid' => $data['openid']));
						redirect($bindUrl);
					}
					else
					{
						if (session("refer"))
						{
							$loginUrl = session("refer");
							session("refer", "");
						}
						else
						{
							$loginUrl = U('User/index');
						}
						if (!$userInfo['nickname'] && $data2['nickname'])
						{
							$data_u['nickname'] = $data2['nickname'];
						}
						if (!$userInfo['headimgurl'] && $data2['headimgurl'])
						{
							$data_u['headimgurl'] = $data2['headimgurl'];
						}
						if ($data_u)
						{
							$where_u2['id'] = $userInfo['id'];
							M("user")->where($where_u2)->save($data_u);
						}
						session("refer", "");
						session('share_uid', '');
						session('wx_nickname', '');
						session('wx_headimgurl', '');
						session('user_id', $userInfo['id']);
						$this->success('登录成功', $loginUrl);
						exit;
					}
				}
				else
				{
					// 没有用户，是否要绑定手机号，是-则跳转绑定，否-则添加新用户
					if ($wx_login_bind_phone)
					{
						session('wx_nickname', $data2['nickname']);
						session('wx_headimgurl', $data2['headimgurl']);
						$bindUrl = U('Index/bind', array('user_id' => 0, 'openid' => $data['openid']));
						redirect($bindUrl);
					}
					else
					{
						$data_add['openid'] = $data['openid'];
						$data_add['openid_temp'] = $data['openid'];
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
						if ($data2['nickname'])
						{
							$data_add['nickname'] = $data2['nickname'];
						}
						if ($data2['headimgurl'])
						{
							$data_add['headimgurl'] = $data2['headimgurl'];
						}
						$res = M("user")->add($data_add);

						if ($res !== false)
						{
							if (session("refer"))
							{
								$loginUrl = session("refer");
								session("refer", "");
							}
							else
							{
								$loginUrl = U('User/index');
							}
							session('share_uid', '');
							session('user_id', $res);
							$this->success("登录成功", $loginUrl);
						}
						else
						{
							$this->error("操作失败，请重新操作");
						}
						exit;
					}
				}
			}
		}
	}


	/**
	 * 获取当前的url 地址
	 * @return type
	 */
	private function get_url()
	{
		$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
		$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
		return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
	}

	/**
	 *
	 * 通过code从工作平台获取openid机器access_token
	 * @param string $code 微信跳转回来带上的code
	 * @return openid
	 */
	public function GetOpenidFromMp($code)
	{
		//通过code换取网页授权access_token  和 openid
		$url = $this->__CreateOauthUrlForOpenid($code);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$res = curl_exec($ch);
		$data = json_decode($res, true);//取出openid access_token
		curl_close($ch);
		return $data;
	}

	/**
	 *
	 * 通过access_token openid 从工作平台获取UserInfo
	 * @return openid
	 */
	public function GetUserInfo($access_token, $openid)
	{
		// 获取用户 信息
		$url = $this->__CreateOauthUrlForUserinfo($access_token, $openid);
		$ch = curl_init();//初始化curl
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$res = curl_exec($ch);//运行curl，结果以jason形式返回
		$data = json_decode($res, true);//取出openid access_token
		curl_close($ch);

		// 获取看看用户是否关注了 你的微信公众号， 再来判断是否提示用户 关注
		/*$access_token2 = $this->get_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token2&openid=$openid";
		$subscribe_info = httpRequest($url,'GET');
		$subscribe_info = json_decode($subscribe_info,true);
		$data['subscribe'] = $subscribe_info['subscribe'];*/

		return $data;
	}


	public function get_access_token()
	{
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->wxconfig[appid]}&secret={$this->wxconfig[key2]}";
		$return = httpRequest($url, 'GET');
		$return = json_decode($return, 1);
		return $return['access_token'];
	}

	/**
	 *
	 * 构造获取code的url连接
	 * @param string $redirectUrl 微信服务器回跳的url，需要url编码
	 *
	 * @return 返回构造好的url
	 */
	private function __CreateOauthUrlForCode($redirectUrl)
	{
		$urlObj["appid"] = $this->wxconfig['appid'];
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		// $urlObj["scope"] = "snsapi_base";
		$urlObj["scope"] = "snsapi_userinfo";
		$urlObj["state"] = "STATE" . "&connect_redirect=1#wechat_redirect";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
	}

	/**
	 *
	 * 构造获取open和access_toke的url地址
	 * @param string $code，微信跳转带回的code
	 *
	 * @return 请求的url
	 */
	private function __CreateOauthUrlForOpenid($code)
	{
		$urlObj["appid"] = $this->wxconfig['appid'];
		$urlObj["secret"] = $this->wxconfig['key2'];
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
	}

	/**
	 *
	 * 构造获取拉取用户信息(需scope为 snsapi_userinfo)的url地址
	 * @return 请求的url
	 */
	private function __CreateOauthUrlForUserinfo($access_token, $openid)
	{
		$urlObj["access_token"] = $access_token;
		$urlObj["openid"] = $openid;
		$urlObj["lang"] = 'zh_CN';
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/userinfo?" . $bizString;
	}

	/**
	 *
	 * 拼接签名字符串
	 * @param array $urlObj
	 *
	 * @return 返回已经拼接好的字符串
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			if ($k != "sign")
			{
				$buff .= $k . "=" . $v . "&";
			}
		}

		$buff = trim($buff, "&");
		return $buff;
	}


	/**
	 * 微信事件推送接收参数
	 */
	public function getxml()
	{

		define("TOKEN", "zilvjun");

//        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
//            file_put_contents('weixin_log.txt', "IP=" . $_SERVER['REMOTE_ADDR'] . PHP_EOL, FILE_APPEND); //记录访问IP到log日志
//            file_put_contents('weixin_log.txt', "QUERY_STRING=" . $_SERVER['QUERY_STRING'] . PHP_EOL, FILE_APPEND);//记录请求字符串到log日志
//            file_put_contents('weixin_log.txt', '$_GET[echostr])=' . htmlspecialchars($_GET['echostr']) . PHP_EOL, FILE_APPEND); //记录是否获取到echostr参数
//
//            header('content-type:text');   //加这一句好像还不行，
//            ob_clean();       //这一句加上就可以了。
//            exit(htmlspecialchars($_GET['echostr']));      //把echostr参数返回给微信开发者后台
//        }
		$href = "./ThinkPHP/Library/Vendor/Wxpay/lib";
		require_once $href . '/WxPayData.php';
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];

		//解析xml
		$array = xmlToArr($xml);
		//解析数组  根据openid判断是新用户还是老用户  新的用户就根据相应值进行三级关系设置

		//判断是什么事件
		$openid = $array['FromUserName'];//新用户opid

		if (!empty($openid))
		{
			$where['openid'] = $openid;
			$user = M('user')->where($where)->find();
			if ($user)
			{
				;
				//有这个人 不操作
			}
			else
			{
				$event = $array['Event'];//事件类型
				if ($event == 'Scan')
				{
					$newUserId = $array['EventKey'];//自己的id
				}
				elseif ($event == 'subscribe')
				{
					//关注之后的
					$user_id = explode('_', $array['EventKey']);
					$newUserId = $user_id[1];//谁邀请的你
				}
				elseif ($event = 'unsubscribe')
				{
					exit;
				}

				//没有这个人  进行三级设置 获取上级id并新建用户
				if (empty($newUserId))
				{
					//进行用户新增
					$newUser['openid'] = $openid;
					$newUserAdd = M('user')->add($newUser);
					session('user_id', $newUserAdd);//存入session
				}
				else
				{
					//查询最低的职能是什么
					$remit['type'] = 2;
					$remitInfo = M('user_medal')->where($remit)->order('condition ASC')->select();
					$medal = $remitInfo[0];//最低的职能
					$medalId = $medal['id'];
					//查询上级有没上级 和上上级
					$top = M('user')->find($newUserId);
					$newUser = array(
						'openid' => $openid,
						'first_leader' => $newUserId,
						'second_leader' => $top['first_leader'],
						'third_leader' => $top['second_leader'],
						'cre_time' => time(),
						'ip' => get_client_ip(),
						'remit' => $medalId,

					);

					$newUserAdd = M('user')->add($newUser);//成绩关系结束  查询要邀请了多少人了是否能够晋级
					session('user_id', $newUserAdd);//存入session
					$reme = M('user_medal')->where('type=2')->select();
					$first['first_leader'] = $newUserId;
					$num = M('user')->where($first)->count();//共邀请人
					$remid = array();
					foreach ($reme as $k => $v)
					{
						$remid[$v['id']] = $v['condition'];
					}
					$remid = asort($remid);
					$man_reme = array();
					foreach ($remid as $k => $v)
					{
						if ($num > $v)
						{
							$man_reme[$k]['id'] = $k;
						}
					}
					//最后一个值就是要还的等级
					if (empty($man_reme))
					{
						//不操作
					}
					else
					{
						//取出最后一个元素
						$new = end($man_reme);
						if ($top['remit'] == $new['id'])
						{
							//不操作
						}
						else
						{
							$save['remit'] = $new['id'];//晋级
							$saveWhere['id'] = $top['id'];
							M('user')->where($saveWhere)->save($save);
						}
					}
					//给上级发推送  谁成为了你的下级
					$content = '一位新用户接受了你的邀请，快去我的影响力看看吧';
					$user_id = $newUserId;
					$index = new IndexController();
					$index->sendmsg($content, $user_id, 1, 0, 0);
				}

				$all = M('user')->select();
				foreach ($all as $k => $v)
				{
					if ($v['id'] == $newUserAdd)
					{
						$rank = $k + 1;
					}
				}
				//查询标语
				$configWhere['name'] = 'welcome';
				$config = M('config_tips')->where($configWhere)->find();
				$new = str_replace('code', $rank, $config['val']);//要发送的话
				//看看是否发送过了  没发过就发送
				$sendWelcome['user_id'] = $newUserAdd;
				$welcome = M('welcome')->where($sendWelcome)->find();
				if (!$welcome)
				{
					$welcome = array(
						'user_id' => $newUserAdd,
						'create_time' => time(),
						'content' => $new,
					);
					M('welcome')->add($welcome);
					$index = new IndexController();

					$index->sendmsg($new, $newUserAdd, 1, 0, 0);
				}
			}
		}

	}

	public function index()
	{
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->wxconfig[appid]}&secret={$this->wxconfig[key2]}";
		$date = postcurl($url);
		$access_token = $date['access_token'];
		return $access_token;
	}


	public function test()
	{
		echo phpinfo();
	}


	//拼接参数，带着access_token请求创建菜单的接口
	public function createmenu()
	{


		$data = '{
                 "button":[
                 {
                              "name":"签到打卡",
                              "sub_button":[
                                {
                                   "type":"view",
                                   "name":"目标设定",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/targetEject"
                                },
                                {
                                   "type":"view",
                                   "name":"7天挑战赛",
                                   "url":"http://zilvjun.yuanzhihang.com/M/Match/comeSevenMatch"
                                },
                                {
                                   "type":"view",
                                   "name":"21天习惯养成",
                                   "url":"http://zilvjun.yuanzhihang.com/M/Match/twoMatchList"
                                },
                                {
                                   "type":"view",
                                   "name":"好友挑战赛",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/matchFriend"
                                },
                                  {
                                   "type":"view",
                                   "name":"排行榜",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/ranking?status=1&type=4"
                                }
                                ]
                  },{

                        "name":"学习课程",
                              "sub_button":[
                                {
                                   "type":"view",
                                   "name":"自律训练营一阶",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/projectDetail?id=-1"
                                },
                                     {
                                   "type":"view",
                                   "name":"自律训练营二阶",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/projectDetail?id=-2"
                                },
                                     {
                                   "type":"view",
                                   "name":"线下课程",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/projectDetail?id=-3"
                                },
                                     {
                                   "type":"view",
                                   "name":"推荐课程",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/project"
                                }
                                ]
                      },{

                        "name":"我的",
                              "sub_button":[
                                {
                                   "type":"view",
                                   "name":"更好的自己",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/dream.html"
                                },
                                     {
                                   "type":"view",
                                   "name":"我的自律日志",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/sign.html"
                                },
                                     {
                                   "type":"view",
                                   "name":"我的影响力",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/fensi"
                                },
                                     {
                                   "type":"view",
                                   "name":"积分商城",
                                   "url":"http://zilvjun.yuanzhihang.com/M/Goods/goodsList"
                                },
                                   {
                                   "type":"view",
                                   "name":"竞赛活动",
                                   "url":"http://zilvjun.yuanzhihang.com/M/User/match?type=1"
                                }
                                ]
                      }
                      

            ]

             }';
		$access_token = $this->index();
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
		$a = postcurl($url, $data);
		print_r($a);
	}


	/**
	 * 预览活动内容
	 */
	public function code()
	{
		$id = I('id');
		$model = M('goods_model')->find($id);
		if (!$model)
		{
			$this->error('该模板已经停用');
		}
		$this->assign('content', htmlspecialchars_decode($model['content']));
		$this->display('Weixin/codeImage');
	}

	/**
	 * 预览活动内容
	 */
	public function goods_detail()
	{
		$id = I('id');
		$goods = M('goods')->find($id);
		if (!$goods)
		{
			$this->error('没有此商品');
		}
		$shopInfo = M("shop")->where("id=" . $goods['shop_id'])->find();
		$images = json_decode($goods["images"]);
		$this->assign('content', htmlspecialchars_decode($goods['content']));
		$this->assign('images', $images);
		$this->assign('shop', $shopInfo);
		$this->display('Weixin/goods_detail');
	}


	/**
	 * 获取相关订阅号的openid
	 */
	public function getAgentOpenid()
	{
		//通过code获得openid
		$code = I("code");
		if (!$code)
		{
			//触发微信返回code码
			$baseUrl = urlencode($this->get_url());
			$url = __CreateOauthUrlForCode($baseUrl); // 获取 code地址
			Header("Location: $url"); // 跳转到微信授权页面 需要用户确认登录的页面
			exit();
		}
		else
		{
			// 上面跳转, 这里跳了回来
			//获取code码，以获取openid
			$data = getOpenidFromMp($code);
			$data2 = GetUserInfo($data['access_token'], $data['openid']);//获取微信openidId的返回值
			//判断openid是否存在
			if (session("refer_wxlogin"))
			{
				redirect(session("refer_wxlogin"));
			}
			else
			{
				$loginUrl = U('User/index');
				redirect($loginUrl);
			}
			exit;
		}
	}
}