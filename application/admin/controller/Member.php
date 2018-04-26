<?php
namespace app\admin\controller;
use think\Model;
use think\helper\Time;
use app\common\model\User;
use app\common\model\Company;
use app\common\model\Staff;
use app\common\model\Freeman;
use app\common\model\Supplier;
use think\Controller;
class Member extends Base{
    public function index(){
        
    }
    
    public function admin(){
        $list = User::where(['rule'=>1])->order('create_time desc')->paginate(10);
        $assign=array(
            'page_title'=>'管理员管理',
            'list'=>$list
        );
        $this->assign($assign);
        return view();
    }
    
    public function company(){
        if($_POST){
            $id=input('id',0,'intval');
            $rate=input('val');
            if($id){
                $res=db('company')->where(['id'=>$id])->setField('rate',$rate);
                if($res){
                    return result(1,'保存成功');
                }else{
                    return result(0,'没修改，没保存');
                }
            }else{
                return result(0,'获取不到ID参数');
            }
        }else{
            $list = User::where(['rule'=>2])->order('create_time desc')->paginate(10);
            $assign=array(
                'page_title'=>'企业管理',
                'list'=>$list
            );
            $this->assign($assign);
            return view();
        }
    }
    
    public function staff(){
        $list = User::where(['rule'=>3])->order('create_time desc')->paginate(10);
        $assign=array(
            'page_title'=>'内部员工管理',
            'list'=>$list
        );
        $this->assign($assign);
        return view();
    }
    public function supplier(){
        $list = User::where(['rule'=>5])->order('create_time desc')->paginate(10);
        $assign=array(
            'page_title'=>'代理商管理',
            'list'=>$list
        );
        $this->assign($assign);
        return view();
    }
    
    
    public function freeman(){
        $list = User::where(['rule'=>4])->order('create_time desc')->paginate(10);
        $assign=array(
            'page_title'=>'自由职业者管理',
            'list'=>$list
        );
        $this->assign($assign);
        return view();
    }
    
    
    public function add(){
        
        $user=new User();
        $aman=input('aman',0,'intval');
        $id=input('id',0,'intval');
        if($_POST || $aman==1){
            if($id){
                $password=input('password');
                if($password=='******'){
                    $data=$this->request->instance()->except('password,uname,aman');
                }else{
                    $data=$this->request->instance()->except('password,uname,aman');
                    $data['password']=md5($password);
                }
                $res=$user->data($data)->isUpdate(true)->save();
            }else{
                $rule=input('rule',4,'intval');
                switch ($rule){
                    case 1:
                        $data=array(
                            'name'=>input('name'),
                            'password'=>md5(input('password')),
                            'rule'=>1,
                            'rule_id'=>0
                        );
                        break;
                    case 2:
                        $model=new Company();
                        $model->save(['name'=>input('uname')]);
                        $rule_id=$model->id;
                        $data=array(
                            'name'=>input('name'),
                            'password'=>md5(input('password')),
                            'rule'=>2,
                            'rule_id'=>$rule_id
                        );
                        break;
                    case 3:
                        $model=new Staff();
                        $model->save(['name'=>input('name')]);
                        $rule_id=$model->id;
                        $data=array(
                            'name'=>input('name'),
                            'password'=>md5(input('password')),
                            'rule'=>3,
                            'rule_id'=>$rule_id
                        );
                        break;
                    case 4:
                        $model=new Freeman();
                        $model->save(['name'=>input('name')]);
                        $rule_id=$model->id;
                        $data=array(
                            'name'=>input('name'),
                            'password'=>md5(input('password')),
                            'rule'=>4,
                            'rule_id'=>$rule_id
                        );
                        break;
                    case 5:
                        $model=new Supplier();
                        $model->save(['name'=>input('name')]);
                        $rule_id=$model->id;
                        $data=array(
                            'name'=>input('name'),
                            'password'=>md5(input('password')),
                            'rule'=>5,
                            'rule_id'=>$rule_id
                        );
                        break;
                        
                }
                
                $res=$user->data($data)->save();
            }
            
            if($res){
                return  result(1, '保存成功');
            }else{
                return  result(0,'保存失败');
            }
        }else{
            $jump=$_SERVER['HTTP_REFERER'];
            if($id){
                $info=$user->get($id);
                $title='修改用户';
            }else{
                $info=array();
                $title='添加用户';
            }
            $assign=array(
                'info'=>$info,
                'page_title'=>'添加用户',
                'jump'=>$jump
            );
            $this->assign($assign);
            return view();
        }
    }
}