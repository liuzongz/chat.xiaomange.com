<?php 
function getStockByFormatAndPeriod($format_id,$period_id){
    $order=new \app\common\model\Orderinfo();
    $format=new \app\common\model\ActivityFormat();
    //已购买的查询条件
    $orderMap1=array(
        'format_id'=>$format_id,
        'period_id'=>$period_id,
        'pay_status'=>1
    );
    //已下单，等待付款的查询条件
    $nowtime=time();
    //前30分钟内的订单
    $pre30time=$nowtime-60*30;
    
    $orderMap9=array(
        'format_id'=>$format_id,
        'period_id'=>$period_id,
        'pay_status'=>9,
        'create_time'=>['between',[$pre30time,$nowtime]]
    );
    $num1=$order->where($orderMap1)->sum('total_num');
    $num9=$order->where($orderMap9)->sum('total_num');
    $allBuyNum=$num1+$num9;
    
    //计算这个规格对应的总库存
    $formatInfo=$format->get($format_id);
    $formatStock=$formatInfo->stock;
    
    //如果总库存大于已经购买掉的总量，那么返回剩余的库存量，否则返回0
    if($formatStock>$allBuyNum){
        return $formatStock-$allBuyNum;
    }else{
        return 0;
    }    
}

function getFormatInfo($id,$type=1){
    $format=new \app\common\model\ActivityFormat();
    if($type==1){
        $formatInfo=$format->get(['activity_id'=>$id]);        
    }else{
        $formatInfo=$format->get($id);
    }
    if($formatInfo){
        return $formatInfo->toArray();
    }else{
        return false;
    }
}

function getActivityBuyer($id,$type=0){
    $order=new \app\common\model\Orderinfo();
    $member=new \app\common\model\Member();    
    $orderList=$order->all(function($q)use($id){
        $q->where(['pay_status'=>1,'activity_id'=>$id])->group('openid');
    });    
    //$orderList=$order->all();
    $memberList=[];
    for($i=0;$i<count($orderList);$i++){
        $memberInfo=$member->get(['openid'=>$orderList[$i]['openid']]);
        $memberList[$i]=array(
            'nickname'=>$memberInfo->nickname,
            'headimgurl'=>$memberInfo->headimgurl
        );
    }
    return $memberList;
}

function getActivitySales($id){
    $order=new \app\common\model\Orderinfo();    
    return $order->where(['pay_status'=>1,'activity_id'=>$id])->sum('total_num');
}

function getActivityOnePeriod($id){
    $model=new \app\common\model\ActivityPeriod();
    $info=$model->get(['activity_id'=>$id]);
    return $info->name;
}
//获取订单利润
function getOrderProfit($id){
    $order=new \app\common\model\Orderinfo();
    $orderInfo=$order->get($id);
    $format=new \app\common\model\ActivityFormat();
    $formatInfo=$format->get($orderInfo->format_id);
    $profit=($formatInfo->price-$formatInfo->cost)*$orderInfo->total_num;
    return $profit;
}

//获取订单金额
function getOrderPayPrice($id){
    $order=new \app\common\model\Orderinfo();
    $orderInfo=$order->get($id);
    return $orderInfo->pay_price;
}

function getUserInfo(){
    $weixinInfo=cookie('weixinInfo');
    if(!$weixinInfo){
        return false;
    }
    
    $user=new \app\common\model\User();
    $useInfo=$user->get(['openid'=>$weixinInfo['openid']]);
    if(!$useInfo){
        return 1;
    }
    
    return $useInfo->toArray();
}

function getActivityTotalSalePrice($id){
    $order=new \app\common\model\Orderinfo();
    $total=$order->where(['pay_status'=>1,'activity_id'=>$id])->sum('total_price');
    return $total;
}

function getActivityTotalProfit($id){
    $commission=new \app\common\model\Commission();
    $list=$commission->all(['activity_id'=>$id]);
    $price=0;
    $cost=0;
    for($i=0;$i<count($list);$i++){
        $price=$price+$list[$i]['price']*$list[$i]['buynum'];
        $cost=$cost+$list[$i]['cost']*$list[$i]['buynum'];
    }
    return $price-$cost;    
}
function getActivityTotalSaleNum($id){
    $order=new \app\common\model\Orderinfo();
    $total=$order->where(['pay_status'=>1,'activity_id'=>$id])->sum('total_num');
    return $total;
}

//根据活动ID，获取某个活动的订单状态总量
function getActivityAllOrderStatus($id,$status){
    $order=new \app\common\model\Orderinfo();
    $total=$order->where(['status'=>$status,'activity_id'=>$id])->count();
    return $total;
}

//根据店铺ID，获取整个店铺的销售额
function getShopTotalSalePriceAndProfit($id){
    $commission=new \app\common\model\Commission();
    $list=$commission->all(['shop_id'=>$id]);
    $price=0;
    $cost=0;
    for($i=0;$i<count($list);$i++){
        $price=$price+$list[$i]['price']*$list[$i]['buynum'];
        $cost=$cost+$list[$i]['cost']*$list[$i]['buynum'];
    }
    $profit=$price-$cost;
    $data=array(
        'price'=>$price,
        'cost'=>$cost,
        'profit'=>$profit
    );
    return $data;
}

//获取活动信息
function getActivityInfo($id){
    $model=new \app\common\model\Activity();
    $info=$model->get($id);
    if($info){
        return $info->toArray();
    }else{
        return false;
    }
}
//获取活动团期信息
function getActivityPeriodInfo($id){
    $model=new \app\common\model\ActivityPeriod();
    $info=$model->get($id);
    if($info){
        return $info->toArray();
    }else{
        return false;
    }
}

//获取活动规格信息
function getActivityFormatInfo($id){
    $model=new \app\common\model\ActivityFormat();
    $info=$model->get($id);
    if($info){
        return $info->toArray();
    }else{
        return false;
    }
}
//获取活动规格信息
function getActivityFormatLowerPrice($id){
    $model=new \app\common\model\ActivityFormat();
    $info=$model->where(['activity_id'=>$id])->min('price');
    if($info){
        return $info;
    }else{
        return false;
    }
}

//获取Openid对应的用户信息
function getMemberInfo($openid){
    $model=new \app\common\model\Member();
    $info=$model->get(['openid'=>$openid]);
    if($info){
        return $info->toArray();
    }else{
        return false;
    }
}


?>