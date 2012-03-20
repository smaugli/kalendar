<?PHP
require_once 'Lib/DB/Post_DB_Api.php';

class Post extends DB_Api
{
    public $db;

    public $table;

    public function __construct(){
        $this->table = "nor";
        return parent::__construct();
    }

    public function get_random($type,$count=1){
        $where = "title = '$type' ";
        $sql = "SELECT * FROM $this->table WHERE $where ORDER BY RAND()  LIMIT 0,$count";
        return $this->db->fetchRow($sql);
    }

    public function getAll($params = array(),$offset,$count){
        $where = '';
        $where_array = array();
        foreach($params as $k=>$v){
            $where_array[] = "$k='$v'";
        }
        $where = implode(' AND ',$where_array);
        return parent::getRecords($this->table,$where," LIMIT $offset,$count ", " ORDER BY timestamp ASC ");
    }

    public function getPosts($type,$offset,$count){
        $where = "lang_id = '".LANG_ID."' AND type='$type'";
        return parent::getRecords($this->table,$where," LIMIT $offset,$count ", " ORDER BY timestamp ASC ");
    }

    public function getPost($params = array(),$offset,$count){
        $where = '';
        $where_array = array();
        foreach($params as $k=>$v){
            $where_array[] = "$k='$v'";
        }
        $where = implode(' AND ',$where_array);
        return parent::getRecords($this->table,$where," LIMIT $offset,$count ", " ORDER BY timestamp ASC ");
    }

    public function getCount($params = array()){
        $where = '';
        $where_array = array();
        foreach($params as $k=>$v){
            $where_array[] = "$k='$v'";
        }
        $where = implode(' AND ',$where_array);
        return parent::getRecordCount($this->table,$where);
    }

    public function  getRecord($where = "1") {
        return parent::getRecord($this->table, $where);
    }

}

?>