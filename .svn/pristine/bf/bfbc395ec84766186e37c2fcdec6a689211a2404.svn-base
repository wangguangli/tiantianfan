<?php
namespace Admin\Controller;

class AdController extends CommonController {

    private $_photo = '';


    //广告列表
	public function index () {
		$page = page('ad', '', '', array('order' => 'sort desc'));
	    $this->assign('list', $page['list']);
        $this->assign('page', $page['show']);
	    $this->display();
	}
    
    
    public function addModId() {
        $mod = I('mod');
        $word = I('word');
        if ($mod == 'goods') {
            $where['is_on_sale']=1;
            if ($word) {
                $where['name'] = array('like', "%{$word}%");
            }
        } else if ($mod == 'shop') {
            $where['status'] = 1;
            if ($word) {
                $where['shop_name'] = array('like', "%{$word}%");
            }
        } else if ($mod == 'article') {
            $where['is_check']=1;
            if ($word) {
                $where['title'] = array('like', "%{$word}%");
            }
        } else {
            $this->error('非法操作');
        }

        $page = page($mod, $where);
        $list = $page['list'];
        
        foreach ($list as $k => $v) {
            if ($mod == 'goods') {
                $list[$k]['title'] = $v['name'];
            } else if ($mod == 'shop') {
                $list[$k]['title'] = $v['shop_name'];
            }
        }

        $this->assign('list', $list);
        $this->assign('page', $page['show']);
        $this->assign('currentmod', $mod);
        $this->display();
    }
	
    private function _check_photo ($w, $h, $is_update = false) {
        
        if ($is_update && $_FILES['photo']['error'] == UPLOAD_ERR_NO_FILE) {
			return false;
		}
        
        
        if ($w && $h) {
            $size = getimagesize($_FILES['photo']['tmp_name']);
            $scale = $w / $h - $size[0] / $size[1];
            if (abs($scale) > 0.5) { return '图片尺寸比例不符合要求';}
        }
        
		$upload = common_upload_photo('photo', 'ad');
        if(empty($upload['success'])) {
            return $upload['msg'];
        }
        $this->_photo = $upload['msg'];
        return false;
    }


    //防止客户误操作前端不展示这个页面
	public function add () {
		if (IS_POST) {
            $description = I('description', '', 'trim');
            $position = I('position', '', 'trim');
            $width = I('width', 0, 'intval');
            $height = I('height', 0, 'intval');
            
            if (empty($description)) {
                $this->error('广告位置不能为空');
            }
            if (empty($position)) {
                $this->error('广告关键字不能为空');
            }
            $msg = $this->_check_photo($width, $height);
            if ($msg) {
                $this->error($msg);
            }

            
            $data = array(
                'title' => I('title'),
                'content' => I('content'),
                'description' => $description,
                'position' => $position,
                'link' => I('link'),
                'photo' => $this->_photo,
                'module' => I('module'),
                'mod_id' => I('mod_id', 0, 'intval'),
                'api_url' => I('api_url'),
                'extend_data' => I('extend_data'),
                'width' => $width,
                'height' => $height,
                'time' => time(),
                'ip' => get_client_ip()
            );
            M('ad')->add($data);
            $this->success('添加成功', U('Ad/index'));
			exit;
		}  
        $this->display();
	}
	
    
    public function sort () {
        $id = I('id', 0, 'intval');
        M('ad')->where('id=' . $id)->setInc('sort');
        $this->success('OK');
    }
	
	public function copy () {
		$id = I('id', 0, 'intval');
		
		$data = M('ad')->find($id);
		unset($data['id']);
        
        $c = M('ad')->where('position="' . $data['position'] . '"')->count();
        $data['description'] = $data['description'] . ($c+1);
		$data['time'] = time();
		$data['ip'] = get_client_ip();
		
		M('ad')->add($data);
		
		$this->success('OK');
	}
	
	
	//修改广告
	public function update () {
        $id = I('id', 0, 'intval');
        $all = I('all');
        
        if (IS_POST) {
            $width = I('width', 0, 'intval');
            $height = I('height', 0, 'intval');
            $data = array(
                'link' => I('link'),
                'module' => I('module'),
                'mod_id' => I('mod_id', 0, 'intval'),
            );
            
            $msg = $this->_check_photo($width, $height, true);
            if ($msg) {
                $this->error($msg);
            }
            if ($this->_photo) {
                $data['photo'] = $this->_photo;
            }
            if ($all) {
                $description = I('description', '', 'trim');
                $position = I('position', '', 'trim');
                if (empty($description)) {
                    $this->error('广告位置不能为空');
                }
                if (empty($position)) {
                    $this->error('广告关键字不能为空');
                }
                
                $data['title'] = I('title');
                $data['content'] = I('content');
                $data['description'] = $description;
                $data['position'] = $position;
                $data['api_url'] = I('api_url');
                $data['extend_data'] = I('extend_data');
                $data['width'] = $width;
                $data['height'] = $height;
            }
            
			M('ad')->where('id='.$id)->save($data);
			$this->success('修改成功', U('Ad/index'));
	      	
	    } else {
            $ad = M('ad')->where('id='.$id)->find();
            $this->assign('showall', $all);
	      	$this->assign('ad', $ad);
	      	$this->display();
	    }
	}
	
	public function delete() {
        $all = I('all');
		$id = I('id', 0, 'intval');
        
        if (! $all) {
            $pos = M('ad')->where('id=' . $id)->getField('position');
            $c = M('ad')->where("position='{$pos}'")->count();
            if ($c == 1) {
                $this->error('每个广告位至少保留一个');
            }
        }
        
		M('ad')->where('id=' . $id)->delete();
		$this->success('OK');
	}
	
    
}