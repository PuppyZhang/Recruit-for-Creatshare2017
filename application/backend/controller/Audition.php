<?php
/**
 * Created by PhpStorm.
 * User: puppy
 */
namespace app\backend\controller;

use think\Controller;
use think\Db;
use think\Request;

class  Audition extends Controller
{
    public function getStudent()
    {
        $directionId = Request::instance()->param("directionId");
        if($directionId  == 0) {
            $directionId = "not null";
        }
        $rst = Db::table("student")->field("student_name,student_num,direction_id,student_class,student_choice_reason")
                                         ->where("direction_id",$directionId)
                                         ->select();
        for($i=0;$i<count($rst);$i++)
        {
            $middle = Db::table("audition")->where("student_num",$rst[$i]['student_num'])->select();
            $rst[$i]['audition_time_first']   = $middle[0]['audition_time'];
            $rst[$i]['audition_status_first'] = $middle[0]['audition_status'];
            $directionId = $middle[0]['direction_id'];
            $rst[$i]['direction_id']    = $this->getDirectionName($directionId);
            $rst[$i]['audition_time_second']  = $middle[1]['audition_time'];
            $rst[$i]['audition_status_second']= $middle[1]['audition_status'];
        }
        header("Access-Control-Allow-Origin:*");
        return $rst;
    }

    public function getDirectionName($directionId)
    {
        $directionName = "";
        switch($directionId)
        {
            case 1: $directionName = "前端";
                        break;
            case 2: $directionName = "服务端";
                        break;
            case 3: $directionName = "产品";
                        break;
            case 4: $directionName = "视觉";
                        break;
            case 5: $directionName = "运营";
                        break;
            case 6: $directionName = "负责人";
                        break;
        }
        return $directionName;
    }
}
