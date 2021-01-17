<?php
namespace Clas;
class Article {
	
	private $_user_id;
	private $_role;
	private $_user;
	private $_MAX_WORD_NUM = 20000;
	private $_thumb;

	
	// role: admin,user,manager,teacher
	public function __construct($user_id, $role, $user = ''){
		// if (func_num_args() < 2) {exit('缺少参数<br>Missing argument!');}
		/*if ($role != 'user' && $role != 'admin') {exit('你没有权限操作<br>You don\'t have the access!');}*/
		
		$this->_user_id = $user_id;
		$this->_role = $role;
		$this->_user = $user;
	}
	
	public function add () {
		$title = I('title');
		$content = I('content');
		
		if (empty($title)) {
			return _dat('请输入标题');
		}
		if (empty($content)) {
			return _dat('文章内容不能为空');
		}
		if (strlen($content) > $this->_MAX_WORD_NUM) {
			return _dat('文章字数超出最大限制');
		}
		
		$ok = $this->_check_thumb();
		if ($ok) {
			return _dat($ok);
		}
		
		
		$data = array(
			'title' => $title,
			'thumb' => $this->_thumb,
			'is_top' => I('is_top', 0, 'intval'),
			'content' => $content,
			'author' => $this->_user_id,
			'time' => time(),
			'ip' => get_client_ip()
		);
		M('article')->add($data);
		return _dat('添加成功', 0);
	}
	
	
	private function _check_thumb ($is_update = false) {
		
		if ($is_update && $_FILES['top_photo']['error'] == UPLOAD_ERR_NO_FILE) {
			return false;
		}
		
		$size = getimagesize($_FILES['photo']['top_photo']);
		if ($size[0] > 1920 || $size[1] > 1080) { return '图片尺寸过大';}    

		$photo = common_upload_photo('thumb', 'article');
		if (! $photo['success']) { return $photo['msg'];}
			
		$this->_thumb = $photo['msg'];
		return false;
	}
	
	
	public  function update () {
		$id = I('id', 0, 'intval');
		$title = I('title');
		$content = I('content');
		
		if (empty($title)) {
			return _dat('请输入标题');
		}
		if (empty($content)) {
			return _dat('文章内容不能为空');
		}
		if (strlen($content) > $this->_MAX_WORD_NUM) {
			return _dat('文章字数超出最大限制');
		}
		
		$data = array(
			'title' => $title,
			'is_top' => I('is_top', 0, 'intval'),
			'content' => $content,
			'update_id' => $this->_user_id
		);
		
		if ($this->_thumb) {
			$data['thumb'] = $this->_thumb;
		}
		
		M('article')->where('id=' . $id)->save($data);
		return _dat('修改成功', 0);
	}
	
	public function delete () {
		$id = I('id', 0, 'intval');
		$art = M('article')->find($id);

		if ($art['is_not_del']) {
			return _dat('非法操作');
		}
		
		if ($art['author'] != $this->_user_id) {
			return _dat('你没有权限删除');
		}
		
		
		M('article')->where('id=' . $id)->delete();
		return _dat('已删除');
	}
	/**
	 * 获取消息和帮助信息
	 * type 1消息，2帮助
	 * @lz
	 */
	public function articleList ($data) {
		$type = $data['type'];
		$page = $data['page'];
		$size = $data['size'] ? $data['size'] : 10;
		if($page){
			$page=I('page',1,'intval');
			$start=($page-1)*$size;
			$count=M('article')->count();
			$max_page=ceil($count/$size);
			$art = M('article')->order('id desc')->limit($start,$size)->select();
			foreach($art as $k=>$v){
				$art[$k]['name'] = M('article_category')->where('id='.$v['cat_id'])->getField('name');
			}
			$article['article'] = $art;
			$article['page']=$page;
			$article['max_page']=$max_page;
		}else{
			if($type==1){
				$article = M('article')->where('cat_id=1')->field('id,title,time,thumb')->select();
			}
			if($type==2){
				$article = M('article')->where('cat_id=2')->field('id,title,time,thumb')->select();
			}
		}
		
		return array('文章列表', 0, $article?$article:array());
	}
	public function articleDetail () {
		$id = $data('id');
		$article = M('article')->where('id='.$id)->field('id,title,time,thumb,content')->find();
		$article['content'] = htmlspecialchars_decode($article['content']);
		return array('文章列表', 0, $article?$article:array());
	}
	//添加文章
    public function addArticle($data){
		$title = $data['title'];
		$cat_id = $data['cat_id'];
		$content = $data['content'];
		if(!$title || !$cat_id || !$content){
			return '必填项不能为空';
		}
		$date = array(
			'title' => $title,
			'content' => htmlspecialchars_decode($data['content']),
			'cat_id' => $cat_id,
			'time' => time(),
			'ip' => get_client_ip(),
		);
		if($data['id']){
			$r = M('article')->where('id='.$data['id'])->save($date);
		}else{
			$r = M('article')->add($date);
		}
		if($r){
			return array('添加成功', 0);
		}else{
			return array('添加失败', 1);
		}  
    }
	/**
	 * 获取文章分类列表
	 * @szl
	 */
	public function article_category ($data) {
		$page = $data['page'];
		if($page){
			$page=I('page',1,'intval');
			$start=($page-1)*10;
			$count=M('article_category')->count();
			$max_page=ceil($count/10);
			$art = M('article_category')->order('id desc')->limit($start,10)->select();
			$article['article'] = $art;
			$article['page']=$page;
			$article['max_page']=$max_page;
		}
		return array('文章分类列表', 0, $article?$article:array());
	}
	/**
	 * 获取文章分类列表
	 * @szl
	 */
	public function add_articleCat ($data) {
		$name = $data['name'];
		if(!$name){
			return '必填项不能为空';
		}
		$date = array(
			'name' => $name,
		);
		if($data['id']){
			$r = M('article_category')->where('id='.$data['id'])->save($date);
		}else{
			$r = M('article_category')->add($date);
		}
		if($r){
			return array('添加成功', 0);
		}else{
			return array('添加失败', 1);
		} 
	}
	
}