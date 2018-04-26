<?php
namespace app\admin\controller;
use think\Model;
use think\helper\Time;
use app\common\model\Activity as Mactivity;
use app\common\model\ActivityFormat;
use app\common\model\ActivityItinerary;
use app\common\model\Orderinfo;
use think\Controller;
use PHPExcel_IOFactory;
use PHPExcel;

class Orders extends Base{
    public function test(){
        $oldOrder=new \app\common\model\Orderinfo03();
        $oldUser=new \app\common\model\User03();
        $oldMember=new \app\common\model\Member03();
        $oldShop=new \app\common\model\Shop03();
        $orderinfo=$oldOrder->get(189);
        $orderinfo=$orderinfo->toArray();
        p_arr($orderinfo);
        $shopInfo=$oldShop->get($orderinfo['shop_id'])->toArray();
        p_arr($shopInfo);
        $shopUserInfo=$oldUser->get($shopInfo['uid'])->toArray();
        p_arr($shopUserInfo);
        echo strtotime('2018-04-11 19:26:35');
        
    }
    public function index(){
        $orders=new Orderinfo();
        $s=input('s',1,'intval');        
        $map=['pay_status'=>$s];
        if($s==10){
            $map=['create_time'=>['lt',time()-1800],'pay_status'=>9];
        }
        if($s==9){
            $map['create_time']=['gt',time()-1800];
        }
        $list=$orders->where($map)->order('update_time desc')->paginate(10);
        
        $assign=array(
            'list'=>$list,
            'status'=>$s
        );
        $this->assign($assign);
        return view();
    }
    
    public function add(){
        $id=input('id',0,'intval');
        if($id){
            $activity=new Mactivity();
            $format=new ActivityFormat();
            $itinerary=new ActivityItinerary();            
            $formatList=$format->all(function($query) use ($id){
                $query->where('activity_id',$id)->order('id', 'asc');
            });
            $itineraryList=$itinerary->all(function($query) use ($id){
                $query->where('activity_id',$id)->order('id', 'asc');
            });
            
            $assign=array(
                'info'=>$activity->get($id),
                'format'=>$formatList,
                'itinerary'=>$itineraryList,
                'id'=>$id
            );
            $this->assign($assign);
        }else{
            $assign=array(
                'id'=>$id
            );
            $this->assign($assign);
        }
        return view();
    }
    
    public function add_do(){
        $aman=input('aman',1,'intval');
        if($_POST || $aman==1){
            $dataType=input('t',1,'intval');
            $id=input('id',0,'intval');
            switch ($dataType){
                case 1:
                    $model=new Mactivity();
                    if($id){
                        $data=array(
                            'title'=>input('title'),
                            'subtitle'=>input('subtitle'),
                            'price'=>input('price'),
                            'headimg'=>input('headimg'),
                            'video'=>input('video'),
                            'tags'=>input('tags'),
                            'fromcity'=>input('fromcity')
                        );
                        $res=$model->save($data,['id'=>$id]);
                        if($res){
                            return result(1,$id);
                        }else{
                            return result(0,'保存失败');
                        }
                    }else{
                        $data=$this->request->instance()->except('id,t,aman');
                        $res=$model->data($data)->save(); 
                        if($res){
                            return result(1,$model->id);
                        }else{
                            return result(0,'保存失败');
                        }
                    }
                    
                    break;
                case 2:                    
                    $model=new ActivityFormat();                    
                    if($id){
                        //检查该活动是否已经有存在规格数据，如果有，直接删除，然后重写 
                        $formats=$_POST['formats'];
                        $data=array();
                        for($i=0;$i<count($formats);$i++){
                            $data[$i]=array(
                                'activity_id'=>$id,
                                'name'=>$formats[$i][0],
                                'price'=>$formats[$i][1],
                                'stock'=>$formats[$i][2],
                                'intro'=>$formats[$i][3],
                                'cost'=>$formats[$i][4]
                            );
                        }
                        if($data){
                            $model->where(['activity_id'=>$id])->delete();
                            $res=$model->saveAll($data);
                            if($res){
                                return result(1,'保存成功');
                            }else{
                                return result(0,'保存失败');
                            }
                        }else{
                            return result(0,'数据为空');
                        }
                    }else{
                        return result(0,'参数不正确');
                    }
                    break;
                case 3:                    
                    $model=new ActivityItinerary();                    
                    if($id){                        
                        //检查该活动是否已经有存在规格数据，如果有，直接删除，然后重写
                        $itinerarys=$_POST['itinerarys'];
                        $data=array();
                        for($i=0;$i<count($itinerarys);$i++){
                            $data[$i]=array(
                                'activity_id'=>$id,
                                'date'=>strtotime($itinerarys[$i][0]),
                                'title'=>$itinerarys[$i][1],
                                'intro'=>$itinerarys[$i][2],
                                'icon'=>$itinerarys[$i][3]
                            );
                        }
                        
                        if($data){
                            $model->where(['activity_id'=>$id])->delete();
                            $res=$model->saveAll($data);
                            if($res){
                                return result(1,'保存成功');
                            }else{
                                return result(0,'保存失败');
                            }
                        }else{
                            return result(0,'数据为空');
                        }
                    }else{
                        return result(0,'参数不正确');
                    }
                    break;                    
            }
            
        }else{
            return result(0,'保存失败');
        }
    }
    
    public function del(){
        $id=input('id',0,'intval');
        if($_POST){
            $activity=new Mactivity();
            $res=$activity->destroy($id);
            if($res){
                return result(1,'删除成功');
            }else{
                return result(0,'删除失败');
            }
        }else{
            return result(0,'非法操作');
        }
    }
    
    public function hot(){
        $id=input('id',0,'intval');
        if($_POST){
            $activity=new Mactivity();
            $res=$activity->update(['hot'=>0]);
            $res=$activity->save(['hot'=>1],['id'=>$id]);
            if($res){
                return result(1,'设置成功');
            }else{
                return result(0,'设置失败');
            }
        }else{
            return result(0,'非法操作');
        }
    }
    
    public function addnotice(){
        $id=input('id',0,'intval');        
        if($_POST && $id){
            $notice=input('post.notice');
            $activity=new Mactivity();
            $res=$activity->save(['notice'=>$notice],['id'=>$id]);
            if($res){
                return result(1,'保存成功');
            }else{
                return result(0,'保存失败');
            }
        }else{
            return result(0,'非法操作');
        }
    }
    
    public function orderDetail(){
        $id=input('id',0,'intval');
        if(!$id){
            die;
        }
        $order=new Orderinfo();
        $info=$order->get($id);
        $this->assign(['info'=>$info]);
        return view();
    }
    
    
    public function outData(){
    	return view();
    }
    public function outExcel(){
    		$start_time=input('start',0);
    		$end_time=input('end',0);
    		if($start_time){
    			$start_time=strtotime($start_time);
    		}
    		if($end_time){
    			$end_time=strtotime($end_time);
    		}
    		if($end_time==$start_time){
    			$end_time=$start_time+86399;
    		}
    		$map=array(
    				'pay_status'=>['in','1,2']
    		);
    		
    		$orderList=db('orderinfo')->where($map)->where('update_time','between time',[$start_time,$end_time])->order('update_time desc')->select();
    		for($i=0;$i<count($orderList);$i++){
    			$orderList[$i]['activity']=db('activity')->where(['id'=>$orderList[$i]['activity_id']])->find();
    		}
    		
    		
    		//p_arr($orderList);die;
    		$path = dirname(__FILE__);
    		$PHPExcel = new PHPExcel();
    		$PHPSheet = $PHPExcel->getActiveSheet();
    		
    		$PHPSheet->setTitle('订单详情');
    		$PHPSheet->setTitle('订单详情');
    		$PHPSheet
    		->setCellValue('A1','订单编号')
    		->setCellValue('B1','姓名')
    		->setCellValue('C1','电话')
    		->setCellValue('D1','付款金额')
    		->setCellValue('E1','购买产品')
    		->setCellValue('F1','购买数量')
    		->setCellValue('G1','购买时间');
    		
    		
    		for($i=0;$i<count($orderList);$i++){
    		    $cell=$i+2;
    		    $PHPSheet
    		    ->setCellValue('A'.$cell,'单号：'.$orderList[$i]['trade'])
    		    ->setCellValue('B'.$cell,$orderList[$i]['contact'])
    		    ->setCellValue('C'.$cell,$orderList[$i]['tel'])
    		    ->setCellValue('D'.$cell,$orderList[$i]['pay_price'])
    		    ->setCellValue('E'.$cell,$orderList[$i]['activity']['title'])
    		    ->setCellValue('F'.$cell,$orderList[$i]['total_num'])
    		    ->setCellValue('G'.$cell,Date('Y-m-d H:i:s',$orderList[$i]['update_time']));
    		}
    		
    		
    		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
    		//按照指定格式生成Excel文件，'Excel2007'表示生成2007版本的xlsx，'Excel5'表示生成2003版本Excel文件
    		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
    		//header('Content-Type:application/vnd.ms-excel');//告诉浏览器将要输出Excel03版本文件
    		$filename='【原子出发】订单信息——'.date('Y-m-d H:i:s').'.xlsx';
    		header('Content-Disposition: attachment;filename="'.$filename.'"');
    		//告诉浏览器输出浏览器名称
    		header('Cache-Control: max-age=0');
    		$PHPWriter->save("php://output");
    }
}