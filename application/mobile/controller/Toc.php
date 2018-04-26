<?php
namespace app\mobile\controller;
use think\Controller;
use think\Request;
use app\common\model\Orderinfo;
use think\Model;
class Toc extends Controller{
    public function _initialize(){        
    	$this->request=Request::instance();
    	$this->weixinInfo=cookie('weixinInfo');
    	if(!$this->weixinInfo){
    	    $url=url('Wechat/getWeixinInfo').'?jump='.$this->request->url(true);
    	    $this->redirect($url);
    	}
    	$this->assign('nowtime',time());
    }
    
    public function deluser(){
        //获取用户
        $userinfo=db('user')->where(['openid'=>$this->weixinInfo['openid']])->find();
        db('shop')->where(['id'=>$userinfo['rule_id']])->delete();
        db('user')->where(['openid'=>$this->weixinInfo['openid']])->delete();
        cookie('weixinInfo',null);
        echo '用户数据删除了，请关闭页面，开始新的测试吧';
    }
    public function index(){
        
    }
    
    public function detail(){
    	$id=input('id',0,'intval');
    	$model=new \app\common\model\Activity();
    	$info=$model->get($id);
    	if(!$info){
    	    $this->redirect('err/errpage',['msg'=>'该活动不存在']);
    	}
    	$info['period']=$info['period'];
    	$info=$info->toArray();
    	$thisId=$info['id'];
    	$tagsAc=new \app\common\model\TagsActivity();
    	$tags=$tagsAc->all(function($q) use ($thisId){
    	    $q->where(['activity_id'=>$thisId]);
    	});
    	    $tagsAy=[];
    	    for($t=0;$t<count($tags);$t++){
    	        $tagsAy[$t]=$tags[$t]['tagsname'];
    	    }
    	    $format=new \app\common\model\ActivityFormat();    	    
    	    $formatInfo=$format->all(function($q)use($thisId){
    	        $q->where(['activity_id'=>$thisId])->order('xu asc');
    	    });
    	    $shopInfo=db('shop')->where(['id'=>$info['shop_id']])->field('shopname,headimg,tel')->find();
    	    
    	    $script = &  load_wechat('Script');
    	    $url=$this->request->url(true);
    	    $options = $script->getJsSign($url);
    	    
    	    // 处理执行结果
    	    if($options===FALSE){
    	        // 接口失败的处理
    	        echo $script->errMsg;
    	    }else{
    	        $options['debug']=true;
    	    }
    	    
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
    	    $buyer=getActivityBuyer($id);
    	    $assign=array(
    	        'info'=>$info,
    	        'format'=>$formatInfo,
    	        'tags'=>$tagsAy,
    	        'shop'=>$shopInfo,
    	        'buyer'=>$buyer,
    	        'buynum'=>count($buyer),
    	        'options'=>$options,
    	        'shareData'=>$shareData
    	    );
    	    
    	    $this->assign($assign);
    	    return view();
    }
    public function fill(){
        $id=input('id',0,'intval');
        $format_id=input('format',0,'intval');
        $period_id=input('period',0,'intval');
        $num=input('num',1,'intval');
        
        $activity=new \app\common\model\Activity();
        $format=new \app\common\model\ActivityFormat();
        $period=new \app\common\model\ActivityPeriod();
        $shop=new \app\common\model\Shop();
        
        $formatInfo=$format->get($format_id)->toArray();
        $periodInfo=$period->get($period_id)->toArray();
        $activityInfo=$activity->get($id)->toArray();
        $shopInfo=$shop->get($activityInfo['shop_id'])->toArray();
         
        $assign=array(
            'activity'=>$activityInfo,
            'format'=>$formatInfo,
            'period'=>$periodInfo,
            'shop'=>$shopInfo,
            'num'=>$num,
            'id'=>$id,
            'format_id'=>$format_id,
            'period_id'=>$period_id
        );
        $this->assign($assign);
        
        
    	return view();
    }
    public function payok(){
        $id=input('id',0,'intval');        
        $order=new \app\common\model\Orderinfo(); 
        $openid=$this->weixinInfo['openid'];
        $orderinfo=$order->get(['id'=>$id,'openid'=>$openid]);
        
        if(!$orderinfo){
            echo '没有找到对应的订单';die;
        }
        $activityData=db('activity')->where(['id'=>$orderinfo->activity_id])->find();
        if(!$activityData){
            echo '没有找到对应的活动数据';die;            
        }
        $orderinfo['activity']=$activityData;
        
        $period=new \app\common\model\ActivityPeriod();
        $periodInfo=$period->get($orderinfo->period_id);
        
        $shopData=db('shop')->where(['id'=>$activityData['shop_id']])->find();
        $title='【'.$activityData['destination'].'】'.$activityData['title'];
        
        $logo_url=$shopData['headimg'];
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
            'promotion_url_name'=>'产品介绍',
            'promotion_url'=>'www.xiaomange.com/mobile/card/detail/id/'.$activityData['id'],
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
            echo $card->errMsg;die;
        }
        
        $card_id=$result['card_id'];
        //生成卡片代码结束
        
        //开始生成卡片代码
        $code=$orderinfo->order_code;
        //$code='1234567890';
        $data=array(
            'code'=>$code,
            'openid'=>$openid
        );
        // 创建SDK实例
        $card = &  load_wechat('Card');
        
        // 通过SDK创建取H5查询JS签名包
        $option = $card ->createAddCardJsPackage($card_id,$data);
        
        // 处理创建结果
        if($option===FALSE){
            echo '卡片生成失败：'.$card->errMsg;die;            
        }
        // 将签名包进行JSON处理
        //$card_sign = json_encode($option, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        // 生成微信卡券二维码
        $result = $card->createCardQrcode($card_id, $code, $openid);
        
        // 处理执行结果
        if($result===FALSE){
            echo '生成二维码失败:'.$card->errMsg;die;            
        }else{
            $qrimg=$result['show_qrcode_url'];
        }
        
        //生成二维码
        $qrimg2=getQrCode($code);
        
        $assign=array(
            'title'=>$title,
            'info'=>$orderinfo,
            'card_sign'=>$option,
            'qrimg'=>$qrimg2,
            'shop'=>$shopData,
            'period'=>$periodInfo
            
        );
        $this->assign($assign);
    	return view();
    }
    
    public function fa(){
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
    	$shopData=db('shop')->where(['id'=>$activityData['shop_id']])->find();
    	$title='【'.$activityData['destination'].'】【'.$shopData['shopname'].'】专享'.$activityData['title'];
    	
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
    	$title=$activityData['title'];
    	$title='自驾穿越沙漠特卖';
    	$brand_name=$shopData['shopname'];
    	
    	$baseinfo=array(
    			'logo_url'=>$logo_url,
    			'code_type'=>'CODE_TYPE_QRCODE',
    			'brand_name'=>'原子出发',
    			'title'=>$title,
    			'color'=>'Color010',
    			'notice'=>'请将此二维码拿给工作人员扫码',
    			'description'=>'这张票可以多次扫描，<br>不会删除哦\n这张票可以多次扫描，\n不会删除哦',
    			'sku'=>$sku,
    			'date_info'=>$date_info,
    			'bind_openid'=>true,
    			'service_phone'=>'17717500923',
    			'custom_url_name'=>'立即使用',
    			'custom_url'=>'www.xiaomange.com/mobile/toc/user',
    			'promotion_url_name'=>'产品介绍',
    			'promotion_url'=>'www.xiaomange.com/mobile/toc/detail/id/'.$activityData['id'],
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
    	$code=$orderinfo->trade;
    	$code='1234567890';
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
    	
    	// 生成微信卡券二维码
    	$result = $card->createCardQrcode($card_id, $code, $tokenData->openid);
    	
    	// 处理执行结果
    	if($result===FALSE){
    		return json(['data'=>['code'=>0,'msg'=>'生成二维码失败:'.$card->errMsg]]);
    		
    	}else{
    		$qrimg=$result['show_qrcode_url'];
    	}
    	// 将签名包进行JSON处理
    	$card_sign = json_encode($option, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    	$this->assign('card_sign',$card_sign);
    	
    	return view();
    }
    
    public function ckStock(){
        $format_id=input('format',0,'intval');
        $period_id=input('period',0,'intval');
        $buynum=input('num',1,'intval');
        $lastStock=getStockByFormatAndPeriod($format_id, $period_id);
        if(!$lastStock){
            return result(0,'该规格下的团期已经没有库存了');
        }
        if($lastStock<$buynum){
            return result(0,'该规格下的该团期库存不足，只剩下'.$lastStock.'份');
        }else{
            return result(1,'还有足够库存');
        }
    }
    
    public function addOrder(){
        $format_id=input('format',0,'intval');
        $period_id=input('period',0,'intval');
        $buynum=input('buynum',1,'intval');
        $id=input('id',0,'intval');
        $lastStock=getStockByFormatAndPeriod($format_id, $period_id);
        if(!$lastStock){
            return result(0,'该规格下的团期已经没有库存了');
        }
        if($lastStock<$buynum){
            return result(0,'该规格下的该团期库存不足，只剩下'.$lastStock.'份');
        }
        
        $contact=input('name',0);
        $tel=input('tel',0);
        $wechat=input('wechat');
        $email=input('email');
        $note=input('note');
        $activity_id=input('id',0,'intval');        
        $orderId=input('orderid',0,'intval');
        
        $time=time();
        $trade=date('Ymd').substr($time,-6);
        $trade=date('Ymd').$time;
        
        //组8位数订单ID
        $time8=substr($time,-8);
        $lastOrderCode=substr($time,-8);        
        
        //获取金额
        $formatInfo=getFormatInfo($format_id,0);
        if(!$formatInfo){
            return result(0,'规格获取失败');
        }
        $total_price=$buynum*$formatInfo['price'];
        
        $orderData=array(
            'openid'=>$this->weixinInfo['openid'],
            'order_code'=>$lastOrderCode,
            'trade'=>$trade,
            'activity_id'=>$activity_id,
            'format_id'=>$format_id,
            'period_id'=>$period_id,
            'total_num'=>$buynum,
            'total_price'=>$total_price,
            'contact'=>$contact,
            'tel'=>$tel,
            'wechat'=>$wechat,
            'email'=>$email,
            'note'=>$note,
            'status'=>9
        );
        
        $order=new \app\common\model\Orderinfo();
        if($orderId){
             $res=$order->save($orderData,['id'=>$orderId]);
             if($res){
                 return result(1,$orderId);
             }else{
                 return result(0,'订单保存失败');
             }
        }
        
        //查询活动对应的商店
        $activity=new \app\common\model\Activity();
        $activityInfo=$activity->get($id)->toArray();
        $orderData['shop_id']=$activityInfo['shop_id'];
        $orderData['element_id']=$activityInfo['element'];
        if($activityInfo['element']){
            $elementInfo=$activity->get($activityInfo['element'])->toArray();
            $orderData['shop_element_id']=$elementInfo['shop_id'];
        }else{
            $orderData['shop_element_id']=$activityInfo['shop_id'];
        }
        
        $res=$order->save($orderData);
        if($res){
            $orderId=$order->id;
            return result(1,$orderId);
        }else{
            return result(0,'订单保存失败');
        }
        
    }
    
    public function getMyAddress(){
        $order=new \app\common\model\Orderinfo();
        $openid=$this->weixinInfo['openid'];
        $orderInfo=$order->get(function($q)use($openid){
            $q->where(['openid'=>$openid])->order('update_time desc');
        });
        
        if($orderInfo){            
            return result(1,$orderInfo->toArray());
        }else{
            return result(0,'新用户');
        }
    }
    
    public function jssign(){
        //JSSDK签名
        $fromurl=input('fromurl');
        $debug=input('debug',0);
        $script = &  load_wechat('Script');
        $options = $script->getJsSign($fromurl);
        if($debug){
            $options['debug']=true;
        }
        //$options['debug']=true;
        return json(['data'=>['code'=>1,'msg'=>$options]]);
    }
    public function jssignAndShare(){
        //JSSDK签名
        $fromurl=input('fromurl');
        $debug=input('debug',0);
        $script = &  load_wechat('Script');
        $options = $script->getJsSign($fromurl);
        if($debug){
            $options['debug']=true;
        }
        //$options['debug']=true;
        
        $id=input('id',0,'intval');
        
        $activity=new \app\common\model\Activity();
        $info=$activity->get($id)->toArray();
        if($info['element']==0){
            $shopid=db('user')->where(['openid'=>$this->weixinInfo['openid']])->value('rule_id');
        }else{
            $shopid=$info['shop_id'];
        }
        $shopInfo=db('shop')->where(['id'=>$shopid])->field('shopname,headimg,tel')->find();
        
        $info['shopinfo']=$shopInfo;
        if($info['headimg']){
            $img=$info['headimg'][0];
            $info['headimgs']=$img;
        }else{
            $info['headimgs']='';
        }
        $title="【".$shopInfo['shopname']."专享 | ".$info['destination']."】".$info['title'];
        
        $shopid=db('user')->where(['openid'=>$this->weixinInfo['openid']])->value('rule_id');
        
        $shareId=db('activity')->where(['shop_id'=>$shopid,'element'=>$id])->value('id');
        if(!$shareId){
            $shareId=$id;
        }
        $shareData=array(
            'title'=>$title,
            'logo'=>$info['headimgs'],
            'url'=>'http://'.$_SERVER['SERVER_NAME'].'/mobile/toc/detail.html?id='.$shareId,
            'desc'=>$info['subtitle']
        );
        
        return json(['data'=>['code'=>1,'msg'=>$options,'share'=>$shareData]]);
    }
    
    public function prepay(){
            $mOrder=new \app\common\model\Orderinfo();                        
            $orderid=input('orderId',0,'intval');
            if(!$orderid){
                $orderid=input('orderid',0,'intval');
            }
            
            $orderinfo=$mOrder->get($orderid);
            $activityInfo=db('activity')->where(['id'=>$orderinfo->activity_id])->find();
            $res=$mOrder->save(['pay_status'=>9],['id'=>$orderid]);
            
            $pay = &load_wechat('Pay');
            $openid=$this->weixinInfo['openid'];
            
            $body=$activityInfo['title'];
            $out_trade_no=$orderinfo['trade'];
            $total_fee=$orderinfo['total_price']*100;
            $total_fee=1*$orderinfo['total_num'];
            
            
            $notify_url='http://'.$_SERVER['SERVER_NAME'].'/api4/notice/notify';
            //echo $openid.'=>'.$body.'=>'.$out_trade_no.'=>'.$total_fee.'=>'.$notify_url;
            $result = $pay->getPrepayId($openid, $body, $out_trade_no, $total_fee, $notify_url, $trade_type = "JSAPI");
            if($result){
                $options = $pay->createMchPay($result);
                $options['success']=1;
                $options['price']=$orderinfo['total_price'];
                $options['prepayId']=$orderinfo['trade'];
                return json(['data'=>['code'=>1,'msg'=>$options]]);
            }else{
                return json(['data'=>['code'=>0,'msg'=>$result]]);
            }
    }
    
    public function cardApi(){
        $id=input('id',0,'intval');
        $order=new \app\common\model\Orderinfo();        
        $openid=$this->weixinInfo['openid'];
        $orderinfo=$order->get(['id'=>$id,'openid'=>$openid]);        
        if(!$orderinfo){
            return json(['data'=>['code'=>0,'msg'=>'没有找到对应的订单']]);
        }
        if($orderinfo->order_code_get){
            return json(['data'=>['code'=>0,'msg'=>'这个订单的卡券您之前已经领取了']]);
        }
        $activityData=db('activity')->where(['id'=>$orderinfo->activity_id])->find();
        if(!$activityData){
            return json(['data'=>['code'=>0,'msg'=>'没有找到对应的活动数据']]);
        }
        $orderinfo['activity']=$activityData;
        
        $period=new \app\common\model\ActivityPeriod();
        $periodInfo=$period->get($orderinfo->period_id);
        
        $shopData=db('shop')->where(['id'=>$activityData['shop_id']])->find();
        $title='【'.$activityData['destination'].'】'.$activityData['title'];
        
        $logo_url=$shopData['headimg'];
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
            'promotion_url_name'=>'产品介绍',
            'promotion_url'=>$_SERVER['SERVER_NAME'].'/mobile/card/detail/id/'.$activityData['id'],
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
        }
        
        $card_id=$result['card_id'];
        //生成卡片代码结束
        
        //开始生成卡片代码
        $code=$orderinfo->order_code;
        //$code='1234567890';
        $data=array(
            'code'=>$code,
            'openid'=>$openid
        );
        // 创建SDK实例
        $card = &  load_wechat('Card');
        
        // 通过SDK创建取H5查询JS签名包
        $option = $card ->createAddCardJsPackage($card_id,$data);
        //$option['success']='getcardok()';
        
        // 处理创建结果
        if($option===FALSE){
            return json(['data'=>['code'=>0,'msg'=>'卡片生成失败：'.$card->errMsg]]);
        }
        
        
        return json(['data'=>['code'=>1,'card_sign'=>$option,'shop_id'=>$shopData['id']]]);
    }
    public function setGetCard(){
        $id=input('id',0,'intval');
        db('orderinfo')->where(['id'=>$id])->setField('order_code_get',1);
    }
    public function tomyorder(){
        $this->redirect('myorder');
    }
    
    public function myorder(){
        $shopid=input('shopid',0,'intval');
        if(!$shopid){
           // $this->error('您访问的页面不存在');
           echo '缺少店铺ID';die;
        }
        
        $openid=$this->weixinInfo['openid'];
        $status=input('status',1,'intval');
        $order=new \app\common\model\Orderinfo();
        $list=$order->all(function($q)use($openid,$status,$shopid){
           $q->where(['openid'=>$openid,'status'=>$status,'shop_id'=>$shopid])->order('create_time desc'); 
        });
        for($i=0;$i<count($list);$i++){
            $model=new \app\common\model\Activity();
            $info=$model->get($list[$i]['activity_id']);
            $list[$i]['activit']=$info;
            $model=new \app\common\model\ActivityPeriod();
            $info=$model->get($list[$i]['period_id']);
            $list[$i]['periodname']=$info->name;
            $model=new \app\common\model\Shop();
            $info=$model->get($list[$i]['shop_id']);
            $list[$i]['shop']=$info;
            $model=new \app\common\model\ActivityFormat();
            $info=$model->get($list[$i]['format_id']);
            $list[$i]['format']=$info;
        }
        
        $count=array(
            's9'=>$order->where(['openid'=>$openid,'status'=>9,'shop_id'=>$shopid])->count(),
            's1'=>$order->where(['openid'=>$openid,'status'=>1,'shop_id'=>$shopid])->count(),
            's2'=>$order->where(['openid'=>$openid,'status'=>2,'shop_id'=>$shopid])->count(),
            's3'=>$order->where(['openid'=>$openid,'status'=>3,'shop_id'=>$shopid])->count()
        );
        $this->assign(['list'=>$list,'status'=>$status,'count'=>$count,'shop_id'=>$shopid]);
        return view();
    }
}