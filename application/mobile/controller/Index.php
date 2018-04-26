<?php
namespace app\mobile\controller;
use think\Model;
use think\Request;
class Index extends Base{
	public function ask(){
	    return view();
	}
	public function index(){
	    $this->redirect(url('shop/index'));
	}
}