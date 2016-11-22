<?php
class LogSmsModel extends Model{
    public function __construct(){
        parent::__construct();
    }
    //发送成功添加记录
    public function addlog($u_id,$sm_senttime,$sm_code,$sm_mobno,$sm_type=0)
    {
        if ($sm_type == 1 || $sm_type == 2) {
            $logNums=$this->where("sm_mobno = '{$sm_mobno}' and sm_type={$sm_type}")->count();
            if(intval($logNums)==0){
                $data['u_id'] = $u_id;
                $data['sm_senttime'] = $sm_senttime;
                $data['sm_code'] = $sm_code;
                $data['sm_mobno'] = $sm_mobno;
                $data['sm_type'] = $sm_type;
                $backvalue=$this->add($data);
            }else{
                $data['u_id'] = $u_id;
                $data['sm_senttime'] = $sm_senttime;
                $data['sm_code'] = $sm_code;
                $data['sm_mobno'] = $sm_mobno;
                $data['sm_type'] = $sm_type;
                $backvalue=$this->where("sm_mobno = '{$sm_mobno}' and sm_type={$sm_type}")->save($data);
            }
        } else {
            $data['u_id'] = $u_id;
            $data['sm_senttime'] = $sm_senttime;
            $data['sm_code'] = $sm_code;
            $data['sm_mobno'] = $sm_mobno;
            $data['sm_type'] = $sm_type;
            $backvalue=$this->add($data);
        }
        return $backvalue;
    }


    //检测是否符合发送数目限制
    public function vfyperiod($uid,$num){
        $sql = "select count(*) n from tdf_log_sms where date(sm_senttime)=date(now()) and u_id = '{$uid}';";
        $arr = $this->query($sql);
        $count = $arr[0][n];
        if($count < $num){
            return true;
        }else{
            return false;
        }
    }

    //根据手机号码检测是否符合当天发送数目限制
    public function vfyperiodByMobno($mobno,$num){
        $sql = "select count(*) n from tdf_log_sms where date(sm_senttime)=date(now()) and sm_mobno = '{$mobno}'";
        $arr = $this->query($sql);
        $count = $arr[0][n];
        if($count < $num){
            return true;
        }else{
            return false;
        }
    }
    //判断提交的码是否正确 old
    public function getcode($mobno,$code){
        $result = $this->field('sm_code')->where("sm_mobno='{$mobno}'")->order('sm_senttime desc')->limit('1')->select();
        if($result == NULL){
            return false;
        }
        if($result[0]['sm_code'] == $code){
            return true;
        }
        else{
            return false;
        }
    }

    //判断提交的码是否正确
    public function verGetcode($mobno,$code,$sm_type=0){
        //echo $code;
        //exit;
        $result = $this->field('sm_code')->where("sm_mobno='{$mobno}' and sm_type={$sm_type}")->select();

        if($result == NULL){
            return false;
        }
        if($result[0]['sm_code'] == $code){
            return true;
        }else{
            return false;
        }
    }
    /*获得验证码
     *  @smMobno手机号
     *  @smType 验证码类型 0为
     */
    public function getMobieCaptchaCode($smMobno,$smType=""){
        $code=mt_rand(100000, 999999);
        return $code;
    }
}

