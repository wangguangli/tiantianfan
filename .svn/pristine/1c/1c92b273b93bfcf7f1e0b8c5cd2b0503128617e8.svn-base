<?php
namespace Api\Controller;

use Clas\Goods;
use Clas\Shop;


class GoodsinController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 新增或删除规格
     * @param null $data type_id 商品小类id  spec_id 规格表id name规格分类名称  order规格排序  spec_item规格集合
     * @mgl
     */
    public function addSpec($data=null)
    {

        if(!$data){

            $data=I();
        }
        $obj = new Goods($data);
        $ok = $obj->addSpec($data);
        if (is_array($ok)){
            jsonout($ok[0],$ok[1],$ok[2]);
        }
        jsonout($ok);
    }

    /**
     *
     * @param int $id 必须    int 规格表id
     * @return int|string
     * @mgl
     */
    public function deleteSpec($data=null)
    {

        if(!$data){
            $data=I();

        }
        $obj = new Goods($data);
        $ok = $obj->deleteSpec($data);

        if (is_array($ok)){
            jsonout($ok[0],$ok[1],$ok[2]);
        }
        jsonout($ok);
    }


    /**
     *
     * @param int $pid 可选  int 父类id
     * @param int $id 可选  int 类别表id
     * @param string $name 可选  string 类别名称
     * @return array|string
     * @mgl
     */
    function editCategory($data=null)
    {
        if(!$data){
            $data=I();

        }

        $obj = new Goods($data);
        $ok = $obj->editCategory($data);
        if (is_array($ok)){
            jsonout($ok[0],$ok[1],$ok[2]);
        }
        jsonout($ok);
    }

    /**
     * @param int $id 可选  int 类别id
     * @return array|string
     * @mgl
     */
    public function deleteCategory($data=null)
    {

        if(!$data){
            $data=I();
        }

        $obj = new Goods($data);
        $ok = $obj->deleteCategory($data);
        if (is_array($ok)){
            jsonout($ok[0],$ok[1],$ok[2]);
        }
        jsonout($ok);
    }


    /**
     * 商品添加
     * 一般传入参数
     * @param   int $user_id 平台管理人员id或商家user_id
     * @param   string $name 商品名称
     * @param   string $subhead 副标题
     * @param   int $price 本店价格
     * @param   int $market_price 市场价格
     * @param   int $hot_id 1.正品热卖 2.热销商品 3.精品推荐 4.新品上市 5.猜你喜欢
     * @param   int $sell_count 商品销量
     * @param   int $store_count 库存
     * @param   int $cat_id 商品分类
     * @param   image $thumb 商品封面图片（正方形）
     * @param   object[] $images 展示图片 可传多个图片，格式：图片1|图片2|图片3
     * @param   int $shipping_fee 邮费 0是包邮
     * @param   int $content 商品详情
     * @return  json
     * 目前只做基础的处理
     * @yh
     */
    public function addGoods($data=null)
    {
        if (!$data)
        {
            $data = I();
        }
        $obj = new Goods($data);
        $a = $obj->add($data);
        jsonout($a);

    }






    /**
     *删除评论
     * @param   images $id 评论表id
     * @return  json
     * @mgl
     */
    function deleteGoodsComment($data=null)
    {
        if (!$data)
        {
            $data = I();
        }
        $obj = new Goods($data);
        $a = $obj->delete_goods_comment($data);
        if(is_array($a)){
            jsonout($a[0], $a[1], $a[2]);

        }
        jsonout($a);

    }

    /*
     * 删除商品
     * 商品  int  id
     * yh
     * */

    public function deleteGoods($data=null) {
        if (!$data)
        {
            $data = I();
        }
        $obj = new Goods($data['shop_id']);
        $del_goods=$obj->delete($data);
        jsonout($del_goods);

    }

	/*
	 * 增加规格类型
	 * 目前只后台用 -- 2020.04.02
	 * @zd
	 * */
	public function addGoodsType()
	{
		$name = I("name", '');      // 规格类型名称
		$shop_id = I("shop_id", 0); // 商家ID
		if (!$name)
		{
			jsonout("请输入名称");
		}
		$data['name'] = $name;
		$data['shop_id'] = $shop_id;
		$data['cre_time'] = time();
		$rs = M("goods_type")->add($data);
		if ($rs)
		{
			$ul = "<option value='0'>请选择规格类型</option>";
			if ($shop_id)
			{
				$where = "shop_id=0 or shop_id=".$shop_id;
			}
			else
			{
				$where = "";
			}
			$list = M("goods_type")->where($where)->order("id desc")->select();
			foreach ($list as $key => $value)
			{
				$ul .= "<option value='" . $value['id'] . "'>" . $value['name'] . "</option>";
			}
			jsonout("添加成功", 0, $ul);
		}
		else
		{
			jsonout("添加失败，请重新添加");
		}
	}

    /**
     * Notes:设置商品上下架
     * User: WangSong
     * Date: 2020/8/7
     * Time: 9:09
     */
    public function goods_is_on_sale(){
	    $goods = new Goods();
        $re = $goods->goods_is_on_sale();
        if(is_numeric($re)){
            jsonout('操作成功',0);
        }else{
            jsonout($re,1);
        }
    }

}