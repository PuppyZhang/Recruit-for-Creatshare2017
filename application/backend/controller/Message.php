<?php
/**
 * Created by PhpStorm.
 * User: puppy
 * Date: 2017/9/24
 * Time: 15:57
 */
namespace app\backend\controller;

use think\Controller;
use think\Db;
use think\Request;
use my\recruitMessage;
class Message extends Controller
{
    public function sendMessage()
    {
        $studentNums = Request::instance()->param("messageNum/a");
        $messageType = Request::instance()->param("type"); //0 初试通知 1复试通知 2结果通知


        $result = ['result'=>'success'];
        return $result;
    }

    public function planAuditionTime()
    {
        $sql = "select * from student where student_num  in (select student_num from audition where audition_type ='初试' and audition_status ='通过')";
        $result = Db::query($sql);
        $a = new recruitMessage();
        for($i=0;$i<count($result);$i++)
        {
            $audition = Db::table("audition")->where("student_num",$result[$i]['student_num'])->where("audition_type","初试")->find();
            $a->firstMessage($result[$i]['student_name'],$audition['audition_time'],$result[$i]['student_phone']);

        }

      /*  echo $a->firstMessage($,,"13772049621");*/

    }
}