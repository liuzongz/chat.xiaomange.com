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
use app\common\model\Orderinfo;
class Toc extends Base{
	public function getActivityDetail(){
		$id=input('id',0,'intval');
		$model=new Mactivity();
		$info=$model->get($id);
		if(!$info){
		    return json(['data'=>['code'=>0,'msg'=>'该活动不存在']]);
		}
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
		$elementActivityFormat=$format->get(['activity_id'=>$info['element']]);
		if(!$elementActivityFormat){
		    return json(['data'=>['code'=>0,'msg'=>'该活动数据不完整，无法展示']]);
		}
		$elementStock=$elementActivityFormat->stock;
		$order=new \app\common\model\Orderinfo();
		$elementSaleNum=$order->where(['element_id'=>$info['element'],'pay_status'=>['in',[1,9]]])->sum('total_num');
		$lastStock=$elementStock-$elementSaleNum;
		$info['lastnum']=$lastStock;
		$info['salenum']=$order->where(['activity_id'=>$info['id'],'pay_status'=>['in',[1,9]]])->sum('total_num');
		
		return json(['data'=>['code'=>1,'info'=>$info]]);	
		
	}
	
	public function getFillPageData(){
		$id=input('id',0,'intval');
		$model=new Mactivity();
		$info=$model->get($id)->toArray();
		$thisId=$info['id'];
		$format=new ActivityFormat();
		$info['format']=$format->get(['activity_id'=>$thisId]);
		$shopInfo=db('shop')->where(['id'=>$info['shop_id']])->field('shopname,headimg,tel')->find();
		$info['shopinfo']=$shopInfo;
		if($info['headimg']){
			$img=$info['headimg'][0];
			$info['headimgs']=$img;
		}else{
			$info['headimgs']='';
		}
		return json(['data'=>['code'=>1,'info'=>$info]]);
	}
	
	
	public function addOrder(){
	    
		$orderId=input('orderid',0,'intval');
		$token=input('token',0);
		$tokenInfo=jwt_ck($token);
		if(!$tokenInfo->user_id){
			return json(['data'=>['code'=>0,'msg'=>'请重新获取一下token']]);
		}
		$time=time();
		$trade=date('Ymd').substr($time,-6).sprintf('%04s', $tokenInfo->user_id);		
		//组8位数订单ID
		$time8=substr($time,-8);
		$lastOrderCode=substr($time,-8);
		
		$buynum=input('buynum',0,'intval');
		$contact=input('name',0);
		$tel=input('tel',0);
		$wechat=input('wechat');
		$email=input('email');
		$note=input('note');
		$activity_id=input('id',0,'intval');
		
		$formatInfo=db('activity_format')->where(['activity_id'=>$activity_id])->find();
		$activityInfo=db('activity')->where(['id'=>$activity_id])->find();
		
		$element_id=$activityInfo['element'];
		
		$orderinfoData=array(
		        'order_code'=>$lastOrderCode,
				'trade'=>$trade,
				'user_id'=>$tokenInfo->user_id,
				'activity_id'=>$activity_id,
				'total_num'=>$buynum,
				'contact'=>$contact,
				'tel'=>$tel,
		        'wechat'=>$wechat,
				'email'=>$email,
				'note'=>$note,
		        'status'=>9  
		);
		
		//先把自身的支付状态变更，避免计算库存时包含了自己
		if($orderId){
			db('orderinfo')->where(['id'=>$orderId])->setField('pay_status',0);
		}
		
		//计算库存
			//先判断该活动是否为转发的活动
			if($activityInfo['element']){			    
				//如果是转发的，那么获取元活动的总库存
				//先获取原活动信息
				$elementActivityInfo=db('activity')->where(['id'=>$activityInfo['element']])->find();
				$formatInfo=db('activity_format')->where(['activity_id'=>$elementActivityInfo['id']])->find();
				$elementStock=$formatInfo['stock'];
				
				//获取已销售的数量
				$elementSaleNum=db('orderinfo')->where(['element_id'=>$activityInfo['element'],'pay_status'=>['in','1,9']])->sum('total_num');
				$activitySaleNum=db('orderinfo')->where(['activity_id'=>$activityInfo['element'],'pay_status'=>['in','1,9']])->sum('total_num');
				$allSaleNum=$elementSaleNum+$activitySaleNum;
				
				//判断库存是否足够
				$lastNum=$elementStock-$allSaleNum;				
				
				if($lastNum>=$buynum){
					//库存足够，开始写入订单
					$orderinfoData['element_id']=$activityInfo['element'];
					//查询元活动对应的商店
					$orderinfoData['shop_element_id']=$elementActivityInfo['shop_id'];
					$orderinfoData['total_price']=$formatInfo['price']*$buynum;
					$orderinfoData['shop_id']=$activityInfo['shop_id'];					
				}else{
					//库存不足，返回数据给前端
					return json(['data'=>['code'=>0,'msg'=>'库存不足，目前只剩下'.$lastNum.'份','lastnum'=>$lastNum]]);
				}				
			}else{
				//如果不是转发的，获取该活动的总库存
				$formatInfo=db('activity_format')->where(['activity_id'=>$activity_id])->find();
				$elementStock=$formatInfo['stock'];
				//获取已销售的数量
				$activitySaleNum=db('orderinfo')->where(['activity_id'=>$activityInfo['element'],'pay_status'=>['in','1,9']])->sum('total_num');
				//判断库存是否足够
				$lastNum=$elementStock-$activitySaleNum;
				if($lastNum>=$buynum){
					//库存足够，开始写入订单
					$orderinfoData['element_id']=0;
					//查询元活动对应的商店
					$orderinfoData['shop_element_id']=0;
					$orderinfoData['shop_id']=$activityInfo['shop_id'];
					$orderinfoData['total_price']=$formatInfo['price']*$buynum;
				}else{
					//库存不足，返回数据给前端
					return json(['data'=>['code'=>0,'msg'=>'库存不足，目前只剩下'.$lastNum.'份','lastnum'=>$lastNum]]);
				}
			}
		$order=new Orderinfo();
		
		if($orderId){
			$res=$order->save($orderinfoData,['id'=>$orderId]);
			$order_id=$orderId;
		}else{
		    $res=$order->save($orderinfoData);
			$order_id=$order->id;			
			$status=new \app\common\model\OrderinfoStatus();
			$status->save(['order_id'=>$order_id,'activity_id'=>$activity_id,'status'=>9]);
			
		}
		
		if($res){	    
			return json(['data'=>['code'=>1,'msg'=>'订单保存成功，请支付','orderid'=>$order_id]]);
		}else{
			return json(['data'=>['code'=>0,'msg'=>'订单保存失败']]);
		}		
	}
	
	public function payok(){
		$id=input('id',0,'intval');
		$token=input('token',0);
		$tokenData=jwt_ck($token);
		$order=new Orderinfo();
		
		$orderinfo=$order->get(['id'=>$id,'user_id'=>$tokenData->user_id]);

		if(!$orderinfo){
			return json(['data'=>['code'=>0,'msg'=>'没有找到对应的订单']]);
		}
		$activityData=db('activity')->where(['id'=>$orderinfo->activity_id])->find();
		if(!$activityData){
			return json(['data'=>['code'=>0,'msg'=>'没有找到对应的活动数据']]);
		}
		$orderinfo['activity']=$activityData;
		$shopData=db('shop')->where(['id'=>$activityData['shop_id']])->find();
		$title='【'.$activityData['destination'].'】'.$activityData['title'];
		
		$logo_url='https://mmbiz.qpic.cn/mmbiz_jpg/jfner3IPIKliaxiaDeMWqTmHjuaLPurkoBd1bc3m2qgHUe9y6aGCLRuWx8MmytH5ictaaQFmMUVkYuibJPDUnWiaicCw/0';
		$sku=array(
				'quantity'=>1
		);
		$year1=time()+365*24*60*60;
		$date_info=array(
				'type'=>1,
				'begin_timestamp'=>time(),
				'end_timestamp'=>$year1
		);		
		
		$cardInfo=json_decode($activityData['card'],true);
		
		$card_title=$activityData['title'];
		if(mb_strlen($card_title,'utf-8')>9){
		    $card_title=mb_substr($card_title,0,8,'utf-8');
		}
		$card_brand=$shopData['shopname'];
		if(mb_strlen($card_brand,'utf-8')>12){
		    $card_brand=mb_substr($card_brand,0,11,'utf-8');
		}
		$card_logo=$shopData['headimg'];
		//$title='我是标题';
		$baseinfo=array(
		    'logo_url'=>$card_logo,
				'code_type'=>'CODE_TYPE_QRCODE',
		        'brand_name'=>$card_brand,
		        'title'=>$card_title,
				'color'=>'Color010',
				'notice'=>'请将此二维码拿给工作人员扫码',
				'description'=>'门票仅供本人在有效期内使用，每单一票，凭门票入场。请服从现场工作人员管理，依序入场。严禁携带枪支弹药，管制道具等违禁物品，严禁携带打火机等易燃易爆物品，请自觉接受安全检查。',
				'sku'=>$sku,
				'date_info'=>$date_info,
				'bind_openid'=>true,
				'service_phone'=>$shopData['tel'],
				/* 'custom_url_name'=>'立即使用',
				'custom_url'=>'www.yuanzigo.com/mobile/toc/user', */
				'promotion_url_name'=>'产品介绍',
				'promotion_url'=>'www.yuanzigo.com/mobile/toc/detail/id/'.$activityData['id'],
				'can_give_friend'=>true,
				'use_custom_code'=>true
		);
				
		
		$data=array(
				'card_type'=>'SCENIC_TICKET',
				'scenic_ticket'=>array(
						'base_info'=>$baseinfo,
						'ticket_class'=>'全票'
				)
		);
		$data=array(
				'card'=>$data
		);
		
		$card = &  load_wechat('Card');
		
		// 创建微信卡券
		$result = $card->createCard($data);
		
		// 处理执行结果
		if($result===FALSE){
			return json(['data'=>['code'=>0,'msg'=>$card->errMsg]]);
			// 接口失败的处理
		}
		
		$card_id=$result['card_id'];
		//生成卡片代码结束
		
		//开始生成卡片代码
		$code=$orderinfo->order_code;
		//$code='1234567890';
		$data=array(
				'code'=>$code,
				'openid'=>$tokenData->openid
		);
		// 创建SDK实例
		$card = &  load_wechat('Card');
		
		// 通过SDK创建取H5查询JS签名包
		$option = $card ->createAddCardJsPackage($card_id,$data);
		
		// 处理创建结果
		if($option===FALSE){
			// 接口失败的处理
			return json(['data'=>['code'=>0,'msg'=>'卡片生成失败：'.$card->errMsg]]);
		}	
		// 将签名包进行JSON处理
		$card_sign = json_encode($option, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		// 生成微信卡券二维码
		$result = $card->createCardQrcode($card_id, $code, $tokenData->openid);
		 
		// 处理执行结果
		if($result===FALSE){
			return json(['data'=>['code'=>0,'msg'=>'生成二维码失败:'.$card->errMsg]]);
		
		}else{
			$qrimg=$result['show_qrcode_url'];
		}
		
		return json(['data'=>['code'=>1,'title'=>$title,'msg'=>$orderinfo,'card_sign'=>$option,'qrimg'=>$qrimg,'shopinfo'=>$shopData]]);
		
	}
	
	public function getMyAddress(){
	    $token=input('token');
	    $tokenData=jwt_ck($token);
	    $userId=$tokenData->user_id;
	    $orderInfo=db('orderinfo')->where(['user_id'=>$userId])->order('id desc')->find();
	    if($orderInfo){
	        return json(['data'=>['code'=>1,'msg'=>$orderInfo]]);
	    }else{
	        return json(['data'=>['code'=>0,'msg'=>'还没有下过单']]);
	    }
	}
	
}