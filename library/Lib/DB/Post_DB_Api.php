<?PHP
require_once 'Lib/DB/DBApi.php';

class Post_DB_Api extends DB_Api
{
        public $table;

        public function __construct(){
            $this->table = "posts";
            return parent::__construct();
        }

        public function	getRandom($where='',$limit=1)
	{
		$sql = "SELECT * FROM $this->table WHERE $where ORDER BY RAND()  LIMIT 0,$limit";
		return $this->db->fetchRow($sql);
	}

        public function	getRecords($where="AND", $limit="")
	{
		return parent::getRecords($this->table, "1 $where meta_type='post'", $limit);
	}

        public function insert($params){
            return $this->db->insert($this->table,$params);
        }

        public function update($params,$id){
            return $this->db->insert($this->table,$params);
        }
}

?>