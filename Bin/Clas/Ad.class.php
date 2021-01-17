<?php
namespace Clas;

class Ad {
	
    //添加广告
    public function addAd($data){
		$description = $data['description'];
		$position = $data['position'];
		if (empty($position)) {
			return '广告关键字不能为空';
		}
		$position_name = M('ad_position')->where('position='."'".$position."'")->find();
		$date = array(
			'title' => $data['title'],
			'content' => $data['content'],
			'description' => $position_name['description'],
			'position' => $position_name['position'],
			'link' => $data['link'],
			'photo' => $data['photo'],
			'module' => $data['module'],
			'width' => $position_name['width'],
			'height' => $position_name['height'],
			'time' => time(),
			'ip' => get_client_ip(),
			'cate_id' => $data['cate_id'],
		);
		if($data['id']){
			$r = M('ad')->where('id='.$data['id'])->save($date);
		}else{
			$r = M('ad')->add($date);
		}
		if($r){
			return array('操作成功', 0);
		}else{
			return array('添加失败或者未做修改', 1);
		}  
    }
	//广告列表
    public function adList($data){
		$position = $data['position'];
		$row = $data['row'];
		if($data['page']){
			$banner = get_ad(null, null, $data['page']);
		}else{
			$banner = get_ad($position, $row);
		}
		return array('广告列表', 0, $banner);
    }
	//添加广告位置
    public function addPosition($data){
		$description = $data['description'];
		$position = $data['position'];
		$width = $data['width'];
		$height = $data['height'];
		if (!$position || !$position || !$width || !$height) {
			return array('必填项不能为空', 1);
		}
		$date = array(
			'description' => $description,
			'position' => $position,
			'width' => $width,
			'height' => $height,
		);
		if($data['id']){
			$r = M('ad_position')->where('id='.$data['id'])->save($date);
		}else{
			$r = M('ad_position')->add($date);
		}
		if($r){
			return array('添加成功', 0);
		}else{
			return array('添加失败', 1);
		}  
    }
	//广告位置列表
    public function positionList($data){
		if($data['page']){
			$page=$data['page'];
			$start=($page-1)*10;
			$count=M('ad_position')->count();
			$max_page=ceil($count/10);
			$newAd['ad'] = M('ad_position')->order('id desc')->limit($start,10)->select();//此处未对页数处理
			$newAd['page']=$page;
			$newAd['max_page']=$max_page;
		}
		return array('广告列表', 0, $newAd);
    }
  
       
}