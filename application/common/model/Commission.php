<?php
namespace app\common\model;
use think\Model;
use traits\model\SoftDelete;
use app\common\model\ActivityFormat;
use app\common\model\ShoppingList;
use app\common\model\OrderTraveler;
use app\common\model\Shop;
class Commission extends Model{
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
	
	protected function getAllpriceAttr($value,$data){
		return $data['price']*$data['buynum'];
	}
	
	protected function getProfitAttr($value,$data){
		return ($data['price']*$data['buynum']-$data['cost']*$data['buynum']);
	}
}