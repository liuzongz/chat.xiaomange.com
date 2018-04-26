<?php
namespace app\mobile\controller;
use think\Controller;
use think\Model;
use think\Request;

class Passport extends Controller {
	public function _initialize() {
		$this->request = Request::instance();
		$this->weixinInfo = cookie('weixinInfo');
		if (!$this->weixinInfo) {
			$url = url('Wechat/getWeixinInfo') . '?jump=' . $this->request->url(true);
			$this->redirect($url);
		}
		$this->assign('weixin', $this->weixinInfo);
	}

	public function reg() {
		$tags = new \app\common\model\Tags();
		$tagsList = $tags->all();
		$data = db('data')->find();
		$activity = new \app\common\model\Activity();
		$list = $activity->where(['hot' => 1])->limit(3)->select();
		for ($i = 0; $i < count($list); $i++) {
			if ($list[$i]['headimg']) {
				$img = $list[$i]['headimg'][0];
				$list[$i]['headimgs'] = $img;
			} else {
				$list[$i]['headimgs'] = '';
			}
			if (mb_strlen($list[$i]['title'], 'utf8') > 10) {
				$list[$i]['title'] = mb_substr($list[$i]['title'], 0, 10, 'UTF-8') . '...';
			}
		}
		$url = 'http://' . $_SERVER['SERVER_NAME'];

		$assign = array(
			'tags' => $tagsList,
			'list' => $list,
			'url' => $url,
			'data' => $data,
		);

		$this->assign($assign);
		return view();
	}

	public function regShop() {
		$mobile = input('mobile');
		$city = input('city');
		$tags = input('tags');
		if (!$mobile) {
			return result(0, '请输入手机号码');
		}

		if (!$city) {
			return result(0, '请选择城市');
		}

		if (!$tags) {
			return result(0, '请至少选择一个关注的领域');
		}

		$tags = explode(',', $tags);

		//开始插入数据
		//先插入店铺数据，获取店铺ID，然后插入User数据，最后插入Member数据
		//店铺数据

		$shopData = array(
			'shopname' => $this->weixinInfo['nickname'] . '的小店',
			'headimg' => $this->weixinInfo['headimgurl'],
			'tel' => $mobile,
			'city' => $city,
			'shop_type' => 1,
			'create_time' => time(),
			'update_time' => time(),
		);
		$shop_id = db('shop')->insertGetId($shopData);
		if (!$shop_id) {
			return result(0, '插入店铺数据有误');
		}
		//插入店铺标签
		$mTags = new \app\common\model\TagsShop();
		for ($i = 0; $i < count($tags); $i++) {
			$tagsData[$i] = array(
				'tags_id' => $tags[$i],
				'shop_id' => $shop_id,
			);
		}

		$res = $mTags->saveAll($tagsData);
		if (!$res) {
			return result(0, '标签插入失败');
		}
		//用户数据
		$userData = array(
			'name' => $this->weixinInfo['nickname'],
			'password' => md5('yz123456'),
			'openid' => $this->weixinInfo['openid'],
			'rule' => 2,
			'rule_id' => $shop_id,
		);
		$user = new \app\common\model\User();
		$user->save($userData);
		$user_id = $user->id;
		if (!$user_id) {
			return result(0, '插入用户数据有误');
		}

		//把新插入的用户的ID更新到店铺信息中
		$res = db('shop')->where(['id' => $shop_id])->setField('uid', $user_id);
		if (!$res) {
			return result(0, '更新店铺数据有误');
		}

		//Member数据
		$memberData = array(
			'name' => $this->weixinInfo['nickname'],
			'nickname' => $this->weixinInfo['nickname'],
			'headimgurl' => $this->weixinInfo['headimgurl'],
			'openid' => $this->weixinInfo['openid'],
			'shop_id' => $shop_id,
			'create_time' => time(),
			'update_time' => time(),
		);
		$ck = db('member')->where(['openid' => $this->weixinInfo['openid']])->find();
		if ($ck) {
			$res = db('member')->where(['openid' => $this->weixinInfo['openid']])->setField('shop_id', $shop_id);
		} else {
			$res = db('member')->insert($memberData);
			if (!$res) {
				return result(0, '插入Member数据有误');
			}
		}

		return result(1, '注册成功');
	}
}