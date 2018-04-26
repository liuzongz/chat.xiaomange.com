<?php
namespace app\admin\controller;
use think\Controller;
use app\common\model\Leftmenu as Lmenu;
use app\common\model\UserRules;
class Leftmenu extends Base{
    public function index(){        
        $menu=new Lmenu();
        $list = $menu::where(['fid'=>0,'rule'=>input('rule',1,'intval')])->order('order')->paginate(10);
        $assign=array(
            'page_title'=>'菜单管理',
            'list'=>$list,
            'rules'=>UserRules::all()
        );
        $this->assign($assign);
        return view();
    }
    
    
    public function add(){
        $id=input('id',0,'intval');
        $lmenu=new Lmenu();
        $aman=input('aman',0,'intval');
        if($_POST || $aman==1){
            if($id){
                //$data=input('post.');
                $data=$this->request->instance()->except('id,aman');
                $res=$lmenu->save($data,['id'=>$id]);
                
            }else{
                $data=$this->request->instance()->except('id,aman');
                $res=$lmenu->data($data)->save();
            }
            if($res){
                return result(1,'保存成功');
            }else{
                return result(0,'保存失败');
            }
        }else{
            $assign=array(
                'page_title'=>'左侧菜单添加',
                'menus'=>$lmenu->all(['fid'=>0]),
                'rules'=>UserRules::all()
            );
            
            if($id){
                $assign['menu']=$lmenu->get($id)->toArray();
            }
            $this->assign($assign);
            return view();
        }                
    }
    
    
    function del(){
        $id=input('id',0,'intval');
        if($_POST && $id){
            $menu=new Lmenu();
            $res=$menu::destroy($id);
            if($res){
                return result(1,'删除成功');
            }else{
                return result(0,'删除失败');
            }
        }else{
            return result(0,'非法操作');
        }
    }
}
