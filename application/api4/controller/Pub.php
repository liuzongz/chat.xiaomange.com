<?php 
namespace app\api4\controller;
use think\Model;
use think\helper\Time;
use think\Controller;
use app\common\model\Question;
use Flc\Alidayu\Client;
use Flc\Alidayu\App;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;
use Firebase\JWT\JWT;
class Pub extends Base{
    public function SendVerificationCode(){
        $mobile=input('mobile',17717500923,'intval');
        $config = [
            'app_key'    => '23795721',
            'app_secret' => '26c07b61de75e288ba24922b3b63f38b',
        ];
        
        $client = new Client(new App($config));
        $req    = new AlibabaAliqinFcSmsNumSend;
        $code=rand(1000, 9999);
        cookie('smscode',$code,86400);
        $req->setRecNum($mobile);
        $req->setSmsParam([
            'code' => $code,
            'product'=>'原子出发'
        ]);
        $req->setSmsFreeSignName('原子出发');
        $req->setSmsTemplateCode('SMS_63800798');
        $resp = $client->execute($req);
        $respay=object2array($resp);
        $result=array();
        if(isset($respay['result']['success'])){
            return json(['data'=>['code'=>$code,'msg'=>'验证码发送成功'],'code'=>1,'message'=>'验证码发送成功']);
        }else{
            return json(['data'=>['code'=>0,'msg'=>$respay['sub_msg']],'code'=>0,'message'=>$respay['sub_msg']]);
        }
    }
    
    public function getTags(){
    	$list=db('tags')->field('id,name')->select();
    	return json(['data'=>['code'=>1,'data'=>$list]]);
    }
    
    public function getQuestionList(){
    	$model=new \app\common\model\Question();
    	$list=$model->all(function($q){
    		$q->order('sort desc');
    	});
    	if($list){
    		return json(['data'=>['code'=>1,'msg'=>$list]]);
    	}else{
    		return json(['data'=>['code'=>0,'msg'=>'暂时没有信息']]);
    	}
    }

}
?>