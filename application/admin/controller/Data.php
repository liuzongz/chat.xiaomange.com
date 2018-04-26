<?php
namespace app\admin\controller;
use think\Controller;
use think\Model;

class Data extends Base {
	public function shop() {
		$shop = new \app\common\model\Shop();
		$day1 = nDay(1);
		$day2 = nDay(2);
		$shopSum = $shop->where('create_time', 'between', [$day2['beginTime'], $day2['endTime']])->count();
		$reg = array(
			'day1' => $shop->where('create_time', 'between', [nday(1)['beginTime'], nday(1)['endTime']])->count(),
			'day2' => $shop->where('create_time', 'between', [nday(2)['beginTime'], nday(2)['endTime']])->count(),
			'day7' => $shop->where('create_time', 'between', [nday(4)['beginTime'], nday(4)['endTime']])->count(),
			'day14' => $shop->where('create_time', 'between', [nday(5)['beginTime'], nday(5)['endTime']])->count(),
			'day30' => $shop->where('create_time', 'between', [nday(6)['beginTime'], nday(6)['endTime']])->count(),
			'day60' => $shop->where('create_time', 'between', [nday(11)['beginTime'], nday(11)['endTime']])->count(),
		);
		$member = new \app\common\model\Member();
		$login = array(
			'day1' => $member->where('create_time', 'between', [nday(1)['beginTime'], nday(1)['endTime']])->where('shop_id', '>', 0)->count(),
			'day2' => $member->where('create_time', 'between', [nday(2)['beginTime'], nday(2)['endTime']])->where('shop_id', '>', 0)->count(),
			'day7' => $member->where('create_time', 'between', [nday(4)['beginTime'], nday(4)['endTime']])->where('shop_id', '>', 0)->count(),
			'day14' => $member->where('create_time', 'between', [nday(5)['beginTime'], nday(5)['endTime']])->where('shop_id', '>', 0)->count(),
			'day30' => $member->where('create_time', 'between', [nday(6)['beginTime'], nday(6)['endTime']])->where('shop_id', '>', 0)->count(),
			'day60' => $member->where('create_time', 'between', [nday(11)['beginTime'], nday(11)['endTime']])->where('shop_id', '>', 0)->count(),
		);

		$this->assign(['reg' => $reg, 'login' => $login]);
		return view();
	}

	public function daily() {
		$activity = new \app\common\model\Activity();
		$order = new \app\common\model\Orderinfo();
		$user = new \app\common\model\User();
		var_dump(nday(2));
		$num = array(
			'day1' => array(
				'youpin' => $activity->where('update_time', 'between', [nday(1)['beginTime'], nday(1)['endTime']])->where('element', '=', 0)->count(),
				'zaishou' => $activity->where('update_time', 'between', [nday(1)['beginTime'], nday(1)['endTime']])->where('element', '>', 0)->count(),
				'ekehu' => $user->where('create_time', 'between', [nday(1)['beginTime'], nday(1)['endTime']])->where(['name' => ['like', '%资源推荐']])->count(),
				'zkehu' => $user->where('create_time', 'between', [nday(1)['beginTime'], nday(1)['endTime']])->where('rule', '=', 2)->count(),
				'sale' => $order->where('create_time', 'between', [nday(1)['beginTime'], nday(1)['endTime']])->where('pay_status', '=', 1)->sum('pay_price'),
			),
			'day2' => array(
				'youpin' => $activity->where('update_time', 'between', [nday(2)['beginTime'], nday(2)['endTime']])->where('element', '=', 0)->count(),
				'zaishou' => $activity->where('update_time', 'between', [nday(2)['beginTime'], nday(2)['endTime']])->where('element', '>', 0)->count(),
				'ekehu' => $user->where('create_time', 'between', [nday(2)['beginTime'], nday(2)['endTime']])->where(['name' => ['like', '%资源推荐']])->count(),
				'zkehu' => $user->where('create_time', 'between', [nday(2)['beginTime'], nday(2)['endTime']])->where('rule', '=', 2)->count(),
				'sale' => $order->where('create_time', 'between', [nday(2)['beginTime'], nday(2)['endTime']])->where('pay_status', '=', 1)->sum('pay_price'),
			),
			'day7' => array(
				'youpin' => $activity->where('update_time', 'between', [nday(4)['beginTime'], nday(4)['endTime']])->where('element', '=', 0)->count(),
				'zaishou' => $activity->where('update_time', 'between', [nday(4)['beginTime'], nday(4)['endTime']])->where('element', '>', 0)->count(),
				'ekehu' => $user->where('create_time', 'between', [nday(4)['beginTime'], nday(4)['endTime']])->where(['name' => ['like', '%资源推荐']])->count(),
				'zkehu' => $user->where('create_time', 'between', [nday(4)['beginTime'], nday(4)['endTime']])->where('rule', '=', 2)->count(),
				'sale' => $order->where('create_time', 'between', [nday(4)['beginTime'], nday(4)['endTime']])->where('pay_status', '=', 1)->sum('pay_price'),
			),
			'day14' => array(
				'youpin' => $activity->where('update_time', 'between', [nday(5)['beginTime'], nday(5)['endTime']])->where('element', '=', 0)->count(),
				'zaishou' => $activity->where('update_time', 'between', [nday(5)['beginTime'], nday(5)['endTime']])->where('element', '>', 0)->count(),
				'ekehu' => $user->where('create_time', 'between', [nday(5)['beginTime'], nday(5)['endTime']])->where(['name' => ['like', '%资源推荐']])->count(),
				'zkehu' => $user->where('create_time', 'between', [nday(5)['beginTime'], nday(5)['endTime']])->where('rule', '=', 2)->count(),
				'sale' => $order->where('create_time', 'between', [nday(5)['beginTime'], nday(5)['endTime']])->where('pay_status', '=', 1)->sum('pay_price'),
			),
			'day30' => array(
				'youpin' => $activity->where('update_time', 'between', [nday(6)['beginTime'], nday(6)['endTime']])->where('element', '=', 0)->count(),
				'zaishou' => $activity->where('update_time', 'between', [nday(6)['beginTime'], nday(6)['endTime']])->where('element', '>', 0)->count(),
				'ekehu' => $user->where('create_time', 'between', [nday(6)['beginTime'], nday(6)['endTime']])->where(['name' => ['like', '%资源推荐']])->count(),
				'zkehu' => $user->where('create_time', 'between', [nday(6)['beginTime'], nday(6)['endTime']])->where('rule', '=', 2)->count(),
				'sale' => $order->where('create_time', 'between', [nday(6)['beginTime'], nday(6)['endTime']])->where('pay_status', '=', 1)->sum('pay_price'),
			),
			'day60' => array(
				'youpin' => $activity->where('update_time', 'between', [nday(11)['beginTime'], nday(11)['endTime']])->where('element', '=', 0)->count(),
				'zaishou' => $activity->where('update_time', 'between', [nday(11)['beginTime'], nday(11)['endTime']])->where('element', '>', 0)->count(),
				'ekehu' => $user->where('create_time', 'between', [nday(11)['beginTime'], nday(11)['endTime']])->where(['name' => ['like', '%资源推荐']])->count(),
				'zkehu' => $user->where('create_time', 'between', [nday(11)['beginTime'], nday(11)['endTime']])->where('rule', '=', 2)->count(),
				'sale' => $order->where('create_time', 'between', [nday(11)['beginTime'], nday(11)['endTime']])->where('pay_status', '=', 1)->sum('pay_price'),
			),
			'all' => array(
				'youpin' => $activity->where('element', '=', 0)->count(),
				'zaishou' => $activity->where('element', '>', 0)->count(),
				'ekehu' => $user->where(['name' => ['like', '%资源推荐']])->count(),
				'zkehu' => $user->where('rule', '=', 2)->count(),
				'sale' => $order->where('pay_status', '=', 1)->sum('pay_price'),
			),
		);

		$this->assign('num', $num);
		return view();
	}

	public function regdata() {
		if ($_POST) {
			$data = $_POST;
			$model = new \app\common\model\Data();
			$res = $model->save([
				'resource' => $data['source'],
				'team' => $data['team'],
				'user' => $data['user'],
			], ['id' => 1]);
			if ($res) {
				echo json_encode(array('msg' => 1));
			} else {
				echo json_encode(array('msg' => 2));
			}
		} else {
			$info = db('data')->find();
			$this->assign(array('info' => $info));
			return view();
		}
	}

	//资源店数据分析
	public function source() {
		$shop = input('shop', 0, 'intval');
		$this->assign('shop_on', $shop);
		$time = input('time', 1, 'intval');
		$this->assign('time_on', $time);

		$model = new \app\common\model\Shop();
		$shop = $model->where(['shop_type' => 1])->select(); //所有的资源店
		$total = count($shop); //总数
		$new = $model->where('create_time', 'between', [nday(2)['beginTime'], nday(2)['endTime']])->select(); //昨日新增
		$new_num = count($new);

		$activity = new \app\common\model\Activity();
		$ziyuan = 0;
		$ziyuan_num = 0;
		foreach ($shop as $v) {
			//每个资源店下的资源数
			$ziyuan += $activity->where(['shop_id' => $v['id']])->count();
			//昨日新增资源数
			$ziyuan_num += $activity->where(['shop_id' => $v['id']])->where('create_time', 'between', [nday(2)['beginTime'], nday(2)['endTime']])->count();
		}

		//销售额
		$order = new \app\common\model\Orderinfo();
		$sale = $order->where(['pay_status' => 1])->sum('pay_price');
		$sale_num = $order->where(['pay_status' => 1])->where('create_time', 'between', [nday(2)['beginTime'], nday(2)['endTime']])->count();

		$map = $model->where(['shop_type' => 1]);
		if ($time == 1) {
			//默认横坐标
			$time = array(
				date('m-d', strtotime('-6 days')),
				date('m-d', strtotime('-5 days')),
				date('m-d', strtotime('-4 days')),
				date('m-d', strtotime('-3 days')),
				date('m-d', strtotime('-2 days')),
				date('m-d', strtotime('-1 days')),
				date('m-d'),
			);
			//资源客户总数
			$shop1 = $map->where('create_time', '<', time())->count();
			$shop2 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0')))->count();
			$shop3 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-1 days'))))->count();
			$shop4 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-2 days'))))->count();
			$shop5 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-3 days'))))->count();
			$shop6 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-4 days'))))->count();
			$shop7 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-5 days'))))->count();
			$shop_all = array($shop7, $shop6, $shop5, $shop4, $shop3, $shop2, $shop1);

			//新增资源客户数
			$new1 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0')), time()])->count();
			$new2 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-1 days'))), strtotime(date('Y-m-d 0:0:0'))])
				->count();
			$new3 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-2 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-1 days')))])->count();
			$new4 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-3 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-2 days')))])->count();
			$new5 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-4 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-3 days')))])->count();
			$new6 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-5 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-4 days')))])->count();
			$new7 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-6 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-5 days')))])->count();
			$new_all = array($new7, $new6, $new5, $new4, $new3, $new2, $new1);
		} else if ($time == 2) {
			//横坐标
			$time = array(
				date('m/d', strtotime('-13 days')) . '-' . date('m/d', strtotime('-12 days')),
				date('m/d', strtotime('-11 days')) . '-' . date('m/d', strtotime('-10 days')),
				date('m/d', strtotime('-9 days')) . '-' . date('m/d', strtotime('-8 days')),
				date('m/d', strtotime('-7 days')) . '-' . date('m/d', strtotime('-6 days')),
				date('m/d', strtotime('-5 days')) . '-' . date('m/d', strtotime('-4 days')),
				date('m/d', strtotime('-3 days')) . '-' . date('m/d', strtotime('-2 days')),
				date('m/d', strtotime('-1 days')) . '-' . date('m/d'),
			);
			//资源客户总数
			$shop1 = $map->where('create_time', '<', time())->count();
			$shop2 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-2 days'))))->count();
			$shop3 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-4 days'))))->count();
			$shop4 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-6 days'))))->count();
			$shop5 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-8 days'))))->count();
			$shop6 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-10 days'))))->count();
			$shop7 = $map->where('create_time', '<', strtotime(date('Y-m-d 0:0:0', strtotime('-12 days'))))->count();
			$shop_all = array($shop7, $shop6, $shop5, $shop4, $shop3, $shop2, $shop1);

			//新增资源客户数
			$new1 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-1 days'))), time()])->count();
			$new2 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-3 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-2 days')))])
				->count();
			$new3 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-5 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-4 days')))])->count();
			$new4 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-7 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-6 days')))])->count();
			$new5 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-9 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-8 days')))])->count();
			$new6 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-11 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-10 days')))])->count();
			$new7 = $map->where('create_time', 'between', [strtotime(date('Y-m-d 0:0:0', strtotime('-13 days'))), strtotime(date('Y-m-d 0:0:0', strtotime('-12 days')))])->count();
			$new_all = array($new7, $new6, $new5, $new4, $new3, $new2, $new1);
		} else if ($time == 3) {

		}

		$assign = array(
			'shop' => $shop,
			'total' => $total,
			'new' => $new,
			'new_num' => $new_num,
			'ziyuan' => $ziyuan,
			'ziyuan_num' => $ziyuan_num,
			'sale' => $sale,
			'sale_num' => $sale_num,
			'time' => json_encode($time),
			'shop_all' => json_encode($shop_all),
			'new_all' => json_encode($new_all),
		);
		$this->assign($assign);
		return view();
	}

	public function tongji() {

		return view();
	}
}