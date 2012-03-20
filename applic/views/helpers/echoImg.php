<?php
class Zend_view_Helper_EchoImg
{
    public function echoImg($id,$name)
    {
        $src = ROOT_URL."img/users/user_$id/$name.jpg";
        if(file_exists($src)){
            echo "<img src='$src' class='left padT5' />";
        }else{
            if($name == 'mainThumb'){
                $src = ROOT_URL."img/users/thumb.jpg";
                echo "<img src='$src' class='left padT5' />";
            }
        }
    }
}

?>
