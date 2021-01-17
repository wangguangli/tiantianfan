<?php
namespace Api\Controller;
use Clas\Article;
use Clas\User;

class ArticleoutController extends CommonController {
	
	/**
	 * 消息，帮助列表
	 * @param   string      $type 	类型 1消息 2帮助        
	 * @return  string
	 * @zd
	 */
	public function articleList($data=null)
	{
		
		if(!$data){
			$data = I();
		}
		$obj = new Article();
		$result = $obj->articleList($data);
	    jsonout($result[0],$result[1],$result[2]);
	}
	/**
	 * 消息，帮助详情(WAP，app)
	 * @param   string      $id 	文章ID        
	 * @return  string
	 * @zd
	 */
	public function articleDetail()
	{
		$id = I('id');
		$article = M('article')->where('id='.$id)->field('id,title,time,thumb,content,time')->find();
		$this->assign('article',$article);
		$this->display('Index:news_detail');
	}
	/**
	 * 文章分类
	 * @param   string      data 	接收数据        
	 * @return  string
	 * @szl
	 */
	public function article_category($data=null)
	{
		if(!$data){
			$data = I();
		}
		$obj = new Article();
		$result = $obj->article_category($data);
	    jsonout($result[0],$result[1],$result[2]);
	}
		
	
}