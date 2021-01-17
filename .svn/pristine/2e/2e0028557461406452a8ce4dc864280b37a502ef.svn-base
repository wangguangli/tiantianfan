<?php
/**
 * 上传附件和上传视频
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
include "Uploader.class.php";
include  'autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            "pathFormat" => $CONFIG['imagePathFormat'],
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}

/*
 * 读取must.json
 * 如果读取不到，则上传到本服务器
 * 如果读取到，则按规格上传
 *
 * */
$qnInfo = file_get_contents("must.json");
$qnInfo = json_decode($qnInfo, true);
if (!$qnInfo)
{
	$qnInfo['upload_type'] = 0;
}
if ($qnInfo['upload_type'] == 1)
{
	$file = $_FILES[$fieldName];
	$bucket = $qnInfo['bucket'];
	$accessKey = $qnInfo['access_key'];
	$secretKey = $qnInfo['secret_key'];
	$auth = new Auth($accessKey, $secretKey);
	$token = $auth->uploadToken($bucket);
	$uploadMgr = new UploadManager();
	$filePath = $file['tmp_name'];
	$key = time() . rand(1000, 9999) . '.png';
	$upInfo = $uploadMgr->putFile($token, $key, $filePath);
	if (!$upInfo[0] && $upInfo[1])
	{
		$imgInfo['state'] = "上传失败，请重新上传或联系客服";
		$imgInfo['url'] = "";
		$imgInfo['title'] = "";
		$imgInfo['original'] = "";
		$imgInfo['type'] = "";
		$imgInfo['size'] = 0;
		return json_encode($imgInfo);
	}
	$imgInfo['state'] = "SUCCESS";
	$imgInfo['url'] = $qnInfo['img_domain']."/".$upInfo[0]['key'];
	$imgInfo['title'] = $upInfo[0]['key'];
	$imgInfo['original'] = $upInfo[0]['key'];
	$imgInfo['type'] = "jpg";
	$imgInfo['size'] = 110;
	return json_encode($imgInfo);
}
else
{
	/* 生成上传实例对象并完成上传 */
	$up = new Uploader($fieldName, $config, $base64);
	/**
	 * 得到上传文件所对应的各个参数,数组结构
	 * array(
	 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
	 *     "url" => "",            //返回的地址
	 *     "title" => "",          //新文件名
	 *     "original" => "",       //原始文件名
	 *     "type" => ""            //文件类型
	 *     "size" => "",           //文件大小
	 * )
	 */

	/* 返回数据 */
	return json_encode($up->getFileInfo());
}

