<?php

    namespace Api\Controller;

    use Clas\Goods;
    use Clas\Shop;

    class ShopoutController extends CommonController
    {


        /**
         * 商家详情
         * 一般传入参数
         * @param int $shop_id 商家id  latitude
         * @param int $latitude 维度
         * @param int $longitude 经度
         * @return  json
         * @wz
         */
        public function shopDetail( $data = null )
        {
            if ( !$data ){
                $data = I();
            }
            $obj = new Shop( 0,'shop',$this->user_id );
            $ok = $obj->shopDetail( $data );
            if ( !is_array( $ok ) ){
                jsonout( $ok );
            }
            jsonout( $ok[ 0 ],$ok[ 1 ],$ok[ 2 ] );
        }

        /**
         * 商家列表
         * 一般传入参数
         * @param string $city 城市名
         * @param int $city_id 城市ID
         * @param int $longitude 经度
         * @param int $latitude 维度
         * @param int $industry_id 行业ID
         * @param string $word 店铺名称查询
         * @param int $page 第几页
         * @param int $num 一页几个
         * @param int $last_id 最后一个ID
         * @return  json
         * @wz
         */
        public function shop( $data = null )
        {

            if ( !$data ){
                $data = I();
            }
            $last_id = $data[ 'last_id' ] ? $data[ 'last_id' ] : 0;
            $page = $data[ 'page' ] ? $data[ 'page' ] : 1;
            $num = $data[ 'num' ] ? $data[ 'num' ] : 10;

            $paging[ 'page' ] = $page;
            $paging[ 'last_id' ] = $last_id;

            if ( !empty( $data[ 'industry_id' ] ) ){
                $industry_name = M( 'industry' )->where( 'id=' . $data[ 'industry_id' ] )->getField( 'name' );
                $res[ 'industry_name' ] = $industry_name;
            }
            $obj = new Shop( 0,'shop',$this->user_id );
            $ok = $obj->shop( $data,$num,$paging );

            if ( !$ok[ "result" ][ "shop" ] ){
                $res[ 'list' ] = array();
                $res[ 'page' ] = $page;
                $res[ 'last_id' ] = $last_id;
                $res[ 'max_page' ] = 0;
            }
            else{
                $numArr = $obj->getNums( $ok[ 'result' ][ 'where' ],$num );
                $res[ 'list' ] = $ok[ 'result' ];
                $res[ 'page' ] = $page;
                $res[ 'last_id' ] = $ok[ 'result' ][ 'last_id' ];
                $res[ 'max_page' ] = $numArr[ 'max_page' ];
            }

            jsonout( $ok[ 'msg' ],$ok[ 'err' ],$res );
        }


        /**
         * 商家评论
         * 一般传入参数
         * @param int $shop_id 商家ID
         * @param int $page 第几页
         * @param int $page_size 一页几个
         * @return  json
         * @wz
         */
        public function shopComment( $data = null )
        {

            if ( !$data ){
                $data = I();
            }

            $data[ 'goods_id' ] = M( 'goods' )->where( "shop_id = {$data['shop_id']}" )->getField( "id",true );

            if ( $data[ 'goods_id' ] ){
                $goods = new Goods( 0 );
                $rs = $goods->goods_comment( $data );
            }
            else{
                jsonout( '暂无数据',1 );
            }
            jsonout( $rs[ 0 ],$rs[ 1 ],$rs[ 2 ] );
        }


        //商家审核
        public function check( $data = null )
        {
            $last_id = I( "last_id",0 );
            $page = I( "page",1 );
            $paging[ 'page' ] = $page;
            $paging[ 'last_id' ] = $last_id;
            $num = 10;

            if ( !$data ){
                $data = I();
            }

            $obj = new Shop( 0,'shop',$this->user_id );
            $ok = $obj->checkShop( $data,$num,$paging );
            $numArr = $obj->getNums( $ok[ 'result' ][ 'where' ],$num );
            $res[ 'list' ] = $ok[ 'result' ];
            $res[ 'page' ] = $page;
            $res[ 'last_id' ] = get_last_id( $ok,'id',$num );
            $res[ 'max_page' ] = $numArr[ 'max_page' ];

            jsonout( $ok[ 'msg' ],$ok[ 'err' ],$res );
        }

        /**
         * 商家订单
         * @param int $shop_id 商家ID
         * @param int $page 第几页
         * @param int $page_size 一页几个
         * @return  json
         * @zd
         * 主订单与子订单进行关联处理
         */
        public function shopOrder( $data = null )
        {
            $shop_id = I( "shop_id",0 );              // 商家ID
            $user_id = I( "user_id",0 );              // 商家UID
            $order_status = I( "order_status",0 );    // 商家UID
            if ( !$shop_id ){
                jsonout( "没有更多数据" );
            }

            // last_id 和 page 可同时存在，last_id优先，page为次，
            $last_id = I( "last_id",0 );
            $page = I( "page",1 );
            $paging[ 'page' ] = $page;
            $paging[ 'last_id' ] = $last_id;
            $num = 10;

            $obj = new Shop( $shop_id,'shop',$this->user_id );
            $where[ 'isdel' ] = 0;
            $where[ 'shop_id' ] = $shop_id;
            if ($order_status >= 0 && is_numeric($order_status))
            {
                $where['order_status'] = $order_status;
            }

            $ok = $obj->orderList( $where,"id desc",$num,$paging );
            if ( !$ok ){
                jsonout( "暂无数据" );
            }
            $numArr = $obj->getNums2( $where,$num );
            $res[ 'list' ] = $ok;
            $res[ 'page' ] = $page;
            $res[ 'last_id' ] = get_last_id( $ok,'id',$num );
            $res[ 'max_page' ] = $numArr[ 'max_page' ];
            jsonout( "商家订单",0,$res );
        }

        /**
         * 商家服务费比例列表
         * @return  json
         * @zd
         */
        public function feeList()
        {
            $list = M( "config_shop_fee" )->order( "id asc" )->select();
            if ( !$list ){
                jsonout( "暂无服务费列表",1 );
            }
            foreach ( $list as $k => &$v ){
                $v[ 'name' ] = '服务费' . $v[ 'percent1' ] . '%，返还客户' . $v[ 'percent2' ] . '%，返还商家' . $v[ 'percent3' ] . '%';
            }
            jsonout( "服务费列表",0,$list );
        }

        /**
         * Notes:获取线下门店
         * User: WangSong
         * Date: 2020/7/9
         * Time: 17:59
         */
        public function get_shop_offline()
        {
            $data = I();
            $shop = new Shop( $data[ 'shop_id' ],'shop',$this->user_id );
            if ( !$data[ 'shop_id' ] ){
                jsonout( '确少shop_id参数' );
            }
            $list = $shop->get_shop_offline( $data[ 'shop_id' ] );
            if ( $list ){
                jsonout( '获取成功',0,$list );
            }
            else{
                jsonout( '暂无数据' );
            }
        }

        /**
         * Notes:上家订单详情
         * User: WangSong
         * Date: 2020/8/7
         * Time: 14:36
         */
        public function shop_order_detail()
        {
            $data = I();
            $shop = new Shop( $data[ 'shop_id' ],'shop',$data[ 'user_id' ] );
            $re = $shop->shop_order_detail();
            if ( is_array( $re ) ){
                jsonout( '获取成功',0,$re );
            }
            else{
                jsonout( $re );
            }
        }

        /**
         * Notes:商家收款二维码
         * User: WangSong
         * Date: 2020/9/9
         * Time: 11:39
         */
        public function get_index_code()
        {
            $token = get_wxtoken();
            if (!$token)
            {
                exit;
            }
            $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $token;

            $user_id = I('user_id');
            $shop = M('shop')->where('user_id='.$user_id.' and status = 1')->find();
            $arr = array(
                'scene' => $shop['id'],
                'page' => 'pages/near/business_details/pay_bill/pay_bill'
            );
            $resp = httpRequest($url, 'POST', json_encode($arr));
            header('content-type:image/jpeg');
            exit($resp);
        }

        /**
         * Notes:获得商家订单列表
         * User: WangSong
         * Date: 2020/9/18
         * Time: 15:02
         */
        public function get_shopOrderList(){
            $data = I();
            $shop = new Shop($data['shop_id'],'shop');
            $list = $shop->get_shopOrderList();
            jsonout('获取成功',0,$list);
        }

    }