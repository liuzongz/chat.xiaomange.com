<?php
namespace app\common\model;
use think\Model;
use traits\model\SoftDelete;
class ShoppingList extends Model{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
	protected $auto = [];
	protected $insert = ['create_time','update_time'];
	protected $update = ['update_time'];
	protected $createTime = 'create_time';
	protected $updateTime = 'update_time';
	protected $autoWriteTimestamp = true;
	protected $type = [
			'create_time'    =>'timestamp:Y-m-d',
			'update_time'     =>'timestamp:Y-m-d'
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
    
	public function format(){
	    return $this->hasOne('activity_format','id','format_id');
	}
	
	public function orderinfo(){
	    return $this->hasOne('orderinfo','id','orderinfo_id');
	}
}