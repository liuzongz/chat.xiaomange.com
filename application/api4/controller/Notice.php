<?php
namespace app\api4\controller;
use think\Controller;
use think\Request;
class Notice extends Controller{
    public function notify(){
        $pay = & load_wechat('Pay');
        $notifyInfo = $pay->getNotify();        
        if($notifyInfo===FALSE){
            // 接口失败的处理
            echo $pay->errMsg;
        }else{
            //支付通知数据获取成功
            if ($notifyInfo['result_code'] == 'SUCCESS' && $notifyInfo['return_code'] == 'SUCCESS') {
                $trade=$notifyInfo['out_trade_no'];
                $txt=ROOT_PATH.'public/Uploads/wxpay/'.date('Y-m-d').'.txt';
                $fp = fopen($txt,'w+');
                fwrite($fp,var_export($notifyInfo,true));
                fclose($fp);
                
                $dates=array(
                    'transaction'=>$notifyInfo['transaction_id'],
                    'pay_price'=>$notifyInfo['total_fee']/100,
                    'pay_status'=>1,
                	'status'=>1	
                );
                $res=db('orderinfo')->where(['trade'=>$trade])->update($dates);
                if($res){
                    $orderinfo=db('orderinfo')->where(['trade'=>$trade])->find();
                    $activityInfo=db('activity')->where(['id'=>$orderinfo['activity_id']])->find();
                    $format=db('activity_format')->where(['id'=>$orderinfo['format_id']])->find();
                    $activity_element_id=$activityInfo['element'];
                  	if($activity_element_id){
                  		$elementActivityInfo=db('activity')->where(['id'=>$activity_element_id])->find();
                  		$shop_element_id=$elementActivityInfo['shop_id'];
                  	}else{
                  		$shop_element_id=0;
                  	}
                  	$commossionData=array(
                  			'order_id'=>$orderinfo['id'],
                  			'activity_id'=>$orderinfo['activity_id'],
                  			'activity_element_id'=>$activity_element_id,
                  			'shop_id'=>$activityInfo['shop_id'],
                  			'shop_element_id'=>$shop_element_id,
                  			'pay_price'=>$orderinfo['total_price'],
                  			'price'=>$format['price'],
                  			'cost'=>$format['cost'],
                  			'buynum'=>$orderinfo['total_num'],
                  			'create_time'=>time(),
                  			'update_time'=>time()
                  	);
                  	
                  	
                    db('commission')->insert($commossionData);
                    
                    $orderStatusData=array(
                    		'order_id'=>$orderinfo['id'],
                    		'activity_id'=>$orderinfo['activity_id'],
                    		'status'=>1,
                    		'create_time'=>time(),
                    		'update_time'=>time()
                    );
                    db('orderinfo_status')->insert($orderStatusData);
                    
                    
                    //通知店家
                    //模板ID：lP48omIlnjhdZdh4YUTvL3To-xbrjR2gTM_kIWncAcw
                    /* 
                        {{first.DATA}}
                                                                用户姓名：{{keyword1.DATA}}
                                                                行程编号：{{keyword2.DATA}}
                                                                行程名称：{{keyword3.DATA}}
                                                                报名时间：{{keyword4.DATA}}
                        {{remark.DATA}}
                     */
                    if($_SERVER['SERVER_NAME']=='chat.xiaomange.com'){
                        $tempId=config('liangzi_temp.baoming');
                    }
                    if($_SERVER['SERVER_NAME']=='www.xiaomange.com'){
                        $tempId=config('bunale_temp.baoming');
                    }
                    if($_SERVER['SERVER_NAME']=='www.yuanzigo.com'){
                        $tempId=config('yuanzi_temp.baoming');
                    }
                    $name=$orderinfo['contact'];
                    $code=$orderinfo['order_code'];
                    $title=$activityInfo['title'];
                    $time=date('Y-m-d H:i:s');
                    $first='有新的订单啦';
                    $remark='请及时与客户进行联系确认哦';
                    $payopenid=$notifyInfo['openid'];
                    $shopID=$orderinfo['shop_id'];
                    $uid=db('shop')->where(['id'=>$shopID])->value('uid');
                    $openid=db('user')->where(['id'=>$uid])->value('openid');
                    $receive=&load_wechat('Receive');
                    $tempData=array(
                        'touser'=>$openid,
                        'template_id'=>$tempId,
                        'url'=>'http://'.$_SERVER['SERVER_NAME'].'/mobile/shop/orderlist?id='.$orderinfo['activity_id'],
                        'topcolor'=>'#FF0000'
                    );
                    $tempNote=array(
                        'first'=>array('value'=>$first),
                        'keyword1'=>array('value'=>$name),
                        'keyword2'=>array('value'=>$code),
                        'keyword3'=>array('value'=>$title),
                        'keyword4'=>array('value'=>$time),
                        'remark'=>array('value'=>$remark)
                    );
                    $tempData['data']=$tempNote;
                    $receive->sendTemplateMessage($tempData);
                    
                    return xml(['return_code' => 'SUCCESS', 'return_msg' => 'DEAL WITH SUCCESS']);
                }
                
            }
        }
    }
}