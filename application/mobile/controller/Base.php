<?php
namespace app\mobile\controller;
use think\Controller;
use think\Request;
use think\Model;
class Base extends Controller{
    public function _initialize(){
    	$this->request=Request::instance();
    	$this->weixinInfo=cookie('weixinInfo');
    	$this->userInfo=getUserInfo();
    	if(!$this->weixinInfo || !$this->userInfo){
    	    $url=url('Wechat/getWeixinInfo').'?jump='.$this->request->url(true);
    	    $this->redirect($url);
    	}
    	
    	if($this->userInfo==1 ||$this->userInfo['rule']!=2){
    	    //未注册
    	    $url=url('Passport/reg').'?jump='.$this->request->url(true);
    	    $this->redirect($url);
    	}
    	    	
    	$this->assign('nowtime',time());$this->assign('nowtime',time());
    }
}