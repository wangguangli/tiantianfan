<?php
namespace Admin\Controller;
use Clas\Article;

class ArticleController extends CommonController {
    
    
    public function index() {
        $page = page('article');
        $this->assign('list', $page['list']);
        $this->assign('page', $page['show']);
        $this->display();
    }
    
    public function detail () {
        $id = I('id', 0, 'intval');
        $article = M('article')->find($id);
        $this->assign('article', $article);
        $this->display();
    }
	
	public function add() {
        if (IS_POST) {
            $a = new Article($this->_user_id, $this->_role);
            $ok = $a->add();
            if ($ok['err']) {
                $this->error($ok['msg']);
            } else {
                $this->success($ok['msg'], U('Article/index'));
            }
        } else {
            $this->display();
        }
    }
    
	public function update() {
        if (IS_POST) {
            $a = new Article($this->_user_id, $this->_role);
            $ok = $a->update();
            if ($ok['err']) {
                $this->error($ok['msg']);
            } else {
                $this->success($ok['msg'], U('Article/index'));
            }
        } else {
            $id = I('id', 0, 'intval');
            $article = M('article')->find($id);
            $this->assign('id',$id);
            $this->assign('article', $article);
            $this->display();
        }
    }
    
    
    public function delete() {
        $a = new Article($this->_user_id, $this->_role);
        $a->delete();
        $this->success('已删除', U('Article/index'));
    }
	
}