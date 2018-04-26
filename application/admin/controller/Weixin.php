<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Db;
use app\common\model\WeixinMenu;

class Weixin extends Base{
    public function index(){
       
        $this->redirect('menu');
    }
    public function menu(Request $request){
        $user = & load_wechat('User');
        $group = $user->getTags();
        if($group===FALSE){
            $group=array();
        }
        $this->assign('group',$group['tags']);
        $tag=input('tag',0,'intval');
        if($tag==999){
            $tag=0;
        }
        $map=array(
            'tag'=>$tag,
            'pid'=>0
        );
        $menu=new WeixinMenu();
        $list=$menu->all(['pid'=>0]);
        
        $this->assign('list',$list);
        return view();
    }
    
    public function menuSort(Request $request){
        if($request){
            $id=input('id',0,'intval');
            $sort=input('sort',0,'intval');
            $sort=$sort+1;
            $map=array('id'=>$id);
            $v=db('weixin_menu')->where($map)->setField('sort',$sort);
        }else{
            $pid=input('pid',0,'intval');
            $menus=db('weixin_menu')->where(array('pid'=>$pid))->order('sort')->select();
            $this->assign('menus',$menus);
            return view();
        }
    }
    public function menuadd(){
            $tag=input('tag',0,'intval');
            $id=input('id',0,'intval');
            $pid=input('pid',0,'intval');
            $title=input('title',0);
            $type=input('type',0);
            $keyword=input('keyword');
            $url=input('url');
            $data=array(
                'status'=>1,
                'sort'=>0,
                'token'=>'jingygr',
                'pid'=>$pid,
                'keyword'=>$keyword,
                'url'=>$url,
                'type'=>$type,
                'title'=>$title,
                'tag'=>$tag
            );
            if($id){
                $map=array(
                    'id'=>$id
                );
                $v=db('weixin_menu')->where($map)->update($data);
                if($v){
                    $this->success('修改成功');
                }else {
                    $this->error('修改失败');
                }
            }else {
                $v=db('weixin_menu')->insert($data);
                if($v){
                    $this->success('添加成功');
                }else {
                    $this->error('添加失败');
                }
            }
    }
    public function menuDel(){
        $id=input('id',0,'intval');
        $pid=db('weixin_menu')->where(array('id'=>$id))->find();
        $pid=$pid['pid'];
        if($pid==0){
            $result=db('weixin_menu')->where(array('id'=>$id))->delete();
        }else{
            $result=db('weixin_menu')->where(array('pid'=>$pid))->delete();
        }
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    function menuGetById(Request $request){
        $pid=input('pid',0,'intval');
        $id=input('id',0,'intval');
        if($id){
            $menu=new WeixinMenu();
            $info=$menu->get(['id'=>$id]);
            $status=1;
        }else {
            $info=0;
            $status=0;
        }
        $user = & load_wechat('User');
        $group = $user->getTags();
        $result=array(
            'info'=>$info,
            'group'=>$group['tags'],
            'menuone'=>$menu->get(['pid'=>0])
        );
        return json($result);
    }
    
    public function menuToWeixin(){
        $info=db('weixin_menu')->where(array('pid'=>0,'tag'=>0))->order('sort')->select();
        $menuAy=array();
        for($i=0;$i<count($info);$i++){
            $matchrule=array(
                'language'=>'zh_CN'
            );
            $subAy=array();
            $menuAy[$i]['name']=$info[$i]['title'];
            $menuAy[$i]['type']=$info[$i]['type'];
            if($info[$i]['type']=='click'){
                $menuAy[$i]['key']=$info[$i]['keyword'];
            }
            if($info[$i]['type']=='view'){
                $menuAy[$i]['url']=str_replace('myurl','http://'.$_SERVER['SERVER_NAME'].'/',$info[$i]['url']);
            }
            $v=db('weixin_menu')->where(array('pid'=>$info[$i]['id']))->order('sort')->select();
            if($v){
                for($ii=0;$ii<count($v);$ii++){
                    $subAy[$ii]['name']=$v[$ii]['title'];
                    $subAy[$ii]['type']=$v[$ii]['type'];
                    if($v[$ii]['type']=='view'){
                        $subAy[$ii]['url']=str_replace('myurl','http://'.$_SERVER['SERVER_NAME'].'/',$v[$ii]['url']);
                    }
                    if($v[$ii]['type']=='click'){
                        $subAy[$ii]['key']=$v[$ii]['keyword'];
                    }
                }
                $menuAy[$i]['sub_button']=$subAy;
            }
            $menuAy[$i]['matchrule']=$matchrule;
        }
       /*  $menuAy[count($info)]=array(
        		'name'=>'扫一扫',
        		'type'=>'scancode_waitmsg',
        		'key'=>'scanqr',
        		'sub_button'=>array()
        ); */
        $menus=array(
            'button'=>$menuAy
        );
        $menu = & load_wechat('menu');
        // 创建微信菜单
        $result = $menu->createMenu($menus);    
        // 处理创建结果
        if($result===FALSE){
            // 接口失败的处理
            $this->error($menu->errMsg);
        }else{
            // 接口成功的处理
            $this->success('同步成功');
        }
    }
    
    public function menuDiy(){
        $tag=input('tag',0);
        $menu=array();
        $map=array(
            'pid'=>0,
        		
        );
        $info=db('weixin_menu')->where($map)->order('sort')->select();
        $matchrule=array(
        		'language'=>'zh_CN',
        		'tag_id'=>$tag
        );
        for($i=0;$i<count($info);$i++){           
            $subAy=array();
            $menuAy[$i]['name']=$info[$i]['title'];
            $menuAy[$i]['type']=$info[$i]['type'];
            if($info[$i]['type']=='click'){
                $menuAy[$i]['key']=$info[$i]['keyword'];
            }
            if($info[$i]['type']=='view'){
                $menuAy[$i]['url']=str_replace('myurl','http://'.$_SERVER['SERVER_NAME'].'/',$info[$i]['url']);
            }
            $v=db('weixin_menu')->where(array('pid'=>$info[$i]['id']))->select();
            if($v){
                for($ii=0;$ii<count($v);$ii++){
                    $subAy[$ii]['name']=$v[$ii]['title'];
                    $subAy[$ii]['type']=$v[$ii]['type'];
                    if($v[$ii]['type']=='view'){
                        $subAy[$ii]['url']=str_replace('myurl','http://fang.xiaomange.com',$v[$ii]['url']);
                    }
                    if($v[$ii]['type']=='click'){
                        $subAy[$ii]['key']=$v[$ii]['keyword'];
                    }
                }
                $menuAy[$i]['sub_button']=$subAy;
            }
            //$menuAy['matchrule']=$matchrule;
        }
        $menuAy[count($info)]=array(
        		'name'=>'二维码验票',
        		'type'=>'scancode_waitmsg',
        		'key'=>'scanqr'
        );
        $data['button']=$menuAy;
        $data['matchrule']=$matchrule;
        $menu = & load_wechat('menu');
        $result = $menu->createCondMenu($data);
        if($result===FALSE){
            // 接口失败的处理
            $this->error($menu->errMsg);
        }else{
            // 接口成功的处理
            $this->success('同步成功');
        }
    }
    
    
    /* 关键词部分开始 */
    public function keyword(){
        $p=input('p',0,'intval');
        $limit=10;
        $pages=floor(db('weixin_keyword')->count()/$limit)+1;
        $this->assign('pages',$pages);
        $list=db('weixin_keyword')->order('ctime desc')->page($p,$limit)->select();
        for($i=0;$i<count($list);$i++){
            $map=array(
                'id'=>$list[$i]['kgroup']
            );
            $list[$i]['kgroup']=db('weixin_keyword_cate')->where($map)->getField('value');
        }
        $this->assign('p',$p);
        $this->assign('list',$list);
        return view();
    }
    
    public function createkeyword(){
        if($_POST){
            $data=array(
                'keyword'=>input('keyword'),
                'kgroup'=>input('kgroup',0,'intval'),
                'ctime'=>time()
            );
            $ktype=input('ktype',0,'intval');
            if($ktype==1){
                $data['ktype']=1;
                $data['ktext']=$_POST['ktext'];
            }else{
                $data['ktype']=2;
                $data['ktitle']=input('ktitle');
                $data['ktitle']=json_encode($data['ktitle']);
            }
            if(db('weixin_keyword')->insert($data)){
                $this->success('添加成功');
            }else {
                $this->error('添加失败');
            }
        }else{
            $this->artCate=db('article_cate')->order('sort')->select();
            $this->cate=db('weixin_keyword_cate')->select();
            return view();
        }
    }
    
    
    public function editkeyword(){
        if($_POST){
            $id=input('id');
            if($id){
                $data=array(
                    'keyword'=>input('keyword'),
                    'kgroup'=>input('kgroup'),
                    'ktext'=>$_POST['ktext'],
                    'ctime'=>time()
                );
                if(db('weixin_keyword')->where(array('id'=>$id))->save($data)){
                    $this->success('修改成功');
                }else {
                    $this->error('修改失败');
                }
            }else{
                $this->error('非法来源');
            }
        }else{
            $id=input('id');
            $keyword=db('weixin_keyword')->where(array('id'=>$id))->find();
            $this->assign('keyword',$keyword);
            $cates=db('weixin_keyword_cate')->select();
            $this->assign('cate',$cates);
            return view();
        }
    }
    
    public function delkeywordhandle(){
        $id=input('id');
        if(db('weixin_keyword')->delete($id)){
            $this->success('删除成功');
        }else {
            $this->error('删除失败');
        }
    }
    
    /* 关键词部分结束 */
}
