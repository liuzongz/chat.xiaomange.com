<?php
namespace app\mobile\controller;
use think\Model;
use think\Request;

class Activity extends Base {
	public function index() {
		$tagsid = input('tagsid', 0, 'intval');
		$keyword = input('keyword', 0);

		$tags = new \app\common\model\Tags();
		$tagsList = $tags->all();
		$activity = new \app\common\model\Activity();
		$map = ['element' => 0];

		if ($keyword) {
			$map['title'] = ['like' => '%' . $keyword . '%'];
		}
		if ($tagsid) {
			$tagsMap = ['tags_id' => $tagsid];
		} else {
			$tagsMap = [];
		}

		$list = $activity
			->where($map)
			->whereTime('start_time', '<', time())
			->order('hot desc')
			->paginate(5);
		for ($i = 0; $i < count($list); $i++) {
			$thisId = $list[$i]['id'];
			//先判断这个活动是否已经有添加过了
			$ck = db('activity')->where(['element' => $thisId, 'shop_id' => $this->userInfo['rule_id']])->find();

			if (!$ck) {
				$list[$i]['ck'] = 0;
			} else {
				$list[$i]['ck'] = 1;
			}

			$tagsAc = new \app\common\model\TagsActivity();
			$tags = $tagsAc->all(function ($q) use ($thisId) {
				$q->where(['activity_id' => $thisId]);
			});
			$tagsAy = [];
			for ($t = 0; $t < count($tags); $t++) {
				$tagsAy[$t] = $tags[$t]['tagsname'];
			}
			$list[$i]['tags'] = $tagsAy;
			$format = new \app\common\model\ActivityFormat();
			$list[$i]['format'] = $format->get(['activity_id' => $list[$i]['id']]);
			$list[$i]['profit'] = $list[$i]['format']['price'] - $list[$i]['format']['cost'];

			if ($list[$i]['headimg']) {
				$img = $list[$i]['headimg'][0];
				$list[$i]['headimgs'] = $img;
			} else {
				$list[$i]['headimgs'] = '';
			}
			$shopname = db('shop')->where(['id' => $list[$i]['shop_id']])->value('shopname');
			$list[$i]['shopname'] = $shopname;

		}

		$assign = [
			'tags' => $tagsList,
			'list' => $list,
		];
		$this->assign($assign);
		return view();
	}
	public function getActivityListApi() {
		$tagsid = input('tagsid', 0, 'intval');
		$keyword = input('keyword', 0);

		$tags = new \app\common\model\Tags();
		$tagsList = $tags->all();
		$activity = new \app\common\model\Activity();
		$map = ['element' => 0];
		if ($keyword) {
			$map['title'] = ['like', '%' . $keyword . '%'];
		}

		if ($tagsid) {
			$tagsMap = ['tags_id' => $tagsid];
			$list = $activity::hasWhere('tags', $tagsMap)
				->where($map)
				->whereTime('end_time', '>', time())
				->order('hot desc')
				->paginate(5);
		} else {
			$list = $activity
				->where($map)
				->whereTime('end_time', '>', time())
				->order('hot desc')
				->paginate(5);
		}

		for ($i = 0; $i < count($list); $i++) {
			$thisId = $list[$i]['id'];
			//先判断这个活动是否已经有添加过了
			$ck = db('activity')->where(['element' => $thisId, 'shop_id' => $this->userInfo['rule_id']])->find();

			if (!$ck) {
				$list[$i]['ck'] = 0;
			} else {
				$list[$i]['ck'] = 1;
			}
			$tagsAc = new \app\common\model\TagsActivity();
			$tags = $tagsAc->all(function ($q) use ($thisId) {
				$q->where(['activity_id' => $thisId]);
			});
			$tagsAy = [];
			for ($t = 0; $t < count($tags); $t++) {
				$tagsAy[$t] = $tags[$t]['tagsname'];
			}
			$list[$i]['tags'] = $tagsAy;
			$format = new \app\common\model\ActivityFormat();
			$list[$i]['format'] = $format->get(['activity_id' => $list[$i]['id']]);
			$list[$i]['profit'] = formatprice($list[$i]['format']['price'] - $list[$i]['format']['cost']);

			if ($list[$i]['headimg']) {
				$img = $list[$i]['headimg'][0];
				$list[$i]['headimgs'] = $img;
			} else {
				$list[$i]['headimgs'] = '';
			}
			$shopname = db('shop')->where(['id' => $list[$i]['shop_id']])->value('shopname');
			$list[$i]['shopname'] = $shopname;
		}

		if ($list) {
			return result(1, $list);
		} else {
			return result(0, '没有数据了');
		}
	}

	public function detail() {
		$id = input('id', 0, 'intval');
		$model = new \app\common\model\Activity();
		$info = $model->get($id);
		if (!$info) {
			$this->redirect('err/errpage', ['msg' => '该活动不存在']);
		}
		$info['period'] = $info['period'];
		$info = $info->toArray();
		$thisId = $info['id'];
		$tagsAc = new \app\common\model\TagsActivity();
		$tags = $tagsAc->all(function ($q) use ($thisId) {
			$q->where(['activity_id' => $thisId]);
		});
		$tagsAy = [];
		for ($t = 0; $t < count($tags); $t++) {
			$tagsAy[$t] = $tags[$t]['tagsname'];
		}
		$format = new \app\common\model\ActivityFormat();
		$formatInfo = $format->all(function ($q) use ($thisId) {
			$q->where(['activity_id' => $thisId])->order('xu asc');
		});

		$shopInfo = db('shop')->where(['id' => $info['shop_id']])->field('shopname,headimg,tel')->find();
		$assign = array(
			'info' => $info,
			'format' => $formatInfo,
			'tags' => $tagsAy,
			'shop' => $shopInfo,
		);

		$this->assign($assign);
		return view();
	}

	public function preview() {
		$id = input('id', 0, 'intval');
		if (!$id) {
			echo '该活动不存在';die;
		}
		if (!$this->copyActivity($id)) {
			echo '添加活动到我的小店失败';die;
		}
		$model = new \app\common\model\Activity();
		$info = $model->get($id);
		if (!$info) {
			$this->redirect('err/errpage', ['msg' => '该活动不存在']);
		}
		$info['period'] = $info['period'];
		$info = $info->toArray();
		$thisId = $info['id'];
		$tagsAc = new \app\common\model\TagsActivity();
		$tags = $tagsAc->all(function ($q) use ($thisId) {
			$q->where(['activity_id' => $thisId]);
		});
		$tagsAy = [];
		for ($t = 0; $t < count($tags); $t++) {
			$tagsAy[$t] = $tags[$t]['tagsname'];
		}
		$format = new \app\common\model\ActivityFormat();
		$formatInfo = $format->all(function ($q) use ($thisId) {
			$q->where(['activity_id' => $thisId])->order('xu asc');
		});
		$shopId = $this->userInfo['rule_id'];
		$shopInfo = db('shop')->where(['id' => $shopId])->field('shopname,headimg,tel')->find();

		$elementActivityFormat = $format->get(['activity_id' => $id]);
		$elementStock = $elementActivityFormat->stock;
		$info['stock'] = $elementStock;
		$order = new \app\common\model\Orderinfo();
		$elementSaleNum = $order->where(['element_id' => $info['element'], 'pay_status' => 1])->sum('total_num');
		$lastStock = $elementStock - $elementSaleNum;
		$info['lastnum'] = $lastStock;
		$info['salenum'] = $elementSaleNum;

		$script = &load_wechat('Script');
		$url = $this->request->url(true);
		$options = $script->getJsSign($url);

		// 处理执行结果
		if ($options === FALSE) {
			// 接口失败的处理
			echo $script->errMsg;
		} else {
			//$options['debug']=true;
		}

		if ($info['headimg']) {
			$img = $info['headimg'][0];
			$info['headimgs'] = $img;
		} else {
			$info['headimgs'] = '';
		}
		$title = "【" . $shopInfo['shopname'] . "专享 | " . $info['destination'] . "】" . $info['title'];

		$shareData = array(
			'title' => $title,
			'logo' => $info['headimgs'],
			'url' => 'http://www.yuanzigo.com/mobile/toc/detail.html?id=' . $id,
			'desc' => $info['subtitle'],
		);

		$assign = array(
			'info' => $info,
			'format' => $formatInfo,
			'tags' => $tagsAy,
			'shop' => $shopInfo,
			'options' => $options,
			'shareData' => $shareData,

		);

		$this->assign($assign);
		return view();
	}

	public function detailtoc() {
		$id = input('id', 0, 'intval');
		$model = new \app\common\model\Activity();
		$info = $model->get($id);
		if (!$info) {
			$this->redirect('err/errpage', ['msg' => '该活动不存在']);
		}
		$info['period'] = $info['period'];
		$info = $info->toArray();
		$thisId = $info['id'];
		$tagsAc = new \app\common\model\TagsActivity();
		$tags = $tagsAc->all(function ($q) use ($thisId) {
			$q->where(['activity_id' => $thisId]);
		});
		$tagsAy = [];
		for ($t = 0; $t < count($tags); $t++) {
			$tagsAy[$t] = $tags[$t]['tagsname'];
		}
		$format = new \app\common\model\ActivityFormat();
		$formatInfo = $format->all(function ($q) use ($thisId) {
			$q->where(['activity_id' => $thisId])->order('xu asc');
		});
		$shopInfo = db('shop')->where(['id' => $info['shop_id']])->field('shopname,headimg,tel')->find();

		$script = &load_wechat('Script');
		$url = $this->request->url(true);
		$options = $script->getJsSign($url);

		// 处理执行结果
		if ($options === FALSE) {
			// 接口失败的处理
			echo $script->errMsg;
		} else {
			//$options['debug']=true;
		}

		if ($info['headimg']) {
			$img = $info['headimg'][0];
			$info['headimgs'] = $img;
		} else {
			$info['headimgs'] = '';
		}
		$title = "【" . $shopInfo['shopname'] . "专享 | " . $info['destination'] . "】" . $info['title'];

		$shareData = array(
			'title' => $title,
			'logo' => $info['headimgs'],
			'url' => 'http://www.yuanzigo.com/mobile/toc/detail.html?id=' . $id,
			'desc' => $info['subtitle'],
		);

		$assign = array(
			'info' => $info,
			'format' => $formatInfo,
			'tags' => $tagsAy,
			'shop' => $shopInfo,
			'options' => $options,
			'shareData' => $shareData,
		);

		$this->assign($assign);
		return view();
	}

	public function copyActivity($id) {
		$shopId = $this->userInfo['rule_id'];
		if (!$shopId || !$id) {
			return false;
		}

		$activity = new \app\common\model\Activity();
		$format = new \app\common\model\ActivityFormat();
		$period = new \app\common\model\ActivityPeriod();
		$tags = new \app\common\model\TagsActivity();
		$ck = db('activity')->where(['element' => $id, 'shop_id' => $shopId])->find();
		if ($ck) {
			return true;
		}
		$activityInfo = $activity->get(function ($q) use ($id) {
			$q->where(['id' => $id])->field('element,title,subtitle,headimg,destination,execution_time,intro,sale_num');
		})->toArray();
		$activityInfo['shop_id'] = $shopId;
		$activityInfo['element'] = $id;
		$res = $activity->save($activityInfo);
		if (!$res) {
			return false;
		}
		$newActivityId = $activity->id;

		$tagsInfo = $tags->all(function ($q) use ($id) {
			$q->where(['activity_id' => $id])->field('tags_id');
		});
		$newTags = [];
		for ($i = 0; $i < count($tagsInfo); $i++) {
			$newTags[$i] = array(
				'tags_id' => $tagsInfo[$i]['tags_id'],
				'activity_id' => $newActivityId,
			);
		}

		$res = $tags->saveAll($newTags);
		if (!$res) {
			db('activity')->where(['id' => $newActivityId])->delete();
			return false;
		}

		$formatInfo = $format->all(function ($q) use ($id) {
			$q->where(['activity_id' => $id])->field('name,price,cost,stock,intro');
		});

		$newFormat = [];
		for ($i = 0; $i < count($formatInfo); $i++) {
			$newFormat[$i] = array(
				'name' => $formatInfo[$i]['name'],
				'price' => $formatInfo[$i]['price'],
				'cost' => $formatInfo[$i]['cost'],
				'stock' => $formatInfo[$i]['stock'],
				'intro' => $formatInfo[$i]['intro'],
				'activity_id' => $newActivityId,
			);
		}
		$res = $format->saveAll($newFormat);
		if (!$res) {
			db('activity')->where(['id' => $newActivityId])->delete();
			db('tags_activity')->where(['activity_id' => $newActivityId])->delete();
			return false;
		}

		$periodInfo = $period->all(function ($q) use ($id) {
			$q->where(['activity_id' => $id])->field('name');
		});

		$newPeriod = [];
		for ($i = 0; $i < count($periodInfo); $i++) {
			$newPeriod[$i] = array(
				'name' => $periodInfo[$i]['name'],
				'activity_id' => $newActivityId,
			);
		}
		$res = $period->saveAll($newPeriod);
		if (!$res) {
			db('activity')->where(['id' => $newActivityId])->delete();
			db('tags_activity')->where(['activity_id' => $newActivityId])->delete();
			db('activity_format')->where(['activity_id' => $newActivityId])->delete();
			return false;
		}
		return true;
	}

	public function getShareInfo() {
		$id = input('id', 0, 'intval');
		$activity = new \app\common\model\Activity();
		$info = $activity->get($id)->toArray();
		$shopInfo = db('shop')->where(['id' => $info['shop_id']])->field('shopname,headimg,tel')->find();

		$info['shopinfo'] = $shopInfo;
		if ($info['headimg']) {
			$img = $info['headimg'][0];
			$info['headimgs'] = $img;
		} else {
			$info['headimgs'] = '';
		}
		$title = "【" . $shopInfo['shopname'] . "专享 | " . $info['destination'] . "】" . $info['title'];

		$shareData = array(
			'title' => $title,
			'logo' => $info['headimgs'],
			'url' => 'http://www.xiaomange.com/mobile/toc/detail.html?id=' . $id,
			'desc' => $info['subtitle'],
		);

		if ($info) {
			return result(1, $shareData);

		} else {
			return result(0, '没找到活动信息');
		}
	}

	public function recommend() {

		$id1 = input('id1', 0, 'intval');
		$id2 = input('id2', 0, 'intval');
		$id3 = input('id3', 0, 'intval');
		$this->assign('id1', $id1);
		$this->assign('id2', $id2);
		$this->assign('id3', $id3);

		$model = new \app\common\model\Activity();
		$info1 = $model->get($id1);
		$info1 = $info1->toArray();
		if ($info1['headimg']) {
			$img = $info1['headimg'][0];
			$info1['headimgs'] = $img;
		} else {
			$info1['headimgs'] = '';
		}
		$shop1 = db('shop')->where(['id' => $info1['shop_id']])->field('shopname')->find();
		$price1 = db('activity_format')->where(['activity_id' => $info1['id']])->find();
		$tag1 = db('tags_activity')->where(['activity_id' => $info1['id']])->field('tags_id')->select();
		foreach ($tag1 as $v) {
			$type1[] = db('tags')->where(['id' => $v['tags_id']])->field('name')->find();
		}
		$data1 = array(
			'info1' => $info1,
			'shop1' => $shop1,
			'price1' => $price1,
			'type1' => $type1,
		);

		$info2 = $model->get($id2);
		$info2 = $info2->toArray();
		if ($info2['headimg']) {
			$img = $info2['headimg'][0];
			$info2['headimgs'] = $img;
		} else {
			$info2['headimgs'] = '';
		}
		$shop2 = db('shop')->where(['id' => $info2['shop_id']])->field('shopname')->find();
		$price2 = db('activity_format')->where(['activity_id' => $info2['id']])->find();
		$tag2 = db('tags_activity')->where(['activity_id' => $info2['id']])->field('tags_id')->select();
		foreach ($tag2 as $v) {
			$type2[] = db('tags')->where(['id' => $v['tags_id']])->field('name')->find();
		}
		$data2 = array(
			'info2' => $info2,
			'shop2' => $shop2,
			'price2' => $price2,
			'type2' => $type2,
		);

		$info3 = $model->get($id3);
		$info3 = $info3->toArray();
		if ($info3['headimg']) {
			$img = $info3['headimg'][0];
			$info3['headimgs'] = $img;
		} else {
			$info3['headimgs'] = '';
		}
		$shop3 = db('shop')->where(['id' => $info3['shop_id']])->field('shopname')->find();
		$price3 = db('activity_format')->where(['activity_id' => $info3['id']])->find();
		$tag3 = db('tags_activity')->where(['activity_id' => $info3['id']])->field('tags_id')->select();
		foreach ($tag3 as $v) {
			$type3[] = db('tags')->where(['id' => $v['tags_id']])->field('name')->find();
		}
		$data3 = array(
			'info3' => $info3,
			'shop3' => $shop3,
			'price3' => $price3,
			'type3' => $type3,
		);

		$this->assign($data1);
		$this->assign($data2);
		$this->assign($data3);

		return view();
	}
}