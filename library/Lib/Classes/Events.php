<?php

require_once 'Lib/DB/Post_DB_Api.php';

class Events extends DB_Api
{
    public $db;


    public function __construct(){
        return parent::__construct();
    }

    public function update($table,$array1,$array2) {
        return $this->db->update($table,$array1,$array2);
    }

    public function getEvents($date){
        $sql = "SELECT event_dates.location,event_dates.date,training_event.*
            FROM training_event
            LEFT JOIN event_dates
            ON event_dates.eventId = training_event.id
            WHERE training_event.published = 1
            AND event_dates.date >= '$date-01' AND event_dates.date <= '$date-31'
            ORDER BY date 
    ";
   
        return $this->db->fetchAll($sql);
    }


    public function getEvent($id){
        $sql = "SELECT training_event.*,users.username, training_type.name AS event_type
                    FROM training_event
                    LEFT JOIN users
                    ON users.id = training_event.userId
                    LEFT JOIN training_type
                    ON training_type.id = training_event.type
                    WHERE training_event.id = $id
            ";
        return $this->db->fetchRow($sql);
    }
}
?>
