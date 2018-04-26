<?php
namespace app\index\controller;
use think\Request;
use app\common\model\User;
use think\Controller;
class Base extends Controller{
    public function _initialize(){
        if(is_weixin){
            $this->redirect(U('weixin/index/index'));
        }
        $this->userinfo=cookie('userinfo');
        if(!$this->userinfo){
            $this->redirect('index/index');
        }
        $this->assign('userinfo',$this->userinfo);
    }
}
