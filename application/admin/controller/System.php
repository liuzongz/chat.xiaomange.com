<?php
namespace app\admin\controller;
use think\Controller;
use app\common\model\User;
use app\common\model\UserRules;
use app\admin\controller\Base;
use app\common\model\Shop;
use app\common\model\Question;
use app\common\model\Tags;
use think\Model;
class System extends Base{
    public function index(){
        return view();
    }
    public function user(){
        $r=input('r',0,'intval');
        if($r){
            $map=['rule'=>$r];
        }else{
            $map=[];
        }
        $assign=array(
            'list'=>User::where($map)->order('create_time desc')->paginate(10),
            'rule'=>UserRules::all(),
            'rl'=>$r
        );
        $id=input('id',0,'intval');
        
        if($id){
            $user=new User();
            $assign['info']=$user->get($id);
        }
        $this->assign($assign);
        return view();
    }
    public function rules(){
        $id=input('id',0,'intval');
        $rulse=new UserRules();
        if($_POST){
            if($id){
                $data=input('post.');
                $res=$rulse->save(['name'=>$data['name']],['id'=>$id]);
            }else{
                $data=$this->request->instance()->except('id');
                $res=$rulse->data($data)->save();
            }
            if($res){
                return  result(1, '保存成功');
            }else{
                return  result(0,'保存失败');
            }
        }else{            
            $assign=array(
                'rules'=>UserRules::all()
            );
            if($id){
                $assign['info']=UserRules::get($id);
            }
            $this->assign($assign);
            return view();
        }        
    }
    
    public function rulesdel(){
        $id=input('id',0,'intval');
        if($_POST && $id){
            $res=UserRules::destroy($id);
            if($res){
                return result(1,'删除成功');
            }else{
                return result(0,'删除失败');
            }
        }
    }
    
    public function useradd(){
        $id=input('id',0,'intval');
        $rule=input('rule',2,'intval');
        $aman=input('aman',0,'intval');
        if($_POST || $aman==1){
            $user=new User();
            $mrule=new UserRules();
            $shop=new Shop();
            $password=input('password');
            $shopname=input('shopname');
            $name=input('name');
            switch ($rule){
                case 1:
                    $data=array(
                        'name'=>$name
                    );                    
                    if($id){
                        if($password!='******'){
                            $data['password']=md5($password);
                        }
                        $res=$user->save($data,['id'=>$id]);
                    }else{
                        $data=array(
                            'name'=>$name,
                            'password'=>md5($password),
                            'rule'=>1
                        );
                        $res=$user->save($data);
                    }
                    break;
                case 2:
                    if($id){
                        $userinfo=$user->get($id);
                        $data=array(
                            'name'=>$name
                        );
                        if($password!='******'){
                            $data['password']=md5($password);
                        }
                        $res=$user->save($data,['id'=>$id]);
                        if(!$res){
                            return result(0,'保存失败');
                        }
                       
                        $res=$shop->save(['shopname'=>$shopname],['id'=>$userinfo->rule_id]);
                    }else{
                        $data=array(
                            'name'=>$name,
                            'password'=>md5($password),
                            'rule'=>$rule,
                            'rule_id'=>0
                        );
                        $res=$user->save($data);
                        $user_id=$user->id;
                        $data=array(
                            'shopname'=>$shopname,
                            'uid'=>$user_id
                        );
                        
                        $res=$shop->save($data);
                        $shop_id=$shop->id;
                        $res=$user->save(['rule_id'=>$shop_id],['id'=>$user_id]);
                    }
                    break;
            }
            if($res){
                return result(1,'保存成功');
            }else{
                return result(0,'保存失败');
            }
        }
    }
    
    public function question(){
        $id=input('id',0,'intval');
        $model=new Question();
        if($_POST){
            if($id){
                $res=$model->save(['title'=>input('title'),'intro'=>input('post.intro')],['id'=>$id]);
            }else{
                $res=$model->save(['title'=>input('title'),'intro'=>input('post.intro')]);
            }
            if($res){
                return result(1,'保存成功');
            }else{
                return result(0,'保存失败');
            }
        }else{
            if($id){
                $info=$model->get($id)->toArray();
                $this->assign(['info'=>$info]);
            }
            $list=$model->all(function($q){
                $q->order('sort');
            });
                $this->assign(['list'=>$list]);
            return view();
        }
    }
    
    public function tags(){
    	$id=input('id',0,'intval');
    	$model=new Tags();
    	if($_POST){
    		if($id){
    			$res=$model->save(['name'=>input('name')],['id'=>$id]);
    		}else{
    			$res=$model->save(['name'=>input('name')]);
    		}
    		if($res){
    			return result(1,'保存成功');
    		}else{
    			return result(0,'保存失败');
    		}
    	}else{
    		if($id){
    			$info=$model->get($id)->toArray();
    			$this->assign(['info'=>$info]);
    		}
    		$list=$model->all();
    		$this->assign(['list'=>$list]);
    		return view();
    	}
    }
}