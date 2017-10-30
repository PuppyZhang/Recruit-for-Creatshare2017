<?php
namespace app\front\controller;

use my\curl;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Account extends Controller
{
    private $checkUrl = "http://corefuture.cn:8080/outnet/netout/login";
    public function login()
    {
        session_start();
        $session_id = session_id();
        $userName = Request::instance()->param("userName");
        $passWord = Request::instance()->param("passWord");
        $checkExist = Db::table("account")->where("account_type",0)
                                                ->where("account_username",$userName)
                                                ->find();
        if($checkExist == NULL) {
            $studentId = $this->firstLogin($userName,$passWord);
            if($studentId == -1) {
                $result = ['result'=>'failed' ,'Msg'=>'-1'];//-1密码或账号不正确
            }else{
                $result = ['result'=>'success','Msg'=>$studentId,"status"=>$this->getStatus($studentId)];
                Session::set($session_id,"username".$userName);
            }
        }else{
            if($checkExist['account_username'] == $userName && $checkExist['account_password'] == md5($passWord) && $checkExist['account_type'] == 0 ) {
                $studentId = Db::table("student")->where("account_id",$checkExist['account_id'])
                                                       ->find();
                $status = $this->getStatus($studentId['student_num']);
                $result = ['result'=>'success','Msg'=>$studentId['student_id'],"status"=>$status];
                Session::set($session_id,"username".$userName);
            }else{
                $result = ['result'=>'failed','Msg'=>-2];
            }
        }
        header("Access-Control-Allow-Origin:join.changxiaoyuan.com");
        return $result;
    }

    private function firstLogin($userName,$passWord)
    {
        $info = [
            'userName'=>$userName,
            'userPassword'=>$passWord
        ];
        $rst = -1;
        $result = json_decode(curl::http_post($this->checkUrl,$info),true);
        if($result['IsSucceed'] == true) {
            $account_data = [
                'account_type'     => 0,
                'account_username' => $userName,
                'account_password' => md5($passWord),
            ];
            if (Db::table("account")->insert($account_data)) {
                $accountId = Db::table("account")->getLastInsID();
                $accountInfo = $result['Obj'];
                $studentData = [
                    'account_id'    => $accountId,
                    'student_num'   => $userName,
                    'student_name'  => $accountInfo['NAME'],
                    'student_sex'   => $accountInfo['SEX'],
                    'student_class' => $accountInfo['BJ'],
                ];
                if (Db::table("student")->insert($studentData)) {
                    $rst = Db::table("student")->getLastInsID();
                }
            }
        }
        return $rst;
    }

    private function getStatus($studentNum)
    {
        $check = DB::table("audition")->where("student_num",$studentNum)->find();
        if($check == NULL) {
            $result = 0;
        } else {
            $result = 1;
        }
        return $result;
    }

    public function getAllScore()
    {
        $studentNum  = Request::instance()->param("number");
        if(!$this->check($studentNum)) {
        return ['result'=>'fail','Msg'=>'not login'];
    }
        $information = [];
        $studentArr  = Db::table("student")->where("student_num",$studentNum)->find();
        $information['direction'] = action("backend/Audition/getDirectionName",$studentArr['direction_id']);
        $information['name']  = $studentArr['student_name'];
        if($studentArr['student_sex'] == 1 ) {
            $information['sex']  = "男";
        } else {
            $information['sex']  = "女";
        }
        $information['tel']   = $studentArr['student_phone'];
        $information['class'] = $studentArr['student_class'];
        $information['mail']  = $studentArr['student_email'];
        $information['reason']= $studentArr['student_choice_reason'];
        $information['chushi']= $this->getAuditionAllInfo($studentNum,"初试");
        $information['fushi'] = $this->getAuditionAllInfo($studentNum,"复试");
        header("Access-Control-Allow-Origin:join.changxiaoyuan.com");
        return $information;
    }

    private function getAuditionAllInfo($studentNum,$auditionType)
    {
        $auditionArr = Db::table("audition")->where("student_num",$studentNum)
            ->where("audition_type",$auditionType)
            ->find();
        $auditionId  = $auditionArr['audition_id'];
        $resultArr   = Db::table("score")->where("audition_id",$auditionId)->find();
        if($resultArr != NULL) {
            $resultArr['sum_first'] = $resultArr['html']+$resultArr['php']+$resultArr['wangluoxieyi']+$resultArr['tiyan'];
            $resultArr['sum_second']= $resultArr['team']+$resultArr['study']+$resultArr['talk']+$resultArr['guina']+$resultArr['luoji']+$resultArr['shijian']+$resultArr['geren']+$resultArr['renmai']+$resultArr['xinge'];
            $resultArr['sum_third'] = $resultArr['handcode']+$resultArr['pagecolumn']+$resultArr['jsframe']+$resultArr['webstandard']+$resultArr['match'];
        }
        $managerArr  = Db::table("manager")->where("manager_num",$auditionArr['manager_num'])->find();
        $resultArr['date'] = $auditionArr['audition_time'];
        if($managerArr['manager_name'] == NULL){
            $resultArr['ephor'] = "";
        }
        $resultArr['ephor']  = $managerArr['manager_name'];
        $resultArr['pinjia'] = $auditionArr['audition_evaluate'];
        $resultArr['beizhu'] = $auditionArr['audition_supplement'];
        $resultArr['status'] = $auditionArr['audition_status'];
        header("Access-Control-Allow-Origin:*");
        return $resultArr;
    }

    private function check($studentId)
    {
        session_start();
        $sessionId = session_id();
        $result = Session::get($sessionId);
        if("username".$studentId == $result) {
            return 1;
        }else{
            return 0;
        }
    }

    public function test()
    {

    }
}
