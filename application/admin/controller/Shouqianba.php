<?php
namespace app\admin\controller;

use app\common\model\Shouqianbas;
use PHPExcel;
use PHPExcel_IOFactory;

class Shouqianba extends Base {

	public function index() {
		$s = input('s', 1, 'intval');
		// $map = ['pay_status' => $s];
		// if ($s == 10) {
		// 	$map = ['create_time' => ['lt', time() - 1800], 'pay_status' => 9];
		// }
		// if ($s == 9) {
		// 	$map['create_time'] = ['gt', time() - 1800];
		// }
		$model = new Shouqianbas('aman_shouqianba');
		$list = $model->order('id desc')->paginate(10);
		// var_dump($list);die;
		$assign = array(
			'list' => $list,
			'status' => $s,
		);
		$this->assign($assign);
		return view();
	}

	public function insert() {
		return view();
	}

	public function detail() {
		$id = input('id', 0, 'intval');
		if (!$id) {
			die;
		}

		$model = new Shouqianbas('aman_shouqianba');
		$info = $model->where(['id' => $id])->find();

		$this->assign(['info' => $info]);
		return view();
	}

	function import() {
		//获取上传文件
		$file = request()->file("excel");
		var_dump($file);
		if ($file) {
			//移动到框架目录
			$ext = date("Y-m-d");
			$info = $file->move(ROOT_PATH . 'public' . DS . 'Uploads' . DS . $ext);
			if ($info) {
				$filename = $info->getSaveName();

				import("Vendor.phpoffice.phpexcel.Classes.PHPExcel");
				$PHPExcel = new \PHPExcel();
				$exts = explode('.', $filename)[1];
				if ($exts == 'xls') {
					// 如果excel文件后缀名为.xls，导入这个类
					import("Vendor.phpoffice.phpexcel.PHPExcel.Reader.Excel5");
				} else
				if ($exts == 'xlsx') {
					import("Vendor.phpoffice.phpexcel.PHPExcel.Reader.Excel2007");
				}
				$cun = ROOT_PATH . 'public' . DS . 'Uploads' . DS . $ext . DS . $filename;
				$objPHPExcel = \PHPExcel_IOFactory::load($cun);

				$sheet = $objPHPExcel->getSheet(0);
				$highestRow = $sheet->getHighestRow(); // 取得总行数

				//循环读取excel表格,读取一条,插入一条
				//j表示从哪一行开始读取  从第二行开始读取，因为第一行是标题不保存
				//$a表示列号
				for ($j = 2; $j <= $highestRow; $j++) {
					$data['time'] = $objPHPExcel->getActiveSheet()->getCell("A" . $j)->getValue() . ' ' . $objPHPExcel->getActiveSheet()->getCell("B" . $j)->getValue();
					$data['order_id'] = $objPHPExcel->getActiveSheet()->getCell("C" . $j)->getValue();
					$data['status'] = $objPHPExcel->getActiveSheet()->getCell("D" . $j)->getValue();
					$data['from'] = $objPHPExcel->getActiveSheet()->getCell("E" . $j)->getValue();
					$data['model'] = $objPHPExcel->getActiveSheet()->getCell("F" . $j)->getValue();
					$data['price'] = $objPHPExcel->getActiveSheet()->getCell("G" . $j)->getValue();
					$data['amount'] = $objPHPExcel->getActiveSheet()->getCell("H" . $j)->getValue();
					$data['try_use'] = $objPHPExcel->getActiveSheet()->getCell("I" . $j)->getValue();
					$data['kou'] = $objPHPExcel->getActiveSheet()->getCell("J" . $j)->getValue();
					$data['post'] = $objPHPExcel->getActiveSheet()->getCell("K" . $j)->getValue();
					$data['shop'] = $objPHPExcel->getActiveSheet()->getCell("L" . $j)->getValue();
					$data['shop_num'] = $objPHPExcel->getActiveSheet()->getCell("M" . $j)->getValue();
					$data['dian'] = $objPHPExcel->getActiveSheet()->getCell("N" . $j)->getValue();
					$data['dian_num'] = $objPHPExcel->getActiveSheet()->getCell("O" . $j)->getValue();
					$data['vender_name'] = $objPHPExcel->getActiveSheet()->getCell("P" . $j)->getValue();
					$data['vender_sn'] = $objPHPExcel->getActiveSheet()->getCell("Q" . $j)->getValue();
					$data['vender_type'] = $objPHPExcel->getActiveSheet()->getCell("R" . $j)->getValue();
					$data['dsn'] = $objPHPExcel->getActiveSheet()->getCell("S" . $j)->getValue();
					$data['user'] = $objPHPExcel->getActiveSheet()->getCell("T" . $j)->getValue();
					$data['save_id'] = $objPHPExcel->getActiveSheet()->getCell("U" . $j)->getValue();
					$data['opreat'] = $objPHPExcel->getActiveSheet()->getCell("V" . $j)->getValue();
					$data['shou'] = $objPHPExcel->getActiveSheet()->getCell("W" . $j)->getValue();
					$data['in_order_id'] = $objPHPExcel->getActiveSheet()->getCell("X" . $j)->getValue();

					if ($data['time']) {
						$res = db('shouqianba')->insert($data); //导入数据库
					}
				}
				if ($res) {
					echo "<script>alert('导入成功！');history.back(-1);</script>";
				} else {
					die("<script>alert('导入出错，请重试！');history.back(-1);</script>");
				}
			}
		} else {
			return view('insert');
		}
	}
}