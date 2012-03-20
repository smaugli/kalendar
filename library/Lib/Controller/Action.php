<?php
require_once 'Lib/Engine.php';

/*
 * mother controller
 */

class Lib_Controller_Action extends Zend_Controller_Action
{
	private $db;
        protected $db_api;
        protected $session;
        protected $type_map;

        public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()){
            $this->session = new Zend_Session_Namespace('mitterformaalne');
            $this->type_map = array(1 => 'pre-scool', 2 => 'toddles', 3 =>'babys', 4 => 'general');
            return parent::__construct($request, $response);
        }

        public function init(){
            $this->db_api = new DB_Api();
            
            $this->view->title = 'Social';

            
           
        }

      
        public function checkAdmin(){
            if(isset($this->session->admin)){
                return true;
            }
            return false;
        }

        public function realRequestParams($params,$arrayToUnset = array()){
            unset($params['controller']);
            unset($params['action']);
            unset($params['module']);
            foreach($arrayToUnset as $key){
                unset($params[$key]);
            }
            return $params;
        }

        public function realRequestParamsReverse($params,$arrayToDontUnset = array()){
            unset($params['controller']);
            unset($params['action']);
            unset($params['module']);
            foreach($params as $key=>$v){
                if(!in_array($key, $arrayToDontUnset)){
                    unset($params[$key]);
                }
            }
            return $params;
        }
        
        public function setSafe($params){
            foreach($params as $key => $value){
                $params[$key] = addslashes($value);
            }
            return $params;
        }

        public function fixSlug($slug){
            $result = trim(urlencode($slug));
            return $result;
        }
        /**
         * returns 1 if no one of array elements is empty
         * @return bool
         */
        public function checkEmpty($array,$fields = array())
	{            
            $return = array();
            $all = false;
            if(empty($fields)){
                $all = true;
            }
            foreach($array as $k => $v){
                if($all || in_array($k, $fields)){
                    if(empty($v)){
                        $return[] = $k;
                    }
                }
            }
            if(empty($return)){
                return false;
            }
            return $return;
        }
	//true, 0:false, 1
	public function checkDigit($v, $l=0)
	{
		if ($l != 0 && strlen($v) != $l)
		{
			return 1;
		}

		if (is_numeric($v))
		{
			$ret = 0;
		}
		else
		{
			$ret = 1;
		}
		return $ret;
	}

	public function checkEmail($v)
	{
		$ret = 1;
		if (ereg("^([0-9a-zA-Z]+[-._+&amp;])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$", $v) == false)
		{
			return 0;
		}

		return $ret;
	}


	public function getMysqlDB()
        {
		if($this->db)
		{
			return $this->db;
		}
		else
		{
                    
			require_once 'Zend/Config/Ini.php';
			$config = new Zend_Config_Ini('../conf/setting.ini', 'database');
			$this->_db_adapter_pdo_mysql = new Zend_Db_Adapter_Pdo_Mysql($config ->toArray());
			return $this->_db_adapter_pdo_mysql;
		}
        }
       
	

	

}
?>