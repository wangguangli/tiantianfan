<?php

namespace M\Controller;

use Clas\Ad;
use Clas\Shop;
use Clas\Address;

header("Content-type: text/html; charset=utf-8");

use Think\Controller;

class NearController extends Controller
{
    //附近  首页
    public function index()
    {
        $ad = new Ad();

        // banner
        $data["position"] = "near_top_banner";
        $data["row"] = 3;
        $ad1 = $ad->adList($data);
        //banner 图
        $ad1 = $ad1[2];

        $catelist = M('industry')->field('id,name,icon')->select();
        if (!$catelist) {
            $catelist = array();
        }

        $ad2 = array();    //广告列表

        $ad = new Ad();
        // 广告列表
        $data["position"] = "near_center";
        $data["row"] = 6;
        $ad3 = $ad->adList($data);
        //广告列表
        $ad3 = $ad3[2];

        $result["ad1"] = full_pre_url($ad1, "photo");
        $catelist = full_pre_url($catelist, "icon");
        foreach ($catelist as $key => &$value) {
            $value["img"] = $value["icon"];
        }
        //分类
        $result["catelist"] = $catelist;
        $result["ad2"] = $ad3;
        $this->assign('data', $result);
        $this->assign('menu_near', 'cut');
        $this->display();
    }

    public function shop()
    {
        $data = I();


        if (!empty($data)) {
            $last_id = $data['last_id'] ? $data['last_id'] : 0;
            $page = $data['page'] ? $data['page'] : 1;
            $num = $data['num'] ? $data['num'] : 10;

            $paging['page'] = $page;
            $paging['last_id'] = $last_id;
            $user_id = session('user_id');
            $obj = new Shop(0, 'shop', $user_id);
            $ok = $obj->shop($data, $num, $paging);

            if (!$ok["result"]["shop"]) {
                $res['list'] = array();
                $res['page'] = $page;
                $res['last_id'] = $last_id;
                $res['max_page'] = 0;
            } else {
                $numArr = $obj->getNums($ok['result']['where'], $num);
                $res['list'] = $ok['result'];
                $res['page'] = $page;
                $res['last_id'] = $ok['result']['last_id'];
                $res['max_page'] = $numArr['max_page'];
            }
            jsonout($ok['msg'], $ok['err'], $res);
        }
    }

    /*
	 * @param   string   市
	 * @lz
	*/
    public function city()
    {
        if (IS_POST) {
            $search = I('content');
            $where['name'] = array('like', '%' . $search . '%');
            $city = M('region')->where('level=2')->where($where)->field('id,name')->select();

            $where_one['name'] = array('in', array('北京市', '天津市', '上海市', '重庆市'));
            $city_a = M('region')->field('id,name')->where($where_one)->select();
            foreach ($city_a as $k => $v) {
                $arr[$k] = $v['id'];

            }
            $where['id'] = array('in',$arr);
            $city_a = M('region')->field('id,name')->where($where)->where($where)->select();

            if(!empty($city_a)){
                foreach ($city_a as $k => $v) {
                    array_unshift($city, $v);
                }
            }

            if ($city) {
                jsonout('成功', 0, $city);
            } else {
                jsonout('失败', 1);
            }

        } else {

            $where_one['name'] = array('in', array('北京市', '天津市', '上海市', '重庆市'));
            $city = M('region')->field('id,name')->where($where_one)->select();
            $arr = array();
            foreach ($city as $k => $v) {
                $arr[$k] = $v['id'];

            }
            $where_two['parent_id'] = array('not in', $arr);
            $sheng = M('region')->where('level = 2')->where($where_two)->field('id,name')->select();
            foreach ($city as $k => $v) {
                array_unshift($sheng, $v);
            }

            $this->assign('city', $sheng);
            $this->display();
        }

    }

    //搜索店铺
    public function search()
    {
        if (IS_POST) {
            $content = I('content');
            $where['shop_name'] = array('like', '%' . $content . '%');
            $shop = M('shop')->where($where)->select();
            if ($shop) {
                echo jsonout('成功', 0, $shop);
            } else {
                echo jsonout('失败', 1);
            }

        } else {
            $this->display();
        }

    }

}