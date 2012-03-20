<?php

require_once 'Lib/DB/Post_DB_Api.php';

class User extends DB_Api
{
    public $db;

    public $table;

    public function __construct(){
        return parent::__construct();
    }

    public function update($table,$array1,$array2) {
        return $this->db->update($table,$array1,$array2);
    }

    public function getUser($id){
        $sql = 'SELECT *
            FROM users
            WHERE users.id = '.$id;

        return $this->db->fetchRow($sql);
    }

    public function  getRecordscontest() {
       $sql = "SELECT contests.*,users.username
            FROM contests
            LEFT JOIN users
            ON contests.userId = users.id
          ";

        return $this->db->fetchAll($sql);
    }
    public  function getUserByUsername($username){
        $sql = 'SELECT id,type,username
            FROM users
            WHERE users.username = "'.$username.'"';

        $res = $this->db->fetchRow($sql);

        if(isset($res['id']) && !empty($res['id'])){
            if($res['type'] == 1){
                return $this->getTrainerInfo($res['id']);
            }elseif($res['type'] == 2){
                return $this->getOrganisationInfo($res['id']);
            }

        }
        return FALSE;

    }



    public function incrementView($viewerId,$viewedId){

    }


    public function getTrainerInfo($id){
        $sql = 'SELECT *,trainers_info.id as trainerinfoid
            FROM trainers
            LEFT JOIN trainers_info
            ON trainers.id = trainers_info.trainerId
            WHERE trainers.userId = '.$id;
//        echo$sql;exit;
        return $this->db->fetchRow($sql);
    }

    public function getOrganisationInfo($id){
        $sql = 'SELECT *
            FROM organisation
            WHERE organisation.userId = '.$id;

        return $this->db->fetchRow($sql);
    }

    public function getWholeInfo($id,$table){
        $sql = "SELECT *
            FROM $table
            LEFT JOIN users
            ON $table.userId = users.id
            WHERE users.id = ".$id;
        return $this->db->fetchRow($sql);
    }

     public function getWholeInfoorg($id,$table){
        $sql = "SELECT *
            FROM $table
            LEFT JOIN users
            ON $table.userId = users.id
            WHERE organisation.id = ".$id;
        return $this->db->fetchRow($sql);
    }


    public function getAssignedUser($id){
        $sql = "SELECT *
            FROM `values`
            LEFT JOIN form_conections
            ON `values`.fieldId = form_conections.id
            WHERE `values`.registeredId = $id
        ";

        $res = $this->db->fetchAll($sql);

        foreach ($res as $r){
            if($r['label'] != ''){
                if($r['formType'] == 'select'){
                    $sql = "SELECT *
                        FROM `form_options`
                        LEFT JOIN `values`
                        ON `values`.value = `form_options`.id
                        WHERE `form_options`.elementId = ".$r['fieldId']." AND `values`.registeredId = $id";

                    $returnArray[strtolower($r['label'])] = $this->db->fetchAll($sql);
                }else{
                    $returnArray[strtolower($r['label'])] = $r['value'];
                }

            }
        }
//          var_dump($returnArray);exit;
        return $returnArray;

    }

    public function getAllAssignedUser($id){
        $sql = "SELECT *
            FROM `values`
            LEFT JOIN form_conections
            ON `values`.fieldId = form_conections.id
            WHERE `values`.registeredId IN (1,2)
        ";

        $res = $this->db->fetchAll($sql);

        foreach ($res as $r){
            if($r['label'] != ''){
                if($r['formType'] == 'select'){
                    $sql = "SELECT *
                        FROM `form_options`
                        LEFT JOIN `values`
                        ON `values`.value = `form_options`.id
                        WHERE `form_options`.elementId = ".$r['fieldId']." AND `values`.registeredId IN (1,2)";

                    $returnArray[strtolower($r['label'])] = $this->db->fetchAll($sql);
                }else{
                    $returnArray[strtolower($r['label'])][] = $r['value'];
                }

            }
        }
          var_dump($returnArray);exit;
        return $returnArray;

    }


    public function getAssignedUsers($id,$where = ''){

       $sql = "SELECT training_event.*,registers.approved as rrapproved,registers.id as regId,`values`.*,form_conections.*,registers.eventId as eventId
            FROM training_event
            LEFT JOIN registers
            ON registers.eventId = training_event.id AND registers.type = 0
            LEFT JOIN `values`
            ON `values`.registeredId = registers.id
            LEFT JOIN form_conections
            ON `values`.fieldId = form_conections.id
            WHERE training_event.userId = $id  AND registers.id != 'null' $where
        ";

        $returnArray = array();
        $res = $this->db->fetchAll($sql);

        foreach ($res as $key => $r){
            if($r['label'] != ''){
                if($r['formType'] == 'select'){
                    $sql = "SELECT *
                        FROM `form_options`
                        LEFT JOIN `values`
                        ON `values`.value = `form_options`.id
                        WHERE `form_options`.elementId = ".$r['fieldId']." AND `values`.registeredId = ".$r['regId'];

                    $returnArray[$r['eventId']][$r['regId']][strtolower($r['label'])] = $this->db->fetchAll($sql);



                }else{

                    $returnArray[$r['eventId']]['title'] = $r['title'];
                    $returnArray[$r['eventId']][$r['regId']]['approved'] = $r['rrapproved'];
                    $returnArray[$r['eventId']][$r['regId']][strtolower($r['label'])] = $r['value'];//$r['value'];
                }


            }
        }


        return $returnArray;

    }


    public function getContestUsers($id){

       $sql = "SELECT contests.*,registers.approved as rrapproved,registers.id as regId,`values`.*,form_conections.*,registers.eventId as eventId
            FROM contests
            LEFT JOIN registers
            ON registers.eventId = contests.id AND registers.type = 1
            LEFT JOIN `values`
            ON `values`.registeredId = registers.id
            LEFT JOIN form_conections
            ON `values`.fieldId = form_conections.id
            WHERE contests.userId = $id  AND registers.id != 'null'
        ";

        $returnArray = array();
        $res = $this->db->fetchAll($sql);

        foreach ($res as $r){
            if($r['label'] != ''){
                $returnArray[$r['eventId']]['title'] = $r['title'];
                $returnArray[$r['eventId']][$r['regId']]['approved'] = $r['rrapproved'];
                $returnArray[$r['eventId']][$r['regId']][strtolower($r['label'])] = $r['value'];//$r['value'];
            }
        }


        return $returnArray;

    }


}
?>
