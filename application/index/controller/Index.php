<?php
namespace app\index\controller;
use think\Request;
use app\common\model\User;
use think\Controller;
class Index extends Controller{
    public function index(){
        $this->userinfo=cookie('userinfo');
        if(isset($this->userinfo['rule']) && $this->userinfo['rule']==1){
            $this->redirect('admin/system/index');
        }
        if(isset($this->userinfo['rule']) && $this->userinfo['rule']==3){
            $this->redirect('shop/activity/index');
        }
        return view();
    }
    
    public function login(){
        $data=array(
            'name'=>input('username'),
            'password'=>md5(input('password'))
        );
        $user=new User();
        $ck=$user->get($data);
        
        if(!$ck){
            return result(0,'用户名或密码有误');
        }else{
            $userinfo=$ck->toArray();
            $userinfo['ruletxt']=$ck->ruletxt;
            cookie('userinfo',$userinfo);
            return result(1,'登录成功');
        }
    }
    
    public function logout(){
        cookie('userinfo',null);
        $this->redirect('index/index');
    }
}
