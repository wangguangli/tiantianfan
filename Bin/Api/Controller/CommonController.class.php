<?php

namespace Api\Controller;
header("Content-type: text/html; charset=utf-8"); // tp输出的编码不是utf8，模板编码是。
use Clas\Orderin;
use Think\Controller;

class CommonController extends Controller
{

	protected $user = array();
	protected $user_id = 0;

	public function __construct()
	{
		parent::__construct();

		_error_log('________post_start___________');
		_error_log($_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
		_error_log(I());
		_error_log('________post_end_____________');

		/**
		 * 公共参数处理
		 * apitype            接口类型，app,wap,pc,mini-小程序,后台的话，直接通过来源判断
		 * appname            app英文名，如chileme
		 * apptime            app请求时间，10位时间戳，加密时，去掉后两位
		 * appos            app当前运行系统，ios和android
		 * appphone            app当前运行手机及型号，如 apple 6
		 * appversion        app当前版本号，如 1.0.5
		 * appsign            app签名，md5小写
		 * identify            唯一标识，安卓取IMEI，IOS取uuid。uuid需保持唯一不变（存储到钥匙串、再从钥匙串获取）
		 *
		 * appsign = md5(apitype+appname+apprnd+apptime后2位)
		 * 此接口会判断100秒 内有效
		 */
		$appname = I("appname");
		$appos = I("appos");
		$appphone = I("appphone");
		$appversion = I("appversion");
		$apitype = I("apitype", '');
		$apptime = I("apptime");
		$appsign = I("appsign");
		$apptime = $apptime ? $apptime : time();
		$identify = I("identify");

		$app_verify = "app_verify_" . $apitype;
		$app_verify = get_config($app_verify);

		// $apitypeArr = array("app", "wap", "pc", "admin");
		$apitypeArr = array("app", "wap", "pc", "mini", "admin");
		if (!in_array($apitype, $apitypeArr) && $app_verify)
		{
			jsonout('API类型无效，请检查后提交');
		}
		// 自动下线操作 --APP
		$single_login = get_config("single_login");
		$single_login = $single_login > 0 ? 1 : 0;
		if (I("user_id") > 0 && $apitype == 'app' && $identify && ACTION_NAME != 'login' && $single_login == 1)
		{
			$where['id'] = I("user_id");
			$userInfo = get_user_info($where, "*", 1);
			if (!$userInfo || $userInfo['is_del'] == 1)
			{
				jsonout('账户不存在或状态异常，请联系客服', 2);
			}
			if (!$userInfo['app_identify'])
			{
				M("user")->where("id=" . $userInfo['id'])->setField("app_identify", $identify);
			}
			else
			{
				if ($userInfo['app_identify'] != $identify)
				{
					jsonout('账号已在其他地方登录，本账户自动退出', 2);
				}

			}
		}


		if ($app_verify)
		{
			$apptime2 = substr($apptime, 0, 8);
			$appname2 = get_config("app_name");
			$apprnd2 = get_config("app_rnd");
			$sign = md5($apitype . $appname2 . $apprnd2 . $apptime2);

			_error_log('sign :'.$sign);
			_error_log('appsign :'.$appsign);

			if ($sign != $appsign || !$appsign)
			{
				jsonout('请求无效，请检查后提交1');
			}

			if ($apitype == "app")
			{
				// APP请求
				// 当前时间 30s，有效10s，40s过期 41.42.
				if ($apptime > time() + 10)
				{
					// 误差不能超过10s
					jsonout('请求无效，请检查后提交2');
				}
				if ($apptime + 100 < time())
				{
					jsonout('请求无效，请检查后提交3');
				}
			}
			elseif (in_array($apitype, $apitypeArr))
			{
				// 非APP的话，有效时间最长一周(避免超时)
				if ($apptime > time() + 10)
				{
					jsonout('请求无效，请检查后提交4');
				}
				// 一周有效期 
				if ($apptime + 604800 < time())
				{
					jsonout('请求无效，请检查后提交5');
				}
			}
			else
			{
				jsonout('请求无效，请检查后提交6');
			}
		}
		$this->user_id = I("user_id", 0);
		if ($this->user_id)
		{
			$where['id'] = $this->user_id;
			$userInfo = get_user_info($where, "*", 1);
			$userInfo['cre_date'] = date("Y-m-d H:i:s", $userInfo['cre_time']);
			if ($userInfo['pay_password'])
			{
				$userInfo["is_paypwd"] = "1";
			}
			else
			{
				$userInfo["is_paypwd"] = "0";
			}
			// 删除密码
			unset($userInfo["password"]);
			unset($userInfo["pay_password"]);

			$userInfo["share_url"] = C("C_HTTP_HOST") . "/index.php/M/Index/reg?tuijian=" . $userInfo["phone"];

			$this->user = $userInfo;
            $time = time() - get_config( 'order_end_time' ) * 60;//30分钟自动取消积分订单
            $where1[ 'user_id' ] = array( 'eq',
                $this->user_id
            );
            $where1[ 'order_status' ] = array( 'eq',
                0
            );
            $where1[ 'add_time' ] = array( 'elt',
                $time
            );
            $order = M( 'order' )->where( $where1 )->select();
            $orderIn = new Orderin();
            foreach ( $order as $k => $v ){
                $re = $orderIn->cancelOrder( $v[ 'id' ] );
                if ( $re && $v['order_type'] == 11){
                    M( 'user' )->where( 'id=' . $this->user_id )->setDec( 'wait_score',$v[ 'actual_order_total' ] );
                }
            }

		}

	}

}