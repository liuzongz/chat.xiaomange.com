<?php
namespace app\common\model;
use think\Model;
use traits\model\SoftDelete;
use phpDocumentor\Reflection\Types\Null_;
class Activity extends Model{
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
			'notice'=>'array',
			'features'=>'array',
			'headimg'=>'array',
			'card'=>'array',
	       'start_time'=>'timestamp:Y-m-d',
	       'end_time'=>'timestamp:Y-m-d',
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
	
	public function getQrAttr($value,$data){
		$img=getQrCode('http://www.yuanzigo.com/mobile/toc/detail?id='.$data['id'],$data['id'],'qrcode/activity/shopid-'.$data['shop_id'].'/');
		return $img;
	}
	
	public function getQrbAttr($value,$data){
	    $img=getQrCode('http://www.yuanzigo.com/mobile/activity/detail?id='.$data['id'],$data['id'],'qrcode/activityb/shopid-'.$data['shop_id'].'/');
	    return $img;
	}
	
	public function getTocurlAttr($value,$data){
	    return 'http://www.yuanzigo.com/mobile/activity/detail?id='.$data['id'];
	}
	
	public function getToburlAttr($value,$data){
	    return 'http://www.yuanzigo.com/mobile/toc/detail?id='.$data['id'];
	}
	
	public function getQroldAttr($value,$data){
	    return '这是新活动';    
	}
	
	public function getPeriodAttr($value,$data){
	    $model=new \app\common\model\ActivityPeriod();
	    $list=$model->all(function($q)use($data){
	        $q->where(['activity_id'=>$data['id']])->order('xu asc');
	    });
	    return $list;
	}
	
	
	
	public function format(){
		return $this->hasMany('activity_format','activity_id','id');
	}
	
	public function shop(){
		return $this->hasOne('shop','id','shop_id');
	}
	
	public function tags(){
		return $this->hasMany('tags_activity','activity_id','id');
	}
	
}