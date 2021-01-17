<?php

    namespace Api\Controller;

    use Think\Controller;

    class PuboutController extends CommonController
    {

        /**
         * 图片上传
         * @param string      handlename    控件名称
         * @return  json
         * @zd
         */
        public function uploads()
        {
            $video = M( 'config' )->where( 'name="video"' )->getField( 'val' );

            $video1 = ( $video + '5' ) . "M";
            $video2 = ( $video + '' ) . "M";


            @ini_set( 'post_max_size',$video1 );
            @ini_set( 'upload_max_filesize',$video2 );

            $handlename = I( "handlename" );

            if ( !$handlename ){
                jsonout( "缺少主要参数" );
            }
            $video = I( 'video' );

            if ( $video ){
                $size = M( 'config' )->where( 'name = "video"' )->getField( 'val' );

                $size = $size * 1024 * 1024;
                $is_video = 1;
                $rs = uploads( $handlename,'',$size,'','',$is_video );
                jsonout( "视频上传",$rs[ 1 ],$rs[ 2 ] );
            }
            else{
                $rs = uploads( $handlename );
                jsonout( "图片上传",$rs[ 1 ],$rs[ 2 ] );
            }
        }


        // 短信验证码
        public function get_phone_code()
        {
            $ok = get_phone_code();
            if ( $ok === true ){
                jsonout( "验证码已发送",0 );
            }
            else{
                jsonout( $ok );
            }
        }

        // 输出公共通用的设置及参数
        public function pubout()
        {
            $code_status = get_config( "code_status" );    // 注册时验证码是否开启并验证，1是，0否
            $code_status = $code_status ? $code_status : "0";

            $name_verify = get_config( "name_verify" );    // 是否开启真实实名认证，1是，0否
            $name_verify = $name_verify ? $name_verify : "0";

            $qq = get_config( "qq" );
            $kefu_mobile = get_config( "kefu_mobile" );
            $qq = $qq ? $qq : "";
            $kefu_mobile = $kefu_mobile ? $kefu_mobile : "";
            $code_login = get_config( "code_login" ); //登陆是否开启验证码登陆
            $code_login = $code_login ? $code_login : "0";

            $alipay_login = get_config( "alipay_login" ); //登陆是否开启支付宝登陆
            $alipay_login = $alipay_login ? $alipay_login : "0";

            $wx_login = get_config( "wx_login" ); //登陆是否开启微信登陆
            $wx_login = $wx_login ? $wx_login : "0";

            $is_second_verify = get_config( "is_second_verify" ); // 是否开启二次验证，1是，0否
            $is_second_verify = $is_second_verify ? $is_second_verify : "0";
            $is_alipay = get_config( "is_alipay" ); // 是否开启支付宝支付，1是，0否
            $is_alipay = $is_alipay ? $is_alipay : "0";
            $is_wx_pay = get_config( "is_wx_pay" ); // 是否开启微信支付，1是，0否
            $is_wx_pay = $is_wx_pay ? $is_wx_pay : "0";
            $is_ye_pay = get_config( "is_ye_pay" ); // 是否开启余额支付，1是，0否
            $is_ye_pay = $is_ye_pay ? $is_ye_pay : "0";
            $score_pay_mode = get_config( "score_pay_mode" ); // 积分商品支付是否支付现金还是积分 1是积分2是现金
            $score_pay_mode = $score_pay_mode ? $score_pay_mode : "0";
            $config[ "code_status" ] = $code_status;
            $config[ "name_verify" ] = $name_verify;
            $config[ "code_login" ] = $code_login;
            $config[ "alipay_login" ] = $alipay_login;
            $config[ "wx_login" ] = $wx_login;
            $config[ "qq" ] = $qq;
            $config[ "kefu_mobile" ] = $kefu_mobile;
            $config[ "is_second_verify" ] = $is_second_verify;
            $config[ "is_alipay" ] = $is_alipay;
            $config[ "is_wx_pay" ] = $is_wx_pay;
            $config[ "is_ye_pay" ] = $is_ye_pay;
            $config[ "score_pay_mode" ] = $score_pay_mode;
            jsonout( "通用信息",0,$config );
        }

        /**
         * 启动页
         */
        /**广告列表
         * @param string $position 广告位置关键字
         * @param string $row 查询几条
         * @szl
         */
        public function startPage( $data = null )
        {
            if ( !$data ){
                $data = I();
            }
            $position = $data[ 'position' ];
            $row = $data[ 'row' ];
            if ( $data[ 'page' ] ){
                $banner = get_ad( null,null,$data[ 'page' ] );
            }
            else{
                $banner = get_ad( $position,$row );
            }
            $result = array(
                '广告列表',
                0,
                $banner
            );
            jsonout( $result[ 0 ],$result[ 1 ],$result[ 2 ] );
        }

    }