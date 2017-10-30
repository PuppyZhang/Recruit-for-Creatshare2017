<?php
/**
 * Created by PhpStorm.
 * User: puppy
 * Date: 2017/9/17
 * Time: 18:47
 */
namespace app\backend\controller;

use my\curl;
use think\Db;
use think\Request;

class Account
{
    private $checkUrl = "http://corefuture.cn:8080/outnet/netout/login";
    public function addAccount()
    {
        //获取cookie 外加验证 两步
        $accountNum  = Request::instance()->param("accountNum");
        $directionId = Request::instance()->param("directionId");
        $checkExist  = Db::table("manager")->where("manager_num",$accountNum)->find();
        //验证账号有效性
        $checkData   = ['userName'=>$accountNum];
        $checkTrue   = json_decode(curl::http_post($this->checkUrl,$checkData),true);

        if($checkExist != NULL ) {
            $result = ['result'=>'failed','Msg'=>'-1'];//-1表示该账号已存在
        }else if($checkTrue['Msg'] == "用户名必须为8位;密码不能为空;" || $checkTrue['Msg'] == "密码不能为空;用户名必须为8位;"){
            $result = ['result'=>'failed','Msg'=>'-2'];//-2表示该账号不合理
        }else{
            $accountData = [
                "direction_id"=>$directionId,
                "manager_num" =>$accountNum,
            ];
            $insertResult = Db::table("manager")->insert($accountData);
            if($insertResult == true) {
                $result = ['result'=>'success'];
            }else{
                $result = ['result'=>'failed','Msg'=>'-1']; //表示已经存在
            }
        }
        header("Access-Control-Allow-Origin:*");
        return $result;
    }

    public function getManagerAccount()
    {
        //TODO 获取cookie 外加验证 两步
        $directionId = Request::instance()->param("directionId");
        if($directionId == 0){
            $resultArr = Db::table("manager")->field("manager_name,manager_num")->select();
        }else{
            $resultArr = Db::table("manager")->field("manager_name,manager_num")
                                                   ->where("direction_id",$directionId)->select();
        }
        header("Access-Control-Allow-Origin:*");
        return $resultArr;
    }

    public function deleteManagerAccount()
    {
        //TODO 获取cookie 外加验证 两步
        $managerNumbers = Request::instance()->param("managerIds/a");
        $result = "";
        for($i=0;$i<count($managerNumbers);$i++)
        {
            if($managerNumbers[$i]!=NULL){
                $mid = Db::table("manager")->where("manager_num",$managerNumbers[$i])->delete();
                if($mid == true){
                    $result = ['result'=>'success'];
                }else{
                    $result = ['result'=>'failed'];
                }
            }
        }
        header("Access-Control-Allow-Origin:*");
        return $result;
    }



}
