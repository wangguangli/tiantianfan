<?php
namespace Admin\Controller;
use Clas\Goods;
use Clas\Ad;
use Clas\Article;
use Think\Image;
use Think\Page;

class MarketingController extends CommonController {

    public function goodscomment () {
        if($_POST){
            $user_id = I('user_id');
            if($user_id){
                $where['user_id'] = array('eq',$user_id);
            }
            $order_id = I('order_id');
            if($order_id){
                $where['order_id'] = array('eq',$order_id);
            }

            $text = I('text');
            if($text){
                $where['content'] = array('like','%'.$text.'%');
            }

            $sTime = I('sTime');
            $eTime = I('eTime');
            if($sTime && $eTime){
                $sTime = strtotime($sTime);
                $eTime = strtotime($eTime);
                $where['time'] = array('between',array($sTime,$eTime));
            }
        }

        $count = M('goods_comment')->where($where)->count();
        $page = new Page($count,10);
        $show = $page->show();
        $result = M('goods_comment')->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('id desc')->select();

        $this->assign("result", $result);
        $this->assign("show", $show);
        $this->display();
    }

    public function  deleteGoodsComment(){
        if (!$data)
        {
            $data = I();
        }
        $res = M('goods_comment')->where('id=' . $data['id'])->delete();
        if ($res) {
            return $this->success('删除成功');
        }
        return '删除失败,请确认id';

    }

    public function addGoodsComment()
    {
        if (!$data) {
            $data = I();
        }
        $goodsData = M('goods')->select();
        $this->assign('goodsData',$goodsData);
        if (IS_POST) {
	        $shop_id = 0;
        	if ($data['goods_id'])
	        {
	        	$shop_id = M("goods")->where("id=".$data['goods_id'])->getField("shop_id");
	        }
        	$data['shop_id'] = $shop_id;
            if (!$data['id']) {
                $data['time'] = time();

                $add = M('goods_comment')->data($data)->add();
                if ($add) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            } else {
                $upd = M('goods_comment')->where('id=' . $data['id'])->data($data)->save();
                if ($upd) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        } else {

            if (!$data['id']) {

                $this->display();
            }else{
                $res = M('goods_comment')->where('id=' . $data['id'])->find();
                $this->assign('res', $res);
                $this->assign('id', $data['id']);
                $this->display();
            }
        }


    }

    public function ad_detail() {
        if (IS_POST) {
            if(!$data){
                $data = I();
            }
            $obj = new Ad();
            $result = $obj->addAd($data);
            if ($result[1] < 1) {
                $this->success($result[0]);
            } else {
                $this->error($result[0]);
            }
        } else {
            $id = I('id');
            if($id){
                $res = M('ad')->where('id='.$id)->find();
                $this->assign('data',$res);
            }

            $ad_position = M('ad_position')->select();
    	    $catelist = M('goods_category')->where("pid=0")->order("id asc")->select();
            $this->assign('ad_position', $ad_position);
            $this->assign('catelist', $catelist);

            $this->display();
        }
    }
    public function edit_ad() {
        $id = I('id');
        $ad = M('ad')->find($id);
        jsonout('广告数据', 0, $ad);
    }
    public function del_ad() {
        $id = I('id');
        $result = M('ad')->delete($id);//未写删除图片操作
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    public function ad_position() {

        $count = M('ad_position')->count();
        $page = new Page($count,10);
        $show = $page->show();
        $result = M('ad_position')->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign("result", $result);
        $this->assign('show',$show);
        $this->display();
    }
    public function add_position() {
        if (IS_POST) {
            if(!$data){
                $data = I();
            }
            $obj = new Ad();
            $result = $obj->addPosition($data);
            if ($result[1] < 1) {
                $this->success($result[0]);
            } else {
                $this->error($result[0]);
            }
        } else {
            $id = I('id');
            if($id){
                $res = M('ad_position')->where('id='.$id)->find();
                $this->assign('data', $res);
            }
            $this->display();
        }
    }
    public function edit_position() {
        $id = I('id');
        $ad_position = M('ad_position')->find($id);
        jsonout('广告数据', 0, $ad_position);
    }
    public function del_position() {
        $id = I('id');
        $result = M('ad_position')->delete($id);
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    public function ad() {

        if($_POST){
            $title = I('title');

            if($title){

                $where['title'] = array('like','%' . $title . '%');
            }
            $id = I('id');
            if($id){
               $description =  M('ad_position')->where('id='.$id)->getField('description');
                $where['description'] = array('eq',"$description");
            }
        }

        $count = M('ad')->where($where)->count();
        $page = new Page($count,10);
        $show = $page->show();
        $result = M('ad')->where($where)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();

        $pos = M('ad_position')->select();
        $this->assign("result", $result);
        $this->assign('pos',$pos);
        $this->assign('show',$show);
        $this->display();
    }
    public function article() {
        if($_POST){
            $title = I('title');

            $where['title'] =  array('like','%'.$title.'%');
            $where1['a.title'] =  array('like','%'.$title.'%');
        }

        $count = M('article')->where($where)->count();
        $page = new Page($count,10);
        $show = $page->show();
        $result = M('article')->alias('a')->join('sns_article_category as b on a.cat_id=b.id')->where($where1)
                ->field('a.id,a.title,a.time,b.name')
            ->order('a.id desc')
            ->limit($page->firstRow.','.$page->listRows)
            ->select();

        $this->assign("result", $result);
        $this->assign('show',$show);
        $this->display();
    }

    //文章添加修改
    public function article_detail() {
       if($_POST){
            $data = I('post.');
            $data['time'] = time();
            IF(!empty($data['id'])){

                $res = M('article')->where('id=' . $data['id'])->data($data)->save();

            }else{

                $res = M('article')->data($data)->add();
            }
            if($res){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
       }
        $id = I('id');
        if($id){
            $res = M('article')->where('id='.$id)->find();
            $this->assign('data', $res);
        }
//        print_r($res);exit();
        $article_category = M('article_category')->select();
        $this->assign('article_category', $article_category);
        $this->display();
    }

    public function edit_article() {
        $id = I('id');
        $ad = M('article')->find($id);
        jsonout('广告数据', 0, $ad);
    }
    public function del_article() {
        $id = I('id');
        $result = M('article')->delete($id);
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    public function article_category() {
        $count = M('article_category')->count();
        $page = new Page();
        $show = $page->show();
        $result = M('article_category')->order('id desc')->limit($page->listRows.','.$page->firstRow)->select();
        $this->assign("result", $result);
        $this->display();
    }

//文章分了添加
    public function add_articleCat() {
        if (IS_POST) {
            if(!$data){
                $data = I();
            }
            $obj = new Article();
            $result = $obj->add_articleCat($data);
            if ($result[1] < 1) {
                $this->success($result[0]);
            } else {
                $this->error($result[0]);
            }
        } else {
            $id = I('id');
            if($id){
                $res = M('article_category')->where('id='.$id)->find();
                $this->assign('data', $res);
            }
            $this->display();
        }


    }
    public function edit_articleCat() {
        $id = I('id');
        $ad = M('article_category')->find($id);
        $ad["content"] = htmlspecialchars_decode($ad["content"]);
        jsonout('文章分类数据', 0, $ad);
    }
    public function del_articleCat() {
        $id = I('id');
        $result = M('article_category')->delete($id);
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

	// 优惠券列表 @zd
	public function couponList()
    {

        $count = M("coupon")->where("status=1 and is_del=0")->order("id desc")->field("id")->count();
        $page = new Page($count,10);
        $show = $page->show();
        $list = M("coupon")->where("status=1 and is_del=0")->order("id desc")->field("id")->limit($page->firstRow.','.$page->listRows)->select();

		foreach ($list as $key=>$value)
		{
			$info = get_coupon_info($value['id']);
			$list[$key] = $info;
		}
		$this->assign('show',$show);
		$this->assign("list", $list);
		$this->display();
	}

	// 优惠券增加/详情 @zd
	public function couponDetail()
	{
		$id = I("id", 0);
		if (IS_POST)
		{
			$data = I();
			$data['money'] = intval($data['money']);
			$data['conditions'] = intval($data['conditions']);
			$data['send_num'] = intval($data['send_num']);
			$data['get_num'] = intval($data['get_num']);
			$data['use_num'] = intval($data['use_num']);

			$data['use_start_time'] = strtotime($data['use_start_time']);
			$data['use_end_time'] = strtotime($data['use_end_time']);

			if ($data['start_type'] == 1)
			{
				$data['use_end_time'] = 0;
			}
			else
			{
				$data['use_days'] = 0;
			}
			if ($data['type'] == 1 || $data['type'] == 2)
			{
				$data['goods_id'] = 0;
				$data['shop_id'] = 0;
			}

			$data['cre_time'] = time();
			$data['cre_date'] = date("Y-m-d H:i:s");
			$data['ip'] = get_client_ip();
			$data['is_del'] = 0;

			if ($data['id'])
			{
				$rs = M("coupon")->where("id=".$data['id'])->save($data);
			}
			else
			{
				unset($data['id']);
				$rs = M("coupon")->add($data);
			}
			if ($rs !== false)
			{
				$this->success("操作成功");
			}
			else
			{
				$this->error("操作失败，请重新操作");
			}
			exit();
		}
		$data = get_coupon_info($id, 1);
		$this->assign("data", $data);
		$this->display();
	}

	// 删除优惠券 @zd
	public function couponDel()
	{
		$id = I("id", 0);
		if (!$id)
		{
			$this->error("参数异常，请确认");
		}
		$rs = M("coupon")->where("id=".$id)->setField("is_del", 1);
		if ($rs !== false)
		{
			$this->success("操作成功");
		}
		else
		{
			$this->error("操作失败，请重新操作");
		}
	}

    //省市区街道管理
    public function region(){
	    if($_POST){
            $type = I('type');
	        $id = I('id');
            if($type){
                $res = M('region')->where('parent_id='.$id)->field('id,name')->select();
                foreach ($res as $k => $v){
                    $html.="<option value=".$v['id'].">".$v['name']."</option>";
                }
                $data['result'] = $html;
                echo json_encode($data);
            }





        }else{
	        $region = M('region')->where('level=1')->select();
	        $this->assign('region',$region);
	        $this->display();
        }
    }


    //添加
    public function region_add(){
	     if($_POST){
            $id = I('id');

            if(!empty($id)){

                $region = M('region')->where('id='.$id)->find();
                $data['level'] = ++$region['level'];
                $data['parent_id'] = $id;
            }else{

                $data['level'] = 1;
            }
            $data['name'] = I('name');
            $data['adcode'] = I('adcode');
            $data['indexes'] = I('indexes');
            $res = M('region')->add($data);
            if($res){

                $this->success('操作成功','region');

            }else{
                $this->error('操作失败');
            }
         }else{
	         $id = I('id');

             if($id){
                 $res = M('region')->where('id='.$id)->find();
                 $da = $res['name'];
                 if($res['level']>1){
                     $ress = M('region')->where('id='.$res['parent_id'])->find();
                     $da = $ress['name'].$da;
                     if($ress['level']>1){
                     $resss = M('region')->where('id='.$ress['parent_id'])->find();
                     $da = $resss['name'].$da;
                     }
                 }

                 $this->assign('da',$da);
                 $this->assign('id',$id);
             }

             $this->display();
	     }



    }


    //修改地址信息
    public function region_upd(){
	    if($_POST){
            $id = I('id');
            $data['name'] = I('name');
            $data['adcode'] = I('adcode');
            $data['indexes'] = I('indexes');
            $res = M('region')->where('id='.$id)->save($data);
            if($res){
                $this->success('修改成功','region');
            }else{
                $this->error('修改失败');
            }
        }else{
            $id= I('id');
            $region  = M('region')->where('id='.$id)->find();
            if($region['level']>1){
              $data =  M('region')->where('id='.$region['parent_id'])->find();
              $da = $data['name'];
              if($data['level']>1){
                  $data = M('region')->where('id='.$data['parent_id'])->find();
                  $da = $data['name'].$da;
                  if($data['level']>1){
                      $date = M('region')->where('id='.$data['parent_id'])->find();
                      $da = $date['name'].$da;
                  }
              }
              $this->assign('da',$da);
            }

            $this->assign('region',$region);
            $this->display();

        }

    }


    //删除省市区
    public function region_del(){
	   $id =  I('id');
        if(!$id){
            $this->error('参数不能为空');
        }
        $region = M('region')->where('parent_id='.$id)->find();
        if($region){
            $this->error('有下级的地址无法删除');
        }else{
            $res = M('region')->where('id='.$id)->delete();
            if($res){
                $this->success('删除成功','region');
            }else{
                $this->error('删除失败');
            }

        }
    }


    /**
     * Notes:领取优惠券列表
     * User: YangHao
     * Date: 2020/10/29
     * Time: 8:54
     */
    public function user_coupon(){
        $id=I("id");
        if (!$id){
            $this->error("缺少重要参数！");
        }
        $count=M("coupon_user")->where("is_hide=0 and cid=".$id)->count();
        $page = new Page($count,10);
        $show = $page->show();
        $coupon_user=M("coupon_user")->where("is_hide=0 and cid=".$id)->select();
        foreach ($coupon_user as $key=>$value){
            $coupon=M("coupon")->where("id=".$value['cid'])->find();
            $coupon_user[$key]['name']=$coupon['name'];
            $where_u['id']=$value['user_id'];
            $user=get_user_info($where_u);
            $coupon_user[$key]['phone']=$user['phone'];

        }

        $this->assign('show',$show);
        $this->assign('list',$coupon_user);
        $this->display();
    }

    public function userCouponDel()
    {
        $id = I("id", 0);
        if (!$id)
        {
            $this->error("参数异常，请确认");
        }
        $rs = M("coupon_user")->where("id=".$id)->setField("is_hide", 1);
        if ($rs !== false)
        {
            $this->success("操作成功");
        }
        else
        {
            $this->error("操作失败，请重新操作");
        }
    }



}