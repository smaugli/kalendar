<?php
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
require_once  'Zend/Auth/Adapter/DbTable.php';

class DB_Api {

	private $_db_adapter_pdo_mysql;

	public $db; 	

	public function __construct()
	{
		$this->db = $this->getMysqlDB();
	}

	public function escape($str)
	{
		return addslashes($str); //mysql_real_escape_string($str);
	}

	public function insert_id()
	{
		return $this->db->lastInsertId();
	}

        public function getAll($table,$params = array(),$offset,$count,$order=''){
            $where = '';
            $where_array = array();
            foreach($params as $k=>$v){
                $where_array[] = "$k='$v'";
            }
            $where = implode(' AND ',$where_array);
            return $this->getRecords($table,$where,"LIMIT $offset,$count",$order);
        }

        public function getCount($table,$params = array()){
            $where = '';
            $where_array = array();
            foreach($params as $k=>$v){
                $where_array[] = "$k='$v'";
            }
            $where = implode(' AND ',$where_array);
            return $this->getRecordCount($table,$where);
        }


	public function getMysqlDB()
	{
		if($this->_db_adapter_pdo_mysql)
		{
			return $this->_db_adapter_pdo_mysql;
		}
		else
		{
			$config = Zend_Registry::get("config");
                        $this->_db_adapter_pdo_mysql = new Zend_Db_Adapter_Pdo_Mysql($config);
			return $this->_db_adapter_pdo_mysql;
		}
	}


	public function insertRecord($table, $params)
	{
            try {
                $this->db->insert($table, $params);
                return true;
            }
            catch(Exception $e)  {
                return $e->getMessage();
            }
	}

        public function update($table,$array1,$array2) {
            return $this->db->update($table,$array1,$array2);

        }

        public function updateRecord($tableName, $params, $id){
                if(count($params) > 0){
                    $updateString = '';
                    foreach($params as $key => $value){
                        $updateString .= "`$key`='$value',";
                    }
                    $updateString = substr($updateString, 0,strlen($updateString)-1);
                    $sql   = "UPDATE $tableName SET $updateString WHERE id IN ($id)";
					//echo $sql;exit;
                    $this->db->query($sql);
                    return true;

                }
                return false;
        }
        public function updateRecordt($tableName, $params, $id){
                if(count($params) > 0){
                    $updateString = '';
                    foreach($params as $key => $value){
                        $updateString .= "`$key`='$value',";
                    }
                    $updateString = substr($updateString, 0,strlen($updateString)-1);
                    $sql   = "UPDATE $tableName SET $updateString WHERE trainerId IN ($id)";
                    $this->db->query($sql);
                    return true;

                }
                return false;
        }
        public function updateRecordtrainer($tableName, $params, $id){
                if(count($params) > 0){
                    $updateString = '';
                    foreach($params as $key => $value){
                        $updateString .= "`$key`='$value',";
                    }
                    $updateString = substr($updateString, 0,strlen($updateString)-1);
                    $sql   = "UPDATE $tableName SET $updateString WHERE userId IN ($id)";
//                   echo $sql;exit;
                    $this->db->query($sql);
                    return true;

                }
                return false;
        }
        

             public function updateRecorddate($tableName, $params, $id){
                if(count($params) > 0){
                    $updateString = '';
                    foreach($params as $key => $value){
                        $updateString .= "`$key`='$value',";
                    }
                    $updateString = substr($updateString, 0,strlen($updateString)-1);
                    $sql   = "UPDATE $tableName SET $updateString WHERE eventId IN ($id)";
                    $this->db->query($sql);
                    return true;

                }
                return false;
        }

	public function deleteRecord($table, $where)
	{
		try {
                    
			$this->db->delete($table, $where);

			return true;
		}
		catch(Exception $e)  {
			return false;
		}
	}



	public function getRecordCount($tableName, $where="")
	{
		if( $where == "" )
			$sql   = "SELECT count(*) FROM $tableName";
		else{
			$sql   = "SELECT count(*) FROM $tableName WHERE $where";
                }
  
		$count = $this->db->fetchOne($sql);

		return $count;
	}

	public function getRecords($tableName, $where="", $limit = "", $order = '')
	{
		if( $where == "" )
			$sql   = "SELECT * FROM $tableName WHERE 1 $order $limit";
		else
			$sql   = "SELECT * FROM $tableName WHERE $where $order $limit";
//                  echo $sql;exit;
		return $this->db->fetchAll($sql);
	}
        public function getRecordsexsp0($tableName, $where="")
	{

			$sql   = "SELECT * FROM $tableName WHERE $where Order by id ASC ";
		return $this->db->fetchAll($sql);
	}

        public function searchEvent($search)
	{

			$sql   = "SELECT * FROM training_event WHERE published=1 AND (title like '%$search%' or keywords like '%$search%' or description like '%$search%')   limit 5";
//		echo $sql;exit;
                        return $this->db->fetchAll($sql);
	}

        public function searchTrainer($search)
	{

			$sql   = "SELECT * FROM trainers 
                                    LEFT JOIN users
                                   ON trainers.userId = users.id
                                   WHERE trainers.name like '%$search%' or trainers.surname like '%$search%' or trainers.email like '%$search%' or trainers.website like '%$search%'
                                    limit 5";
		return $this->db->fetchAll($sql);
	}
        public function searchOrganisation($search)
	{

			$sql   = "SELECT * FROM organisation WHERE name like '%$search%' or address like '%$search%' or email like '%$search%' or website like '%$search%' limit 5";
		$sql   = "SELECT * FROM organisation 
                                    LEFT JOIN users
                                   ON organisation.userId = users.id
                                   WHERE organisation.name like '%$search%' or organisation.address like '%$search%' or organisation.email like '%$search%' or organisation.website like '%$search%'
                                    limit 5";

                        return $this->db->fetchAll($sql);
	}

	public function	getRecord($table, $array)
	{
                  $where = array();
                    foreach ($array as $k => $v) {
                        $where[] = "$k='$v'";
                    }
                    $where = implode(' && ', $where);

                    if (empty($array)) {
                        $where = 0;
        }
		$sql = "SELECT * FROM $table WHERE $where LIMIT 0,1 " ;
//               echo $sql;exit;
		return $this->db->fetchRow($sql);
	}

	public function checkValueExistence($table, $field, $value, $where = "",$id = null)
	{


		$sql   = "SELECT count(*) FROM $table WHERE $field = '$value'" ;
		
		if ($where!="")
		{
			$sql .= " AND $where";
		}
  
		$count = $this->db->fetchOne($sql);

		return $count;
	}




	
}
?>