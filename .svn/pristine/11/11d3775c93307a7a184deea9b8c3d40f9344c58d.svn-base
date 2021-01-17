<?php

    namespace Clas;
    use Think\Page;

    class Shop
    {

        private $_user_id;
        private $_shop_id;
        private $_role;

        private $_thumb = '';
        private $_latitude;
        private $_longitude;
        private $_shop_name = '';
        private $_license = '';

        private $_address = array();

        // role: admin,shop
        public function __construct( $shop_id,$role,$user_id )
        {
            if ( func_num_args() < 2 ){
                throw new \Exception( '缺少参数<br>Missing argument!' );
            }
            $this->_user_id = $user_id;
            $this->_role = $role;
            $this->_shop_id = $shop_id;
        }

        private function _check()
        {

            $shop = M( 'shop' )->where( 'user_id=' . $this->_user_id )->find();
            if ( $shop ){
                if ( $shop[ 'status' ] ){
                    return '你已是商家不能重复申请';
                }
                else{
                    return '你的申请正在审核中';
                }
            }

            $ck = $this->_check_shop_name();
            if ( $ck ){
                return $ck;
            }
            // $ck1 = $this->_check_thumb();
            // if ($ck1) {
            // 	return $ck1;
            // }

            $ck2 = $this->_check_address();
            if ( $ck2 ){
                return $ck2;
            }

            $ck3 = $this->_check_coordinate();
            if ( $ck3 ){
                return $ck3;
            }

            // $ck4 = $this->_check_license();
            // if ($ck4) {
            // 	return $ck4;
            // }

            if ( $this->_user_id ){
                $c = M( 'shop' )->where( 'user_id=' . $this->_user_id )->count();
                if ( $c ){
                    return '不能重复申请商家';
                }
            }

            return false;
        }

        private function _check_shop_name()
        {
            $shop_name = I( 'shop_name','','trim' );
            if ( empty( $shop_name ) ){
                return '请输入店铺名';
            }
            $where = array( 'shop_name' => $shop_name,
                            'city_id'   => I( 'district',0,'intval' )
            );
            $c = M( 'shop' )->where( $where )->count();
            if ( $c ){
                return '同一地区不能出现相同的名字';
            }
            $this->_shop_name = $shop_name;
            return false;
        }

        private function _check_thumb( $is_update = false )
        {
            if ( $is_update && $_FILES[ 'thumb' ][ 'error' ] == UPLOAD_ERR_NO_FILE ){
                return false;
            }

            $photo = common_upload_photo( 'thumb','shop' );
            if ( !$photo[ 'success' ] ){
                return '门店照：' . $photo[ 'msg' ];
            }
            $this->_thumb = $photo[ 'path' ];
            return false;
        }

        private function _check_address()
        {
            $province_id = I( 'province_id',0,'intval' );
            $city_id = I( 'city_id',0,'intval' );
            $district_id = I( 'district_id',0,'intval' );
            $town_id = I( 'town_id',0,'intval' );
            $address = I( 'address' );

            $province = I( 'province','','trim' );
            $city = I( 'city','','trim' );
            $district = I( 'district','','trim' );

            if ( $province ){
                $province_id = M( 'region' )->where( array( 'name' => array( 'like',
                    "{$province}%"
                )
                ) )->getField( 'id' );
            }
            if ( $city ){
                $city_id = M( 'region' )->where( array( 'name' => array( 'like',
                    "{$city}%"
                )
                ) )->getField( 'id' );
            }
            if ( $district ){
                $district_id = M( 'region' )->where( array( 'name' => array( 'like',
                    "{$district}%"
                )
                ) )->getField( 'id' );
            }

            if ( empty( $province_id ) || empty( $city_id ) || empty( $district_id ) ){
                return '请选择地区';
            }
            if ( empty( $address ) ){
                return '请输入详情地址';
            }
            if ( empty( $province ) ){
                $province = get_region_name( $province_id );
            }
            if ( empty( $city ) ){
                $city = get_region_name( $city_id );
            }
            if ( empty( $district ) ){
                $district = get_region_name( $district_id );
            }

            $data[ 'province' ] = $province;
            $data[ 'city' ] = $city;
            $data[ 'district' ] = $district;
            $data[ 'town' ] = get_region_name( $town_id );
            $data[ 'address' ] = $address;
            $data[ 'province_id' ] = $province_id;
            $data[ 'city_id' ] = $city_id;
            $data[ 'district_id' ] = $district_id;
            $data[ 'town_id' ] = $town_id;

            $this->_address = $data;
            return false;
        }


        private function _check_coordinate()
        {
            $latitude = I( 'latitude',0 );
            $longitude = I( 'longitude',0 );

            $coor = I( 'coordinate','','trim' );
            if ( $coor ){
                if ( !strpos( $coor,',' ) ){
                    return '请按要求的格式输入坐标';
                }
                list( $this->_latitude,$this->_longitude ) = explode( ',',$coor );
            }
            else{
                $this->_latitude = $latitude;
                $this->_longitude = $longitude;
            }

            if ( !is_numeric( $this->_latitude ) || !is_numeric( $this->_longitude ) ){
                return '坐标数值有误';
            }
            return false;
        }


        public function addUser()
        {
            if ( $this->_user_id ){
                return false;
            }
            if ( $this->_role != 'admin' ){
                return '请选择用户';
            }
            $newUser = new User( 0 );
            $u = $newUser->add();
            if ( !is_numeric( $u ) ){
                return $u;
            }
            $this->_user_id = $u;
            return false;
        }

        public function add( $agent_id = 0 )
        {
            $u = $this->addUser();
            if ( $u ){
                return $u;
            }

            $ok = $this->_check();
            if ( $ok ){
                return $ok;
            }


            $industry = I( 'industry',0,'intval' );
            $status = 0;

            $shop_fee_id = I( 'shop_fee_id',1,'intval' );

            if ( empty( $industry ) ){
                return '请选择行业';
            }
            if ( $this->_role == 'admin' ){
                $status = 1;
            }

            $list_img = explode( "|",I( 'images' ) );
            $list_img = json_encode( $list_img );

            $data = array( 'user_id'        => $this->_user_id,
                           'shop_name'      => $this->_shop_name,
                           'logo'           => I( "thumb" ),
                           'thumb'          => I( "thumb" ),
                           'type'           => I( 'type',0,'intval' ),
                           'contact_person' => I( 'contact_person','' ),
                           'tel'            => I( 'tel' ),
                           'province'       => $this->_address[ 'province' ],
                           'city'           => $this->_address[ 'city' ],
                           'district'       => $this->_address[ 'district' ],
                           'town'           => $this->_address[ 'town' ],
                           'address'        => $this->_address[ 'address' ],
                           'province_id'    => $this->_address[ 'province_id' ],
                           'city_id'        => $this->_address[ 'city_id' ],
                           'district_id'    => $this->_address[ 'district_id' ],
                           'town_id'        => $this->_address[ 'town_id' ],

                           'latitude'    => $this->_latitude,
                           'longitude'   => $this->_longitude,
                           'description' => I( 'description' ),

                           'industry'          => $industry,
                           'industry_name'     => get_industry( $industry ),
                // 'list_img' => $this->getTempImages(I('images')),
                           'list_img'          => $list_img,
                           'card_img1'         => I( "license1" ),
                           'card_img2'         => I( "license2" ),
                           'license'           => I( "license3" ),
                           'agent_id'          => $agent_id,
                           'status'            => $status,
                           'date'              => date( 'Y-m-d' ),
                           'time'              => time(),
                           'ip'                => get_client_ip(),
                           'shop_fee_id'       => $shop_fee_id,
                           'offline_shop_auth' => I( "offline_shop_auth" ),
                //审核是否有线下门店1是具有权限 0是没有权限
            );

            $this->_shop_id = M( 'shop' )->add( $data );


            if ( $status == 1 ){
                M( 'user' )->where( 'id=' . $this->_user_id )->setField( 'is_shop',1 );
            }
            else{
                M( 'user' )->where( 'id=' . $this->_user_id )->setField( 'is_shop',3 );
            }
            return $this->_shop_id;
        }

        public function getFullUpdate( &$data,$shop_name )
        {
            $shop_name1 = I( 'shop_name' );
            if ( $shop_name != $shop_name1 ){
                $err1 = $this->_check_shop_name();
                if ( $err1 ){
                    return $err1;
                }
                $shop_name = $this->_shop_name;
            }

            $err2 = $this->_check_address();
            if ( $err2 ){
                return $err2;
            }

            $industry = I( 'industry',0,'intval' );
            if ( empty( $industry ) ){
                return '请选择行业';
            }

            $data[ 'shop_name' ] = $shop_name;
            $data[ 'province' ] = $this->_address[ 'province' ];
            $data[ 'city' ] = $this->_address[ 'city' ];
            $data[ 'district' ] = $this->_address[ 'district' ];
            $data[ 'town' ] = $this->_address[ 'town_id' ];
            $data[ 'province_id' ] = $this->_address[ 'province_id' ];
            $data[ 'city_id' ] = $this->_address[ 'city_id' ];
            $data[ 'district_id' ] = $this->_address[ 'district_id' ];
            $data[ 'town_id' ] = $this->_address[ 'town_id' ];

            $data[ 'industry' ] = $industry;
            $data[ 'industry_name' ] = get_industry( $industry );
            return false;
        }


        //更新商家基本信息
        public function updateInfo()
        {
            $address = I( 'address' );
            if ( empty( $address ) ){
                return '请输入详情地址';
            }
            $shop = M( 'shop' )->find( $this->_shop_id );
            $shop_name = I( "shop_name" );
            $thumb = I( "thumb" );
            $contact_person = I( "contact_person" );
            $tel = I( "tel" );
            $latitude = I( "latitude" );
            $longitude = I( "longitude" );
            if ( empty( $shop_name ) ){
                return '请输入店铺名称';
            }
            if ( empty( $thumb ) ){
                return '请输入店铺缩略图';
            }
            if ( empty( $contact_person ) ){
                return '请输入店铺所有人';
            }
            if ( empty( $tel ) ){
                return '请输入联系电话';
            }
            if ( !$latitude || !$longitude ){
                return '请重新选择店铺地址';
            }
            $images = I( "images" );
            if ( !$images ){
                return '请上传店内图片';
            }
            $data = array( 'shop_name'      => $shop_name,
                           'thumb'          => $thumb,
                           'contact_person' => $contact_person,
                           'tel'            => $tel,
                           'address'        => $address,
                           'latitude'       => $latitude,
                           'longitude'      => $longitude,
                           'description'    => I( 'description' ),
                           'district_id'    => I( 'district_id' ),
                           'city_id'        => I( 'city_id' ),
                           'province_id'    => I( 'province_id' ),
            );
            $images = I( 'images' );
            if ( $images ){
                $list_img = explode( "|",I( 'images' ) );
                $data[ 'list_img' ] = json_encode( $list_img );
            }
            if ( $this->_role == 'admin' ){
                $err = $this->getFullUpdate( $data,$shop[ 'shop_name' ] );
                if ( is_string( $err ) ){
                    return $err;
                }
            }
            M( 'shop' )->where( 'user_id=' . $this->_user_id )->save( $data );
            return 'ok';
        }


        public function _check_license()
        {
            $arr = array();
            $img1 = common_upload_photo( 'license1','license' );
            if ( $this->_role != 'admin' && !$img1[ 'success' ] ){
                return '身份证正面：' . $img1[ 'msg' ];
            }
            if ( $img1[ 'path' ] ){
                $arr[] = $img1[ 'path' ];
            }

            $img2 = common_upload_photo( 'license2','license' );
            if ( $this->_role != 'admin' && !$img2[ 'success' ] ){
                return '身份证反面:' . $img2[ 'msg' ];
            }
            if ( $img2[ 'path' ] ){
                $arr[] = $img2[ 'path' ];
            }

            $img3 = common_upload_photo( 'license3','license' );
            if ( I( 'type',0,'intval' ) == 1 && $this->_role != 'admin' && !$img3[ 'success' ] ){
                return '营业执照:' . $img3[ 'msg' ];
            }
            if ( $img3[ 'path' ] ){
                $arr[] = $img3[ 'path' ];
            }

            if ( $arr ){
                $this->_license = json_encode( $arr );
            }
            return false;
        }


        public function addListImage()
        {
            //只在页面中调用
            $a = common_upload_photo( 'image','temp' );
            if ( $a[ 'success' ] ){
                exit( json_encode( _dat( $a[ 'path' ],0 ) ) );
            }
            api_is_str( $a[ 'msg' ] );
        }


        public function getTempImages( $images,$old_images = '' )
        {
            if ( empty( $images ) ){
                return '';
            }
            if ( $images == $old_images ){
                return $images;
            }

            if ( strpos( $images,'|' ) !== false ){
                $arr = explode( '|',$images );
            }
            else{
                $arr = explode( ',',$images );
            }

            foreach ( $arr as $k => $v ){
                $arr[ $k ] = str_replace( C_HTTP_HOST,'',$v );
            }

            $new_arr = array();

            if ( $old_images ){
                $old_arr = explode( '|',$old_images );
                foreach ( $old_arr as $o ){
                    if ( array_search( $o,$arr ) === false ){
                        delete_abs_file( $o );
                    }
                }
            }

            foreach ( $arr as $i ){
                if ( strpos( $i,'temp' ) !== false ){
                    $g = str_replace( 'temp','shop',$i );
                    $path = dirname( '.' . $g );
                    if ( !is_dir( $path ) ){
                        mkdir( $path );
                    }
                    copy( '.' . $i,'.' . $g );
                    delete_abs_file( $i );
                    $new_arr[] = $g;
                }
                else{
                    $new_arr[] = $i;
                }
            }

            return json_encode( $new_arr ); ////join('|', $new_arr);
        }

        public function delete( $shop_id )
        {

            $shop = M( 'shop' )->find( $shop_id );
            if ( $shop[ 'status' ] == 0 || $shop[ 'status' ] == -1 ){
                M( 'shop' )->where( 'id=' . $shop_id )->delete();
            }
            else{
                M( 'user' )->where( 'id=' . $shop[ 'user_id' ] )->setField( 'isshop',0 );
                M( 'shop' )->where( 'id=' . $shop_id )->setField( 'user_id',0 );
            }
            return true;
        }

        public function shopDetail( $data )
        {
            $latitude = $data[ 'latitude' ];
            $longitude = $data[ 'longitude' ];

            $shop_id = $data[ 'shop_id' ];

            if ( !$shop_id ){
                return '请传入商家信息';
            }

            $where_s[ "id" ] = $shop_id;

            $shop = M( 'shop' )->where( $where_s )->find();
            if ( empty( $shop ) ){
                return '商家不存在';
            }

            $list_img = array();
            if ( $shop[ 'list_img' ] ){
                $list_img = json_decode( $shop[ 'list_img' ],true );
            }
            foreach ( $list_img as $key => &$value ){
                $value = full_pre_url( $value );
            }

            $favorite_count = M( 'favorites' )->where( "fav_id={$shop_id} and type=2" )->count();

            $_d = get_distance( $shop[ 'latitude' ],$shop[ 'longitude' ],$latitude,$longitude );

            $tx_money = M( "tx_log" )->where( "utype=2 and ischeck=1 and user_id=" . $shop[ 'user_id' ] )->sum( "tx_money" );
            $tx_money = $tx_money > 0 ? $tx_money : '0.00';
            $pay_url = C( "C_HTTP_HOST" ) . "/index.php/M/Index/pay?via=snapp&shop_id=" . $shop[ "id" ];
            $pay_url = str_replace( "com//","com/",$pay_url );

            $shopFee = get_config_shop_fee( $shop[ 'shop_fee_id' ] );
            $shop_fee_name = "服务费" . $shopFee[ 'percent1' ] . "%";

            $newShop = array( 'shop_id'        => $shop[ "id" ],
                              'shop_name'      => $shop[ 'shop_name' ],
                              'logo'           => full_pre_url( $shop[ 'thumb' ] ),
                              'thumb'          => full_pre_url( $shop[ 'thumb' ] ),
                              'cre_time'       => get_time( $shop[ 'cre_time' ] ),
                              'contact_person' => $shop[ 'contact_person' ],
                              'phone'          => $shop[ 'tel' ],
                              'tel'            => $shop[ 'tel' ],
                              'description'    => $shop[ 'description' ],
                              'address'        => $shop[ 'address' ],
                              'distance'       => get_kilometre( $_d ),
                              'latitude'       => $shop[ 'latitude' ],
                              'longitude'      => $shop[ 'longitude' ],
                              'shop_money'     => $shop[ 'shop_money' ],
                              'money_wait'     => $shop[ 'money_wait' ],
                              'money'          => $shop[ 'money' ],
                              'tx_money'       => $tx_money,
                              'province'       => $shop[ 'province' ],
                              'province_id'    => $shop[ 'province_id' ],
                              'city'           => $shop[ 'city' ],
                              'city_id'        => $shop[ 'city_id' ],
                              'district'       => $shop[ 'district' ],
                              'district_id'    => $shop[ 'district_id' ],
                              'industry'       => $shop[ 'industry' ],
                              'industry_name'  => $shop[ 'industry_name' ],
                              'pay_url'        => $pay_url,
                              'shop_fee_id'    => $shop[ 'shop_fee_id' ],
                              'shop_fee_name'  => $shop_fee_name,
                              'license1'       => $shop[ 'card_img1' ],
                              'license2'       => $shop[ 'card_img2' ],
                              'license3'       => $shop[ 'license' ],
            );
            if ( $shop[ 'license' ] ){
                $license = json_decode( $shop[ 'license' ],true );
            }
            $license = $license ? $license : array();

            return array( '店铺详情',
                0,
                array( 'shop'           => $newShop,
                       'list_img'       => $list_img,
                       'license'        => $license,
                       'favorite_count' => $favorite_count,
                       'is_favorite'    => Favorite::is_favorite( $this->_user_id,$shop_id,'shop' )
                )
            );
        }

        public function shop( $data,$num = 10,$paging = array() )
        {

            $city = $data[ 'city' ];
            $city_id = $data[ 'city_id' ];    // 城市ID
            $latitude = $data[ 'latitude' ];


            $longitude = $data[ 'longitude' ];

            $word = $data[ 'word' ];
            $industry = $data[ 'industry_id' ];
            $last_id = $data[ 'last_id' ] ? $data[ 'last_id' ] : 0;
            $status = $data[ 'status' ];
            $content = $data[ 'user_content' ];

            $district_id = $data[ 'district_id' ];    // 区域ID

            if ( $latitude || $longitude ){
                if ( empty( $latitude ) || empty( $longitude ) ){
                    return _dat( '坐标格式错误' );
                }
                if ( !( is_numeric( $latitude ) && is_numeric( $longitude ) ) ){
                    return _dat( '非法操作' );
                }
            }
            $order = 'id desc';
            if ( $data[ 'order_by' ] ){
                switch ( $data[ 'order_by' ] ){
                    case 3:
                        $order = 'score desc';
                        break;
                    case 2:
                        $order = 'distance asc';
                        break;
                    default:
                        $order = 'id desc';
                        break;
                }
            }

            $num = $num <= 0 ? 10 : $num;
            if ( !$paging ){
                $paging[ 'page' ] = 1;
                $paging[ 'last_id' ] = 0;
            }
            // 如果 last_id 和 page 都 <=0 即表示刚开始获取数据
            // 如果 last_id 和 page 都 >0，则以 last_id 为先
            if ( $paging[ 'last_id' ] > 0 ){
                $where[ 'id' ] = array( "lt",
                    $paging[ 'last_id' ]
                );
                $limit = $num;
            }
            else{
                $paging[ 'page' ] = $paging[ 'page' ] <= 1 ? 1 : $paging[ 'page' ];
                $start = ( $paging[ 'page' ] - 1 ) * $num;
                $limit = $start . "," . $num;
            }

            if ( $city && !$city_id ){
                $cid = M( 'region' )->where( array( 'name' => $city ) )->getField( 'id' );
                if ( empty( $cid ) ){
                    $city_where[ 'name' ] = array( 'like',
                        "{$city}%"
                    );
                    $cid = M( 'region' )->where( $city_where )->getField( 'id' );
                }
                if ( $cid ){
                    $city_id = $cid;
                }
            }
            if ( $word ){
                $where[ 'shop_name' ] = array( 'like',
                    "%{$word}%"
                );
            }
            $city_n = explode( ",",$city_n );

            if ( count( $city_n ) > 1 ){
                $where[ 'city_id|district_id' ] = array( "in",
                    $city_n
                );
            }
            else{
                if ( $city_id ){
                    $where[ 'city_id|district_id' ] = $city_id;
                }
                // $where['city_id'] = $city_id;
            }

            if ( $latitude > 0 && $longitude > 0 ){

                $distance = $data[ 'distance' ];
                if ( !$distance ){
                    $distance = 0.5;
                }

                // $where['_string'] = "abs(latitude-{$latitude})<{$distance} and abs(longitude-{$longitude})<{$distance}";
            }
            if ( $industry ){
                $where[ 'industry' ] = $industry;
            }
            if ( $status == 99 ){
                $where[ 'status' ] = 0;
            }
            else{
                $where[ 'status' ] = 1;
            }
            if ( $data[ 'shop_id' ] ){
                $where[ 'id' ] = $data[ 'shop_id' ];
            }

            if ( $data[ 'content' ] ){
                $where[ 'contact_person|tel|shop_name' ] = array( 'like',
                    '%' . $data[ 'content' ] . '%'
                );
            }

            if ( $data[ 'user_content' ] ){
                if ( is_numeric( $data[ 'user_content' ] ) && strlen( $data[ 'user_content' ] ) < 10 ){
                    // 小于10位，并且是数字，按用户ID搜索
                    $where_u[ 'id' ] = $data[ 'user_content' ];
                    $userArr = M( 'user' )->where( $where_u )->getField( "id",true );
                    if ( $userArr ){
                        $where[ 'user_id' ] = array( "in",
                            $userArr
                        );
                    }
                }
                else{
                    // 非数字，或长度大于10位，按用户名或手机号搜索
                    $where_u[ 'phone|realname' ] = array( "like",
                        "%" . $data[ 'user_content' ] . "%"
                    );
                    $userArr = M( 'user' )->where( $where_u )->getField( "id",true );
                    if ( $userArr ){
                        $where[ 'user_id' ] = array( "in",
                            $userArr
                        );
                    }
                }
            }


            if ( $data[ 'cre_date' ] ){
                $getCreTime = get_time_style( $data[ 'cre_date' ] );
                $where[ 'cre_time' ] = array( array( "gt",
                    $getCreTime[ 0 ]
                ),
                    array( "elt",
                        $getCreTime[ 1 ]
                    )
                );
            }


            if ( $content ){
                if ( is_numeric( $content ) && strlen( $content ) < 10 ){
                    // 小于10位，并且是数字，按用户ID搜索
                    $where_u[ 'id' ] = $content;
                    $userArr = M( 'user' )->where( $where_u )->getField( "id",true );
                    if ( $userArr ){
                        $where[ 'user_id' ] = array( "in",
                            $userArr
                        );
                    }
                }
                else{
                    // 非数字，或长度大于10位，按用户名或手机号搜索
                    $where_u[ 'phone|realname' ] = array( "like",
                        "%" . $content . "%"
                    );
                    $userArr = M( 'user' )->where( $where_u )->getField( "id",true );
                    if ( $userArr ){
                        $where[ 'user_id' ] = array( "in",
                            $userArr
                        );
                    }
                }
            }
            $EARTH = 6378.137;     //地球半径
            $PI = 3.1415926535898; //PI值
            if ( $longitude ){
                if ( $data[ 'order_by' ] == 3 ){
                    $shop = M( "shop as s" )->join( 'left join sns_goods_comment as c on s.id=c.shop_id' )->where( $where )->limit( $limit )->field( "s.*,SUM('c.service_attitude') as service_attitude,$EARTH* ASIN(SQRT(POW(SIN($PI*(" . $longitude . "-latitude)/360),2)+COS($PI*" . $latitude . "/180)* COS(latitude * $PI/180)*POW(SIN($PI*(" . $longitude . "-longitude)/360),2))) as distance" )->group( 's.id' )->order( 'service_attitude desc' )->select();
                }
                else{
                    $shop = M( "shop" )->where( $where )->limit( $limit )->field( "*,$EARTH* ASIN(SQRT(POW(SIN($PI*(" . $longitude . "-latitude)/360),2)+COS($PI*" . $latitude . "/180)* COS(latitude * $PI/180)*POW(SIN($PI*(" . $longitude . "-longitude)/360),2))) as distance" )->order( $order )->select();
                }
            }
            else{
                if ( $data[ 'order_by' ] == 3 ){
                    $shop = M( "shop as s" )->join( 'left join sns_goods_comment as c on s.id=c.shop_id' )->where( $where )->limit( $limit )->field( "s.*,SUM('c.service_attitude') as service_attitude,$EARTH* ASIN(SQRT(POW(SIN($PI*(" . $longitude . "-latitude)/360),2)+COS($PI*" . $latitude . "/180)* COS(latitude * $PI/180)*POW(SIN($PI*(" . $longitude . "-longitude)/360),2))) as distance" )->group( 's.id' )->order( 'service_attitude desc' )->select();
                }
                else{
                    $shop = M( "shop" )->where( $where )->limit( $limit )->order( "id desc" )->select();
                }
            }
            if ( empty( $shop ) ){
                return _dat( '暂无数据' );
                $last_id = 0;
            }
            else{
                $shop_end = end( $shop );
                $last_id = $shop_end[ 'id' ];
            }

            $data = array();


            foreach ( $shop as $v ){
                if ( $v[ 'distance' ] ){
                    $d = $v[ 'distance' ];
                }
                else{
                    $d = '';
                }
                $d = round( $d,2 );
                /*		$d = GetDistance($latitude, $longitude, $v['latitude'], $v['longitude']) . 'KM';
                        if (!$latitude || !$longitude)
                        {
                            $d = '';
                        }*/
                $where1[ 'shop_id' ] = array( 'eq',
                    $v[ 'id' ]
                );
                $where1[ 'order_status' ] = array( 'in',
                    '1,2,3,5,7,8'
                );
                $shop_total = M( 'order_goods' )->where( $where1 )->sum( 'goods_total' );
                $shopFee = get_config_shop_fee( $v[ 'shop_fee_id' ] );
                $total_1 = M( "order" )->where( "order_status>0 and isdel=0 and order_type<1 and shop_id=" . $v[ 'user_id' ] )->sum( "total_commodity_price" );
                $total_2 = M( "order" )->where( "order_status>0 and isdel=0 and order_type=2 and shop_id=" . $v[ 'user_id' ] )->sum( "shop_note" );
                $total = round( floatval( $total_1 ) + floatval( $total_2 ),2 );
                $data[] = array( 'shop_id'        => $v[ 'id' ],
                                 'user_id'        => $v[ 'user_id' ],
                                 'user_name'      => get_user_field( $v[ 'user_id' ],"realname" ),
                                 'shop_name'      => $v[ 'shop_name' ],
                                 'shop_money'     => $v[ 'shop_money' ],
                                 'contact_person' => $v[ 'contact_person' ],
                                 'industry_name'  => $v[ 'industry_name' ],
                                 'cre_date'       => $v[ 'cre_date' ],
                                 'thumb'          => full_pre_url( $v[ 'thumb' ] ),
                                 'tel'            => $v[ 'tel' ],
                                 'description'    => $v[ 'description' ],
                                 'address'        => $v[ 'province' ] . $v[ 'city' ] . $v[ 'district' ] . $v[ 'address' ],
                                 'distance'       => $d,
                                 'latitude'       => $v[ 'latitude' ],
                                 'longitude'      => $v[ 'longitude' ],
                                 'shop_fee_id'    => $v[ 'shop_fee_id' ],
                                 'percent1'       => $shopFee[ 'percent1' ] ? $shopFee[ 'percent1' ] : 0,
                    // 上交服务费，百分比
                                 'percent2'       => $shopFee[ 'percent2' ] ? $shopFee[ 'percent2' ] : 0,
                    // 返还给用户，百分比
                                 'percent3'       => $shopFee[ 'percent3' ] ? $shopFee[ 'percent3' ] : 0,
                    // 返还给商家，百分比
                                 'total'          => $total,
                                 'shop_total'     => $shop_total ? $shop_total : '0.00',
                );
                if ( empty( $city_id ) ){
                    $city_id = $v[ 'city_id' ];
                }
            }

            return _dat( '商家列表',0,array( 'shop'    => $data,
                                         'last_id' => $last_id,
                                         'city_id' => $city_id,
                                         'where'   => $where,
            ) );
        }


        public function getNums( $where,$num )
        {
            $num = $num >= 1 ? $num : 10;
            $total = M( "shop" )->where( $where )->count();
            $total = $total ? $total : 0;
            $max_page = ceil( $total / $num );
            $data[ 'total' ] = $total;
            $data[ 'max_page' ] = $max_page;
            return $data;
        }

        public function checkShop( $data,$num,$paging )
        {

        }

        /**
         * 商家订单列表 ，可搜索
         * @param string $where 搜索条件
         * @param string $order 搜索排序
         * @param string $num 展示数量
         * @param string $paging 分页展示，last_id 和 page 可同时存在，last_id优先，page为次，
         * @return  json
         * @zd
         */
        public function orderList( $where,$order = "id desc",$num = 10,$paging = array() )
        {

            // 搜索条件
            if ( !isset( $where[ "isdel" ] ) ){
                $where[ "isdel" ] = 0;
            }

            $num = $num <= 0 ? 10 : $num;
            if ( !$paging ){
                $paging[ 'page' ] = 1;
                $paging[ 'last_id' ] = 0;
            }
            // 如果 last_id 和 page 都 <=0 即表示刚开始获取数据
            // 如果 last_id 和 page 都 >0，则以 last_id 为先
            if ( $paging[ 'last_id' ] > 0 ){
                $where[ 'id' ] = array( "lt",
                    $paging[ 'last_id' ]
                );
                $limit = $num;
            }
            else{
                $paging[ 'page' ] = $paging[ 'page' ] <= 1 ? 1 : $paging[ 'page' ];
                $start = ( $paging[ 'page' ] - 1 ) * $num;
                $limit = $start . "," . $num;
            }

            $where[ 'order_type' ] = 0;

            $list = M( "order_goods" )->where( $where )->limit( $limit )->order( $order )->select();
            if ( $list ){
                foreach ( $list as $key => &$value ){
                    $order = M('order')->where('id='.$value['order_id'])->find();
                    $value['shipping_type'] = $order['shipping_type'];
                    $value[ 'goods_thumb' ] = full_pre_url( $value[ 'goods_thumb' ] );
                    $value[ "order_status_str" ] = get_order_status( $value[ 'order_status' ] );
                    if($order['shipping_type'] == 1){
                        $value['shipping_type_name'] = '快递发货';
                    }
                    if($order['shipping_type'] == 2){
                        $value['shipping_type_name'] = '门店自提';
                        if($value['order_status'] == 1 || $value['order_status'] == 2){
                            $value[ "order_status_str" ] = "待自提";
                        }
                    }
                }

                return $list;
            }
            else{
                return array();
            }

        }

        /**
         * 获取数量 / 分页
         * @param array $where 条件
         * @param int $num 分页数量
         * @return  array            总页数和总数
         * @zd
         */
        public function getNums2( $where,$num )
        {
            $num = $num >= 1 ? $num : 10;
            $total = M( "order_goods" )->where( $where )->count();
            $total = $total ? $total : 0;
            $max_page = ceil( $total / $num );
            $data[ 'total' ] = $total;
            $data[ 'max_page' ] = $max_page;
            return $data;
        }

        /**
         * @param $data
         * Notes:添加线下门店
         * User: WangSong
         * Date: 2020/7/8
         * Time: 9:03
         */
        public function applay_offile_shop()
        {
            $data = I();
            if ( !$data[ 'shop_name' ] ){
                return '请输入店铺名称';
            }
            if ( !$data[ 'shop_logo' ] ){
                return '请输入店铺logo';
            }
            if ( !$data[ 'tel' ] ){
                return '请输入店铺电话';
            }
            if ( !$data[ 'business_hours' ] ){
                return '请输入商家营业时间';
            }
            //商家营业日期 周一 周二等
            if ( !$data[ 'week' ] ){
                return '请输入商家营业日期';
            }
            if ( !$data[ 'shop_id' ] ){
                $shop = M( 'shop' )->where( 'user_id=' . $this->_user_id . ' and status=1' )->find();
                if ( !$shop ){
                    return '该商家不存在';
                }
                else{
                    $data[ 'shop_id' ] = $shop[ 'id' ];
                }
            }
            if ( $data[ 'id' ] ){
                $shop_offline = M( 'shop_offline' )->where( 'id=' . $data[ 'id' ] . ' and is_del=0' )->find();
                if ( !$shop_offline ){
                    return '该线下门店信息不存在';
                }
            }
            if ( $data[ 'id' ] ){
                $data[ 'up_time' ] = time();
                $re = M( 'shop_offline' )->where( 'id=' . $data[ 'id' ] )->save( $data );
            }
            else{
                $data[ 'add_time' ] = time();
                $re = M( 'shop_offline' )->add( $data );
            }
            if ( !$re ){
                return '操作失败';
            }
            else{
                return $re;
            }

        }

        /**
         * Notes:删除线下门店
         * User: WangSong
         * Date: 2020/7/8
         * Time: 9:33
         */
        public function delete_shop_offile()
        {
            $data = I();
            if ( !$data[ 'id' ] ){
                return '缺少线下门店id参数';
            }
            $shop_offline = M( 'shop_offline' )->where( 'id=' . $data[ 'id' ] . ' and is_del=0' )->find();
            if ( !$shop_offline ){
                return '该线下门店不存在';
            }
            $da[ 'is_del' ] = 1;
            $da[ 'up_time' ] = time();
            $re = M( 'shop_offline' )->where( 'id=' . $data[ 'id' ] )->save( $da );
            if ( !$re ){
                return '删除失败';
            }
            else{
                return $re;
            }

        }

        /**
         * Notes:开启或者关闭线下门店
         * User: WangSong
         * Date: 2020/7/8
         * Time: 9:38
         */
        public function is_offline_shop()
        {
            $data = I();
            if ( !$data[ 'shop_id' ] ){
                return '缺少shop_id参数';
            }
            $shop = M( 'shop' )->where( 'id=' . $data[ 'shop_id' ] )->find();
            if ( !$shop ){
                return '该商家不存在';
            }
            if ( $shop[ 'offline_shop_auth' ] == 0 ){
                return '该商家暂时没有线下门店权限';
            }
            $da[ 'up_time' ] = time();
            $da[ 'up_date' ] = date( 'Y-m-d H:i:s',time() );
            $da[ 'is_offline_shop' ] = $data[ 'is_offline_shop' ];
            $re = M( 'shop' )->where( 'id=' . $data[ 'shop_id' ] )->save( $da );
            if ( $re ){
                return $re;
            }
            else{
                return '操作失败';
            }
        }

        /**
         * @param $shop_id
         * @return mixed
         * Notes:获取线下门店
         * User: WangSong
         * Date: 2020/7/10
         * Time: 8:37
         */
        public function get_shop_offline( $shop_id )
        {
            $shop_offline = M( 'shop_offline' )->where( 'shop_id=' . $shop_id )->order( 'id desc' )->select();
            foreach ( $shop_offline as $k => &$v ){
                $v[ 'business_hours' ] = $v[ 'week1' ] . '到' . $v[ 'week2' ] . ';' . $v[ 'business_hours1' ] . '-' . $v[ 'business_hours2' ];
            }
            return $shop_offline;
        }

        /**
         * Notes:商家订单详情
         * User: WangSong
         * Date: 2020/8/7
         * Time: 14:36
         */
       /* public function shop_order_detail()
    {
        $data = I();
        if ( !$data[ 'order_goods_id' ] ){
            return '缺少订单id参数';
        }
        $order_goods = M( 'order_goods' )->where( 'id=' . $data[ 'order_goods_id' ] )->find();
        $order_goods[ 'order_status_sn' ] = get_order_status( $order_goods[ 'order_status' ] );
        $order = M('order')->where('id='.$order_goods['order_id'])->find();
        if($order['shipping_type'] == 2){
            if($order_goods['order_status'] == 1 ||$order_goods['order_status'] == 2){
                $order_goods[ 'order_status_sn' ] = "待自提";
            }
        }
        if ( !$order_goods ){
            return '该订单不存在';
        }
        if ( !$order_goods[ 'shop_id' ] ){
            $order_goods[ 'shop_name' ] = '平台商城';
            $order_goods[ 'thumb' ] = C( 'C_DF_SHOP_LOGO' );
            $order_goods[ 'phone' ] = get_config('kefu_mobile');
        }
        else{
            $shop = M( 'shop' )->where( 'id=' . $order_goods[ 'shop_id' ] )->find();
            $order_goods[ 'shop_name' ] = $shop[ 'shop_name' ];
            $order_goods[ 'thumb' ] = $shop[ 'thumb' ];
            $order_goods[ 'phone' ] = $shop[ 'tel' ];
        }

        $order = M( 'order' )->where( 'id=' . $order_goods[ 'order_id' ] )->find();

        $order['add_date'] = date('Y-m-d H:i:s',$order['add_time']);
        $da[ 'order_goods' ] = $order_goods;
        $da[ 'order' ] = $order;
        return $da;
    }*/
        public function shop_order_detail()
        {
            $data = I();
            if ( !$data[ 'order_id' ] ){
                return '缺少订单id参数';
            }
            $order_goods = M( 'order_goods' )->where( 'order_id=' . $data[ 'order_id' ].' and shop_id='.$this->_shop_id.' and order_status=1' )->select();
            $order = M('order')->where('id='.$data[ 'order_id' ])->find();
            if($order['shipping_type'] == 2){
                if($order_goods['order_status'] == 1 ||$order_goods['order_status'] == 2){
                    $order_goods[ 'order_status_sn' ] = "待自提";
                }
            }
            if ( !$order_goods ){
                return '该订单不存在';
            }
          /*  if ( !$order_goods[ 'shop_id' ] ){
                $order_goods[ 'shop_name' ] = '平台商城';
                $order_goods[ 'thumb' ] = C( 'C_DF_SHOP_LOGO' );
                $order_goods[ 'phone' ] = get_config('kefu_mobile');
            }
            else{
                $shop = M( 'shop' )->where( 'id=' . $order_goods[ 'shop_id' ] )->find();
                $order_goods[ 'shop_name' ] = $shop[ 'shop_name' ];
                $order_goods[ 'thumb' ] = $shop[ 'thumb' ];
                $order_goods[ 'phone' ] = $shop[ 'tel' ];
            }*/

            $order['add_date'] = date('Y-m-d H:i:s',$order['add_time']);
            $da[ 'order_goods' ] = $order_goods;
            $da[ 'order' ] = $order;
            return $da;
        }

        /**
         * @param $data
         * @return array
         * Notes:获取商家订单列表
         * User: WangSong
         * Date: 2020/9/18
         * Time: 17:43
         */
        public function get_shopOrderList($data){
            if(!$data){
                $data = I();
            }
            if (intval($data['page']) < 1)
            {
                $page = 1;
            }
            else
            {
                $page = $data['page'];
            }
            $num = 10;

            if ($data['order_status'] >= 0 && is_numeric($data['order_status']))
            {
                $where['order_status'] = $data['order_status'];
            }
            if ($data['add_time'])
            {
                $addTimeArr = explode(" - ", $data['add_time']);
                $addTimeArr[0] = strtotime($addTimeArr[0]);
                $addTimeArr[1] = strtotime($addTimeArr[1]);
                $where['add_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
            }
            if ($data['pay_time'])
            {
                $addTimeArr = explode(" - ", $data['pay_time']);
                $addTimeArr[0] = strtotime($addTimeArr[0]);
                $addTimeArr[1] = strtotime($addTimeArr[1]);
                $where['pay_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
            }
            if ($data['shipping_time'])
            {
                $addTimeArr = explode(" - ", $data['shipping_time']);
                $addTimeArr[0] = strtotime($addTimeArr[0]);
                $addTimeArr[1] = strtotime($addTimeArr[1]);
                $where['shipping_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
            }
            if ($data['confirm_time'])
            {
                $addTimeArr = explode(" - ", $data['confirm_time']);
                $addTimeArr[0] = strtotime($addTimeArr[0]);
                $addTimeArr[1] = strtotime($addTimeArr[1]);
                $where['confirm_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
            }
            if ($data['content'])
            {
                if (is_numeric($data['content']) && strlen($data['content']) < 10)
                {
                    // 小于10位，并且是数字，按用户ID搜索
                    $where_u['id'] = $data['content'];
                    $userArr = M('user')->where($where_u)->getField("id", true);
                    if ($userArr)
                    {
                        $where['user_id'] = array("in", $userArr);
                    }
                }
                else
                {
                    // 非数字，或长度大于10位，按用户名或手机号搜索
                    $where_u['phone|realname'] = array("like", "%" . $data['content'] . "%");
                    $userArr = M('user')->where($where_u)->getField("id", true);
                    if ($userArr)
                    {
                        $where['user_id'] = array("in", $userArr);
                    }
                }
            }
            if ($data['goods_name'])
            {
                $where['goods_name'] = array("like", "%" . $data['goods_name'] . "%");

            }
            if ($data['order_no'])
            {
                $where['order_no'] = array("like", "%" . $data['order_no'] . "%");

            }
            if ($data['tracking_number'])
            {
                $where['tracking_number'] = array("like", "%" . $data['tracking_number'] . "%");
            }
            if ($data['payment_type'])
            {
                $where['payment_type'] = $data['payment_type'];
            }
            if ($data['pay_status'])
            {
                $where['pay_status'] = $data['pay_status'];
            }
            $where['isdel'] = 0;
            $where['shop_id'] = $data['shop_id'];
            $where["order_type"] = array('not in','1,2,3,4');
            $new_list = M('order_goods')->where($where)->group('order_id,shop_id')->order('order_id desc')
                ->field('shop_id,order_id as id,order_status,order_no,user_id,order_type,pay_status,shipping_status')->select();
            $count = count($new_list);
            $pages = new Page($count,$num);
            $list = array_slice($new_list,($page-1)*$num,$num);
            foreach ($list as $k=>&$v){
                $goods = M('order_goods')->where('order_id=' . $v['id'].' and shop_id='.$v['shop_id'])->select();
                $order = M('order')->where('id='.$v['id'])->find();
                $v['total_commodity_price'] = M('order_goods')->where('order_id=' . $v['id'].' and shop_id='.$v['shop_id'])->sum('goods_total');
                $v['goods'] = $goods ? $goods : array();
                $v['goods_count'] = count($goods);
                $v[ "order_status_str" ] = get_order_status( $v[ 'order_status' ] );
                $v['order_status_cn'] = get_order_status($v['order_status']);
                $v['order_type_cn'] = get_order_type_cn($v['order_type']);
                $v['pay_status_cn'] = get_pay_status_cn($v['pay_status']);
                $v['shipping_status_cn'] = get_shipping_status_cn($v['shipping_status']);
                $v['shipping_type_cn'] = get_shipping_type_cn($order['shipping_type']);
                $v['payment_type_cn'] = payment_type_cn($order['payment_type']);

                $v['serial_no'] = get_order_trade_no($order['id'], '', "serial_no");
                $v['trade_no'] = get_order_trade_no($order['id'], '', "trade_no");

                $v['add_time_format'] = get_time($v['add_time']);
                $v['pay_time_format'] = get_time($v['pay_time']);
                $v['consignee'] = $order['consignee'];
                $v['phone'] = $order['phone'];
                $v['province_cn'] = $order['province_cn'];
                $v['city_cn'] = $order['city_cn'];
                $v['district_cn'] = $order['district_cn'];
                $v['address'] = $order['address'];
                if($order['shipping_type'] == 1){
                    $v['shipping_type_name'] = '快递发货';
                }
                if($order['shipping_type'] == 2){
                    $v['shipping_type_name'] = '门店自提';
                    if($v['order_status'] == 1 || $v['order_status'] == 2){
                        $v[ "order_status_str" ] = "待自提";
                    }
                }
            }
            return array(
                'list' => $list,
                'page' => $page,
                'current_page' => $data['page'],
                'max_page' => ceil($count/$num),
                'show' => $pages->show()
            );
        }
    }