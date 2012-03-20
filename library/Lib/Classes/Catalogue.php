<?PHP
class Catalogue extends DB_Api
{
        public $table;

        public function __construct(){
            $this->table = "catalogue";
            return parent::__construct();
        }

        public function getAll($params = array()){
            $where = '';
            $where_array = array();
            foreach($params as $k=>$v){
                $where_array[] = "$k='$v'";
            }
            $where = implode(' AND ',$where_array);
            return parent::getRecords($this->table,$where,'', " ORDER BY ordering ASC ");
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
}

?>