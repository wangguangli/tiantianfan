<?php
namespace Api\Controller;

use Clas\User;
use Think\Model;

class UserinController extends CommonController
{


	/**
	 * 用户添加
	 * @param   string $phone 手机号
	 * @param   string $password 密码
	 * @param   string $repassword 确认密码
	 * @param   string $tuijian 推荐手机号
	 * @param   string $phone_code 验证码
	 * @return  json
	 * 此处一般指APP端注册
	 * @zd
	 */
	public function register()
	{
		$obj = new User();
		$ok = $obj->register_user();
		if (!is_numeric($ok)) {
			jsonout($ok);
		}
		jsonout("注册成功", 0);
	}

	/**
	 * 用户登录
	 * @param   string $phone 手机号
	 * @param   string $password 密码
	 * @return  json
	 * @zd
	 */
	public function login()
	{
		$phone = I("phone");
		$password = I("password");
		$login_type = I("login_type",1);
		if (!$phone || !check_phone($phone)) {
			jsonout("请输入正确的手机号");
		}

		$where["phone"] = $phone;
		$userInfo = get_user_info($where, "*", 1);
		if (!$userInfo || $userInfo["is_del"]) {
			jsonout("没有此用户");
		}
        if ($userInfo["status"] == 2) {
            jsonout("账户冻结，请联系客服");
        }
		if($login_type == 1){
            if (!$password) {
                jsonout("请输入密码");
            }
            $password = get_pwd($password);
            if ($userInfo["password"] !== $password) {
                jsonout("密码不正确");
            }

            unset($userInfo['password']);
            unset($userInfo['pay_password']);
        }else{
		    $phone_code = I("phone_code");
		    if(!$phone_code){
                jsonout("验证码不正确");
            }
            if (!check_phone_code($phone, $phone_code) ) {

                jsonout("验证码不正确");
            }
        }

		// 写入登录标识码 identify
		$identify = I("identify");
		if ($identify)
		{
			M("user")->where("id=".$userInfo['id'])->setField("app_identify", $identify);
		}
		session('user_id', $userInfo['id']);
		session('user_info', $userInfo);
		jsonout("登录成功", 0, $userInfo);
	}


	/**
	 * 用户信息修改
	 * @param   int $user_id 手机号
	 * @param   string                其他各种修改信息
	 * @return  json
	 * 说明：不包括手机号、密码、金额的修改，只是普通信息的修改，比如昵称、性别、头像、
	 * @zd
	 */
	public function editUser()
	{
		$obj = new User();
		$ok = $obj->editUser();
		if (!is_numeric($ok)) {
			jsonout($ok);
		}
		jsonout("修改成功", 0);
	}

	/**
	 * 修改登录密码
	 * @param   int $user_id 用户ID
	 * @param   string $oldpwd 原密码
	 * @param   string $newpwd 新密码
	 * @param   string $confirmpwd 确认密码
	 * @return  json
	 * @zd
	 */
	public function editPassword()
	{
		$obj = new User();
		$ok = $obj->editPassword();
		if (!is_numeric($ok)) {
			jsonout($ok);
		}
		jsonout("修改成功", 0);
	}

	/**
	 * 修改支付密码
	 * @param   int $user_id 用户ID
	 * @param   string $oldpwd 原密码
	 * @param   string $newpwd 新密码
	 * @param   string $phone_code 手机验证码
	 * @param   string $type 修改类型，默认0初始没有支付密码，1有密码 进行修改
	 * @return  json
	 * 说明：可分为未设置支付密码和已设置密码密码
	 * @zd
	 */
	public function editPayPassword()
	{
		$obj = new User();
		$res = $obj->editPayPassword();
		jsonout($res[1], $res[0]);
	}

	/**
	 * 找回登录密码
	 * @param   string $phone 用户ID
	 * @param   string $newpwd 新密码
	 * @param   string $confirmpwd 确认密码
	 * @param   string $phone_code 手机验证码
	 * @return  json
	 * @zd
	 */
	public function findPassword($data = null)
	{
		if (!$data) {
			$data = I();
		}
		$obj = new User();
		$ok = $obj->findPassword($data);
		if (!is_numeric($ok)) {
			jsonout($ok);
		}
		jsonout("密码找回成功", 0);
	}

	/**
	 * 更换手机号
	 * @param   int $user_id 用户ID
	 * @param   string $phone 原手机号
	 * @param   string $newphone 新手机号
	 * @param   string $phone_code 原手机验证码
	 * @return  json
	 * @zd
	 */
	public function changePhone()
	{
		$obj = new User();
		$ok = $obj->changePhone();

		if (!is_numeric($ok)) {
			jsonout($ok);
		}
		jsonout("更换成功", 0);
	}

	/**
	 * 实名认证
	 * @param   int $user_id 用户ID
	 * @param   string $id_card 身份证号
	 * @param   string $bank_name 持卡人姓名
	 * @param   string $phone 原手机号 -- 真实名时用
	 * @param   string $bank_no 银行卡号 -- 真实名时用
	 * @return  string
	 * @zd
	 */
	public function nameVerify()
	{
		$obj = new User();
		$ok = $obj->nameVerify();
		if (!is_numeric($ok)) {
			jsonout($ok);
		}
		jsonout("认证成功", 0);
	}


	/**
	 * 用户提现
	 * @param  $user_id int  用户id
	 * @param  $bank_id int  用户id
	 * @param  $tx_money int  用户id
	 * @param  $type int  1消费者，2商家，3代理
	 * @return json 结果集
	 * @mgl
	 */
	public function moneyOut($data = null)
	{
		if (!$data) {
			$data = I();
		}
		$obj = new User($data['user_id']);
		$ok = $obj->Tixian($data);
		if (is_array($ok)) {

			jsonout($ok[0], $ok[1], $ok[2]);
		}
		jsonout($ok);
	}

	/**
	 *
	 * 提现审核
	 * @param  $txu_id int  提现表id
	 * @param  $shtype int  审核类型1审核通过2拒绝
	 * @return json 结果集
	 * @mgl
	 */
	public function tx_shenhe($data = null)
	{
		if (!$data) {

			$data = I();
		}
		$obj = new User($data['user_id']);
		$ok = $obj->tx_shenhe($data);
		if (is_array($ok)) {

			jsonout($ok[0], $ok[1], $ok[2]);
		}
		jsonout($ok);
	}

	/**
	 *
	 * 提现审核
	 * @param  $tx_id int  提现表id
	 * @return json 结果集
	 * @mgl
	 */
	public function tx_status($data = null)
	{
		if (!$data) {

			$data = I();
		}
		$obj = new User($data['user_id']);
		$ok = $obj->tx_status($data);
		if (is_array($ok)) {

			jsonout($ok[0], $ok[1], $ok[2]);
		}
		jsonout($ok);

	}

	/**
	 *
	 * 签到
	 * @param  int  $user_id  用户ID
	 * @return json 结果集
	 * @mgl
	 */
	public function signIn()
	{
		$user_id = I("user_id");
		$start = strtotime(date("Y-m-d"));
		$end = $start+86400;

		$where["user_id"] = $user_id;
		$where["deal_type"] = 9;	// 签到日志
		$where["cre_time"] = array("between", array($start, $end));
		$count = M("account_log")->where($where)->count();
		if ($count > 0) 
		{
			jsonout("今天已签到");
		}
		$amount = get_config_project("sign_points");
		$rs = change_money_log($user_id, 1, "score", 0, $amount, 16, 9, "每日签到账户积分", 1, 1, 1);
		if ($rs) 
		{
			jsonout("签到成功", 0);
		}
		else
		{
			jsonout("签到失败，请重新签到");
		}
	}


	// 通过openid获取用户信息&获取openid
	public function userByOpenid()
	{
		$type = I("type", 1, 'intval'); //1微信APP，3微信公众号，4微信小程序
		$openid = I("openid", '', 'trim');
		$unionid = I("unionid", '', 'trim');
		if (!$openid) {
			$code = I('code', '');
			if ($code) {
				$wx = M('config_pay3')->where('type='.$type)->find();
				$appid = $wx['appid'];
				$secret = $wx['key2'];
				$url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
				$res = file_get_contents($url);
				$res = json_decode($res, true);
				$openid = $res['openid'];
				if($res['unionid']){
					$unionid = $res['unionid'];
				}				
			}
		}
		if(!empty($unionid)){
			$where["unionid"] = $unionid;    //unionid登录
		}elseif($type==1){
			$where["openid_app"] = $openid;  //app 微信登录
		}elseif($type==4){
			$where["openid_mini"] = $openid; //app 小程序
		}elseif($type==3){
			$where["openid"] = $openid;      //app 微信公众号
		}
		$rs = get_user_info($where);		
		// wxtype：1 openid用户有手机号，不需要其他操作，2 没有关联openid的用户，需要去绑定手机号，3 没有关联，但是不需要绑定
		if (!$rs) 
		{			
			$result = $res;
			$result["wxtype"] = 2;	// 注册绑定			
			jsonout("没有相关用户，需要去关联手机号", 0, $result);
		}
		else
		{
			if(!empty($unionid)){
				//当unionid登录时存储相应的openid
				if($type==1 && $rs["openid_app"]==''){
					$data["openid_app"] = $openid; //APP 
				}elseif($type==3 && $rs["openid"]==''){
					$data["openid"] = $openid;     //微信公众号
				}elseif($type==4 && $rs["openid_mini"]==''){
					$data["openid_mini"] = $openid;//小程序
				}
				if(!empty($data)){
					M('user')->where($where)->save($data);
					$rs = get_user_info($where);
                    //已提金额
                    $txWhere['user_id'] = $rs['id'];
                    $txWhere['utype'] = 1;
                    $txWhere['ischeck'] = 1;
                    $price = M('tx_log')->where($txWhere)->sum('tx_money');
                    if(!$price){
                        $price = 0;
                    }
                    $rs['tx_price'] = $price;
                    //预得佣金
                    $rebateWhere['user_id'] = $rs['id'];
                    $rebateWhere['is_profit'] = 0;
                    $rebateMoney = M('distribution_log')->where($rebateWhere)->sum('money');
                    if(!$rebateMoney){
                        $rebateMoney = 0;
                    }
                    $rs['rebateMoney'] = $rebateMoney;
				}
			}
			$rs["wxtype"] = 1;	// 正常
			jsonout("登录正常", 0, $rs);
		}
	}

	// 注册openid 或 绑定手机号
	public function bindOpenid()
	{
		$phone = I("phone");
		$unionid = I("unionid", '', 'trim');
		$openid = I("openid");
		$nickname = I("nickname");	// 微信昵称
		$headimgurl = I("headimgurl");	// 微信头像
		$type = I("type", 1, 'intval'); //1微信APP，3微信公众号，4微信小程序
		$tuijian_id = I("tuijian_id", 0, 'intval'); //用于小程序的推荐分享
		if (!$phone) 
		{
			jsonout("请传入手机号");
		}
		if (!$openid) 
		{
			jsonout("请传入openid");
		}
		$info = M("user")->where("phone='".$phone."' ")->find();
		if ($info) 
		{
			// 如果已有手机关联的用户，则可能需要关联
			if($type==1){
				if ($info["openid_app"]){
					jsonout("已绑定微信，无需再绑");
				}
				$data["openid_app"] = $openid;
			}elseif($type==3){
				if ($info["openid"]){
					jsonout("已绑定微信，无需再绑");
				}
				$data["openid"] = $openid;
			}elseif($type==4){
				if ($info["openid_mini"]){
					jsonout("已绑定微信，无需再绑");
				}
				$data["openid_mini"] = $openid;
			}
			if (!$info["headimgurl"]) 
			{
				$data["headimgurl"] = $headimgurl;
			}
			if (!$info["unionid"] && $unionid != '' &&  $unionid != 'undefined') 
			{
				$data["unionid"] = $unionid;
			}
			$rs = M("user")->where("phone='".$phone."' ")->save($data);
			if ($rs !== false) 
			{
				$map['phone'] = $phone;
				$info = get_user_info($map, "*", 1);
				$info["wxtype"] = 1;
				jsonout("绑定成功", 0, $info);
			}
			else
			{
				jsonout("绑定失败，请重新绑定");
			}
		}
		else
		{
			// 如果没有手机注册用户，则注册用户、绑定微信
			$model = M();
			$model->startTrans();
			if($type==1){
				$data["openid_app"] = $openid;
			}elseif($type==3){
				$data["openid"] = $openid;
			}elseif($type==4){
				$data["openid_mini"] = $openid;
			}
			$data["phone"] = $phone;			
			$data["nickname"] = $nickname;
			$data["headimgurl"] = $headimgurl;
			$data["unionid"] = $unionid;
			$data["cre_time"] = time();
			$data["ip"] = get_client_ip();
			if($tuijian_id>0){
				// 此处推荐人id
				$where["id"] = $tuijian_id;
				$tInfo = get_user_info($where);
				if ($tInfo) {
					$data["first_leader"] = $tInfo["id"];
					$data["second_leader"] = $tInfo["first_leader"];
					$data["third_leader"] = $tInfo["second_leader"];
				} 
			}
			$rs = M("user")->add($data);
			
			$map['id'] = $rs;
			$rs3 = get_user_info($map, "*", 1);
			if ($rs && $rs3) 
			{
				$model->commit();
				$rs3["wxtype"] = 1;
				jsonout("绑定成功", 0, $rs3);
			}
			else
			{
				$model->rollback();
				jsonout("绑定失败，请重新绑定");
			}
		}
	}	
	/**
     * 微信小程序绑定手机号
     */

    public function bindPhone(){
        $code = I("code");
        $encryptedData = I("encryptedData");
        $iv = I("iv");        
       
        if (!$code || !$encryptedData || !$iv)
        {
            jsonout("参数异常，不能绑定手机号");
        }
        $configPayInfo = get_config_pay3(4);
        if (!$configPayInfo)
        {
            jsonout("参数异常，请联系客服配置");
        }
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$configPayInfo['appid']."&secret=".$configPayInfo['key2']."&js_code=".$code."&grant_type=authorization_code";
        $sessionInfo = file_get_contents($url);
        $sessionInfo = json_decode($sessionInfo,  true);
        if ($sessionInfo['errcode'])
        {
            jsonout($sessionInfo['errmsg']);
        }
        $openId = $sessionInfo['openid'];
        $sessionKey = $sessionInfo['session_key'];

        $res = wxDataDecrypt($encryptedData, $iv, $sessionKey);
        if (!$res || !$res['purePhoneNumber'])
        {
            jsonout("手机号获取失败，请重新获取");
        }
        //获取到的手机号
        $where['phone'] = $res['purePhoneNumber'];
		$userInfo = get_user_info($where, "*", 1);
        if(empty($userInfo) || $userInfo['openid_mini']==''){
            jsonout("该用户不存在请注册", 0, $res['purePhoneNumber']);
        }else{
			$userInfo["wxtype"] = 1;
			jsonout("手机号获取手机号成功", 0, $userInfo);
		}
        //$rs = M('user')->where('id='.$user_id)->save($data);
        // if($rs){
            // $userInfo = M('user')->find($user_id);
            // jsonout("手机号获取手机号成功", 0, $userInfo);
        // }else{
            // jsonout("手机号获取失败，请重新获取");
        // }
    }
    /**
     * 判断手机验证码是否正确
     * @param   int $phone  用户手机号
     * @param   string $phone_code 手机验证码
     * @return  json
     * @yh
     */
    public function judgePhoneCode()
    {
        $data=I();
        $obj = new User();
        $ok = $obj->judge_phone_code($data);
        if ($ok=="true") {
            jsonout("修改成功", 0);
        }else{
            jsonout($ok);
        }

    }
	/**
     * 银行卡的详情
     */
    public function bankinfo(){
        $bank_id = I('bank_id');
        if(!$bank_id){
            $this->error('参数错误，请选择银行卡');
        }
        $bankInfo = M('user_bank')->find($bank_id);
        jsonout('查询成功',0,$bankInfo);
    }

    /**
     * Notes:会员签到接口
     * User: WangSong
     * Date: 2020/9/29
     * Time: 8:58
     */
    public function sign(){
        $userin = new User();
        $re = $userin->user_sign();
        if(is_numeric($re)){
            jsonout('操作成功',0);
        }else{
            jsonout($re,1);
        }
    }

    /**
     * Notes:会员签到记录
     * User: WangSong
     * Date: 2020/9/29
     * Time: 8:59
     */
    public function sign_list(){
        $userin = new User();
        if(!I('user_id')){
            jsonout('缺少user_id参数',1);
        }
        $list = $userin->get_user_sign_log();
        jsonout('获取成功',0,$list);
    }

}