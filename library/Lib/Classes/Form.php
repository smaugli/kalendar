<?php

require_once 'Lib/DB/Post_DB_Api.php';

class Form extends DB_Api
{
    public $db;

    public function __construct(){

    
        return parent::__construct();

    }
    

    public function getFormFields($id){
        $sql = "SELECT form_conections.*,form_options.elementId,form_options.options,form_options.id as optionId
            FROM form_conections
            LEFT JOIN forms
            ON form_conections.formId = forms.id AND form_conections.formId = '$id'
            LEFT JOIN form_options
            ON form_options.elementId = form_conections.id
            WHERE formId = $id
            ORDER BY form_conections.ordering";
          
        return $this->db->fetchAll($sql);
    }

    public function getField($id,$userId){
        $sql = "SELECT form_conections.*
            FROM form_conections
            LEFT JOIN forms
            ON form_conections.formId = forms.id
            LEFT JOIN form_options
            ON form_conections.id = form_options.elementId
            WHERE form_conections.id = $id AND forms.userId=$userId";

        return $this->db->fetchRow($sql);
    }

    public function isValidOption($id,$userId){
        $sql = "SELECT form_conections.*
            FROM form_conections
            LEFT JOIN forms
            ON form_conections.formId = forms.id
            LEFT JOIN form_options
            ON form_conections.id = form_options.elementId
            WHERE form_options.id = $id AND forms.userId=$userId";
       
        if($this->db->fetchRow($sql)){
            return true;
        }
        return false;
    }

    public function deleteField($id){
        $db = new DB_Api();
        $db->deleteRecord('form_conections', 'id='.$id);
        $db->deleteRecord('form_options', 'elementId='.$id);
    }

   
}
?>
