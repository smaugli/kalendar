<?php

require_once 'Zend/Cache.php';

class Memcache_Api {

    private $autoincrement;
    public static $cache;
    private $name;
    private $expire;

    public function  __construct()
    {
        $this->cache = $this->getMemcache();
        $this->getAutoincrement();
    }

    private static function getMemcache(){
        if(isset(self::$cache)){
            return self::$cache;
        }else{
            $backendOptions = array(
                'servers' => array(array('host' => 'localhost','port' => 11211)),
                'compression' => false
            );
            $frontendOptions = array('lifeTime' => 60*60*24, 'caching' => true, 'automatic_serialization' => true);
            self::$cache = Zend_Cache::factory('Core', 'Memcached', $frontendOptions, $backendOptions);
            return self::$cache;
        }
    }

    public function loadAllMessages(){
        return $this->cache->load('id');
    }

    private function getAutoincrement(){
        $cacheId = $this->name.'_autoincrement';
        $this->autoincrement = $this->cache->load($cacheId);
        if(!$this->autoincrement){
            $this->autoincrement = 1;
            $this->cache->save($this->autoincrement,$cacheId,array(),0);
        }
    }
    private function autoIncrement(){
        $cacheId = $this->name.'_autoincrement';
        $this->autoincrement++;
        if(!$this->autoincrement){
            $this->autoincrement = 1;
        }
        $this->cache->save($this->autoincrement,$cacheId,array(),0);
    }

    public function reset(){
        $this->autoincrement = 1;
    }

    public function insert($element){
        $this->autoIncrement();
        $cacheId = 't_'.$this->name.'_'.$this->autoincrement;
        $this->cache->save($element,$cacheId,array(),$this->expire);
        return $this->autoincrement;
    }

    public function fetchRow($id){
        $cacheId = 't_'.$this->name.'_'.$id;
        return $this->cache->load($cacheId);
    }

    public function fetchAll($start=0,$end=0){
        $elements = array();
        $try = 0;
        while($try < 5){
            $cacheId = 't_'.$this->name.'_'.$start;
            $element = $this->cache->load($cacheId);
            if($element){
                $element['id'] = $start;
                $elements[] = $element;
                $start++;
                $try = 0;
            }else{
                $try++;
                $start++;
            }
        }
        return array_reverse($elements);
    }



}
?>