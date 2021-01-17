<?php
namespace Clas;
class Team {
	
	private $_user_id;
	private $_role;
    private $_user;
    

	
	// role: admin,user
	public function __construct($user_id, $role){
		if (func_num_args() < 2) {exit('缺少参数<br>Missing argument!');}
		$this->_user_id = $user_id;
		$this->_role = $role;
	}
    
    public function pyramid ($my_id, $level) {
        
        $ids = array_merge(array($my_id), $this->get_pyramid($my_id));
        
        
        
        $nodes = M('zx_user_pyramid_a')->where('node_id in(' . join(',', $ids) . ') and level=' . $level)->getField('node_id,user_id,is_out', true);
        $user = get_array_column($nodes, 'user_id', 'get_users');
        
        $new_arr = array();
        foreach ($ids as $i) {
            
            $a = array(
                'node_id' => $i,
                'is_null' => 1,
                'user_name' => '',
                'level' => '',
                'phone' => '',
                'is_out' => 0,
            );
            if (array_key_exists($i, $nodes)) {
                
                $uid = $nodes[$i]['user_id'];
                $u = $user[$uid];
                
                $a = array(
                    'node_id' => $i,
                    'is_null' => 0,
                    'real_name' => $u['realname'],
                    'user_name' => $u['nickname'],
                    'level' => $u['level'],
                    'phone' => $u['phone'],
                    'is_out' => $nodes[$i]['is_out']
                );
            }
            
            $new_arr[] = $a;
        }
        
        return $new_arr;
        
    }
    
    public function get_pyramid ($my_id) {
       
        $floor1 = array(
           0 => $my_id * 3 - 1,
           1 => $my_id * 3,
           2 => $my_id * 3 + 1
        );
        
        $all = $floor1;
        foreach ($floor1 as $i) {
            $all[] = $i * 3 - 1;
            $all[] = $i * 3;
            $all[] = $i * 3 + 1;
        }
        
        return $all;
    }
    
    
}