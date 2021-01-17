<?php
namespace Admin\Controller;

class MessageController extends CommonController {
    
    public function index() {
        $page = page('message');
        $this->assign('list', $page['list']);
        $this->assign('page', $page['page']);
        $this->display();
    }
    
    
    
    public function add () {
        if (IS_POST) {
            $title = I('title', '', 'trim');
            $content = I('content');
            
            if (empty($title)) {
                $this->error('请输入标题');
            }
            
            $data = array(
                'title' => $title,
                'content' => $content,
                'time' => time(),
                'ip' => get_client_ip()
            );
            M('message')->add($data);
            $this->success('OK', U('Message/index'));
            exit;
        }
        $this->display();
    }
    
    public function detail () {
        $id = I('id', 0, 'intval');
        
        $message = M('message')->find($id);
        $this->assign('message', $message);
        $this->display();
    }
	
    public function delete () {
        $id = I('id', 0, 'intval');
        M('message')->where('nexus_id=' . $id)->delete();
        M('message')->where('id=' . $id)->delete();
        $this->success('已删除', U('Message/index'));
    }

    
	
}