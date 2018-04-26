<?php
namespace app\mobile\controller;
use think\Model;

class Shop extends Base {
	public function index() {
		$shopId = $this->userInfo['rule_id'];
		$shopSaleData = getShopTotalSalePriceAndProfit($shopId);

		$this->assign(['shopdata' => $shopSaleData, 'create_time' => $this->userInfo['create_time'], 'shopid' => $shopId]);
		$model = new \app\common\model\Activity();
		$list = $model->all(function ($q) use ($shopId) {
			$q->where(['shop_id' => $shopId, 'status' => 1])->order('create_time desc');
		});

		for ($i = 0; $i < count($list); $i++) {
			$id = $list[$i]['id'];
			$com = new \app\common\model\Commission();
			$allPrice = 0;
			$allProfit = 0;
			$allSaleNum = 0;
			$comList = $com->all(['activity_id' => $id]);
			for ($c = 0; $c < count($comList); $c++) {
				$allProfit = $allProfit + $comList[$c]['profit'];
				$allPrice = $allPrice + $comList[$c]['allprice'];
				$allSaleNum = $allSaleNum + $comList[$c]['buynum'];
			}
			$list[$i]['allPrice'] = $allPrice;
			$list[$i]['profit'] = $allProfit;
			$list[$i]['allsalenum'] = $allSaleNum;

			$url = "http://chat.xiaomange.com/mobile/toc/detail.html?id=" . $list[$i]['id'];
			// var_dump($url);

			$list[$i]['code'] = getUrlQr($url, $list[$i]['id']);

			$format = new \app\common\model\ActivityFormat();
			$formatInfo = $format->get(['activity_id' => $id]);

			$list[$i]['format'] = $formatInfo;

			$elementActivityFormat = $format->get(['activity_id' => $list[$i]['element']]);
			$elementStock = $elementActivityFormat->stock;
			$order = new \app\common\model\Orderinfo();
			$elementSaleNum = $order->where(['element_id' => $list[$i]['element'], 'pay_status' => ['in', [1, 9]]])->sum('total_num');
			$lastStock = $elementStock - $elementSaleNum;
			$list[$i]['lastnum'] = $lastStock;

			if ($list[$i]['headimg']) {
				$img = $list[$i]['headimg'][0];

				$path = ROOT_PATH . 'public' . DS . 'static' . DS . 'upload' . DS;
				$img = $this->crabImage($img, $path, "", $i);

				$list[$i]['headimgs'] = "http://chat.xiaomange.com/static/upload/" . $img['file_name'];
				// $list[$i]['headimgs'] = $img;
			} else {
				$list[$i]['headimgs'] = '';
			}

			$now = date('Y-m-d');
			$data = db('data')->find();
			$time = date('Y-m-d', $data['create_time']);
			$num1 = rand(5, 10);
			$num2 = rand(2, 6);
			$num3 = rand(10, 20);
			if ($now != $time) {
				$model = new \app\common\model\Data();
				$change = $model->save([
					'source' => $data['source'] + $num1,
					'team' => $data['team'] + $num2,
					'user' => $data['user'] + $num3,
				], ['id' => 1]);
			}

		}
		$this->assign('list', $list);

		return view();
	}

	function crabImage($imgUrl, $saveDir, $fileName = null, $i) {
		if (empty($imgUrl)) {
			return false;
		}

		//获取图片信息大小
		$imgSize = getImageSize($imgUrl);
		if (!in_array($imgSize['mime'], array('image/jpg', 'image/gif', 'image/png', 'image/jpeg'), true)) {
			return false;
		}

		//获取后缀名
		$_mime = explode('/', $imgSize['mime']);
		$_ext = '.' . end($_mime) . $i;

		if (empty($fileName)) {
			//生成唯一的文件名
			$fileName = time() . $_ext;
		}

		//开始攫取
		ob_start();
		readfile($imgUrl);
		$imgInfo = ob_get_contents();
		ob_end_clean();

		if (!file_exists($saveDir)) {
			mkdir($saveDir, 0777, true);
		}
		$fp = fopen($saveDir . $fileName, 'a');
		$imgLen = strlen($imgInfo); //计算图片源码大小
		$_inx = 1024; //每次写入1k
		$_time = ceil($imgLen / $_inx);
		for ($i = 0; $i < $_time; $i++) {
			fwrite($fp, substr($imgInfo, $i * $_inx, $_inx));
		}
		fclose($fp);

		return array('file_name' => $fileName, 'save_path' => $saveDir . $fileName);
	}

	public function myActivityApi() {
		$shopId = $this->userInfo['rule_id'];
		$status = input('status', 1, 'intval');
		$model = new \app\common\model\Activity();
		$list = $model->all(function ($q) use ($shopId, $status) {
			$q->where(['shop_id' => $shopId, 'status' => $status])->order('create_time desc');
		});

		for ($i = 0; $i < count($list); $i++) {
			$id = $list[$i]['id'];
			$com = new \app\common\model\Commission();
			$allPrice = 0;
			$allProfit = 0;
			$allSaleNum = 0;
			$comList = $com->all(['activity_id' => $id]);
			for ($c = 0; $c < count($comList); $c++) {
				$allProfit = $allProfit + $comList[$c]['profit'];
				$allPrice = $allPrice + $comList[$c]['allprice'];
				$allSaleNum = $allSaleNum + $comList[$c]['buynum'];
			}
			$list[$i]['allPrice'] = $allPrice;
			$list[$i]['profit'] = $allProfit;
			$list[$i]['allsalenum'] = $allSaleNum;

			$format = new \app\common\model\ActivityFormat();
			$formatInfo = $format->get(['activity_id' => $id]);

			$list[$i]['format'] = $formatInfo;

			$elementActivityFormat = $format->get(['activity_id' => $list[$i]['element']]);
			$elementStock = $elementActivityFormat->stock;
			$order = new \app\common\model\Orderinfo();
			$elementSaleNum = $order->where(['element_id' => $list[$i]['element'], 'pay_status' => ['in', [1, 9]]])->sum('total_num');
			$lastStock = $elementStock - $elementSaleNum;
			$list[$i]['lastnum'] = $lastStock;

			if ($list[$i]['headimg']) {
				$img = $list[$i]['headimg'][0];
				$list[$i]['headimgs'] = $img;
			} else {
				$list[$i]['headimgs'] = '';
			}
		}
		if ($list) {
			return result(1, $list);
		} else {
			return result(0, '没有数据');
		}
	}
	public function stopSale() {
		$id = input('id', 0, 'intval');
		if (!$id) {
			return result(0, '缺少参数');
		}
		$res = db('activity')->where(['id' => $id])->setField('status', 0);
		if ($res) {
			return result(1, '设置成功');
		} else {
			return result(0, '设置失败');
		}
	}

	public function setSale() {
		$id = input('id', 0, 'intval');
		if (!$id) {
			return result(0, '缺少参数');
		}
		$res = db('activity')->where(['id' => $id])->setField('status', 1);
		if ($res) {
			return result(1, '设置成功');
		} else {
			return result(0, '设置失败');
		}
	}
	public function orderlist() {
		$id = input('id', 0, 'intval');
		$order = new \app\common\model\Orderinfo();
		$format = new \app\common\model\ActivityFormat();
		$activity = new \app\common\model\Activity();
		$period = new \app\common\model\ActivityPeriod();
		$member = new \app\common\model\Member();

		$list = $order->all(function ($q) use ($id) {
			$q->where(['activity_id' => $id, 'status' => 1])->order('create_time desc');
		});

		for ($i = 0; $i < count($list); $i++) {
			$list[$i]['format'] = $format->get($list[$i]['format_id']);
			$list[$i]['period'] = $period->get($list[$i]['period_id']);
			$list[$i]['member'] = $member->get(['openid' => $list[$i]['openid']]);
		}
		$count = array(
			's9' => getActivityAllOrderStatus($id, 9),
			's1' => getActivityAllOrderStatus($id, 1),
			's2' => getActivityAllOrderStatus($id, 2),
			's3' => getActivityAllOrderStatus($id, 3),
		);
		$assign = array(
			'list' => $list,
			'info' => $activity->get($id),
			'count' => $count,
		);
		$this->assign($assign);
		return view();
	}

	public function orderlistApi() {
		$id = input('id', 0, 'intval');
		$status = input('status', 0, 'intval');
		$order = new \app\common\model\Orderinfo();
		$format = new \app\common\model\ActivityFormat();
		$activity = new \app\common\model\Activity();
		$period = new \app\common\model\ActivityPeriod();
		$member = new \app\common\model\Member();

		$list = $order->all(function ($q) use ($id, $status) {
			$q->where(['activity_id' => $id, 'status' => $status])->order('create_time desc');
		});
		for ($i = 0; $i < count($list); $i++) {
			$list[$i]['format'] = $format->get($list[$i]['format_id']);
			$list[$i]['period'] = $period->get($list[$i]['period_id']);
			$list[$i]['member'] = $member->get(['openid' => $list[$i]['openid']]);
		}
		if ($list) {
			return result(1, $list);
		} else {
			return result(0, '没有数据');
		}
	}

	public function profit() {
		return view();
	}

	public function cost() {
		return view();
	}

	public function setting() {
		$shopId = $this->userInfo['rule_id'];
		$assign = array(
			'shopinfo' => $this->userInfo,
			'khurl' => 'http://' . $_SERVER['SERVER_NAME'] . '/mobile/toc/myorder?shopid=' . $shopId,
		);
		$this->assign($assign);
		return view();
	}

	public function checkOrderByScanQrcode() {
		$shopId = $this->userInfo['rule_id'];
		$code = input('code', 0, 'intval');
		if (!$code) {
			return result(0, '没有传入订单号');
		}
		$order = new \app\common\model\Orderinfo();
		$info = $order->get(['order_code' => $code]);
		if (!$info) {
			return result(0, '没有找到对应的订单');
		}
		$orderinfo = $info->toArray();
		if ($shopId != $orderinfo['shop_id']) {
			return result(0, '这个订单不是在您这里购买的');
		}
		$activity = getActivityInfo($orderinfo['activity_id']);
		$format = getActivityFormatInfo($orderinfo['format_id']);
		$period = getActivityPeriodInfo($orderinfo['period_id']);
		$member = getMemberInfo($orderinfo['openid']);
		$data = array(
			'info' => $orderinfo,
			'activity' => $activity,
			'format' => $format,
			'period' => $period,
			'member' => $member,
		);
		return result(1, $data);
	}

	public function setOrderCodeStatus() {
		$code = input('code', 0, 'intval');
		if (!$code) {
			return result(0, '没有传入订单号');
		}
		$updata = array(
			'order_code_status' => 1,
			'status' => 2,
		);
		$res = db('orderinfo')->where(['order_code' => $code])->update($updata);
		if ($res) {
			$orderId = db('orderinfo')->where(['order_code' => $code])->find();
			$ostatus = new \app\common\model\OrderinfoStatus();
			$ostatus->save(['order_id' => $orderId['id'], 'activity_id' => $orderId['activity_id'], 'status' => 2]);
			return result(1, '核销成功');
		} else {
			return result(0, '核销失败');
		}
	}
}