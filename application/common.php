<?php
use Firebase\JWT\JWT;
use OSS\Core\OssException;
use OSS\OssClient;
// 应用公共文件
function p_arr($arr) {
	echo "<pre>";
	var_dump($arr);
	echo "</pre>";
}
//判断是否是微信登录
function is_weixin() {
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
		return true;
	} else {
		return false;
	}
}

function formatprice($price) {
	return number_format($price, 2);
}

function result($status, $msg) {
	$result = array(
		'status' => $status,
		'msg' => $msg,
	);
	return $result;
}
//对象转数组
function object2array(&$object) {
	$object = json_decode(json_encode($object), true);
	return $object;
}
function ip_info_taobao($ip) {
	$taobaoIP = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;
	$IPinfo = json_decode(file_get_contents($taobaoIP));
	$info = array(
		'country' => $IPinfo->data->country,
		'prov' => $IPinfo->data->region,
		'city' => $IPinfo->data->city,
		'city_id' => $IPinfo->data->city_id,
		'isp' => $IPinfo->data->isp,
		'ip' => $ip,
	);
	return $info;
}
//微信
function &load_wechat($type = '') {
	static $wechat = array();
	$index = md5(strtolower($type));
	if (!isset($wechat[$index])) {
		$url = $_SERVER['SERVER_NAME'];
		if ($url == 'chat.xiaomange.com') {
			$options = array(
				'token' => '', // 填写你设定的key
				'appid' => 'wx34bfb8b717dd996c', // 填写高级调用功能的app id, 请在微信开发模式后台查询
				'appsecret' => '352217d62b9ac98fca376a13c28a2442', // 填写高级调用功能的密钥
				'encodingaeskey' => '', // 填写加密用的EncodingAESKey（可选，接口传输选择加密时必需）
				'mch_id' => '1502342621', // 微信支付，商户ID（可选）
				'partnerkey' => 'welcometoliangzichufa20180418338', // 微信支付，密钥（可选）
				'ssl_cer' => ROOT_PATH . 'public/static/cert_liangzi/apiclient_cert.pem', // 微信支付，双向证书（可选，操作退款或打款时必需）
				'ssl_key' => ROOT_PATH . 'public/static/cert_liangzi/apiclient_key.pem', // 微信支付，双向证书（可选，操作退款或打款时必需）
				'cachepath' => '', // 设置SDK缓存目录（可选，默认位置在Wechat/Cache下，请保证写权限）
			);
			
		} else {
			$options = array(
				'token' => 'YuanZiGo', // 填写你设定的key
				'appid' => 'wx5a4dd3805c7c738f', // 填写高级调用功能的app id, 请在微信开发模式后台查询
				'appsecret' => '00c14250c08090cb6dbc900d9c139cba', // 填写高级调用功能的密钥
				'encodingaeskey' => '', // 填写加密用的EncodingAESKey（可选，接口传输选择加密时必需）
				'mch_id' => '1470204002', // 微信支付，商户ID（可选）
				'partnerkey' => 'welcometoyuanzichufacorp20170428', // 微信支付，密钥（可选）
				'ssl_cer' => ROOT_PATH . 'public/static/cert_yuanzi/apiclient_cert.pem', // 微信支付，双向证书（可选，操作退款或打款时必需）
				'ssl_key' => ROOT_PATH . 'public/static/cert_yuanzi/apiclient_key.pem', // 微信支付，双向证书（可选，操作退款或打款时必需）
				'cachepath' => '', // 设置SDK缓存目录（可选，默认位置在Wechat/Cache下，请保证写权限）
			);
		}
		\Wechat\Loader::config($options);
		$wechat[$index] = \Wechat\Loader::get($type);
	}
	return $wechat[$index];
}

//获取周几
function wk($time) {
	if (strstr('-', $time)) {

	} else {
		$time = date('Y-m-d', $time);
	}
	$datearr = explode("-", $time); //将传来的时间使用“-”分割成数组
	$year = $datearr[0]; //获取年份
	$month = sprintf('%02d', $datearr[1]); //获取月份
	$day = sprintf('%02d', $datearr[2]); //获取日期
	$hour = $minute = $second = 0; //默认时分秒均为0
	$dayofweek = mktime($hour, $minute, $second, $month, $day, $year); //将时间转换成时间戳
	$shuchu = date("w", $dayofweek); //获取星期值
	$weekarray = array("周日", "周一", "周二", "周三", "周四", "周五", "周六");
	return $weekarray[$shuchu];
}

function nDay($q = "") {
	$text = '';
	$now = time();
	if ($q === 1) {
// 今天
		$text = '今天';
		$beginTime = date('Y-m-d 00:00:00', $now);
		$endTime = date('Y-m-d 23:59:59', $now);
	} elseif ($q === 2) {
// 昨天
		$text = '昨天';
		$time = strtotime('-1 day', $now);
		$beginTime = date('Y-m-d 00:00:00', $time);
		$endTime = date('Y-m-d 23:59:59', $time);
	} elseif ($q === 3) {
// 三天内
		$text = '三天内';
		$time = strtotime('-2 day', $now);
		$beginTime = date('Y-m-d 00:00:00', $time);
		$endTime = date('Y-m-d 23:59:59', $now);
	} elseif ($q === 4) {
// 本周
		$text = '本周';
		$time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);
		$beginTime = date('Y-m-d 00:00:00', $time);
		$endTime = date('Y-m-d 23:59:59', strtotime('Sunday', $now));
	} elseif ($q === 5) {
// 上周
		$text = '上周';
		// 本周一
		$time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);
		// 上周一
		$lastMonday = strtotime('-7 days', $time);
		$beginTime = date('Y-m-d 00:00:00', $time);
		$endTime = date('Y-m-d 23:59:59', strtotime('last sunday', $time));
	} elseif ($q === 6) {
// 本月
		$text = '本月';
		$beginTime = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m', $now), '1', date('Y', $now)));
		$endTime = date('Y-m-d 23:39:59', mktime(0, 0, 0, date('m', $now), date('t', $now), date('Y', $now)));
	} elseif ($q === 7) {
// 三月内
		$text = '三月内';
		$time = strtotime('-2 month', $now);
		$beginTime = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m', $time), 1, date('Y', $time)));
		$endTime = date('Y-m-d 23:39:59', mktime(0, 0, 0, date('m', $now), date('t', $now), date('Y', $now)));
	} elseif ($q === 8) {
// 半年内
		$text = '半年内';
		$time = strtotime('-5 month', $now);
		$beginTime = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m', $time), 1, date('Y', $time)));
		$endTime = date('Y-m-d 23:39:59', mktime(0, 0, 0, date('m', $now), date('t', $now), date('Y', $now)));
	} elseif ($q === 9) {
// 一年内
		$text = '一年内';
		$beginTime = date('Y-m-d 00:00:00', mktime(0, 0, 0, 1, 1, date('Y', $now)));
		$endTime = date('Y-m-d 23:39:59', mktime(0, 0, 0, 12, 31, date('Y', $now)));
	} elseif ($q === 10) {
// 三年内
		$text = '三年内';
		$time = strtotime('-2 year', $now);
		$beginTime = date('Y-m-d 00:00:00', mktime(0, 0, 0, 1, 1, date('Y', $time)));
		$endTime = date('Y-m-d 23:39:59', mktime(0, 0, 0, 12, 31, date('Y')));
	} elseif ($q === 11) {
		$text = '上月';
		$time = strtotime('-1 month', $now);
		$beginTime = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m', $time), 1, date('Y', $time)));
		$endTime = date('Y-m-d 23:39:59', mktime(0, 0, 0, date('m', $time), date('t', $time), date('Y', $time)));
	} elseif ($q === 12) {
		$text = '前天';
		$time = strtotime('-2 day', $now);
		$beginTime = date('Y-m-d 00:00:00', $time);
		$endTime = date('Y-m-d 23:59:59', $time);
	} elseif ($q === 13) {
		$text = '大前天';
		$time = strtotime('-3 day', $now);
		$beginTime = date('Y-m-d 00:00:00', $time);
		$endTime = date('Y-m-d 23:59:59', $time);
	}
	if ($q != "") {
		$nDay = array(
			'text' => $text,
			'beginTime' => strtotime($beginTime),
			'endTime' => strtotime($endTime),
		);
	} else {
		$nDay = array(
			'1' => '今天',
			'2' => '昨天',
			'3' => '三天内',
			'4' => '本周',
			'5' => '上周',
			'6' => '本月',
			'7' => '三月内',
			'8' => '半年内',
			'9' => '一年内',
			'10' => '三年内',
			'11' => '上月',
			'12' => '前天',
			'13' => '大前天',
		);

	}
	return $nDay;
}

/*
以下是关于微信文章采集的
 */
function getWeixinArtice($url) {
	$content = file_get_contents($url);
	preg_match_all('/id="js_content">(.*?)<script/iUs', $content, $contentAy);
	$content = str_replace('id="js_content">', '', $contentAy[0]);
	$content = $content[0];
	if (strstr($content, '<script')) {
		$content = substr($content, 0, strpos($content, '<script'));
	}
	$ext = 'jpg|jpeg|gif|bmp|png';
	$img_list = array();
	$img_list = img_match($content, $ext);
	for ($i = 0; $i < count($img_list); $i++) {
		if (isset($img_list[$i]['src'])) {
			if ($img_list[$i]['src']) {
				$newpic = getWechatPic($img_list[$i]['src']);
				if ($newpic) {
					$content = str_replace($img_list[$i]['src'], $newpic . '" id="' . time() . rand(1, 99) . '" class="img-responsive', $content);
					$content = str_replace('data-src', 'src', $content);
				}
			}
		}
	}
	/* if(strstr($content,'<script')){
		$content=substr($content,0,strstr($content,'<script'));
		} */
	return $content;
}
function getWeixinArticeByCopy($content) {
	$ext = 'jpg|jpeg|gif|bmp|png';
	$img_list = array();
	$img_list = img_match($content, $ext);
	for ($i = 0; $i < count($img_list); $i++) {
		if (isset($img_list[$i]['src'])) {
			if ($img_list[$i]['src']) {
				$newpic = getWechatPic($img_list[$i]['src']);
				if ($newpic) {
					$content = str_replace($img_list[$i]['src'], $newpic . '" id="' . time() . rand(1, 99) . '" class="img-responsive', $content);

				}
			}
		}
	}
	//$content=substr_replace('/data-src="(.*?)"/i','',$content);
	return $content;
}
function img_match($str, $ext) {
	$list = array();
	$c1 = preg_match_all('/<img\s.*?>/', $str, $m1);
	for ($i = 0; $i < $c1; $i++) {
		$c2 = preg_match_all('/(\w+)\s*=\s*(?:(?:(["\'])(.*?)(?=\2))|([^\/\s]*))/', $m1[0][$i], $m2);
		for ($j = 0; $j < $c2; $j++) {
			$list[$i][$m2[1][$j]] = !empty($m2[4][$j]) ? $m2[4][$j] : $m2[3][$j];
		}
	}
	return $list;
}
function getWechatPic($url) {
	$pathDir = ROOT_PATH . 'public' . DS . 'Uploads/' . Date('Y-m-d');
	if (!is_dir($pathDir)) {
		mkdir($pathDir, 0777);
	}
	$path = $pathDir . '/'; // 上传路径
	$urlAy = explode('/', $url);
	if (strstr($url, 'mmbiz') && count($urlAy) == 6) {
		$type = str_replace('mmbiz_', '.', $urlAy[3]);
	} else {
		$type = '.jpg';
	}

	$picname = time() . rand(1, 99);
	$name = $picname . $type;
	$savename = 'Uploads/' . Date('Y-m-d') . '/' . $name;

	$ch = curl_init();
	$httpheader = array(
		'Host' => 'mmbiz.qpic.cn',
		'Connection' => 'keep-alive',
		'Pragma' => 'no-cache',
		'Cache-Control' => 'no-cache',
		'Accept' => 'textml,application/xhtml+xml,application/xml;q=0.9,image/webp,/;q=0.8',
		'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36',
		'Accept-Encoding' => 'gzip, deflate, sdch',
		'Accept-Language' => 'zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4',
	);
	$options = array(
		CURLOPT_HTTPHEADER => $httpheader,
		CURLOPT_URL => $url,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_FOLLOWLOCATION => 1,
		CURLOPT_RETURNTRANSFER => true,
	);
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close($ch);
	if (file_put_contents($savename, $result)) {
		return getSiteUrl() . '/' . $savename;
	} else {
		return 0;
	}
}

function fixHtmlTag($param = array()) {
	//参数的默认值
	$html = '';
	$tagArray = array();
	$type = 'NEST';
	$length = null;
	$lowerTag = TRUE;
	$XHtmlFix = TRUE;

	//首先获取一维数组，即 $html 和 $options （如果提供了参数）
	extract($param);

	//如果存在 options，提取相关变量
	if (isset($options)) {
		extract($options);
	}

	$result = ''; //最终要返回的 html 代码
	$tagStack = array(); //标签栈，用 array_push() 和 array_pop() 模拟实现
	$contents = array(); //用来存放 html 标签
	$len = 0; //字符串的初始长度

	//设置闭合标记 $isClosed，默认为 TRUE, 如果需要就近闭合，成功匹配开始标签后其值为 false,成功闭合后为 true
	$isClosed = true;

	//将要处理的标签全部转为小写
	$tagArray = array_map('strtolower', $tagArray);

	//“合法”的单闭合标签
	$singleTagArray = array(
		'<meta',
		'<link',
		'<base',
		'<br',
		'<hr',
		'<input',
		'<img',
	);

	//校验匹配模式 $type，默认为 NEST 模式
	$type = strtoupper($type);
	if (!in_array($type, array('NEST', 'CLOSE'))) {
		$type = 'NEST';
	}

	//以一对 < 和 > 为分隔符，将原 html 标签和标签内的字符串放到数组中
	$contents = preg_split("/(<[^>]+?>)/si", $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

	foreach ($contents as $tag) {
		if ('' == trim($tag)) {
			$result .= $tag;
			continue;
		}

		//匹配标准的单闭合标签，如<br />
		if (preg_match("/<(\w+)[^\/>]*?\/>/si", $tag)) {
			$result .= $tag;
			continue;
		}

		//匹配开始标签，如果是单标签则出栈
		else if (preg_match("/<(\w+)[^\/>]*?>/si", $tag, $match)) {
			//如果上一个标签没有闭合，并且上一个标签属于就近闭合类型
			//则闭合之，上一个标签出栈

			//如果标签未闭合
			if (false === $isClosed) {
				//就近闭合模式，直接就近闭合所有的标签
				if ('CLOSE' == $type) {
					$result .= '</' . end($tagStack) . '>';
					array_pop($tagStack);
				}
				//默认的嵌套模式，就近闭合参数提供的标签
				else {
					if (in_array(end($tagStack), $tagArray)) {
						$result .= '</' . end($tagStack) . '>';
						array_pop($tagStack);
					}
				}
			}

			//如果参数 $lowerTag 为 TRUE 则将标签名转为小写
			$matchLower = $lowerTag == TRUE ? strtolower($match[1]) : $match[1];

			$tag = str_replace('<' . $match[1], '<' . $matchLower, $tag);
			//开始新的标签组合
			$result .= $tag;
			array_push($tagStack, $matchLower);

			//如果属于约定的的单标签，则闭合之并出栈
			foreach ($singleTagArray as $singleTag) {
				if (stripos($tag, $singleTag) !== false) {
					if ($XHtmlFix == TRUE) {
						$tag = str_replace('>', ' />', $tag);
					}
					array_pop($tagStack);
				}
			}

			//就近闭合模式，状态变为未闭合
			if ('CLOSE' == $type) {
				$isClosed = false;
			}
			//默认的嵌套模式，如果标签位于提供的 $tagArray 里，状态改为未闭合
			else {
				if (in_array($matchLower, $tagArray)) {
					$isClosed = false;
				}
			}
			unset($matchLower);
		}

		//匹配闭合标签，如果合适则出栈
		else if (preg_match("/<\/(\w+)[^\/>]*?>/si", $tag, $match)) {

			//如果参数 $lowerTag 为 TRUE 则将标签名转为小写
			$matchLower = $lowerTag == TRUE ? strtolower($match[1]) : $match[1];

			if (end($tagStack) == $matchLower) {
				$isClosed = true; //匹配完成，标签闭合
				$tag = str_replace('</' . $match[1], '</' . $matchLower, $tag);
				$result .= $tag;
				array_pop($tagStack);
			}
			unset($matchLower);
		}

		//匹配注释，直接连接 $result
		else if (preg_match("/<!--.*?-->/si", $tag)) {
			$result .= $tag;
		}

		//将字符串放入 $result ，顺便做下截断操作
		else {
			if (is_null($length) || $len + mb_strlen($tag) < $length) {
				$result .= $tag;
				$len += mb_strlen($tag);
			} else {
				$str = mb_substr($tag, 0, $length - $len + 1);
				$result .= $str;
				break;
			}
		}
	}

	//如果还有将栈内的未闭合的标签连接到 $result
	while (!empty($tagStack)) {
		$result .= '</' . array_pop($tagStack) . '>';
	}
	return $result;
}

//根据URL获取二维码
function getUrlQr($url, $scene_id) {

	$qrCode = new Endroid\QrCode\QrCode($url);
	$qrCode->setSize(300);
	$qrCode
		->setWriterByName('png')
		->setMargin(10)
		->setEncoding('UTF-8')
		->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0])
		->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255])
		->setValidateResult(false);
	$name = $scene_id . '.png';
	$pathDir = ROOT_PATH . 'public' . DS . 'Uploads/qrcode/url/';
	if (!is_dir($pathDir)) {
		mkdir($pathDir, 0777);
	}
	$savename = 'http://' . $_SERVER['SERVER_NAME'] . '/' . 'Uploads/qrcode/url/' . $name;
	$path = $pathDir . '/'; // 上传路径
	saveFile($path . $name, $qrCode->writeString());
	return $savename;
}

//保存文件到本地
function saveFile($filename, $filecontent) {
	$local_file = fopen($filename, 'w');
	if (false !== $local_file) {
//不恒等于（恒等于=== 就是false只能等于false，而不等于0）
		if (false !== fwrite($local_file, $filecontent)) {
			fclose($local_file);
		}
	}
}

//阿里云
function new_oss() {
	$config = config('aliyun_oss');
	$oss = new \OSS\OssClient($config['KeyId'], $config['KeySecret'], $config['Endpoint']);
	return $oss;
}
/**
 * 上传指定的本地文件内容
 *
 * @param OssClient $ossClient OSSClient实例
 * @param string $bucket 存储空间名称
 * @param string $object 上传的文件名称
 * @param string $Path 本地文件路径
 * @return null
 */
function upOss($bucket, $object, $Path) {
	try {
		$ossClient = new_oss();
		$ossClient->uploadFile($bucket, $object, $Path);
	} catch (OssException $e) {
		return $e->getMessage();
	}
	return true;
}

function jwt_ck($jwt) {
	$key = "yuanzigo";
	try {
		$userinfo = JWT::decode($jwt, $key, array('HS256'));
		return $userinfo;
	} catch (Exception $e) {
		return false;
	}
}

function jwt_code($jwt) {
	$key = "yuanzigo";
	try {
		$userinfo = JWT::decode($jwt, $key, array('HS256'));
		return $userinfo;
	} catch (Exception $e) {
		return false;
	}
}
//判断数据不是JSON格式:
function is_not_json($str) {
	return is_null(json_decode($str));
}

//判断数据是合法的json数据: (PHP版本大于5.3)
function is_json($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

//
function filterEmoji($str) {
	$str = preg_replace_callback(
		'/./u',
		function (array $match) {
			return strlen($match[0]) >= 4 ? '' : $match[0];
		},
		$str);
	return $str;
}

function mkMyDir($pathDir) {
	$pathAy = explode('/', $pathDir);

	for ($i = 0; $i < count($pathAy); $i++) {
		$path = $pathAy[$i];
		if ($path == '.') {
			$newPath = '.';
			$url = '';
		} else {
			if ($path != '') {
				$newPath = $newPath . '/' . $path;
				$url = $url . '/' . $path;
			}
			if (!is_dir($newPath)) {
				mkdir($newPath, 0777);
			}
		}
	}
	return ['truepath' => $newPath . '/', 'url' => $url . '/'];
}

function getQrCode($msg, $name = '', $path = '') {
	if ($name == '') {
		$name = time();
	}
	if ($path == '') {
		$path = './Uploads/qrcode/' . date('Y-m-d') . '/';
	} else {
		$path = './Uploads/' . $path;
	}
	$png = new \BaconQrCode\Renderer\Image\Png();
	$png->setHeight(256);
	$png->setWidth(256);
	$writer = new \BaconQrCode\Writer($png);
	$pathDir = mkMyDir($path);
	$img = $pathDir['truepath'] . $name . '.png';
	$url = $pathDir['url'] . $name . '.png';
	$writer->writeFile($msg, $img);
	return $url;
}

