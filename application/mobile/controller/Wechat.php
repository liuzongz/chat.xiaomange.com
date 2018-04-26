<?php 

namespace app\mobile\controller;
use think\Controller;
use think\Request;
use think\Model;
class Wechat extends Controller{
	public function getWeixinInfo(){
		$this->request=Request::instance();
		$jump=input('jump',0);
		if($jump){
			cookie('jump',$jump);
		}else{
		    $url=url('mobile/index/index');
			cookie('jump',$url);
		}
		$this->redirect('weixinInfo');
	}
	
	public function weixinInfo(){
		if(isset($_GET['code'])){
			$oauth = & load_wechat('Oauth');
			$result = $oauth->getOauthAccessToken();
			if($result===FALSE){
				return false;
			}else{
				$userinfo=$oauth->getOauthUserinfo($result['access_token'],$result['openid']);
				cookie('weixinInfo',$userinfo);
				$member=new \app\common\model\Member();
				$ck=$member->get(['openid'=>$userinfo['openid']]);
				if(!$ck){
				    $memberData=array(
				        'name'=>$userinfo['nickname'],
				        'nickname'=>$userinfo['nickname'],
				        'openid'=>$userinfo['openid'],
				        'headimgurl'=>$userinfo['headimgurl']
				    );
				    $member->save($memberData);
				}
				$this->redirect(cookie('jump'));
			}
		}else{
			$request = Request::instance();
			$callback=$request->url(true);
			$state='STATE';
			$scope='snsapi_userinfo';
			$oauth = & load_wechat('Oauth');
			$result = $oauth->getOauthRedirect($callback, $state, $scope);
			if($result===FALSE){
				// 接口失败的处理
				return false;
			}else{
				// 接口成功的处理
				header("Location:".$result);
				die;
			}
		}
	}
		

	public function jssign(){
		//JSSDK签名
		$fromurl=input('fromurl');
		$debug=input('debug',0);
		$script = &  load_wechat('Script');
		$options = $script->getJsSign($fromurl);
		if($debug){
			$options['debug']=true;
		}
		//$options['debug']=true;
		return json(['data'=>['code'=>1,'msg'=>$options]]);		 
	}
	
		

}