<?php 
namespace app\api4\controller;
use think\Model;
use think\helper\Time;
use think\Controller;
use Firebase\JWT\JWT;
use app\common\model\Activity as Mactivity;
use app\common\model\TagsActivity;
use app\common\model\TagsShop;
use app\common\model\ActivityFormat;
class Activity extends Base{
    public function getActivityList(){
        $token=input('token');
        $TokenData=jwt_ck($token);
        $tagsid=input('tagsid',0,'intval');
        $keyword=input('keyword',0);
        
        
    }
	public function getAllActivityList(){
		$token=input('token');
		$userinfo=jwt_ck($token);
		$tagsid=input('tagsid',0,'intval');
		$keyword=input('keyword',0);
		$model=new Mactivity();
		if($tagsid){
		    if($keyword){
		        $list=Mactivity::hasWhere('tags',['tags_id'=>$tagsid])->where(['element'=>0,'title'=>['like','%'.$keyword.'%']])->paginate(10);
		    }else{
		        $list=Mactivity::hasWhere('tags',['tags_id'=>$tagsid])->where(['element'=>0])->order('update_time desc')->limit(100)->paginate(10);
		    }
		}else{
		    if($keyword){
		        $list=$model->all(function($q)use($userinfo,$keyword){
		            $q->where(['element'=>0,'title'=>['like','%'.$keyword.'%']])->order('update_time desc')->paginate(10);
		        });
		    }else{
		        $list=$model->all(function($q)use($userinfo){
		            $q->where(['element'=>0])->order('update_time desc')->paginate(10);
		        });
		    }
			
		}
		
		for($i=0;$i<count($list);$i++){
			$thisId=$list[$i]['id'];
			//先判断这个活动是否已经有添加过了
			$ck=db('activity')->where(['element'=>$thisId,'shop_id'=>$userinfo->shop_id])->find();
			//p_arr($ck);
			if(!$ck){
				$list[$i]['ck']=0;
			}else{
				$list[$i]['ck']=1;
			}
			$tagsAc=new TagsActivity();			
			$tags=$tagsAc->all(function($q) use ($thisId){
				$q->where(['activity_id'=>$thisId]);
			});
			$tagsAy=[];
			for($t=0;$t<count($tags);$t++){
				$tagsAy[$t]=$tags[$t]['tagsname'];
			}
			$list[$i]['tags']=$tagsAy;
			$format=new ActivityFormat();
			$list[$i]['format']=$format->get(['activity_id'=>$list[$i]['id']]);
			if($list[$i]['headimg']){
				$img=$list[$i]['headimg'][0];
				$list[$i]['headimgs']=$img;
			}else{
				$list[$i]['headimgs']='';
			}			
			$shopname=db('shop')->where(['id'=>$list[$i]['shop_id']])->value('shopname');
			$list[$i]['shopname']=$shopname;
			
		}
		if($list){
			return json(['data'=>['code'=>1,'list'=>$list]]);
		}else{
			return json(['data'=>['code'=>0,'list'=>[]]]);
		}
		
	}
	
	public function getActivityDetail(){
		$id=input('id',0,'intval');
		$model=new Mactivity();
		$info=$model->get($id)->toArray();
		$thisId=$info['id'];
		$tagsAc=new TagsActivity();
		$tags=$tagsAc->all(function($q) use ($thisId){
			$q->where(['activity_id'=>$thisId]);
		});
		$tagsAy=[];
		for($t=0;$t<count($tags);$t++){
			$tagsAy[$t]=$tags[$t]['tagsname'];
		}
		$info['tags']=$tagsAy;
		$format=new ActivityFormat();
		$info['format']=$format->get(['activity_id'=>$thisId]);
		$shopInfo=db('shop')->where(['id'=>$info['shop_id']])->field('shopname,headimg,tel')->find();
		$info['shopinfo']=$shopInfo;
		
		/* $elementActivityFormat=$format->get(['activity_id'=>$info['element']]);
		$elementStock=$elementActivityFormat->stock;
		$order=new \app\common\model\Orderinfo();
		$elementSaleNum=$order->where(['element_id'=>$info['element'],'pay_status'=>1])->sum('total_num');
		$lastStock=$elementStock-$elementSaleNum;
		$info['lastnum']=$lastStock;
		$info['salenum']=$elementSaleNum; */
		
		
		return json(['data'=>['code'=>1,'info'=>$info]]);
		
	}
	
	public function getActivityPreview(){
		$token=input('token');
		$userinfo=jwt_ck($token);
		$id=input('id',0,'intval');
		$model=new Mactivity();
		$info=$model->get($id)->toArray();
		$thisId=$info['id'];
		$tagsAc=new TagsActivity();
		$tags=$tagsAc->all(function($q) use ($thisId){
			$q->where(['activity_id'=>$thisId]);
		});
		$tagsAy=[];
		for($t=0;$t<count($tags);$t++){
			$tagsAy[$t]=$tags[$t]['tagsname'];
		}
		$info['tags']=$tagsAy;
		$format=new ActivityFormat();
		$info['format']=$format->get(['activity_id'=>$thisId]);
		$shopInfo=db('shop')->where(['id'=>$info['shop_id']])->field('shopname,headimg,tel')->find();
		$info['shopinfo']=$shopInfo;
		
		$elementActivityFormat=$format->get(['activity_id'=>$id]);
		$elementStock=$elementActivityFormat->stock;
		$info['stock']=$elementStock;
		$order=new \app\common\model\Orderinfo();
		$elementSaleNum=$order->where(['element_id'=>$info['element'],'pay_status'=>1])->sum('total_num');
		$lastStock=$elementStock-$elementSaleNum;
		$info['lastnum']=$lastStock;
		$info['salenum']=$elementSaleNum;
		
		return json(['data'=>['code'=>1,'info'=>$info]]);
	
	}
	
	public function addActivity(){
		$token=input('token');
		$userinfo=jwt_ck($token);
		$id=input('id',0,'intval');
		
		//复制原有活动
		$token=input('token');
		$userinfo=jwt_ck($token);
		//获取要添加的人的的shop_id
		$shop_id=$userinfo->shop_id;
		if(!$shop_id){
			return json(['data'=>['code'=>0,'msg'=>'请先注册成商家']]);
		}
		$id=input('id',0,'intval');
		$model=new Mactivity();
		$info=$model->get(function($q)use($id){
			$q->where(['id'=>$id])->field('title,subtitle,headimg,destination,execution_time,intro,features,notice,card');
		})->toArray();
		$info['shop_id']=$shop_id;
		$info['element']=$id;
		
		//插入前先判断是否添加了
		$ck=db('activity')->where(['shop_id'=>$shop_id,'element'=>$id])->find();
		if($ck){
			return json(['data'=>['code'=>1,'newid'=>$ck['id'],'msg'=>'保存成功']]);
		}
		//插入新的活动数据，并获取新活动的ID
		$model->save($info);
		$newActivityId=$model->id;
		if(!$newActivityId){
			return json(['data'=>['code'=>0,'msg'=>'保存新活动的基本信息失败']]);
		}
		//查询老活动的Tags数据，并把新活动ID重新组合进去，最后再保存新活动的Tags
		$tagsAc=new TagsActivity();
		$tags=$tagsAc->all(function($q) use ($id){
			$q->where(['activity_id'=>$id])->field('tags_id');
		});
		$newTags=[];
		for($i=0;$i<count($tags);$i++){
			$newTags[$i]['tags_id']=$tags[$i]['tags_id'];
			$newTags[$i]['activity_id']=$newActivityId;
		}
		$res=$tagsAc->saveAll($newTags);
		if(!$res){
		    db('activity')->where(['id'=>$newActivityId])->delete();
		    
			return json(['data'=>['code'=>0,'msg'=>'保存新活动的Tags失败']]);
		}
		
		//查询老活动的Format数据，并把新活动的ID重新组合进去，最后再保存新活动的Format
		$format=new ActivityFormat();
		$oldFormatData=$format->all(function($q) use ($id){
			$q->where(['activity_id'=>$id])->field('name,price,cost,stock,intro');
		});
		$newFormatData=[];
		for($i=0;$i<count($oldFormatData);$i++){
			$newFormatData[$i]['name']=$oldFormatData[$i]['name'];
			$newFormatData[$i]['price']=$oldFormatData[$i]['price'];
			$newFormatData[$i]['cost']=$oldFormatData[$i]['cost'];
			$newFormatData[$i]['stock']=$oldFormatData[$i]['stock'];
			$newFormatData[$i]['intro']=$oldFormatData[$i]['intro'];
			$newFormatData[$i]['activity_id']=$newActivityId;
		}
		
		$res=$format->saveAll($newFormatData);
		if(!$res){
			return json(['data'=>['code'=>0,'msg'=>'保存新活动的Format失败']]);
		}
		return json(['data'=>['code'=>1,'newid'=>$newActivityId,'msg'=>'保存成功']]);
	}
	
	public function getShareInfo(){
		$token=input('token');
		$tokenData=jwt_ck($token);
		$id=input('id',0,'intval');
		
		$activity=new \app\common\model\Activity();
		$info=$activity->get($id)->toArray();
		
		$shopInfo=db('shop')->where(['id'=>$info['shop_id']])->field('shopname,headimg,tel')->find();
		
		$info['shopinfo']=$shopInfo;
		if($info['headimg']){
			$img=$info['headimg'][0];
			$info['headimgs']=$img;
		}else{
			$info['headimgs']='';
		}
		$title="【".$shopInfo['shopname']."专享 | ".$info['destination']."】".$info['title'];

		$shareData=array(
				'title'=>$title,
				'logo'=>$info['headimgs'],
				'url'=>'http://www.yuanzigo.com/mobile/toc/detail.html?id='.$id,
				'desc'=>$info['subtitle']
		);
		if($info){
			return json(['data'=>['code'=>1,'msg'=>$shareData]]);
		}else{
			return json(['data'=>['code'=>0,'msg'=>'没找到活动信息']]);
		}
	}
}
?>