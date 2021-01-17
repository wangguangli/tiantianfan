<?php
namespace Clas;
class Jssdk {
 
    public function __construct() {}

    /*
     * 向用户推送消息
     */
    public function push_msg($openid,$content){
        $access_token = get_access_token($this->_weixin_config);
        $url ="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";        
        $post_arr = array(
                        'touser'=>$openid,
                        'msgtype'=>'text',
                        'text'=>array(
                                'content'=>$content,
                            )
                        );
        $post_str = json_encode($post_arr,JSON_UNESCAPED_UNICODE);        
        httpRequest($url,'POST',$post_str);
        // _error_log(json_decode($ok,true));
    }
    
    
  // 签名
  public function getSign($type) {

      $wx_config = M('config_pay3')->where("type=3")->find();

      $jt = $this->getJsApiTicket($wx_config);
      // 注意 URL 一定要动态获取，不能 hardcode.
      if($type==1){
          $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
          $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

      }else{
          $url = I('url','','trim');
      }
//        $url = encodeURIComponent();
      $timestamp = time();
      $nonceStr = mt_rand(1000, 9999) . uniqid();
      // 这里参数的顺序要按照 key 值 ASCII 码升序排序
      $string = "jsapi_ticket={$jt}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
      $signature = sha1($string);
      $signPackage = array(
          "appId" => $wx_config['appid'],
          "nonceStr" => $nonceStr,
          "timestamp" => $timestamp,
          "url" => $url,
          "rawString" => $string,
          "signature" => $signature
      );
      return $signPackage;
  }
    
    
    
    
    public function getJsApiTicket($wx_config) {
        
        $ticket = S('weixin_ticket');
        if(!empty($ticket)) {
            return $ticket;
        }
        
        $access_token = get_access_token($wx_config);
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
        $req = httpRequest($url, 'GET');
        $data = json_decode($req, 1);
        S('weixin_ticket', $data['ticket'], 7000);
        return $data['ticket'];
    }  
    
}