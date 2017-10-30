<?php
/**
 * Created by PhpStorm.
 * User: puppy
 */
namespace app\backend\controller;

use my\curl;
use think\Controller;
use think\Db;
use think\Request;

class Manager extends Controller
{
    private $checkUrl = "http://corefuture.cn:8080/outnet/netout/login";

    public function login()
    {
        $userName  = Request::instance()->param("userName");
        $passWord  = Request::instance()->param("passWord");
        $checkExist = Db::table("manager")->where("manager_num",$userName)->find();
        $direction = $checkExist['direction_id'];
        if( $checkExist == NULL ) {
            $result = [ 'result'=>'failed','Msg'=>'-1' ];//result = -1 用户名不存在
        }else if( $userName == NULL || $passWord == NULL) {
            $result = [ 'result'=>'failed','Msg'=>'-2' ];//result = -2 用户名密码不能为空
        }else{
            $checkArr = Db::table("account")->where("account_username",$userName)
                                                  ->find();
            if($checkArr == NULL) {
                $addResult = $this->firstLogin( $userName, $passWord ,$checkExist['manager_id']);
                if($addResult == false){
                    $result = ['result'=>'failed','Msg'=>'-3'];//result = -3密码错误
                }else{
                    $direction = $checkExist['direction_id'];
                    $result    = ['result'=>'success','Msg'=>$direction];
                }
            }else{
                if(md5($passWord) != $checkArr['account_password']){
                    $result = ['result'=>'failed','Msg'=>'-3']; //result = -3密码错误
                }else{
                    $result = ['result'=>'success','Msg'=>$direction];
                }
            }
        }
        header("Access-Control-Allow-Origin:*");
        return $result;
    }

    private function firstLogin( $userName, $passWord ,$managerId )
    {
        $data = [
                    'userName'    =>$userName,
                    'userPassword'=>$passWord
        ];
        $result = false;
        $infoArr = json_decode(curl::http_post($this->checkUrl,$data),true);
        if($infoArr['IsSucceed'] == true) {
            $account_data = [
                'account_type'     => 1,
                'account_username' => $userName,
                'account_password' => md5($passWord),
            ];
            if (Db::table("account")->insert($account_data)) {
                $accountId = Db::table("account")->getLastInsID();
                $managerInfo = $infoArr['Obj'];
                $managerData = [
                    'account_id'    => $accountId,
                    'manager_name'  => $managerInfo['NAME'],
                    'manager_sex'   => $managerInfo['SEX'],
                    'manager_class' => $managerInfo['BJ'],
                ];
                $result = Db::table("manager")->where("manager_id",$managerId)
                                                    ->update($managerData);
            }
        }else{
            $result = false;
        }
        return $result;
    }

    public function getManagerInfo()
    {
        $number = Request::instance()->param("number");
        return Db::table("manager")->where("manager_num",$number)->field("direction_id,manager_num,manager_name")->find();
    }

    public function getStudentInfo()
    {
        $number = Request::instance()->param("number");
        $result = Db::table("student")->where("student_num",$number)
                                            ->field("student_num,student_name,student_class,direction_id,student_phone,student_email,student_choice_reason")
                                            ->find();
        return $result;
    }

}