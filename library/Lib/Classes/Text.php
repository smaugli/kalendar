<?php
require_once 'Lib/DB/DBApi.php';

class Text extends DB_Api
{

    public $db;

    public $table;

    public function checkEmpty($array,$fields = array())
    {        
        $return = array();
        $all = false;
        if(empty($fields)){
            $all = true;
        }
        foreach($array as $k => $v){
            if($all || in_array($k, $fields)){
                if(empty($v) && $v != '0'){
                    $return[] = $k;
                }
            }
        }
        if(!empty($return)){
            //title,keywords,description,price,deadline
	    if(is_array($return)){
		foreach($return as $key => $err){
			switch ($err){
				case 'title': $return[$key] = 'Pealkiri'; break;
				case 'keywords': $return[$key] = 'Komadega eraldatud märksõnad'; break;
				case 'description': $return[$key] = 'Koolitustegevuse ülevaade'; break;
				case 'price': $return[$key] = 'Hind'; break;
				case 'deadline': $return[$key] = 'Taotlus-/registreerimistähtaeg'; break;
			}	

		}


	    }  
            $message = implode(',',$return).': on kohustuslikud väljad!';
            throw new Exception($message);
        }


        return $return;
    }
    public function checkEmptycont($array,$fields = array())
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
        if(!empty($return)){

            $message = implode(',',$return).': Is Required';
            throw new Exception($message);
        }


        return $return;
    }


    public function checkDublicate($table,$field,$value,$where = null){

        if($this->checkValueExistence($table,$field,$value,$where)){
            throw new Exception('Such '.$field.' Exists');
        }
        return true;
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
               throw new Exception('Invalid Email');
        }

        return $ret;
    }

    public function comunicalStr($str){
        $str = str_replace('+','',$str);
        $str = str_replace(' ','_',$str);
        $str = str_replace('.','',$str);
        $str = str_replace('-','',$str);
        $str = str_replace('!','',$str);
        $str = str_replace(':','',$str);
        return $str;
    }
    

   
}
?>
