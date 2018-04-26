<?php 

namespace app\api4\controller;
use think\Controller;
use think\Request;
use think\Model;
use Firebase\JWT\JWT;
class Wechat extends Base{
	public function getToken(){
		$this->request=Request::instance();
		$jump=input('jump',0);
		if($jump){
			cookie('jump',$jump);
		}else{
			cookie('jump','http://www.yuanzigo.com/mobile/index/index');
		}
		$this->redirect('getWeixinInfo');
	}
	
	public function getWeixinInfo(){
		if(isset($_GET['code'])){
			$oauth = & load_wechat('Oauth');
			$result = $oauth->getOauthAccessToken();
			if($result===FALSE){
				return false;
			}else{
				$userinfo=$oauth->getOauthUserinfo($result['access_token'],$result['openid']);
				
				$openid=$userinfo['openid'];
				$hasMember=db('member')->where(['openid'=>$openid])->find();
				if($hasMember){
				    db('member')->where(['openid'=>$openid])->setField('update_time',time());
				    db('user')->where(['openid'=>$openid])->setField('update_time',time());
					$memberData=array(
							'nickname'=>$hasMember['nickname'],
							'openid'=>$openid,
							'headimgurl'=>$userinfo['headimgurl'],
							'shop_id'=>$hasMember['shop_id']
					);
					$key = "yuanzigo";
					$token=JWT::encode($memberData, $key);					
					//组装跳转网址
					$jump=cookie('jump');
					if(strpos($jump,'#')){
						$jumpAy=explode('#',$jump);
						$jump=$jumpAy[0].'?token='.$token.'#'.$jumpAy[1];
					}else{
						$jump=$jump.'?token='.$token;
					}
					cookie('jump',null);
					if(!$hasMember['shop_id']){
					    //需要注册
					    $memberData=array(
					        'nickname'=>$userinfo['nickname'],
					        'openid'=>$userinfo['openid'],
					        'headimgurl'=>$userinfo['headimgurl'],
					        'shop_id'=>0
					    );
					    $key = "yuanzigo";
					    $token=JWT::encode($memberData, $key);
					    
					    $jump='http://www.yuanzigo.com/mobile/index/reg?token='.$token;
					    $this->redirect($jump);
					}else{
					    $this->redirect($jump);	
					}
				}else{
					//需要注册
					$memberData=array(
							'nickname'=>$userinfo['nickname'],
							'openid'=>$userinfo['openid'],
							'headimgurl'=>$userinfo['headimgurl'],
							'shop_id'=>0
					);
					$key = "yuanzigo";
					$token=JWT::encode($memberData, $key);
					
					$jump='http://www.yuanzigo.com/mobile/index/reg?token='.$token;
					$this->redirect($jump);
				}
				
				
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
		
	public function getUserInfoByToken(){
		$token=input('token');
		$userinfo=jwt_ck($token);
		return json(['data'=>['userinfo'=>$userinfo]]);
	}
	
	
	
	
	public function getTocToken(){
		$this->request=Request::instance();
		$jump=input('jump',0);
		if($jump){			
			cookie('jump',$jump);
		}else{
			cookie('jump','http://www.yuanzigo.com/mobile/Toc/index');
		}
		$this->redirect('getWeixinInfoToc');
	}
	
	public function getWeixinInfoToc(){
		if(isset($_GET['code'])){
			$oauth = & load_wechat('Oauth');
			$result = $oauth->getOauthAccessToken();
			if($result===FALSE){
				return false;
			}else{
				$userinfo=$oauth->getOauthUserinfo($result['access_token'],$result['openid']);
				$openid=$userinfo['openid'];
				$hasMember=db('member')->where(['openid'=>$openid])->find();
				if($hasMember){
				    db('user')->where(['openid'=>$openid])->setField('update_time',time());
				    db('member')->where(['openid'=>$openid])->setField('update_time',time());
					if($hasMember['shop_id']){
						$user_id=db('user')->where(['rule_id'=>$hasMember['shop_id']])->value('id');
					}else{
						$user_id=db('user')->where(['rule_id'=>$hasMember['id']])->value('id');
					}
					$memberData=array(
							'nickname'=>$hasMember['nickname'],
							'openid'=>$openid,
							'headimgurl'=>$userinfo['headimgurl'],
							'member_id'=>$hasMember['id'],
							'user_id'=>$user_id
					);
					$key = "yuanzigo";
					$token=JWT::encode($memberData, $key);
					//组装跳转网址
					$jump=cookie('jump');
					if(strpos($jump,'#')){
						$jumpAy=explode('#',$jump);
						$jump=$jumpAy[0].'?token='.$token.'#'.$jumpAy[1];
					}else{
						if(strpos($jump,'?')){
							$jump=$jump.'&token='.$token;
						}else{
							$jump=$jump.'?token='.$token;
						}						
					}
					cookie('jump',null);
					$this->redirect($jump);
					die;
						
				}else{
					//需要注册
					$memberData=array(
							'name'=>$userinfo['nickname'],
							'nickname'=>$userinfo['nickname'],
							'openid'=>$userinfo['openid'],
							'headimgurl'=>$userinfo['headimgurl']
					);
					$member=new \app\common\model\Member();
					$member->save($memberData);
					$member_id=$member->id;
					$userData=array(
							'name'=>$userinfo['nickname'],
							'password'=>md5('123456'),
							'rule'=>3,
							'rule_id'=>$member_id,
							'openid'=>$userinfo['openid']
					);
					
					$user=new \app\common\model\User();
					$user->save($userData);
					$user_id=$user->id;
					
					$memberData=array(
							'nickname'=>$userinfo['nickname'],
							'openid'=>$openid,
							'headimgurl'=>$userinfo['headimgurl'],
							'member_id'=>$member_id,
							'user_id'=>$user_id
					);
					$key = "yuanzigo";
					$token=JWT::encode($memberData, $key);
					$jump=cookie('jump');
					cookie('jump',null);
					$this->redirect($jump);
					die;
				}
	
	
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