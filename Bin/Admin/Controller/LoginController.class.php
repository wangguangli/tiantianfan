<?php
namespace Admin\Controller;
header("Content-type: text/html; charset=utf-8"); // tp输出的编码不是utf8，模板编码是。
use Think\Controller;
class LoginController extends Controller {
	
	public function index () {
        $username = I('username', '', 'trim');
        $password = I('password', '', 'trim');
		if (IS_POST) {

			$admin = M('admin')->where(array('username' => $username))->find();
			if ($admin) {
				
				if ($admin['password'] == get_pwd($password, true, $admin['random_code'])) {
				    $admin_role=M("admin_role")->where("role_id=".$admin['role_id'])->find();
					session('[start]');
					session('admin_id', $admin['id']);
                    session('role_id', $admin['role_id']);
                    session('act_list', $admin_role['act_list']);
					add_admin_login_log(1, $username, '', $admin['id']);

					//修改为两位小数

                    $all_money = get_config('all_money',true);
                    $all_money = round($all_money,2);
                    $save['val'] = $all_money;
                    M('config')->where('id=40')->save($save);
					//先判断是不是子管理登陆的
                    if($admin['role_id'] == 1){
                        redirect(U('Index/index'));
                    }else{
                        $act_list = session('act_list');
                        $menu_list = getMenuList($act_list);

                        foreach ($menu_list as $k => $v){
                            if(empty($v['sub_menu'])){
                                unset($menu_list[$k]);
                            }
                        }
                        foreach ($menu_list as $k => $v){

                            $action = $v['sub_menu'][0]['control'].'/'.$v['sub_menu'][0]['act'];
                        }
                        redirect(U('Admin/'.$action));
                    }

				} else {
					add_admin_login_log(0, $username, $password, $admin['id']);
					$this->error('密码错误');
				}					
				
			} else {
				add_admin_login_log(0,$username, $password);
				$this->error('用户名不正确或密码错误');
			}
            exit;
		}
        $this->display();
	}
	
}