<?php
namespace app\common\model;
use think\Model;
use traits\model\SoftDelete;
class Activityold extends Model{
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
	    'intro'=>'array',
	];	
	protected function setCreateTimeAttr($value){
	    if($value){
	        return $value;
	    }else{
	        return time();
	    }
	}
	
	public function getQrAttr($value,$data){
	    $img=getQrCode('http://www.yuanzigo.com/wx1801/#/video/'.$data['id'],$data['id'],'qrcode/activity/shopid-'.$data['shop_id'].'/');
	    return $img;
	}
		
	protected function setUpdateTimeAttr(){
		return time();
	}   
    
	public function format(){
	    return $this->hasMany('activity_format','activity_id','id');
	}
	
	public function itinerary(){
	    return $this->hasMany('activity_itinerary','activity_id','id');
	}
	public function shop(){
	    return $this->hasOne('shop','id','shop_id');
	}
}