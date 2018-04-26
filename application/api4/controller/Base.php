<?php
namespace app\api4\controller;
use think\Controller;
use think\Request;
class Base extends Controller{
    public function _initialize(){        
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
    }
}