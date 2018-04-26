<?php
namespace app\common\model;
use think\Model;
use traits\model\SoftDelete;
class Tags extends Model{
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
			'update_time'     =>'timestamp:Y-m-d',
	];
	
	protected function setCreateTimeAttr($value){
	    return time();
	}
	
			
	protected function setUpdateTimeAttr(){
		return time();
	}
	
	function shop(){
		return $this->hasMany('tags_shop','shop_id','id');
	}
	
	function activity(){
		return $this->hasMany('tags_activity','activity_id','id');
	}
   
}