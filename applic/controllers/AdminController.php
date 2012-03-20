<?php

class AdminController extends Lib_Controller_Action {

    public function  init() {
        $url = ROOT_URL . "index";
        if(!$this->session->user){

            $this->_redirect($url);
        }
        $userClass = new User();
        $this->view->typeId = 3;
        $user = $userClass->getUser($this->session->user);
        if(empty($user) || $user['type'] != 3 || $user['level'] != 1){
            $this->_redirect($url);
        }
    }

    protected function array_transpon($f_array)
    {
        if (!is_array($f_array)) return "ERROR Š² Ń„Ń�Š½ŠŗŃ†ŠøŠø array_transpon(). Š�Ń€Š³Ń�Š¼ŠµŠ½Ń‚ Š½Šµ Š¼Š°Ń�Ń�ŠøŠ².";
        foreach ($f_array as $row => $cval)
        {
            if (is_array($cval)) foreach ($cval as $col => $val) $new_array[$col][$row] = $val;
            else $new_array[0][$row] = $cval;
        }
        return $new_array;
    }

    public function indexAction(){
        $url = ROOT_URL . "profile";
        $this->_redirect($url);
    }

    public function sectorsAction(){
        $db = new DB_Api();
        $this->view->items = $db->getRecords('sector');

        $this->view->title = 'Sektorid';

    }
 
    public function sectoraddAction(){
        $db = new DB_Api();
        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            unset ($params['id']);
            $db->insertRecord('sector', $params);
            $url = ROOT_URL . "admin/sectors";
            $this->_redirect($url);
        }

        $this->view->title = 'Lisa sektor';
    }

    public function removeAction(){
        $type = (string)$this->getRequest()->getParam('type');
        $id = (int)$this->getRequest()->getParam('id');

        $db = new DB_Api();

        $array = array('counties'=>'counties','organisation'=>'organizations' ,'languages' => 'languages','sector'=>'sectors','target_groups' => 'targets','topics' => 'topics','training_type' => 'types','teaching_materials' => 'teachingmaterials','contests' => 'contests', 'trainers' => 'trainers');
        if(array_key_exists($type, $array)){
            $where = 'id='.$id;
            $array3['id'] = $id;
            $item = $db->getRecord($type, $array3);
            $db->deleteRecord($type, $where);
            if(file_exists("img/".$array[$type].'/'.$item['contest_file'])){
                
                unlink("img/".$array[$type].'/'.$item['contest_file']);
            }
        }
    	if($type == 'organisation') {
    		$url = 'admin/'.$type.'s';
    	} else {
        	$url = 'admin/'.$array[$type];
    	}
        
        $this->_redirect($url);
    }

    public function sectoreditAction(){
        $db = new DB_Api();

        $id = (int)$this->getRequest()->getParam('id');
        $array['id'] = $id;
        
        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);           
            $db->updateRecord('sector', $params, $params['id']);
            $url = ROOT_URL . "admin/sectors";
            $this->_redirect($url);
        }
        $this->view->title = 'Sektori muutmine';
        $this->view->item = $db->getRecord('sector',$array);
    }


    public function countiesAction(){
        $db = new DB_Api();
        $this->view->items = $db->getRecords('counties');
        $this->view->title = 'Maakonnad';
    }

    public function countiesaddAction(){
        $db = new DB_Api();
        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            unset ($params['id']);
            $db->insertRecord('counties', $params);
            $url = ROOT_URL . "admin/counties";
            $this->_redirect($url);
        }
    }

    public function countieseditAction(){
        $db = new DB_Api();
        $id = (int)$this->getRequest()->getParam('id');
        $array['id'] = $id;

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            $db->updateRecord('counties', $params, $params['id']);
            $url = ROOT_URL . "admin/counties";
            $this->_redirect($url);
        }

        $this->view->item = $db->getRecord('counties',$array);
    }


    public function contestsAction(){
        $db = new DB_Api();
        $userClass = new User();
        $this->view->items = $userClass->getRecordscontest();
//        $this->view->items = $db->getRecords('contests');
        
    }

    public function contestaddAction(){
        $db = new DB_Api();
        $userClass = new User();
        $error = '';
        $id = $this->session->user;
        $this->view->languages = $db->getRecords('languages');
         if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            try{
                $textModel = new Text();
                $params = $this->realRequestParams($params);
                $checkArray = array('title','organiser','email','details','location');
                $this->view->data = $params;
                $textModel->checkEmptycont($params,$checkArray);
                $textModel->checkEmail($params['email']);
//                var_dump($y);exit;
                if(isset($params['free'])){
                    $params['price'] = 0;
                    unset ($params['free']);
                }
                $params['date'] = date('Y-m-d 00:00:00',strtotime($params['date']));

                if(isset($params['language']) && count($params['language'] > 0)){
                    $params['languages'] = implode(',', $params['language']);
                }
                unset($params['language']);
               
                $db->insertRecord('contests',$params);
                
                $upload = new Zend_File_Transfer_Adapter_Http();

                $lastId = $db->insert_id();
                
                if(isset($_FILES['contest_file'])){
                    $uploader = new Upload($_FILES['contest_file']);
                    if ($uploader->uploaded) {
                      $uploader->file_new_name_body = $lastId.$uploader->file_new_name_body;
                      $uploader->Process(ROOT_DIR.'/img/contests/');
                      if ($uploader->processed) {
                        $update['contest_file'] = $uploader->file_dst_name;
                        $db->updateRecord('contests',$update,$lastId);
                        $uploader->Clean();
                      } else {
                        echo 'viga: ' . $uploader->error;
                      }
                    }
                }

                $this->_redirect('admin/contests');

            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }
        }
        $this->view->error = $error;
        
    }


    public function contesteditAction(){
        $db = new DB_Api();
        $userClass = new User();
        $error = '';

        $this->view->languages = $db->getRecords('languages');

        $id = (int)$this->getRequest()->getParam('id');
        $array['id'] = $id;
        $this->view->id = $id;

        

       
         if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            try{
                $textModel = new Text();
                $params = $this->realRequestParams($params);
                $checkArray = array('title','organiser','email','details','location');
//                $textModel->checkEmpty($params,$checkArray);
                if(isset($params['free'])){
                    $params['price'] = 0;
                    unset ($params['free']);
                }
                $params['date'] = date('Y-m-d 00:00:00',strtotime($params['date']));

                if(isset($params['language']) && count($params['language'] > 0)){
                    $params['languages'] = implode(',', $params['language']);
                }else{
                    $params['languages'] = '';
                }
                unset($params['language']);
                if($params['published'] == 'Yes'){
                    $params['published'] = 1;
                }else{
                    $params['published'] = 0;
                }
                $db->updateRecord('contests',$params,$params['id']);

                $upload = new Zend_File_Transfer_Adapter_Http();

                if(isset($_FILES['contest_file'])){
                    $uploader = new Upload($_FILES['contest_file']);
                    if ($uploader->uploaded) {
                      $uploader->file_new_name_body = $lastId.$uploader->file_new_name_body;
                      $uploader->Process(ROOT_DIR.'/img/contests/');
                      if ($uploader->processed) {
                        $update['contest_file'] = $uploader->file_dst_name;
                        //$db->updateRecord('training_event',$update,$lastId);
                        $uploader->Clean();
                      } else {
                        echo 'viga: ' . $uploader->error;
                      }
                    }
                }

//                $this->_redirect('admin/contests');

            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }
        }
        $this->view->data = $db->getRecord('contests', $array);
    }
    public function  languagesAction(){
        $db = new DB_Api();
        $this->view->items = $db->getRecords('languages');
        $this->view->title = 'TĆ¶Ć¶keeled';

    }

    public function  languageaddAction(){
        $db = new DB_Api();
        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            unset ($params['id']);
            $db->insertRecord('languages', $params);
            $url = ROOT_URL . "admin/languages";
            $this->_redirect($url);
        }

        $this->view->title = 'Lisa tĆ¶Ć¶keel';
    }



    public function  languageeditAction(){
        $db = new DB_Api();
        $id = (int)$this->getRequest()->getParam('id');
        $array['id'] = $id;
        //var_dump($id);exit;
        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            $db->updateRecord('languages', $params, $params['id']);
            $url = ROOT_URL . "admin/languages";
            $this->_redirect($url);
        }
        $this->view->title = 'Muuda tĆ¶Ć¶keelt';
        $this->view->item = $db->getRecord('languages',$array);
        
    }

    public function targetsAction(){
        $db = new DB_Api();
        $targets = $db->getRecords('target_groups','','','ORDER BY `parentId`');
        $tmpArray = array();
        foreach($targets as $target){
            if($target['parentId'] == 0){
                $tmpArray[$target['id']] = $target;
            }else{
                $tmpArray[$target['parentId']]['children'][] = $target;
            }
        }
//        var_dump($tmpArray);exit;
        $this->view->items = $tmpArray;
        $this->view->title = 'Sihtgrupid';

    }

    public function targetaddAction(){
      
        $db = new DB_Api();
        if($this->getRequest()->isPost()){
            
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            unset ($params['id']);
            $db->insertRecord('target_groups', $params);
            $url = ROOT_URL . "admin/targets";
            $this->_redirect($url);
        }
        $this->view->items = $db->getRecords('target_groups','parentId = 0');
        $this->view->title = 'Lisa sihtgrupp';
    }


    public function targeteditAction(){
        $db = new DB_Api();

        $id = (int)$this->getRequest()->getParam('id');
        $array['id'] = $id;

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            $db->updateRecord('target_groups', $params, $params['id']);
            $url = ROOT_URL . "admin/targets";
            $this->_redirect($url);
        }
        $this->view->title = 'Muuda sihtgruppi';
        $this->view->item = $db->getRecord('target_groups',$array);
        $this->view->items = $db->getRecords('target_groups','parentId = 0');

    }

    public function topicsAction(){
        $db = new DB_Api();

        $targets = $db->getRecords('topics','','','ORDER BY `parentId`');
        $tmpArray = array();
        foreach($targets as $target){
            if($target['parentId'] == 0){
                $tmpArray[$target['id']] = $target;
            }else{
                $tmpArray[$target['parentId']]['children'][] = $target;
            }
        }
//        var_dump($tmpArray);exit;
        $this->view->items = $tmpArray;
        $this->view->title = 'Teemad';

    }

    public function topicaddAction(){

        $db = new DB_Api();
        
        if($this->getRequest()->isPost()){

            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            unset ($params['id']);
            $db->insertRecord('topics', $params);
            $url = ROOT_URL . "admin/topics";
            $this->_redirect($url);
        }
        
        $this->view->items = $db->getRecords('topics','parentId = 0');
        $this->view->title = 'Lisa teema';
    }


    public function topiceditAction(){
        $db = new DB_Api();

        $id = (int)$this->getRequest()->getParam('id');
        $array['id'] = $id;

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            $db->updateRecord('topics', $params, $params['id']);
            $url = ROOT_URL . "admin/topics";
            $this->_redirect($url);
        }
        $this->view->title = 'Muuda teemat';
        $this->view->item = $db->getRecord('topics',$array);
        $this->view->items = $db->getRecords('topics','parentId = 0');

    }


    public function teachingmaterialsAction(){
        $db = new DB_Api();
        $this->view->items = $db->getRecords('teaching_materials');

        $this->view->title = 'Materjalid';

    }


    public function typesAction(){
        $db = new DB_Api();
        $this->view->items = $db->getRecords('training_type');

        $this->view->title = 'Koolituse vormid';

    }

    public function typeaddAction(){
         $db = new DB_Api();

        if($this->getRequest()->isPost()){

            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            unset ($params['id']);
            $db->insertRecord('training_type', $params);
            $url = ROOT_URL . "admin/types";
            $this->_redirect($url);
        }

        $this->view->title = 'Lisa koolituse vorm';

    }
    
        public function typeeditAction(){
            $db = new DB_Api();

        $id = (int)$this->getRequest()->getParam('id');
        $array['id'] = $id;

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            $db->updateRecord('training_type', $params, $params['id']);
            $url = ROOT_URL . "admin/types";
            $this->_redirect($url);
        }
        $this->view->title = 'Muuda koolituse vormi';
        $this->view->item = $db->getRecord('training_type',$array);
    }

    public function teachingmaterialaddAction(){
        $db = new DB_Api();
        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            unset ($params['id']);
            $db->insertRecord('teaching_materials', $params);
            $url = ROOT_URL . "admin/teachingmaterials";
            $this->_redirect($url);
        }

        $this->view->title = 'Lisa materjal';
    }


    public function teachingmaterialeditAction(){
        $db = new DB_Api();

        $id = (int)$this->getRequest()->getParam('id');
        $array['id'] = $id;

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            $db->updateRecord('teaching_materials', $params, $params['id']);
            $url = ROOT_URL . "admin/teachingmaterials";
            $this->_redirect($url);
        }
        $this->view->title = 'Muuda materjali';
        $this->view->item = $db->getRecord('teaching_materials',$array);
    }

    public function trainersAction(){
        $db = new DB_Api();

        $this->view->items = $db->getRecords('trainers');
    }

    public function trainerseditAction(){
        $db = new DB_Api();
        $user = new User();

        $id = (int)$this->getRequest()->getParam('id');


        
        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            try{
                $textModel = new Text();
                $params = $this->realRequestParams($params);

                $checkArray = array('username','email','name','surname','address','county','phone');
                $textModel->checkEmpty($params,$checkArray);

                $where['userId'] = $id;
                $where1 = "Id=$id";
                $params1['username'] = $params['username'];
                $photoUrl = $params['username'];
                $photoUrl = $textModel->comunicalStr($photoUrl);
                unset ($params['username']);
                $db->updateRecord('users', $params1, $id);
                $db->updateRecordtrainer('trainers', $params, $id);
                $user_id = $id;
                $upload = new Zend_File_Transfer_Adapter_Http();
            if(isset($_FILES['photo'])){
                    $image_name = $photoUrl;
                    $uploader = new Upload($_FILES['photo']);

                    if ($uploader->file_is_image && $uploader->uploaded) {
                      $uploader->file_new_name_body = $image_name;
                      $uploader->Process(ROOT_DIR.'/img/trainers/');
                      if ($uploader->processed) {
                        $update['photo'] = $uploader->file_dst_name;
                        $db->updateRecordtrainer('trainers',$update,$id);
                        $uploader->Clean();
                      } else {
                        echo 'viga: ' . $uploader->error;
                      }
                    }
                }

            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }

            $this->view->data = $params;
        }
        $this->view->data = $user->getWholeInfo($id,'trainers');

        $this->view->organisations = $db->getRecords('organisation');
        $this->view->counties = $db->getRecords('counties');
//        $this->_redirect(ROOT_URL . "admin/trainersedit/");
    }

    public function organisationsAction(){
        $db = new DB_Api();

        $this->view->items = $db->getRecords('organisation');
    }

    public function organizationeditAction(){
        $db = new DB_Api();
        $user = new User();

        $id1 = (int)$this->getRequest()->getParam('id');
       
      $db = new DB_Api();

      $user = new User();

      $this->view->organisations = $db->getRecords('organisation');
      $this->view->counties = $db->getRecords('counties');

      $this->view->data = $user->getWholeInfoorg($id1,'organisation');
      $error = '';
      $user_id =  $this->view->data['userId'];
      if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            try{
                $textModel = new Text();
                $params = $this->realRequestParams($params);
                $checkArray = array('username','email','name','surname','address','county','phone');
                $textModel->checkEmpty($params,$checkArray);

                $usersData['username'] = $params['username'];
                $usersData['type'] = 2;
                $where = "id not in ($user_id)";
                $textModel->checkDublicate('users','username',$usersData['username'],$where);
                $textModel->checkEmail($params['email']);
                $where = "userId not in ($user_id)";
                $textModel->checkDublicate('organisation','email',$params['email'],$where);


                $photoUrl = $params['username'];
                $photoUrl = $textModel->comunicalStr($photoUrl);
                $id ="id ='$user_id'";
                $db->update('users', $usersData, $id);
                unset($params['username'],$params['password']);
                $id ="userId ='$user_id'";
                $db->update('organisation', $params, $id);

                $upload = new Zend_File_Transfer_Adapter_Http();

                if($upload->isUploaded('logo')){

                    $upload->setDestination("img/organizations/");
                    $upload->receive();
                    $name = $upload->getFileName('logo');
                    $renameFile = $photoUrl.'.jpg';
                    $fullFilePath = 'img/organizations/'.$renameFile;
                    $filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));
                    $filterFileRename -> filter($name);


                    $update['logo'] = $renameFile;
                    $db->updateRecord('organisation',$update,$user_id);
                }

            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }
            $this->view->data = $user->getWholeInfoorg($id1,'organisation');
        }
//        $this->_redirect(ROOT_URL . "profile/editteacher");

    }

    public function eventsAction(){
        $db = new DB_Api();


        $where1 = "deadline < NOW()";
        $where2 = "deadline >= NOW()";
        $this->view->itemsnew = $db->getRecords('training_event',$where2);
        
        $this->view->itemsold = $db->getRecords('training_event',$where1);

    }

    public function eventeditAction(){
        $db = new DB_Api();
        $id = (int)$this->getRequest()->getParam('id');
        $this->view->languages = $db->getRecords('languages');
        $wheredate = "eventId=$id";
        $this->view->prefix = 'trainerprofile';
        $this->view->list = $db->getRecords('organisation');

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            try{
                $datesArray = array();
                $textModel = new Text();
                $params = $this->realRequestParams($params);
                $checkArray = array('title','keywords','description','type','location','price','deadline');
                $textModel->checkEmpty($params,$checkArray);


                if(isset($params['free'])){
                    $params['price'] = 0;
                    unset ($params['free']);
                }

                $params['deadline'] = date('Y-m-d 00:00:00',strtotime($params['deadline']));
                $dateArray = $params['date'];
                $locationArray = $params['location'];
				
                $assignValues = @$params['assign'];
                //unset($params['date'],$params['location']);
                unset($params['assign']);
                
                if(isset($params['organisations']) && count($params['organisations'] > 0)){
                    $params['organisationsevent'] = implode(',', $params['organisations']);

                }
                unset($params['organisations']);

                if(isset($params['teachers']) && count($params['teachers'] > 0)){
                    $params['teachersevent'] = implode(',', $params['teachers']);
                }
                
                unset($params['teachers']);

                if(isset($params['language']) && count($params['language'] > 0)){
                    $params['languages'] = implode(',', $params['language']);
                }
                unset($params['language']);
                if(count($dateArray) > 0){

                    $db->deleteRecord('event_dates', $wheredate);
                    $count = count($dateArray);


                    for($i=0;$i < $count ;$i++){
                        $datesArray['date'] = date('Y-m-d 00:00:00',strtotime(array_shift($dateArray)));
                        $datesArray['location'] = array_shift($locationArray);
                        $datesArray['eventid'] = $id;
//                        if($datesArray['date'] != '1970-01-01 00:00:00'){
                            $db->insertRecord('event_dates', $datesArray);

//                        }

                    }
                }

                unset($params['date']);
                unset($params['location']);

                $db->updateRecord('training_event', $params, $id);
//                $db->insertRecord('training_event',$params);


                $upload = new Zend_File_Transfer_Adapter_Http();

                $lastId = $id;
				
                if(isset($_FILES['logo'])){
                    $uploader = new Upload($_FILES['logo']);
                    if ($uploader->uploaded) {
                      $uploader->file_new_name_body = $lastId.$uploader->file_new_name_body;
                      $uploader->Process(ROOT_DIR.'/img/events/');
                      if ($uploader->processed) {
                        $update['logo'] = $uploader->file_dst_name;
                        $db->updateRecord('training_event',$update,$lastId);
                        $uploader->Clean();
                      } else {
                        echo 'viga: ' . $uploader->error;
                      }
                    }
                }
                
                if (isset($_FILES['file'])) {
                   $files = $this->array_transpon($_FILES['file']);
                   $count = count($files);
                   for($i=0;$i < $count ;$i++){
                      $file = array_shift($files);
                      $uploader = new Upload($file);
                      if ($uploader->uploaded) {
                         $uploader->Process(ROOT_DIR.'/img/events/');
                         if ($uploader->processed) {
                            $attachment['id'] = NULL;
                            $attachment['attachment'] = $uploader->file_src_name_body.$uploader->file_src_name_ext;
                            $attachment['eventId'] = $lastId;
                            print_r($attachment);echo '<br>';
                            $db->insertRecord('event_attachments',$attachment);
                         }  else {
                            echo 'viga: ' . $uploader->error;
                         }
                      }
                      $uploader->Clean();
                   }
                }
            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }
            $this->_redirect(ROOT_URL . "admin/events");
        }

        $array['id'] = $id;
        $this->view->data = $db->getRecord('training_event',$array);

        $array1['id'] = $this->view->data['userId'];
        $type = $db->getRecord('users', $array1);
        $this->view->type = $type['type'];
        if($this->view->type == 1){
            $this->view->prefix = 'trainerprofile';
            $this->view->list = $db->getRecords('organisation');
            $this->view->data['organisationsevent'] = explode(',', $this->view->data['organisationsevent']);
        }else{
            $this->view->prefix = 'organisationprofile';
            $this->view->list = $db->getRecords('trainers');
            $this->view->data['teachersevent'] = explode(',', $this->view->data['teachersevent']);
        }
         $this->view->formList = $db->getRecords('forms', 'userId='.$this->session->user);

        $this->view->data1 = $db->getRecords('event_dates', $wheredate);
        $this->view->files = $db->getRecords('event_attachments', $wheredate);
    }

    public function approveAction(){
        $db = new DB_Api();

        $id = (int)$this->getRequest()->getParam('id');

        $params['published'] = 1;
        $db->updateRecord('training_event', $params, $id);
        $eventsid = array();
        $eventsid['id'] = $id;
        $ev = $db->getRecord('training_event', $eventsid);
        $tr = array();
        $tr['userId '] = $ev['userId'];
        $trainer = $db->getRecord('trainers', $tr);
        if(empty ($trainer)){
            $trainer = $db->getRecord('organisation', $tr);
        }
                        $to = $trainer['email'];
			$subject = "New contact from the website";
			//headers and subject
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From:Admin \r\n";
			$body = "Your event  approved";
                        mail($to, $subject, $body, $headers);
        

        $this->_redirect($_SERVER['HTTP_REFERER']);
    }
     public function notapproveAction(){
        $db = new DB_Api();

        $id = (int)$this->getRequest()->getParam('id');
        $params['published'] = 0;
        $db->updateRecord('training_event', $params, $id);
        $eventsid = array();
        $eventsid['id'] = $id;
        $ev = $db->getRecord('training_event', $eventsid);
        $tr = array();
        $tr['userId '] = $ev['userId'];
        $trainer = $db->getRecord('trainers', $tr);
                        $to = $trainer['email'];
			$subject = "New contact from the mitteformaalne.ee";
			//headers and subject
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From:Admin \r\n";
			$body = $_POST['email'];
                        mail($to, $subject, $body, $headers);
        $this->_redirect($_SERVER['HTTP_REFERER']);
    }
       public function eventremoveAction(){
        $db = new DB_Api();
        $user = new User();
        $id = (int)$this->getRequest()->getParam('id');
        $array['id'] = $id;
        $data = $db->getRecord('training_event',$array);
        $filename = "img/events/".$data['logo'];
        @unlink($filename);
        $where = "Id=$id";
        $where1 = "eventId=$id";
        $user->deleteRecord('training_event', $where);
        $user->deleteRecord('event_dates', $where1);
        $this->_redirect(ROOT_URL . "profile/trainingedit");
    }

     public function contestapproveAction(){
        $db = new DB_Api();

        $id = (int)$this->getRequest()->getParam('id');
        $params['level'] = 0;
        $db->updateRecord('users', $params, $id);

        $this->_redirect($_SERVER['HTTP_REFERER']);
    }

    public function contestnotapproveAction(){
        $db = new DB_Api();

        $id = (int)$this->getRequest()->getParam('id');
        $params['level'] = 1;
        $db->updateRecord('users', $params, $id);

        $this->_redirect($_SERVER['HTTP_REFERER']);
    }
}

