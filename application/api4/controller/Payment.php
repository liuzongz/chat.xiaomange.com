<?php
namespace app\api4\controller;
use think\Controller;
use think\Request;
use app\common\model\Member;
use app\common\model\User;
use app\common\model\Orderinfo;
use think\Model;
use Firebase\JWT\JWT;
class Payment extends Base{    
    public function prepay(){
        $requestType=$_SERVER['REQUEST_METHOD'];
        if(strtoupper($requestType)=='OPTIONS'){
            
        }else{
            $mOrder=new Orderinfo();
    	    $token=input('token');
    	    $tokenInfo=jwt_ck($token);
    	    
    	    $orderid=input('orderid',0,'intval');
    	    
    	    $orderinfo=Orderinfo::get($orderid);
    	        	    
    	    $activityInfo=db('activity')->where(['id'=>$orderinfo->activity_id])->find();
    	    $res=$mOrder->save(['pay_status'=>9],['id'=>$orderid]);
    	    
    	    $pay = &load_wechat('Pay');
    	    $openid=$tokenInfo->openid;
    	    
    	    $body=$activityInfo['title'];
    	    $out_trade_no=$orderinfo['trade'];
    	    $total_fee=$orderinfo['total_price']*100;
    	    //$total_fee=1*$orderinfo['total_num'];
    	    
    	    
    	    $oprice=$orderinfo['total_price'];
    	    $notify_url='http://www.yuanzigo.com/api4/notice/notify';
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
	}
	
	public function checkRemote(){
	        $orderid=input('orderid');
	        $order=new Orderinfo();
	        $orderinfo=$order->get($orderid);
	        $trade=$orderinfo->trade;
	        $pay = & load_wechat('Pay');
	        // 调用查询接口
	        $result = $pay->queryOrder($trade);
	        // 处理查询结果
	        if($result===FALSE){
	            // 接口失败的处理
	            return json(['data'=>$pay->errMsg,'code'=>1]);
	        }else{
	            // 接口成功的处理
	            if($result['trade_state']=='SUCCESS'){
	                $updata=array(
	                    'transaction'=>$result['transaction_id'],
	                    'pay_price'=>$result['total_fee']/100,
	                    'pay_status'=>1
	                );
	                $res=$order->save($updata,['trade'=>$trade]);
	                if($res){
	                    return json(['data'=>'支付成功','code'=>1]);
	                }else{
	                    return json(['data'=>'支付成功保存数据失败','code'=>1]);
	                }
	            }else{
	                return json(['data'=>'未支付','code'=>1]);
	            }
	        }
	}
	
	
	public function notice(){
	    $pay = &load_wechat('Pay');
	    $notifyInfo = $pay->getNotify();
	    if($notifyInfo===FALSE){
	        // 接口失败的处理
	        echo $pay->errMsg;
	    }else{
	        $trade=$notifyInfo['out_trade_no'];
	        $date=array(
	            'transaction'=>$notifyInfo['transaction_id'],
	            'pay_total'=>$notifyInfo['total_fee'],
	            'pay_status'=>1
	        );
	        $res=db('orderinfo')->where(['trade'=>$trade])->update($date);
	        if($res){
	            return xml(['return_code' => 'SUCCESS', 'return_msg' => 'SAVE DATA SUCCESS']);
	        }
	    }
	}
}