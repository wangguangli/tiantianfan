<?php
namespace Api\Controller;
use Clas\Article;
use Clas\User;

class ArticleController extends CommonController {
	
	/**消息，帮助列表
	 * @param   string      $type 	类型 1消息 2帮助        
	 * @return  string
	 * @szl
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
	/**消息，帮助详情
	 * @param   string      $id 	文章ID        
	 * @return  string
	 * @szl
	 */
	public function articleDetail()
	{
		$id = I('id');
		$article = M('article')->where('id='.$id)->field('id,title,time,thumb,content,time')->find();
		$this->assign('article',$article);
		$this->display('index:news_detail');
		
		// return array('文章列表', 0, $article?$article:array());
		// $obj = new Article();
		// $result = $obj->articleDetail();
	    // jsonout($result[0],$result[1],$result[2]);
	}
	/**消息，帮助文章添加
	* @param   string      title 	标题
	* @param   string      cat_id 	类别 
	* @param   string      content 	详情 
	* @return  string
	* @szl
	*/
	public function addArticle($data=null)
	{
		if(!$data){
			$data = I();
		}
		$obj = new Article();
		$result = $obj->addArticle($data);
	    jsonout($result[0],$result[1],$result[2]);
	}
	/**文章分类添加
	* @param   string      name 	名称
	* @return  string
	* @szl
	*/
	public function add_articleCat($data=null)
	{
		if(!$data){
			$data = I();
		}
		$obj = new Article();
		$result = $obj->add_articleCat($data);
	    jsonout($result[0],$result[1],$result[2]);
	}
		
	
}