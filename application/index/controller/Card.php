<?php
namespace app\index\controller;
use think\Request;
use think\Controller;
class Card extends Controller{
	public function index(){
		$logo_url='https://mmbiz.qpic.cn/mmbiz_jpg/jfner3IPIKliaxiaDeMWqTmHjuaLPurkoBd1bc3m2qgHUe9y6aGCLRuWx8MmytH5ictaaQFmMUVkYuibJPDUnWiaicCw/0';
		$sku=array(
				'quantity'=>1
		);
		$date_info=array(
				'type'=>1,
				'begin_timestamp'=>1522771200,
				'end_timestamp'=>1523857599
		);
		$baseinfo=array(
				'logo_url'=>$logo_url,
				'code_type'=>'CODE_TYPE_QRCODE',
				'brand_name'=>'邂逅MeetYou',
				'title'=>'F1赛车VIP看台入场券',
				'color'=>'Color010',
				'notice'=>'请将此二维码拿给工作人员扫码',
				'description'=>'这张票可以多次扫描，<br>不会删除哦\n这张票可以多次扫描，\n不会删除哦',
				'sku'=>$sku,
				'date_info'=>$date_info,
				'bind_openid'=>true,
				'service_phone'=>'17717500923',
				'custom_url_name'=>'立即使用',
				'custom_url'=>'www.xiaomange.com/index/card/useit',
				'promotion_url_name'=>'产品介绍',
				'promotion_url'=>'www.xiaomange.com/index/card/introit',
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
			// 接口失败的处理
			echo $card->errMsg;
		}else{
			p_arr($result);
			// 接口成功的处理
			echo 'ok <br>';
		}
	}
	
	
	public function fa(){
		$card_id='pKK0BwTBIo3cssda8fB8fwiqVTJY';
		$data=array(
				'code'=>'123123123-1',
				'openid'=>''
		);
		// 创建SDK实例
		$card = &  load_wechat('Card');
		
		// 通过SDK创建取H5查询JS签名包
		$option = $card ->createAddCardJsPackage($card_id,$data);
		
		// 处理创建结果
		if($option===FALSE){
			// 接口失败的处理
			echo $card->errMsg;
		}else{
			// 接口成功的处理
		}
		
		$script = &  load_wechat('Script');
		
		// 获取JsApi使用签名，通常这里只需要传 $ur l参数
		$url='http://www.xiaomange.com/index/card/fa';
		$options = $script->getJsSign($url);
		//$options['debug']=true;
		// 处理执行结果
		if($options===FALSE){
			// 接口失败的处理
			echo $script->errMsg;
		}else{
			// 接口成功的处理
		}
		
		
		// 将签名包进行JSON处理
		$card_sign = json_encode($option, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$this->assign('card_sign',$card_sign);
		$this->assign('options',$options);
		return view();
	}
	
	public function xiao(){
		$card_id='pKK0BwfMwOuEW7Nvx3h-uxKPqSY0';
		$code=842782494422;
		$card = &  load_wechat('Card');
		// 微信卡卷code核查
	$result = $card->consumeCardCode($code, $card_id);		
		// 处理执行结果
		if($result===FALSE){
			// 接口失败的处理
			echo $card->errMsg;
		}else{
			// 接口成功的处理
			p_arr($result);
		}
		
		$card = &  load_wechat('Card');		
		// 查询微信卡券详情
		$result = $card->getCardInfo($card_id);		
		// 处理执行结果
		if($result===FALSE){
			// 接口失败的处理
			echo $card->errMsg;
		}else{
			// 接口成功的处理
			p_arr($result);
		}
	}
	
	public function he(){
		$card = &  load_wechat('Card');		
		// 微信卡卷code核查
		$result = $card->checkCardCodeList('pKK0BwRfyy3vnCKJQiOmZeMOhOcs',[]);
		
		// 处理执行结果
		if($result===FALSE){
			// 接口失败的处理
			echo $card->errMsg;
		}else{
			// 接口成功的处理
		}
	}
		
		public function useit(){
			$encrypt_code=input('encrypt_code',0);
			$card_id=input('card_id',0);
			echo '$encrypt_code:';
			echo $encrypt_code;
			echo '<br>$card_id:';
			echo $card_id;
			echo '<br>';
			
			$card = &  load_wechat('Card');
			$result = $card->decryptCardCode($encrypt_code);
			
			// 处理执行结果
			if($result===FALSE){
				// 接口失败的处理
				echo $card->errMsg;
			}else{
				// 接口成功的处理
				$code=$result['code'];
				$new_code='123123123-2';
				$result = $card->updateCardCode($code, $card_id, $new_code);
				
				// 处理执行结果
				if($result===FALSE){
					// 接口失败的处理
					echo $card->errMsg;
				}else{
					// 接口成功的处理
					p_arr($result);
				}
				
				
			}
		}
		
		public function introit(){
			$encrypt_code=input('encrypt_code',0);
			$card_id=input('card_id',0);
			echo '$$encrypt_code:';
			echo $encrypt_code;
			echo '<br>$card_id:';
			echo $card_id;
		}
}