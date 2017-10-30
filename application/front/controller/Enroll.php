<?php
/**
 * Created by PhpStorm.
 * User: puppy
 */
namespace app\front\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Enroll extends Controller
{
    public function supplyInfo()
    {
        $studentId    = Request::instance()->param("studentId");//学生学号
        $directionId  = Request::instance()->param("directionId");
        $studentPhone = Request::instance()->param("studentPhone");
        $studentMail  = Request::instance()->param("studentMail");
        $choseReason  = Request::instance()->param("choseReason");

        if(!$this->check($studentId)) {
            return ['result'=>'fail','Msg'=>'not login'];
        }else{
            $middle = Db::table("student")->where("student_num",$studentId)->find();
            if($middle['direction_id']!=NULL){
                return ['result'=>'failed','Msg'=>'has existed'];
            }
        }
        $studentData  = [
                'direction_id' =>$directionId,
                'student_phone'=>$studentPhone,
                'student_email' =>$studentMail,
                'student_choice_reason'=>$choseReason
            ];
        $account = Db::table("account")->where("account_username",$studentId)->find();
        $operateResult = Db::table("student")->where("account_id",$account['account_id'])->update($studentData);

        if($operateResult == true) {
            $data = [
                'student_num'      => $studentId,
                'audition_type'   => '初试',
                'audition_status' => '未面试',
                'direction_id'    => $directionId,
            ];
            $mid = Db::table("audition")->insert($data);
            $insertId = Db::table("audition")->getLastInsID();
            $data = ['audition_id'=>$insertId];
            Db::table("score")->insert($data);
            $data = [
                'student_num'      => $studentId,
                'audition_type'   => '复试',
                'audition_status' => '未面试',
                'direction_id'    => $directionId,
            ];
            $middle = Db::table("audition")->insert($data);
            $insertId = Db::table("audition")->getLastInsID();
            $data = ['audition_id'=>$insertId];
            Db::table("score")->insert($data);
            if($mid == true && $middle == true) {
                $result = ['result'=>'success'];
            }else{
                $result = ['result'=>'failed'];
            }
        } else {
            $result = ['result'=>'failed'];
        }
        return $result;
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


}