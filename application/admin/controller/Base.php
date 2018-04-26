<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\common\model\User;
use app\common\model\Leftmenu;
use think\Model;
class Base extends Controller{
    public function _initialize(){
        $this->userinfo=cookie('userinfo');
        if(!$this->userinfo){
            $this->redirect(url('index/index/index'));
        }else{
            
            if($this->userinfo['rule']!=1){
                $this->redirect(url('index/index/index'));
            }
            $this->assign('userinfo',$this->userinfo);
            $this->request=Request::instance();
            $menuinfo=array(
                'controller'=>$this->request->controller(),
                'action'=>$this->request->action()
            );
            $this->assign('menuinfo',$menuinfo);
            $menu=new Leftmenu();
            $leftMenu=Leftmenu::all(function($query){
                $query->where(['fid'=>0,'rule'=>1])->order('order');
            });
            for($i=0;$i<count($leftMenu);$i++){
                $data=['fid'=>$leftMenu[$i]['id']];
                $leftMenu[$i]['son']=Leftmenu::all(function($query) use ($data) {
                    $query->where(['fid'=>$data['fid'],'rule'=>1])->order('order');
                });
            }
            $this->assign(['leftMenu'=>$leftMenu]);
        }
    }
}