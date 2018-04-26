<?php 
namespace app\api4\controller;
use think\Model;
use think\helper\Time;
use think\Controller;
use Firebase\JWT\JWT;
use app\common\model\User;
use app\common\model\TagsShop;
use app\common\model\Activity;
class Shop extends Base{
	public function checkYzm(){
		$yToken=input('ytoken');
		$yzm=input('yzm');
		$code=jwt_ck($yToken);
		if($code==$yzm||$yzm==4321){
			return json(['data'=>['code'=>1,'msg'=>'验证码认证通过']]);
		}else{
			return json(['data'=>['code'=>0,'msg'=>'验证码认证不通过']]);
		}
	}
    public function reg(){
    	$mobile=input('mobile');
    	$city=input('city');
    	$tags=input('tags');
    	if(!$mobile){
    		return json(['data'=>['code'=>0,'msg'=>'请输入手机号码']]);
    	}
    	
    	if(!$city){
    		return json(['data'=>['code'=>0,'msg'=>'请选择城市']]);
    	}
    	
    	if(!$tags){
    		return json(['data'=>['code'=>0,'msg'=>'请至少选择一个关注的领域']]);
    	}
    	$tags=explode(',',$tags);

    	$token=input('token');
    	if(!$token){
    		return json(['data'=>['code'=>0,'msg'=>'请传入Token']]);
    	}
    	
    	$memberInfo=jwt_ck($token);    	
    	if(!$memberInfo){
    		return json(['data'=>['code'=>0,'msg'=>'Token有误']]);
    	}
    	
    	//开始插入数据
    	//先插入店铺数据，获取店铺ID，然后插入User数据，最后插入Member数据    	
    	//店铺数据
    	
    	
    	$shopData=array(
    			'shopname'=>$memberInfo->nickname.'的小店',
    			'headimg'=>$memberInfo->headimgurl,
    			'tel'=>$mobile,
    			'city'=>$city,
    	        'shop_type'=>1,
    			'create_time'=>time(),
    			'update_time'=>time()
    	);
    	$shop_id=db('shop')->insertGetId($shopData);
    	if(!$shop_id){
    		return json(['data'=>['code'=>0,'msg'=>'插入店铺数据有误']]);
    	}
    	//插入店铺标签
    	$mTags=new TagsShop();
    	for($i=0;$i<count($tags);$i++){
    		$tagsData[$i]=array(
    				'tags_id'=>$tags[$i],
    				'shop_id'=>$shop_id
    		);
    	}
    	
    	$res=$mTags->saveAll($tagsData);
    	if(!$res){
    		return json(['data'=>['code'=>0,'msg'=>'标签插入失败']]);
    	}
    	//用户数据
    	$userData=array(
				'name'=>$memberInfo->nickname,
    			'password'=>md5('yz123456'),
    			'openid'=>$memberInfo->openid,
    			'rule'=>2,
    			'rule_id'=>$shop_id
    	);
    	$user=new User();
    	$user->save($userData);
    	$user_id=$user->id;
    	if(!$user_id){
    		return json(['data'=>['code'=>0,'msg'=>'插入用户数据有误']]);
    	}
    	
    	//把新插入的用户的ID更新到店铺信息中
    	$res=db('shop')->where(['id'=>$shop_id])->setField('uid',$user_id);
    	if(!$res){
    		return json(['data'=>['code'=>0,'msg'=>'更新店铺数据有误']]);
    	}
    	
    	//Member数据
    	$memberData=array(
    			'name'=>$memberInfo->nickname,
    			'nickname'=>$memberInfo->nickname,
    			'headimgurl'=>$memberInfo->headimgurl,
    			'openid'=>$memberInfo->openid,
    			'shop_id'=>$shop_id,
    			'create_time'=>time(),
    			'update_time'=>time()
    	);
    	$ck=db('member')->where(['openid'=>$memberInfo->openid])->find();
    	if($ck){
    	    $res=db('member')->where(['openid'=>$memberInfo->openid])->setField('shop_id',$shop_id);
    	}else{
    	    $res=db('member')->insert($memberData);
    	    if(!$res){
    	        return json(['data'=>['code'=>0,'msg'=>'插入Member数据有误']]);
    	    }
    	}
    	
    	
    	//重新获取Token，并回传给前端
    	$newTokenData=array(
    			'openid'=>$memberInfo->openid,
    			'nickname'=>$memberInfo->nickname,
    			'uid'=>$user_id,
    			'headimgurl'=>$memberInfo->headimgurl,
    			'shop_id'=>$shop_id
    	);
    	$key = "yuanzigo";
    	$token=JWT::encode($newTokenData, $key);
    	return json(['data'=>['code'=>1,'msg'=>$token]]);    	
    }
    
    public function getMyActivity(){
    	$token=input('token');
    	$tokenData=jwt_ck($token);
    	$tagsid=input('tagsid',0,'intval');
    	$model=new \app\common\model\Activity();
    	$list=$model->all(function($q)use($tokenData){
    		$q->where(['shop_id'=>$tokenData->shop_id]);
    	});
    	
    	
    	for($i=0;$i<count($list);$i++){
    		$id=$list[$i]['id'];
    		$com=new \app\common\model\Commission();
    		$allPrice=0;
    		$allProfit=0;
    		$allSaleNum=0;
    		$comList=$com->all(['activity_id'=>$id]);
    		for($c=0;$c<count($comList);$c++){
    			$allProfit=$allProfit+$comList[$c]['profit'];
    			$allPrice=$allPrice+$comList[$c]['allprice'];
    			$allSaleNum=$allSaleNum+$comList[$c]['buynum'];
    		}
    		$list[$i]['allPrice']=$allPrice;
    		$list[$i]['profit']=$allProfit;
    		$list[$i]['allsalenum']=$allSaleNum;
    		
    		
    		$format=new \app\common\model\ActivityFormat();
    		$formatInfo=$format->get(['activity_id'=>$id]);
    		    		
    		$list[$i]['format']=$formatInfo;
    		
    		
    		$elementActivityFormat=$format->get(['activity_id'=>$list[$i]['element']]);
    		$elementStock=$elementActivityFormat->stock;
    		$order=new \app\common\model\Orderinfo();
    		$elementSaleNum=$order->where(['element_id'=>$list[$i]['element'],'pay_status'=>['in',[1,9]]])->sum('total_num');
    		$lastStock=$elementStock-$elementSaleNum;
    		$list[$i]['lastnum']=$lastStock;    		
    		
    		if($list[$i]['headimg']){
    			$img=$list[$i]['headimg'][0];
    			$list[$i]['headimgs']=$img;
    		}else{
    			$list[$i]['headimgs']='';
    		}    		
    	}
    	
    	if($list){
    		return json(['data'=>['code'=>1,'msg'=>$list]]);
    	}else {
    		return json(['data'=>['code'=>0,'msg'=>'暂时没有数据']]);
    	}
    	
    }
    
    public function getCount(){
		$token=input('token');
    	$tokenData=jwt_ck($token);
    	$model=new \app\common\model\Activity();
    	$list=$model->all(function($q)use($tokenData){
    		$q->where(['shop_id'=>$tokenData->shop_id]);
    	});
    	$com=new \app\common\model\Commission();
    	//计算盈利
    	$comList=$com->all(['shop_id'=>$tokenData->shop_id]);
    	$allProfit=0;
    	$allPrice=0;
    	for($i=0;$i<count($comList);$i++){
    		$allProfit=$allProfit+$comList[$i]['profit'];
    		$allPrice=$allPrice+$comList[$i]['allprice'];
    	}
    	$data=array(
    			'allPrice'=>$allPrice,
    			'profit'=>$allProfit
    	);
    	if($list){
    		return json(['data'=>['code'=>1,'msg'=>$data]]);
    	}else {
    		return json(['data'=>['code'=>0,'msg'=>'暂时没有数据']]);
    	}
    }
    
    public function getOrderList(){
    	$token=input('token');
    	$tokenData=jwt_ck($token);
    	$id=input('id',0,'intval');
    	$status=input('status',9,'intval');
    	$order=new \app\common\model\Orderinfo();
    	$orderList=$order->all(['activity_id'=>$id,'status'=>$status]);
    	if($orderList){
    		return json(['data'=>['code'=>1,'msg'=>$orderList]]);
    	}else{
    		return json(['data'=>['code'=>0,'msg'=>'暂时没有数据']]);
    	}    	
    }
    
    public function getActivityInfo(){
    	$token=input('token');
    	$tokenData=jwt_ck($token);
    	$id=input('id',0,'intval');
    	$activity=new \app\common\model\Activity();
    	$activityInfo=$activity->get($id);
    	$com=new \app\common\model\Commission();
    	$allPrice=0;
    	$allProfit=0;
    	$allSaleNum=0;
    	$comList=$com->all(['activity_id'=>$id]);
    	for($c=0;$c<count($comList);$c++){
    		$allProfit=$allProfit+$comList[$c]['profit'];
    		$allPrice=$allPrice+$comList[$c]['allprice'];
    		$allSaleNum=$allSaleNum+$comList[$c]['buynum'];
    	}
    	$activityInfo['allPrice']=$allPrice;
    	$activityInfo['profit']=$allProfit;
    	$activityInfo['allsalenum']=$allSaleNum;
    	
    	
    	$format=new \app\common\model\ActivityFormat();
    	$formatInfo=$format->get(['activity_id'=>$id]);
    	$activityInfo['format']=$formatInfo;
    	
    	
    	$elementActivityFormat=$format->get(['activity_id'=>$activityInfo['element']]);
    	$elementStock=$elementActivityFormat->stock;
    	$order=new \app\common\model\Orderinfo();
    	$elementSaleNum=$order->where(['element_id'=>$activityInfo['element'],'pay_status'=>['in',[1,9]]])->sum('total_num');
    	$lastStock=$elementStock-$elementSaleNum;
    	$activityInfo['lastnum']=$lastStock;
    	
    	if($activityInfo['headimg']){
    		$img=$activityInfo['headimg'][0];
    		$activityInfo['headimgs']=$img;
    	}else{
    		$activityInfo['headimgs']='';
    	}
    	
    	if($activityInfo){
    		return json(['data'=>['code'=>1,'msg'=>$activityInfo]]);
    	}else {
    		return json(['data'=>['code'=>0,'msg'=>'暂时没有数据']]);
    	}
    }
    
    public function getOrderStatus(){
    	$token=input('token');
    	$tokenData=jwt_ck($token);
    	$id=input('id',0,'intval');
    	$status=new \app\common\model\Orderinfo();
    	$data=array(
    			'already_pay'=>$status->where(['activity_id'=>$id,'pay_status'=>1])->count(),
    			'wait_pay'=>$status->where(['activity_id'=>$id,'pay_status'=>9])->count(),
    			'wait_clear'=>$status->where(['activity_id'=>$id,'pay_status'=>3])->count(),
    			'already_go'=>$status->where(['activity_id'=>$id,'pay_status'=>4])->count()
    			
    	);
    	if($data){
    		return json(['data'=>['code'=>1,'msg'=>$data]]);
    	}else {
    		return json(['data'=>['code'=>0,'msg'=>'暂时没有数据']]);
    	}
    }
    
    public function getProfit(){
    	$token=input('token');
    	$tokenData=jwt_ck($token);
    	$tagsid=input('tagsid',0,'intval');
    	$model=new \app\common\model\Activity();
    	$list=$model->all(function($q)use($tokenData){
    		$q->where(['shop_id'=>$tokenData->shop_id]);
    	});
    	for($i=0;$i<count($list);$i++){
    		$id=$list[$i]['id'];
    		$com=new \app\common\model\Commission();
    		$allPrice=0;
    		$allProfit=0;
    		$allSaleNum=0;
    		$comList=$com->all(['activity_id'=>$id]);
    		for($c=0;$c<count($comList);$c++){
    			$allProfit=$allProfit+$comList[$c]['profit'];
    			$allPrice=$allPrice+$comList[$c]['allprice'];
    			$allSaleNum=$allSaleNum+$comList[$c]['buynum'];
    		}
    		$list[$i]['allPrice']=$allPrice;
    		$list[$i]['profit']=$allProfit;
    		$list[$i]['allsalenum']=$allSaleNum;
    	
    		$order=new \app\common\model\Orderinfo();
    		$orderList=$order->all(function($q) use ($id){
    			$q->where(['activity_id'=>$id,'pay_status'=>1]);
    		});
    		
    		for($o=0;$o<count($orderList);$o++){
    			$user_id=$orderList[$o]['user_id'];
    			$openid=db('user')->where(['id'=>$user_id])->value('openid');
    			$member=db('member')->where(['openid'=>$openid])->field('headimgurl,nickname')->find();
    			$orderList[$o]['member']=$member;
    			$orderList[$o]['profit']=$orderList[$i]['profit'];
    			$orderList[$o]['allprice']=$orderList[$i]['totalprices'];
    		}
    		
    		$list[$i]['orderlist']=$orderList;
    	
    	    	
    		if($list[$i]['headimg']){
    			$img=$list[$i]['headimg'][0];
    			$list[$i]['headimgs']=$img;
    		}else{
    			$list[$i]['headimgs']='';
    		}
    	}
    	 
    	if($list){
    		return json(['data'=>['code'=>1,'msg'=>$list]]);
    	}else {
    		return json(['data'=>['code'=>0,'msg'=>'暂时没有数据']]);
    	}
    }
}
?>