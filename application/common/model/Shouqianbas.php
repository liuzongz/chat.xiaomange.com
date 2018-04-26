<?php
namespace app\common\model;
use think\Model;

class Shouqianbas extends Model {
	protected $table = 'aman_shouqianba';

	public function __construct($table) {
		$this->table = $table;
	}

	public function getInfo() {
		return $this->select();
	}

}