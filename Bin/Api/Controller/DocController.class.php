<?php

namespace Api\Controller;

use Think\Controller;

class DocController extends Controller
{

    public function _before_index()
    {
        $api_type = M('config')->where('id=23')->getField('val');
        if ($api_type == "0"){
            exit("Hello world!");
        }

    }

    private function get_classify()
    {
        $arr = M('api')->group('classify')->getField('classify', true);
        return $arr;
    }

    public function index()
    {
        $id = I('id', 0, 'intval');
        if ($id) {
            $api = M('api')->find($id);
            if (empty($api)) {
                $this->error('接口不存在');
            }
            $api['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $api['url'];
            $req = M('api_req')->where('aid=' . $api['id'])->order('sort desc,id asc')->select();
            $resp = M('api_resp')->where('aid=' . $api['id'])->select();
            if (!strpos($api['method'], ',')) {
                $api['method'] = '<strong style="color:red">' . $api['method'] . '</strong>';
            }
            $this->assign('req', $req);
            $this->assign('resp', $resp);
            $this->assign('api', $api);
        }

        $list = M('api')->where('is_hide=0')->order('classify asc,sort desc,id asc')->select();

        //分类
        $c = $this->get_classify();
        $list1 = array();
        foreach ($list as $v) {
            $k = 0;
            if ($v['classify']) {
                $k = array_search($v['classify'], $c);
            }
            $list1[$k]['name'] = $v['classify'];
            $list1[$k]['data'][] = $v;
        }
        $this->assign('list', $list1);
        $this->display();
    }


    public function delete()
    {
        $id = I('id', 0, 'intval');
        $pwd = I('pwd', '');
        if ($pwd != 'a123') {
            $this->error('No permission to access');
        }

        M('api')->where('id=' . $id)->delete();
        M('api_req')->where('aid=' . $id)->delete();
        M('api_resp')->where('aid=' . $id)->delete();
        $this->success('已删除', U('Doc/index'));
    }


    public function add()
    {
        $pwd = I('pwd', '');
        if ($pwd != 'a123') {
            $this->error('No permission to access');
        }

        $this->assign('classify', $this->get_classify());
        $this->assign('pwd', $pwd);
        $this->display();
    }

    public function update()
    {
        $id = I('id', 0, 'intval');

        $api = M('api')->find($id);
        if (empty($api)) {
            $this->error('接口不存在');
        }

        $req = M('api_req')->where('aid=' . $api['id'])->order('sort desc')->select();
        $resp = M('api_resp')->where('aid=' . $api['id'])->select();

        $this->assign('classify', $this->get_classify());
        $this->assign('req', $req);
        $this->assign('resp', $resp);
        $this->assign('api', $api);
        $this->display();
    }


    public function copy()
    {
        $pwd = I('pwd', '');
        if ($pwd != 'a123') {
            $this->error('请输入密码');
        }
        $id = I('id', 0, 'intval');
        $api = M('api')->find($id);
        if (empty($api)) {
            $this->error('接口不存在');
        }
        $aid = $api['id'];
        unset($api['id']);
        $api['title'] = '复制的   ' . $api['title'];

        $req = M('api_req')->where('aid=' . $aid)->select();
        $resp = M('api_resp')->where('aid=' . $aid)->select();

        $aid1 = M('api')->add($api);

        foreach ($req as $k => $v) {
            unset($req[$k]['id']);
            $req[$k]['aid'] = $aid1;
        }
        M('api_req')->addAll($req);

        foreach ($resp as $k1 => $v1) {
            unset($resp[$k1]['id']);
            $resp[$k1]['aid'] = $aid1;
        }
        M('api_resp')->addAll($resp);
        $this->success('复制成功', U('Doc/index', 'id=' . $aid1));
    }


    public function updateAct()
    {
        $pwd = I('pwd', '');
        if ($pwd != 'a123') {
            $this->error('请输入密码');
        }
        $id = I('id', 0, 'intval');
        $api = array(
            'classify' => I('classify', '', 'trim'),
            'title' => I('title', '', 'trim'),
            'url' => I('url', '', 'trim'),
            'version' => I('version'),
            'method' => I('method'),
            'resp_type' => I('resp_type'),
            'note' => I('note', '')
        );
        M('api')->where('id=' . $id)->save($api);

        $req = I('req');
        foreach ($req as $k => $v) {
            $parameter = trim($v['parameter']);
            if (empty($parameter)) {
                M('api_req')->where('id=' . $k)->delete();
            } else {
                M('api_req')->where('id=' . $k)->save($v);
            }
        }

        $resp = I('resp');
        foreach ($resp as $k => $v) {
            $parameter = trim($v['parameter']);
            if (empty($parameter)) {
                M('api_resp')->where('id=' . $k)->delete();
            } else {
                M('api_resp')->where('id=' . $k)->save($v);
            }
        }

        $req1 = I('req1');
        $resp1 = I('resp1');

        foreach ($req1 as $k => $v) {
            $parameter = trim($v['parameter']);
            if ($parameter) {
                $v['aid'] = $id;
                M('api_req')->add($v);
            }
        }
        foreach ($resp1 as $k => $v) {
            $parameter = trim($v['parameter']);
            if ($parameter) {
                $v['aid'] = $id;
                M('api_resp')->add($v);
            }
        }

        $this->success('修改成功', U('Doc/index', 'id=' . $id));
    }


    public function addAct()
    {
        $pwd = I('pwd', '');
        if ($pwd != 'a123') {
            $this->error('请输入密码');
        }

        $api['classify'] = I('classify', '', 'trim');
        $api['title'] = I('title', '', 'trim');
        $api['url'] = I('url', '', 'trim');
        $api['version'] = I('version');
        $api['method'] = I('method');
        $api['resp_type'] = I('resp_type');
        $api['note'] = I('note', '');
        $api['time'] = time();
        $api['ip'] = get_client_ip();

        $aid = M('api')->add($api);

        $_req = I('req_parameter', '', 'trim');
        $req_ismust = I('req_ismust');
        $req_para_type = I('req_para_type');
        $req_miaoshu = I('req_miaoshu');
        $req = array();

        for ($i = 0; $i < count($_req); $i++) {
            if ($_req[$i]) {
                $a['aid'] = $aid;
                $a['parameter'] = $_req[$i] ? $_req[$i] : '';
                $a['ismust'] = $req_ismust[$i] ? $req_ismust[$i] : '';
                $a['type'] = $req_para_type[$i] ? $req_para_type[$i] : '';
                $a['miaoshu'] = $req_miaoshu[$i] ? $req_miaoshu[$i] : '';
                $req[] = $a;
            }
        }

        M('api_req')->addAll($req);

        $_resp = I('resp_parameter', '', 'trim');
        $resp_para_type = I('resp_para_type');
        $resp_miaoshu = I('resp_miaoshu');
        $resp = array();
        for ($i = 0; $i < count($_resp); $i++) {
            if ($_resp[$i]) {
                $a['aid'] = $aid;
                $a['parameter'] = $_resp[$i] ? $_resp[$i] : '';
                $a['type'] = $resp_para_type[$i] ? $resp_para_type[$i] : '';
                $a['miaoshu'] = $resp_miaoshu[$i] ? $resp_miaoshu[$i] : '';
                $resp[] = $a;
            }
        }
        M('api_resp')->addAll($resp);

        $this->success('ok', U('Doc/index', 'id=' . $aid));
    }
}