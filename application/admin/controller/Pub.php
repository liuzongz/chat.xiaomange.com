<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;
use think\Model;
use Flc\Alidayu\Client;
use Flc\Alidayu\App;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;

class Pub extends Controller{
    public function sms(){
        $trade=input('trade');
        $tradeInfo=db('company_payment')->where('trade',$trade)->find();
        if($tradeInfo){
            $companyInfo=db('company')->where('id',$tradeInfo['cid'])->find();
            $msg='尊敬的'.$companyInfo['name'].'，您好！您上传的打款信息（批次：'.$trade.'）已经通过审核，请及时等来网站支付相关手续费！';
            $tel=$companyInfo['tel'];
        }else{
            return result(0,'查找不到对应的公司信息');
        }
        
        
        $config = [
            'app_key'    => '24535716',
            'app_secret' => 'c7f7a9defb5d8606a10f9b1dd524132f',
        ];
        
        $client = new Client(new App($config));
        $req    = new AlibabaAliqinFcSmsNumSend;
        
        $req->setRecNum($tel);
        $req->setSmsParam([
            'name' => $companyInfo['name'],
            'trade'=>$trade
        ]);
        $req->setSmsFreeSignName('海营之家');
        $req->setSmsTemplateCode('SMS_122445003');
        
        $resp = $client->execute($req);
        $respay=object2array($resp);
        $result=array();
        if(isset($respay['result']['success'])){
            $result=array(
                'status'=>1,
                'txt'=>'短信发送成功'
            );
            return json($result);
        }else{
            $result=array(
                'status'=>0,
                'txt'=>$respay['sub_msg']
            );
            return json($result);
        }
    }
    
    public function uposs(){
        $pathDir='yuanzi/'.date('Y-m-d');        
        $path = $pathDir.'/'; //上传路径
        if (isset($_POST)) {
            $name = $_FILES['file']['name'];
            $size = $_FILES['file']['size'];
            $name_tmp = $_FILES['file']['tmp_name'];
            if (empty($name)) {
                echo json_encode(array("error" => "您还未选择图片"));
                exit;
            }
            $type = strtolower(substr(strrchr($name, '.'), 1)); //获取文件类型
            $typeArr = array("jpg", "png", "gif","jpeg"); //允许上传文件格式
            if (!in_array($type, $typeArr)) {
                echo json_encode(array("error" => "清上传jpg,png或gif类型的图片！"));
                exit;
            }
            if ($size > (50000 * 1024)) { //上传大小
                echo json_encode(array("error" => "图片大小已超过50000KB！"));
                exit;
            }
            $picname=time() . rand(10000, 99999);
            $pic_name =  $picname. "." . $type; //图片名称
            $urlpic='http://aman.oss-cn-shanghai.aliyuncs.com/yuanzi/'.date('Y-m-d').'/'.$pic_name;
            $pic_url = $path . $pic_name; //上传后图片路径+名称
            
            if (upOss('aman',$pic_url,$name_tmp)) { //临时文件转移到目标文件夹    
                echo json_encode(array("error" => "0", "pic" => $urlpic, "name" => $pic_name,"path"=>date('Y-m-d').'/'));
            } else {
                echo json_encode(array("error" => "上传有误，清检查服务器配置！"));
            }
        }
    }
}