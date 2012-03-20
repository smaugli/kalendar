<?php

require_once 'Lib/Memcache/MemcacheApi.php';

class UsersCache extends Memcache_Api {

    private $room_id;

    public function __construct($room_id){
        parent::__construct();
        $this->name = 'users';
        $this->expire = 60*60;
        $this->room_id = $room_id;
    }

    
}
?>