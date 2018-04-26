<?php
namespace app\common\model;
use think\Model;
use traits\model\SoftDelete;
use app\common\model\ActivityFormat;
use app\common\model\ShoppingList;
use app\common\model\OrderTraveler;
use app\common\model\Shop;
class Orderinfo extends Model{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
	protected $auto = [];
	protected $insert = ['create_time','update_time'];
	protected $update = ['update_time'];
	protected $createTime = 'create_time';
	protected $updateTime = 'update_time';
	protected $autoWriteTimestamp = true;
	protected $type = [
			'create_time'    =>'timestamp:Y-m-d H:i:s',
			'update_time'     =>'timestamp:Y-m-d H:i:s',
	        'shopping_list'=>'array'   
	];
	
	
	protected function setCreateTimeAttr($value){
	    if($value){
	        return $value;
	    }else{
	        return time();
	    }
	}
		
	protected function setUpdateTimeAttr(){
		return time();
	}   
    public function activity(){
        return $this->hasOne('activity','id','activity_id');
    }
	public function format(){
	    return $this->hasMany('activity_format','id','activity_id');
	}	
	
	public function userinfo(){
	    return $this->hasOne('user','id','user_id');
	}
	public function getstatustxtAttr($value,$data){
	    $ay=['9'=>'待支付','1'=>'已付款','2'=>'已完成','3'=>'订单取消','0'=>'无效订单'];
	    $lasttime=$data['create_time']+1800;
	    if($data['pay_status']==9){
	        if(time()>$lasttime){
	            return '已过期';
	        }else{
	            return '待支付';
	        }
	    }else{
	        return $ay[$data['pay_status']];
	    }	    
	    
	}
	
	public function getOvertimeAttr($value,$data){
	    $ct=$data['create_time'];
	    $ot=$ct+1800;
	    $nt=time();
	    if($nt>$ot){
	        return 0;
	    }else{
	        return $ot-$nt;
	    }
	}
	
	
	protected function getProfitAttr($value,$data){
		$com=new \app\common\model\Commission();
		$cominfo=$com->get(['order_id'=>$data['id']]);
		return $cominfo->profit;
	}
	
	protected function getTotalpricesAttr($value,$data){
		$com=new \app\common\model\Commission();
		$cominfo=$com->get(['order_id'=>$data['id']]);
		return $cominfo->allprice;
	}
	
	public function getShopinfoAttr($value,$data){
		$shop=new Shop();
		return $shop->get($data['shop_id']);
	}
	
}