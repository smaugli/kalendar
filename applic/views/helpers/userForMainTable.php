<?php
class Zend_view_Helper_UserForMainTable
{
    public function userForMainTable($id,$message=null)
    {
        if($id){
            return '<b class="tlP">Բլոտող '.$id.'</b>';//todo echo name
        }else{
            switch($message){
                case null:
                    return "<b class='tlA' onclick=\"window.location='".ROOT_URL."home/game'\">Խաղալ</b>";
                    break;
                default:
                    return "<b class='f40'>$message</b>";
                    break;
            }
            
        }
    }
}

?>
