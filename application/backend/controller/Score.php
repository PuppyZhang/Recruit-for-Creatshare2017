<?php
/**
 * Created by PhpStorm.
 * User: puppy
 * Date: 2017/9/24
 * Time: 17:08
 */
namespace app\backend\controller;

use think\Controller;
use think\Db;
use think\Request;

class Score extends Controller
{
    public function saveScore()
    {
        $auditionType = Request::instance()->param("auditionType");//初试/复试
        $studentNum   = Request::instance()->param("studentNum");//学生学号
        $managerNum   = Request::instance()->param("managerNum");
        $skillHtml    = Request::instance()->param("skillHtml");
        $skillPhp     = Request::instance()->param("skillPhp");
        $skillWLXY    = Request::instance()->param("skillWangLuoXieYi");
        $skillFeel    = Request::instance()->param("skillTiYan");
        $skillTeam    = Request::instance()->param("skillTeam");
        $skillStudy   = Request::instance()->param("skillStudy");
        $skillTalk    = Request::instance()->param("skillTalk");
        $skillGuiNa   = Request::instance()->param("skillGuiNa");
        $skillLuoJi   = Request::instance()->param("skillLuoJi");
        $skillShiJian = Request::instance()->param("skillShiJian");
        $skillGeRen   = Request::instance()->param("skillGeRen");
        $skillRenMai  = Request::instance()->param("skillRenMai");
        $skillXinGe   = Request::instance()->param("skillXinGe");
        $skillHandCode= Request::instance()->param("skillHandCode");
        $skillPageColumn = Request::instance()->param("skillPageColumn");
        $skillJsFrame = Request::instance()->param("skillJsFrame");
        $skillWebStandard = Request::instance()->param("skillWebStandard");
        $skillMatch   = Request::instance()->param("skillMatch");
        $evaluate     = Request::instance()->param("evaluate");//评价
        $supplement   = Request::instance()->param("supplement");//备注
        $status       = Request::instance()->param("status");//状态 通过/淘汰


        $updateData = [
                    'audition_evaluate'=>$evaluate,
                    'audition_supplement'=>$supplement,
                    'audition_status'=>$status,
                    'manager_num'=>$managerNum
            ];
        $updateResult = Db::table("audition")->where("student_num",$studentNum)
                                                   ->where("audition_type",$auditionType)
                                                   ->update($updateData);
        $auditionObj  = Db::table("audition")->where("student_num",$studentNum)
                                                   ->where("audition_type",$auditionType)
                                                   ->find();
        $auditionId = $auditionObj['audition_id'];
        $insertData = [
                    'audition_id'  => $auditionId,
                    'html'         => $skillHtml,
                    'php'          => $skillPhp,
                    'wangluoxieyi' => $skillWLXY,
                    'tiyan'        => $skillFeel,
                    'team'         => $skillTeam,
                    'study'        => $skillStudy,
                    'talk'         => $skillTalk,
                    'guina'        => $skillGuiNa,
                    'luoji'        => $skillLuoJi,
                    'shijian'      => $skillShiJian,
                    'geren'        => $skillGeRen,
                    'renmai'       => $skillRenMai,
                    'xinge'        => $skillXinGe,
                    'handcode'     => $skillHandCode,
                    'pagecolumn'   => $skillPageColumn,
                    'jsframe'      => $skillJsFrame,
                    'webstandard'  => $skillWebStandard,
                    'match'        => $skillMatch
        ];
        $insertResult = Db::table("score")->where("audition_id",$auditionId)->update($insertData);
        if($updateResult == true && $insertResult == true) {
            $result = ['result'=>'success'];
        } else {
            $result = ['result'=>'failed'];
        }
        header("Access-Control-Allow-Origin:*");
        return $result;
    }

    public function getAllScore()
    {
        $studentNum  = Request::instance()->param("number");
        $information = [];
        $studentArr  = Db::table("student")->where("student_num",$studentNum)->find();
        $information['direction'] = action("Audition/getDirectionName",$studentArr['direction_id']);
        $information['name']  = $studentArr['student_name'];
        $information['tel']   = $studentArr['student_phone'];
        $information['class'] = $studentArr['student_class'];
        $information['mail']  = $studentArr['student_email'];
        $information['reason']= $studentArr['student_choice_reason'];
        $information['chushi']= $this->getAuditionAllInfo($studentNum,"初试");
        $information['fushi'] = $this->getAuditionAllInfo($studentNum,"复试");
        header("Access-Control-Allow-Origin:*");
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

}