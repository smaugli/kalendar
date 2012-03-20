<?php
class Zend_view_Helper_Text
{


    public function checkEmpty($array,$fields = array())
    {
        $return = array();
        $all = false;
        if(empty($fields)){
            $all = true;
        }
        foreach($array as $k => $v){
            if($all || in_array($k, $fields)){
                if(empty($v)){
                    $return[] = $k;
                }
            }
        }
        if(empty($return)){
            return false;
        }
        return $return;
    }
    //true, 0:false, 1
    public function checkDigit($v, $l=0)
    {
        if ($l != 0 && strlen($v) != $l)
        {
                return 1;
        }

        if (is_numeric($v))
        {
                $ret = 0;
        }
        else
        {
                $ret = 1;
        }
        return $ret;
    }

    public function checkEmail($v)
    {
        $ret = 1;
        if (ereg("^([0-9a-zA-Z]+[-._+&amp;])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$", $v) == false)
        {
                return 0;
        }

        return $ret;
    }
}

?>
