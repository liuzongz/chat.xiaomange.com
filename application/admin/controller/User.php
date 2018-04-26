<?php
namespace app\admin\controller;
use think\Model;
use app\common\model\User as Muser;
use app\common\model\UserRules;
use app\common\model\Company;
use think\Controller;
use think\helper\Time;
class User extends Base{
    public function index(){
        $this->assign(['page_title'=>'用户管理中心']);
        $admins=array(
            'total'=>$this->getUserTotal(1),
            'today'=>$this->getUserToday(1),
            'week'=>$this->getUserWeek(1),
            'txt'=>$this->getUserInLastWeek(1)
        );
        $members=array(
            'total'=>$this->getUserTotal(2),
            'today'=>$this->getUserToday(2),
            'week'=>$this->getUserWeek(2),
            'txt'=>$this->getUserInLastWeekForJson(2)
        );
        $companys=array(
            'total'=>$this->getUserTotal(3),
            'today'=>$this->getUserToday(3),
            'week'=>$this->getUserWeek(3),
            'txt'=>$this->getUserInLastWeekForJson(3)
        );
        $proxys=array(
            'total'=>$this->getUserTotal(4),
            'today'=>$this->getUserToday(4),
            'week'=>$this->getUserWeek(4),
            'txt'=>$this->getUserInLastWeekForJson(4)
        );
        $assign=array(
            'admins'=>$admins,
            'members'=>$members,
            'companys'=>$companys,
            'proxys'=>$proxys
        );
        $this->assign($assign);
		return view();
    }
    
    public function admin(){
        $user=new Muser();
        $list = Muser::where(['rule'=>1])->order('create_time desc')->paginate(10);
        
        $assign=array(
            'page_title'=>'企业管理',
            'list'=>$list
        );
        $this->assign($assign);
        return view();
    }
    
    public function company(){
        $user=new Muser();
        $list = Muser::where(['rule'=>3,'rule_id'=>['gt',0]])->order('create_time desc')->paginate(10);
        for($i=0;$i<count($list);$i++){
            $list[$i]['info']=db('company')->where('id',$list[$i]['rule_id'])->find();
        }
        $assign=array(
            'page_title'=>'企业管理',
            'list'=>$list
        );
        $this->assign($assign);
        return view();
    }
    public function companyAdd(){
        $id=input('id',0,'intval');
        $user=new Muser();
        $aman=input('aman',0,'intval');
        if($_POST ||$aman==1){            
            if($id){
                $password=input('password');
                if($password=='******'){
                    $data=$this->request->instance()->except('password,name');
                }else{
                    $data=$this->request->instance()->except('aman,name');
                    $data['password']=md5($password);
                }
                $res=$user->data($data)->isUpdate(true)->save();
            }else{
                $company=new Company();
                $company->save(['name'=>input('name')]);
                $rule_id=$company->id;
                $data=$this->request->instance()->except('id,aman,name');
                $data['rule_id']=$rule_id;
                $res=$user->data($data)->save();
            }
            if($res){
                return  result(1, '保存成功');
            }else{
                return  result(0,'保存失败');
            }
        }else{
            if($id){
                $company=new Company();
                $info=$user->get($id)->toArray();
                $info['company']=$company->get($info['rule_id'])->toArray();
                $this->assign('info',$info);
            }
            return view();
        }
        
    }
    
    public function companydetail(){
        $id=input('id',0,'intval');
        $model=new Company();
        $upmodel=model('Companyupload');
        $info=$model->get($id);
        $assign=array(
            'page_title'=>'企业资料',
            'info'=>$info,
            'upload'=>$upmodel->all(['cid'=>$id])
        );
        $this->assign($assign);
        return view();
    }
    
    public function proxy(){
        $user=new Muser();
        $list = Muser::where(['rule'=>4,'rule_id'=>['gt',0]])->order('create_time desc')->paginate(10);
        for($i=0;$i<count($list);$i++){
            $list[$i]['info']=db('proxy')->where('id',$list[$i]['rule_id'])->find();
            $list[$i]['proxy']=db('user')->where('id',$list[$i]['proxy'])->find();
        }
        $assign=array(
            'page_title'=>'代理商管理',
            'list'=>$list
        );
        $this->assign($assign);        
        return view();
    }
    
    public function proxyAdd(){
        $id=input('id',0,'intval');
        if($_POST){
            $user=new Muser();
            $data=array(
                'name'=>input('name'),
                'rule'=>4,
                'proxy'=>input('fid',0)
            );
            $password=input('password',0);
            if($password){
                $data['password']=md5($password);
            }
            if($id){
                $txt='保存';
                $res=db('user')->where('id',$id)->update($data);
                $data=array(
                    'name'=>input('name'),
                    'tel'=>input('tel'),
                    'prov'=>input('prov'),
                    'city'=>input('city'),
                    'area'=>input('area'),
                    'fid'=>input('fid')
                );                
                $res=db('proxy')->where(['uid'=>$id])->update($data);                
            }else{
                $txt='添加';
                $user->save($data);
                $uid=$user->id;
                //添加完用户数据后，添加代理商数据
                $data=array(
                    'name'=>input('name'),
                    'tel'=>input('tel'),
                    'prov'=>input('prov'),
                    'city'=>input('city'),
                    'area'=>input('area'),
                    'fid'=>input('fid'),
                    'uid'=>$uid
                );
                $proxy=new Proxy();
                $proxy->save($data);
                $proxyId=$proxy->id;
                $res=db('user')->where('id',$uid)->setField('rule_id',$proxyId);
            }
            if($res){
                return result(1,$txt.'成功');
            }else{
                return result(0,$txt.'失败');
            }
        }else{
            $prov=db('citydata')->where('fid',1)->select();
            $proxy=db('proxy')->where('fid',0)->select();            
            $assign=array(
                'proxy'=>$proxy,
                'prov'=>$prov,
                'page_title'=>'添加代理商'
            );
            if($id){
                $proxy=new Proxy();
                $info=$proxy->get($id)->toArray();
                $assign['info']=$info;
            }
            $this->assign($assign);
            return view();
        }        
    }
    
    public function member(){
        $user=new Muser();
        $list = Muser::where(['rule'=>2,'rule_id'=>['gt',0]])->order('create_time desc')->paginate(10);
        for($i=0;$i<count($list);$i++){
            $list[$i]['info']=db('member')->where('mid',$list[$i]['rule_id'])->find();
            $list[$i]['proxy']=db('user')->where('id',$list[$i]['proxy'])->find();            
        }
        $assign=array(
            'page_title'=>'会员管理',
            'list'=>$list
        );
        $this->assign($assign);
        return view();
    }
    
    
    public function getUserInLastWeek($rule){        
        $user=new Muser();
        $str=array();
        for($i=6;$i>-1;$i--){
            $start=strtotime(date('Y-m-d'))-$i*86400;
            $end=$start+86399;
            $map=array(
                'create_time'=>array('between',[$start,$end]),
                'rule'=>$rule
            );
            $num=$user->where($map)->count();
            array_push($str, $num);
        }
        return implode(',', $str);
    }
    public function getUserInLastWeekForJson($rule){
        $user=new Muser();
        $str=array();
        for($i=6;$i>-1;$i--){
            $start=strtotime(date('Y-m-d'))-$i*86400;
            $end=$start+86399;
            $map=array(
                'create_time'=>array('between',[$start,$end]),
                'rule'=>$rule
            );
            $num=$user->where($map)->count();
            $ay=array(
                'elapsed'=>date('Y-m-d',$start),
                'value'=>$num
            );
            array_push($str, $ay);
        }
        return json_encode($str);
    }
    public function getUserTotal($rule){
        $user=new Muser();
        $map=array(
            'rule'=>$rule
        );
        return $num=$user->where($map)->count();
    }
    public function getUserToday($rule=1){
        $user=new Muser();
        $today=Time::today();        
        $map=array(
            'create_time'=>array('between',$today),
            'rule'=>$rule
        );
        return $user->where($map)->count();
    }
    public function getUserWeek($rule=1){
        $user=new Muser();
        $week=Time::week();      
        $map=array(
            'create_time'=>array('between',$week),
            'rule'=>$rule
        );
        return $user->where($map)->count();
    }
    
    
    public function getCityArray(){
        $id=input('id',1,'intval');
        $ay=db('citydata')->where('fid',$id)->select();
        return result(1,$ay);
    }
    
    //指派代理商
    public function appointProxy(){
        $id=input('id',0,'intval');
        if(!$id){
            echo '非法操作';die;
        }
        if($_POST){
            $pid=input('uid',0,'intval');
            if(!$pid){
                return result(0,'非法操作');
            }else{                
                $uid=db('user')->where(['rule'=>3,'rule_id'=>$id])->value('id');
                $res=db('company')->where(['id'=>$id])->setField('proxy',$pid);
                $res2=db('user')->where(['id'=>$uid])->setField('proxy',$pid);
                if($res||$res2){
                    return result(1,'指派成功');
                }else{
                    return result(0,'指派失败');
                }
            }
        }else{
            $user=new Muser();
            $list = Muser::where(['rule'=>4,'rule_id'=>['gt',0]])->order('create_time desc')->paginate(10);
            for($i=0;$i<count($list);$i++){
                $list[$i]['info']=db('proxy')->where('id',$list[$i]['rule_id'])->find();
                $list[$i]['proxy']=db('user')->where('id',$list[$i]['proxy'])->find();
            }
            $company=new Company();
            $assign=array(
                'page_title'=>'指派代理商',
                'list'=>$list,
                'company'=>$company->get($id)
            );
            $this->assign($assign);
            return view();
        }        
    }
    
    public function proxyCompany(){
        $id=input('id',0,'intval');
        if(!$id){
            echo '非法操作';
        }
        $company=new Company();
        $list = $company->where(['proxy'=>$id])->order('create_time desc')->paginate(10);
        $assign=array(
            'proxy'=>db('proxy')->where('id',$id)->find(),
            'page_title'=>'代理商放下的企业 ',
            'list'=>$list
        );
        $this->assign($assign);
        return view();
        
    }
}
