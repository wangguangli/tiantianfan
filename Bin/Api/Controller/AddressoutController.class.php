<?php
namespace Api\Controller;
use Clas\Address;
use Clas\User;

class AddressoutController extends CommonController {
	
	/**我的地址
	 *
	 * @param   int      	$user_id   	用户ID
	 * @param   string      $consignee 	收货人
	 * @param   string      $phone 	    手机号码
	 * @param   string      $zipcode 	邮政编码
	 * @param   string      $address   	 地址
	 * @return  string      $is_default   默认收货地址
	 * @return  string
	 * @lz
	 */
	public function address($data=null)
	{
		if(!$data){
			$data = I();
		}
		$obj = new Address();
		$result = $obj->address($data);
		jsonout($result[0],$result[1],$result[2]);
	}	
	/*
	 * @param   string    省
	 * @lz
	*/
	public function province(){
	    $obj = new Address();
		$result = $obj->province();
	    jsonout($result[0],$result[1],$result[2]);
	}
	/*
	 * @param   string   市
	 * @lz
	*/
	public function  city(){
	    $obj = new Address();
		$result = $obj->city();
	    jsonout($result[0],$result[1],$result[2]);
	}
	/*
	 * @param   string    县
	 * @lz
	*/
	public function  district(){
        $obj = new Address();
		$result = $obj->district();
	    jsonout($result[0],$result[1],$result[2]);	
	
  }
    /**
     * Notes: 用户地址修改获取数据
     * User: wangsong
     * Date: 2019/10/22
     * Time: 10:48
     */
    public function get_address($address_id=0,$user_id=0){

        $obj = new Address();
        $result = $obj->get_address_detail($address_id,$user_id);

        jsonout("操作成功",0,$result);
    }

    /**
     * Notes:输出城市接口（ios）
     * User: WangSong
     * Date: 2019/10/30
     * Time: 9:30
     */
    public function  get_city(){
       /* $json_string = file_get_contents('ios_region.json');
        jsonout("操作成功",0,\Qiniu\json_decode($json_string,true));*/

        $region_in = M("region")->where("level = 2")->order("indexes")->group("indexes")->field("indexes")->select();
        $resultList= array();
        foreach ($region_in as $k=>$v){
            //$data[$k]=$v["indexes"];
            $region = M("region")->where("level=2 and  indexes = '".$v["indexes"]."'")->select();
            $data1 = array();
            foreach ($region as $key=>$value){
                $data1[$key]["gStr"] = $value["indexes"];
                $data1[$key]["id"] = $value["id"];
                $data1[$key]["name"] = ($value["name"]);

            }
            $newItem[$k] = array(
                "name"=>$v["indexes"],
                "citys"=>$data1

            );
            $resultList = $newItem;

        }

// 把PHP数组转成JSON字符串
        $json_string  = $this->json_encode_no_zh($resultList);
        jsonout("操作成功",0,\Qiniu\json_decode($json_string,true));
    }
    /**
     * Notes:json中文不转编译
     * User: WangSong
     * Date: 2019/10/29
     * Time: 17:42
     * @param $arr
     * @return string|string[]|null
     */
    function json_encode_no_zh($arr) {
        $str = str_replace ( "\\/", "/", json_encode ( $arr ) );
        $search = "#\\\u([0-9a-f]+)#ie";

        if (strpos ( strtoupper(PHP_OS), 'WIN' ) === false) {
            $replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";//LINUX
        } else {
            $replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";//WINDOWS
        }

        return preg_replace ( $search, $replace, $str );
    }
}