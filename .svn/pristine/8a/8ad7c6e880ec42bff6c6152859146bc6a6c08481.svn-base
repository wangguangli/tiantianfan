<?php
namespace Admin\Controller;


class ApiController extends CommonController {

    public function getRegion(){
        $parent_id = I('get.parent_id');
        $data = M('region')->where("parent_id=$parent_id")->select();
        $html = '';
        if($data){
            foreach($data as $h){
                $html .= "<option value='{$h['id']}'>{$h['name']}</option>";
            }
        }
        echo $html;
    }



}	