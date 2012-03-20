<?php

class ProfileController extends Lib_Controller_Action {

    public function  init() {

        if(!$this->session->user && $this->getRequest()->getActionName()!= 'user'){
             $this->_redirect(ROOT_URL . "index");
        }

        if($this->getRequest()->getActionName()!= 'user'){
            $userClass = new User();
            $userInfo = $userClass->getUser($this->session->user);
            $this->view->typeId = $userInfo['type'];
            $this->view->level = $userInfo['level'];
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

    public function indexAction() {
        if(!$this->session->user){
             $this->_redirect(ROOT_URL . "index");
        }

        $userClass = new User();
        $db = new DB_Api();



        $request = $this->getRequest();
        $username = (string)$this->getRequest()->getParam('username');
        if($username != ''){
             $where = "username='".$username."'" ;
        }else{
            $where = "users.id='".$this->session->user."'" ;
        }

        $user = $db->getRecords('users', $where);


        $this->view->contests = $db->getRecords('contests', 'published=1');

        if(empty($user) && !array($user)){
            $user = $userClass->getUser($this->session->user);
        }
        $user = @current($user);

        if($this->session->user != $user['id']){
            $userClass->incrementView($this->session->user,$user['id']);
        }


        $this->view->assignedUsers = $userClass->getAssignedUsers($user['id']);

        $this->view->contestUsers = $userClass->getContestUsers($user['id']);

        $this->view->userInfo =  $userClass->getUserByUsername($user['username']);


    }


    public function exporteventsAction(){

        $userClass = new User();
        $db = new DB_Api();

        $request = $this->getRequest();
        $username = (string)$this->getRequest()->getParam('username');
        if($username != ''){
             $where = "username='".$username."'" ;
        }else{
            $where = "users.id='".$this->session->user."'" ;
        }

        $user = $db->getRecords('users', $where);


        $this->view->contests = $db->getRecords('contests', 'published=1');

        if(empty($user) && !array($user)){
            $user = $userClass->getUser($this->session->user);
        }
        $user = @current($user);

        if($this->session->user != $user['id']){
            $userClass->incrementView($this->session->user,$user['id']);
        }


//        $this->view->assignedUsers = $userClass->getAssignedUsers($user['id'],'AND registers.approved=0'); Example
         $this->view->assignedUsers = $userClass->getAssignedUsers($user['id']);

    }

    public function contestmoreAction(){
        $id = (int)$this->getRequest()->getParam('id');
        $db = new DB_Api();
        $array['id'] = $id;

        $item = $db->getRecord('contests', $array);

        $this->view->item = $item;
        if($item['languages'] != ''){


        $larray['id'] = $item['languages'];

        $this->view->languages = $db->getRecords('languages','id IN ('.$item['languages'].')');
        }

    }


    public function contestassignAction(){
        $id = (int)$this->getRequest()->getParam('id');

        $db = new DB_Api();
        $form = new Form();

        $array['id'] = $id;
        $item = $db->getRecord('contests', $array);
//        var_dump($item);exit;

        if(isset($item['formId'])){
            $formId = (int)$item['formId'];
            $tmps = $form->getFormFields($formId);
            foreach($tmps as $tmp){
                if(!isset($newArray[$tmp['id']])){
                    $newArray[$tmp['id']] = $tmp;
                }
                if(!empty($tmp['options'])){
                    $newArray[$tmp['id']]['option'][] = $tmp;
                }
            }
            if(isset($newArray)){
                $this->view->formElements = $newArray;
            }
        }

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            try{
                $params = $this->realRequestParams($params);

                $array1['eventId'] = $params['id'];
                $array1['type'] = 1;
                $array1['approved'] = 0;
                $db->insertRecord('registers', $array1);

                $lastId = $db->insert_id();

                unset($params['id']);
                $valueArray = $params['field'];
                if(count($valueArray) > 0){
                    foreach($valueArray as $key => $param){
                        $array2['fieldId']      =   $key;
                        $array2['value']        =   $param;
                        $array2['registeredId'] =   $lastId;
                        $db->insertRecord('values', $array2);
                    }
                }
            }catch(Exception $ex){

            }
            $this->view->message = "Edukalt saadetud!";
        }

    }





    public function assignedinfoAction(){
        $id = (int)$this->getRequest()->getParam('id');
        if(!$id){
            $this->_redirect(ROOT_URL . "profile");
        }

        $userClass = new User();

        $this->view->info = $userClass->getAssignedUser($id);

    }

    public function contestsAction(){
        $db = new DB_Api();
        $sesuser = $this->session->user;

        $where = "userId=$sesuser";

        $this->view->items = $db->getRecords('contests', $where);

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
                $params['userId'] = $this->session->user;

                $assignValues = @$params['assign'];
                unset($params['assign']);

                $db->insertRecord('contests',$params);

                $upload = new Zend_File_Transfer_Adapter_Http();

                $lastId = $db->insert_id();

                if(isset($assignValues['formId'])){
                        $params222['formId'] = $assignValues['formId'];
                        $db->updateRecord('contests', $params222, $lastId);
                    }

                if(isset($_FILES['assign'])){

                        $uploader = new Upload($_FILES['assign']);


                        if ($uploader->uploaded) {
                          $image_name = $lastId.'_form_'.$uploader->file_src_name_body;
                          $uploader->file_new_name_body = $image_name;
                          $uploader->Process(ROOT_DIR.'/img/forms/');

                          if ($uploader->processed) {
                            $update['assignValue'] = $uploader->file_dst_name;
                            $db->updateRecord('contests', $update, $lastId);
                            $uploader->Clean();
                          } else {
                            echo 'error : ' . $uploader->error;
                          }
                        }

                    }


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
                        echo 'error : ' . $uploader->error;
                      }
                    }
                }



                $this->_redirect('profile/contests');

            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }
        }
        $this->view->error = $error;
        $this->view->formList = $db->getRecords('forms', "userId='".$this->session->user."'AND type=1");
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
                $textModel->checkEmpty($params,$checkArray);
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

                    $params['published'] = 0;

                $assignValues = @$params['assign'];
                unset($params['assign']);
                $db->updateRecord('contests',$params,$params['id']);
                if(isset($assignValues['formId'])){
                        $params222['formId'] = $assignValues['formId'];
                        $db->updateRecord('contests', $params222, $params['id']);
                    }

                if(isset($_FILES['assign'])){

                        $uploader = new Upload($_FILES['assign']);


                        if ($uploader->uploaded) {
                          $image_name = $lastId.'_form_'.$uploader->file_src_name_body;
                          $uploader->file_new_name_body = $image_name;
                          $uploader->Process(ROOT_DIR.'/img/forms/');

                          if ($uploader->processed) {
                            $update['assignValue'] = $uploader->file_dst_name;
                            $db->updateRecord('contests', $update, $params['id']);
                            $uploader->Clean();
                          } else {
                            echo 'error : ' . $uploader->error;
                          }
                        }

                    }
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
                        echo 'error : ' . $uploader->error;
                      }
                    }
                }

//                $this->_redirect('admin/contests');

            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }
        }
        $this->view->data = $db->getRecord('contests', $array);
        $this->view->formList = $db->getRecords('forms', "userId='".$this->session->user."'AND type=1");
    }

    public function contestremoveAction(){
        $db = new DB_Api();
        $id = (int)$this->getRequest()->getParam('id');
        $where = "id=$id";
        $db->deleteRecord('contests', $where);
        $this->_redirect('profile/contests');
    }

    public function trainingaddAction(){

        if(!$this->session->user){
             $this->_redirect(ROOT_URL . "index");
        }

        $db = new DB_Api();
        $userClass = new User();

        $error = '';
        $id = $this->session->user;
        $this->view->languages = $db->getRecords('languages');
        $this->view->sectors = $db->getRecords('sector');
        $this->view->eventTypes = $db->getRecords('training_type');

        $this->view->type = $this->session->type;
        if($this->session->type == 1){
            $this->view->prefix = 'trainerprofile';
            $this->view->list = $db->getRecords('organisation');
        }else{
            $this->view->prefix = 'organisationprofile';
            $this->view->list = $db->getRecords('trainers', "", "", 'order by surname ASC');
        }

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            try{
                $datesArray = array();
                $textModel = new Text();
                $params = $this->realRequestParams($params);
                $checkArray = array('title','keywords','description','type','location','price','deadline');
                //$textModel->checkEmpty($params,$checkArray);
                $this->view->data = $params;
//                var_dump($_FILES['file']);exit;
                if(isset($params['free'])) {
                    $params['price'] = 0;
                    unset ($params['free']);
                }

                $params['deadline'] = date('Y-m-d 00:00:00',strtotime($params['deadline']));

                $dateArray = $params['date'];
                $locationArray = $params['location'];

                $assignValues = @$params['assign'];
                unset($params['date'],$params['location']);
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
                $params['userId'] = $this->session->user;
                $db->insertRecord('training_event',$params);
                 /*sent the email admin*/
                $admin_email = '';
                $userarray['id'] = $this->session->user;
                $username = $db->getRecord('users', $userarray);
                        $to = $admin_email;

			$subject = "New event";
			//headers and subject
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From: ".$username['username']."\r\n";
			$body = $params['title']."<br />";
                        //mail($to, $subject, $body, $headers); TODO: Uncomment this before commit

                $lastId = $db->insert_id();

                if(count($dateArray) > 0){
                    $count = count($dateArray);
                    for($i=0;$i < $count ;$i++){
                        $datesArray['date'] = date('Y-m-d 00:00:00',strtotime(array_shift($dateArray)));
                        $datesArray['location'] = array_shift($locationArray);
                        $datesArray['eventid'] = $lastId;
                        $db->insertRecord('event_dates', $datesArray);

                    }
                }

                    if(isset($assignValues['formId'])){
                        $params222['assignValue'] = $assignValues['formId'];
                        $db->updateRecord('training_event', $params222, $lastId);
                    }

                    if(isset($_FILES['assign'])){

                        $uploader = new Upload($_FILES['assign']);


                        if ($uploader->uploaded) {
                          $image_name = $lastId.'_form_'.$uploader->file_src_name_body;
                          $uploader->file_new_name_body = $image_name;
                          $uploader->Process(ROOT_DIR.'/img/forms/');

                          if ($uploader->processed) {
                            $update['assignValue'] = $uploader->file_dst_name;
                            $db->updateRecord('training_event', $update, $lastId);
                            $uploader->Clean();
                          } else {
                            echo 'error : ' . $uploader->error;
                          }
                        }

                        $datesArray['value'] = $assignValues['formId'];
                        $datesArray['eventId'] = $lastId;
                        $db->insertRecord('assign_value', $datesArray);
                    }

                    if(isset($assignValues['assignLink'])){
                        $datesArray2['assignValue'] = $assignValues['assignLink'];
                        $db->updateRecord('training_event', $datesArray2, $lastId);
                    }


                $upload = new Zend_File_Transfer_Adapter_Http();

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
                        echo 'error : ' . $uploader->error;
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
                            $attachment['attachment'] = $uploader->file_src_name_body.'.'.$uploader->file_src_name_ext;
                            $attachment['eventId'] = $lastId;
                            $db->insertRecord('event_attachments',$attachment);
                         }  else {
                            echo 'error : ' . $uploader->error;
                         }
                      }
                      $uploader->Clean();
                   }
                }
                $this->_redirect(ROOT_URL . "admin/events");
            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }
        }
        $this->view->formList = $db->getRecords('forms', "userId='".$this->session->user."'AND type=0");

        $this->view->error = $error;

    }
    public function trainingeditAction(){
        $db = new DB_Api();
        $sesuser = $this->session->user;

        $where = "userId=$sesuser";

        $this->view->itemsnew = $db->getRecords('training_event',$where);


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
    public function editorganizationAction(){

      $db = new DB_Api();

      $user = new User();

      $this->view->organisations = $db->getRecords('organisation');
      $this->view->counties = $db->getRecords('counties');

      $this->view->data = $user->getWholeInfo($this->session->user,'organisation');
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

            $this->view->data = $params;
        }
        $this->_redirect(ROOT_URL . "profile/editteacher");

    }

    public function trainersorgAction(){
        if(!$this->session->user){
             $this->_redirect(ROOT_URL . "index");
        }
        $db = new DB_Api();

        $this->view->items = $db->getRecords('trainers');

    }

    public function profileinfoAction(){
        if(!$this->session->user){
             $this->_redirect(ROOT_URL . "index");
        }
        $db = new DB_Api();
        $id = $this->session->user;

        $user = new User();

        $topics = $db->getRecords('topics','','','ORDER BY parentId');
        $targets = $db->getRecords('target_groups','','','ORDER BY parentId');

        $userInfo =  $user->getTrainerInfo($this->session->user);


        $userTopicArray = array();
        $userTargetArray = array();
        $userLanguagesArray = array();

        if(isset($userInfo['topics'])){
            $userTopicArray = explode(',',$userInfo['topics']);
        }
        if(isset($userInfo['targets'])){
            $userTargetArray = explode(',',$userInfo['targets']);
        }

        if(isset($userInfo['languages'])){
            $userLanguagesArray = explode(',',$userInfo['languages']);
        }

        $tmpArray = array();
        foreach($targets as &$target){
            $target['checked'] = false;
            if(in_array($target['id'],$userTargetArray)){
                $target['checked'] = true;
            }

            if($target['parentId'] == 0){
                $tmpArray[$target['id']] = $target;
            }else{
                $tmpArray[$target['parentId']]['children'][] = $target;
            }
        }

        $tmpArray2 = array();
        foreach($topics as &$topic){
            $topic['checked'] = false;
            if(in_array($topic['id'],$userTopicArray)){
                $topic['checked'] = true;
            }

            if($topic['parentId'] == 0){
                $tmpArray2[$topic['id']] = $topic;
            }else{
                $tmpArray2[$topic['parentId']]['children'][] = $topic;
            }
        }

        $this->view->targets = $tmpArray;
        $this->view->topics = $tmpArray2;

        $this->view->data = $userInfo;

        $languages = $db->getRecords('languages');


        foreach ($languages as &$language){
            $language['checked'] = false;
            if(in_array($language['id'], $userLanguagesArray)){
               $language['checked'] = true;
            }
        }
        $this->view->languages  = $languages;

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();

            $textModel = new Text();
            $params = $this->realRequestParams($params);


            if(!isset($params['targets'])){
                $params['targets'] = null;
            }
            if(!isset($params['topics'])){
                $params['topics'] = null;
            }
            if(!isset($params['languages'])){
                $params['languages'] = null;
            }



            if(is_array($params['targets']) && count($params['targets']) > 0){
                $params['targets'] = implode(',',$params['targets']);
            }

            if(is_array($params['topics']) && count($params['topics']) > 0){
                $params['topics'] = implode(',',$params['topics']);
            }

            if(is_array($params['languages']) && count($params['languages']) > 0){
                $params['languages'] = implode(',',$params['languages']);
            }





//            foreach($params['experience'] as &$exp){
//                $exp['trainerId'] = $id;
//                $db->insertRecord('experience',$exp);
//            }

            unset($params['experience']);
            $params['trainerId'] = $userInfo['id'];
            $db->updateRecord('trainers_info',$params,$userInfo['trainerId']);


            $this->_redirect(ROOT_URL . "profile/profileinfo");
        }
    }

    public function edittrainerAction(){
        $db = new DB_Api();
        $user = new User();

        $this->view->organisations = $db->getRecords('organisation');
        $this->view->counties = $db->getRecords('counties');
        $this->view->data = $user->getWholeInfo($this->session->user,'trainers');
        if($this->getRequest()->isPost()){

            $params = $this->getRequest()->getParams();
            try{

                $textModel = new Text();
                $params = $this->realRequestParams($params);

                $checkArray = array('username','email','name','surname','address','phone');
                $textModel->checkEmpty($params,$checkArray);


                $id = $this->session->user;


                $where['userId'] = $id;
                $where1 = "Id=$id";
                $params1['username'] = $params['username'];
                $photoUrl = $params['username'];
                $photoUrl = $textModel->comunicalStr($photoUrl);
                unset ($params['username']);
                $db->updateRecord('users', $params1, $id);
                if(!isset($params['publicPhone'] )){
                    $params['publicPhone'] = 0;
                }
                if(!isset($params['publicAdrress'] )){
                    $params['publicAdrress'] = 0;
                }
                $db->updateRecordtrainer('trainers', $params, $id);
                $user_id = $this->session->user;
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
                        echo 'error : ' . $uploader->error;
                      }
                    }
                }

            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }

            $this->view->data = $params;
        }

        $this->_redirect(ROOT_URL . "profile/editteacher");
    }

    public function newformAction(){
        if($this->getRequest()->isPost()){
            $db = new DB_Api();
            $params = $this->getRequest()->getParams();
//            var_dump($params);exit;
            $textModel = new Text();
            $error = '';

            try{
                $params = $this->realRequestParams($params);
//                var_dump($params);exit;
                $textModel->checkEmpty($params);
//                var_dump($params);exit;
                if(isset($params['adddefault']) && !empty($params['adddefault'])){
                    $addDef = true;
                    unset($params['adddefault']);
                }


                $params['userId'] = $this->session->user;

                $res = $db->insertRecord('forms',$params);
                if($db->insert_id()){

                    $formId = $db->insert_id();

                    if($addDef){

                       $arraf = array(
                          "firstname" => "text",
                          "lastname" => "text",
                          "mothertongue" => array('select' => array('eesti','inglise','vene','muu')),
                          "organisation" => "text",
                          "typeoforganisation" => array('select' => array('huvikool','kirik','kultuuri- vĆµi spordiasutus','maavalitsus')),
                          "role" => "text",
                          "email" => "text",
                          "phone" => "text",
                          "address" => "text",
                          "why_interested" => "textarea",
                          "why_important" => "textarea",
                          "everyday_needs" => "textarea",
                          "where_did_you_hear_about_us" => "textarea",
                          "do_you_take_part_of_full_amount" => "textarea",
                          "other_comments" => "textarea",
                          "special_needs" => "text",
                          "motivation" => "textarea",
                        );
                        $counter = 0;



                        foreach ($arraf as $key => $ai){
                           if(!is_array($ai)){
                                $insertFields['name'] = $key;
                                $insertFields['label'] = implode(' ',explode('_',ucfirst($key)));
                                $insertFields['formId'] = $formId;
                                $insertFields['formType'] = $ai;
                                $insertFields['ordering'] = $counter;

                                $db->insertRecord('form_conections', $insertFields);
                           }else{

                               foreach ($ai as $selectKey => $selectedItems){
                                    $insertFields['name'] = $key;
                                    $insertFields['label'] = implode(' ',explode('_',ucfirst($key)));
                                    $insertFields['formId'] = $formId;
                                    $insertFields['formType'] = $selectKey;
                                    $insertFields['ordering'] = $counter;

                                    $db->insertRecord('form_conections', $insertFields);


                                    $fieldId = $db->insert_id();

                                   foreach($selectedItems as $selectedItem){
                                       $insertFieldsoptions['elementId'] = $fieldId;
                                       $insertFieldsoptions['options'] = $selectedItem;

                                       $db->insertRecord('form_options', $insertFieldsoptions);
                                   }
                               }
                           }

                           $counter++;

                        }
                    }else{
                    $arraf = array(
                          "firstname" => "text",
                          "lastname" => "text",
                          "email" => "text",

                        );
                        $counter = 0;



                        foreach ($arraf as $key => $ai){
                           if(!is_array($ai)){
                                $insertFields['name'] = $key;
                                $insertFields['label'] = implode(' ',explode('_',ucfirst($key)));
                                $insertFields['formId'] = $formId;
                                $insertFields['formType'] = $ai;
                                $insertFields['ordering'] = $counter;

                                $db->insertRecord('form_conections', $insertFields);
                           }else{

                               foreach ($ai as $selectKey => $selectedItems){
                                    $insertFields['name'] = $key;
                                    $insertFields['label'] = implode(' ',explode('_',ucfirst($key)));
                                    $insertFields['formId'] = $formId;
                                    $insertFields['formType'] = $selectKey;
                                    $insertFields['ordering'] = $counter;

                                    $db->insertRecord('form_conections', $insertFields);


                                    $fieldId = $db->insert_id();

                                   foreach($selectedItems as $selectedItem){
                                       $insertFieldsoptions['elementId'] = $fieldId;
                                       $insertFieldsoptions['options'] = $selectedItem;

                                       $db->insertRecord('form_options', $insertFieldsoptions);
                                   }
                               }
                           }

                           $counter++;

                        }

                   $error[] = $res;
                }


                   $this->_redirect(ROOT_URL . "form/edit/".$formId);
                }
            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }
            $this->view->error = $error;
        }
    }

    public function updateAction(){
        $formId = (int)$this->getRequest()->getParam('id');

        $db = new DB_Api();
        $textModel = new Text();
        $formClass = new Form();
        $array['id'] = $formId;
        $array['userId'] = $this->session->user;
//        var_dump($array);exit;
        $record = $db->getRecord('forms',$array);

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);

            if(isset($params['add']) && !empty($params['add'])){
                $fieldArray['name'] = $params['fieldname'];
                $fieldArray['label'] = $params['fieldname'];
                $fieldArray['formId'] = $record['id'];
                $fieldArray['formType'] = "text";
                $fieldArray['ordering'] = 1;
                $db->insertRecord('form_conections', $fieldArray);

                $this->_redirect($_SERVER['HTTP_REFERER']);
            }
            if(isset($params['adoption']) && !empty($params['adoption'])){
                $fieldArray['elementId'] = $params['optionField'];
                $fieldArray['options'] = $params['optionvalue'];

                $db->insertRecord('form_options', $fieldArray);
                $this->_redirect($_SERVER['HTTP_REFERER']);

            }
            if(isset($params['delete']) && !empty($params['delete'])){
                echo "delete field";
            }
        }
        $newArray = array();
        $tmps = $formClass->getFormFields($record['id']);
        foreach($tmps as $tmp){
            if(!isset($newArray[$tmp['id']])){
                $newArray[$tmp['id']] = $tmp;
            }
            if(!empty($tmp['options'])){
                $newArray[$tmp['id']]['option'][] = $tmp;
            }
        }
        $this->view->formElements = $newArray;
    }

    public function editfieldAction(){
        $fieldId = (int)$this->getRequest()->getParam('id');
        $db = new DB_Api();
        $formModel = new Form();
        $userId = $this->session->user;
        $item = $formModel->getField($fieldId,$userId);
        if($item){
            $this->view->item = $item;
        }else{
            $this->_redirect('form');
        }

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            $params = $this->realRequestParams($params);
            unset($params['id']);
            $db->updateRecord('form_conections', $params, $fieldId);
            $this->_redirect('form/edit/'.$item['formId']);
        }
    }


    public function deletefieldAction(){
        $fieldId = (int)$this->getRequest()->getParam('id');
        $db = new DB_Api();
        $formModel = new Form();
        $userId = $this->session->user;
        $item = $formModel->getField($fieldId,$userId);
        if($item){
            $formModel->deleteField($item['id']);
        }else{
            $this->_redirect('form');
        }

        $this->_redirect($_SERVER['HTTP_REFERER']);

    }


    public function deleteoptionAction(){
        $optionId = (int)$this->getRequest()->getParam('id');
        $userId = $this->session->user;

        $formModel = new Form();

        if($formModel->isValidOption($optionId,$userId)){
            $formModel->deleteRecord('form_options', 'id='.$optionId);
        }
        $this->_redirect($_SERVER['HTTP_REFERER']);
    }

    public function formsAction(){
        $db = new DB_Api();
        $userModel = new User();



        $id = $this->session->user;

        $user = $userModel->getUser($id);

        $where = "userId=$id";
        $this->view->records = $db->getRecords('forms',$where);
    }

    public function editteacherAction(){
        $db = new DB_Api();

        $user = new User();

        $this->view->organisations = $db->getRecords('organisation');
        $this->view->counties = $db->getRecords('counties');
        if($this->view->typeId == 2){
            $this->view->data = $user->getWholeInfo($this->session->user,'organisation');
        }elseif($this->view->typeId == 1){
            $this->view->data = $user->getWholeInfo($this->session->user,'trainers');
        }



    }

    public function editaccountAction(){
        $db = new DB_Api();
        $id = $this->session->user;
        $where = "trainerId=$id";
        $user = new User();
        $topics = $db->getRecords('topics');
        $targets = $db->getRecords('target_groups');

        $tmpArray = array();
        foreach($targets as $target){
            if($target['parentId'] == 0){
                $tmpArray[$target['id']] = $target;
            }else{
                $tmpArray[$target['parentId']]['children'][] = $target;
            }
        }

        $tmpArray2 = array();
        foreach($topics as $topic){
            if($topic['parentId'] == 0){
                $tmpArray2[$topic['id']] = $topic;
            }else{
                $tmpArray2[$topic['parentId']]['children'][] = $topic;
            }
        }
        $this->view->targets = $tmpArray;
        $this->view->topics = $tmpArray2;
        $this->view->languages  = $db->getRecords('languages');
        $datas = array();
        $datas['trainerId'] = $this->session->user;
        $this->view->data = $user->getRecord('trainers_info', $datas);
//        echo '<pre>'; var_dump($this->view->data); echo '</pre>'; die;
        $this->view->data["targets"] = explode(',', $this->view->data["targets"]);
        $this->view->target = $this->view->data["targets"];
        @$this->view->data["topics"] = explode(',', $this->view->data["topics"]);
        $this->view->topic = $this->view->data["topics"];
        $this->view->exp  = $db->getRecordsexsp0('experience', $where);

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            try{
                $params = $this->getRequest()->getParams();

                $textModel = new Text();
                $params = $this->realRequestParams($params);
                if(!isset($params["targets"])){$params["targets"] = '';}
                if(!isset($params["topics"])){$params["topics"] = '';}
                if(!isset($params["experience"])){$params["experience"] = '';}
                if(!isset($params["languages"])){$params["languages"] = '';}
                $checkArray = array('description','topics','targets','languages');

                $textModel->checkEmpty($params,$checkArray);

                if(isset($params['targets']) && count($params['targets']) > 0){
                    $params['targets'] = implode(',',$params['targets']);
                }

                if(isset($params['topics']) && count($params['topics']) > 0){
                    $params['topics'] = @implode(',',$params['topics']);
                }



                if(isset($params['languages']) && count($params['languages']) > 0){
                    $params['languages'] = @implode(',',$params['languages']);
                }
                if($params['experience']){
                $where = "trainerId=$id";
                $db->deleteRecord('experience', $where);
//                var_dump($params['experience']);exit;
               foreach($params['experience'] as &$exp){
                    $exp['trainerId'] = $id;
                    $db->insertRecord('experience',$exp);
                }
                }
                unset($params['experience']);
                $user->updateRecordt('trainers_info', $params, $id);
//                $db->insertRecord('trainers_info',$params);


               $this->_redirect(ROOT_URL . "profile/editaccount");
            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }
        }
        if(isset($error)){
            $this->view->error = $error;

         }
    }
    public function deleteformAction(){
        $formId = (int)$this->getRequest()->getParam('id');
        $db = new DB_Api();
        $textModel = new Text();
        $formClass = new Form();

        $id = $this->session->user;


        $where = "id=$formId AND userId=".$id;
        $res = $db->getRecords('forms',$where);

        if(!empty ($res)){
            $where1 = "formId=$formId";
            $res1 = $db->getRecords('form_conections',$where1);
            foreach ($res1 as $key => $value) {
                $where2 = "elementId=$value[id]";
                $db->deleteRecord('form_options', $where2);
            }
            $where3 = "formId=$formId";
            $db->deleteRecord('form_conections', $where3);
            $where4 = "id=$formId AND userId=".$id;
            $db->deleteRecord('forms',$where4);

        }

        $this->_redirect('profile/forms');
    }

    public function userAction(){
        $db = new DB_Api();
        $userClass = new User();

        $request = $this->getRequest();
        $username = (string)$this->getRequest()->getParam('username');
        $where = "username='".$username."'" ;
        $user = $db->getRecords('users', $where);
//        echo '<pre>'; print_r($user); echo '</pre>';die;
        if($user[0]['type'] == 1){
            $us = $userClass->getTrainerInfo($user[0]['id']);
    //        var_dump($us);exit;
            if(empty($user)){
                 $this->_redirect('profile');

            }elseif(is_array($user)){
                if(!$this->session->user){
                    $params = array();
                    $params['viewerId'] = 0;
                    $params['viewedId'] = $user[0]['id'];
                    $db->insertRecord('views',$params);

                }elseif($this->session->user != $user[0]['id']){
                    $params = array();
                    $params['viewerId'] = $this->session->user;
                    $params['viewedId'] = $user[0]['id'];
                    $db->insertRecord('views',$params);
                }
                $where3 = "viewedId='".$user[0]['id']."'" ;
                $this->view->viewers = count($db->getRecords('views', $where3));
                $this->view->data = $userClass->getTrainerInfo($user[0]['id']);
                $org = array();
                $org['id'] = $this->view->data['organisation'];
                $this->view->org = $db->getRecord('organisation', $org);

                $country = array();
//                echo '<pre>'; print_r($this->view->data); echo '</pre>';die;
                $country['id'] = $this->view->data['county'];
                $this->view->country = $db->getRecord('counties', $country);

                $datas['trainerId'] = $user['0']['id'];
//                $this->view->exp = $userClass->getRecord('trainers_info', $datas);
                $where = 'trainerId="' . $user['0']['id'] . '"'; 
                $this->view->exp  = $db->getRecordsexsp0('experience', $where);
                $this->view->training_info = $userClass->getRecord('trainers_info', $datas);

                $topics = $db->getRecords('topics');
                $targets = $db->getRecords('target_groups');

                $tmpArray = array();
                foreach($targets as $target){
                    if($target['parentId'] == 0){
                        $tmpArray[$target['id']] = $target;
                    }else{
                        $tmpArray[$target['parentId']]['children'][] = $target;
                    }
                }

                $tmpArray2 = array();
                foreach($topics as $topic){
                    if($topic['parentId'] == 0){
                        $tmpArray2[$topic['id']] = $topic;
                    }else{
                        $tmpArray2[$topic['parentId']]['children'][] = $topic;
                    }
                }
                $this->view->targets = $tmpArray;
                $this->view->topics = $tmpArray2;
                @$this->view->data["topics"] = explode(',', $this->view->training_info["topics"]);
                $this->view->topic = $this->view->data["topics"];
                $this->view->languages  = $db->getRecords('languages');
                $this->view->data["targets"] = explode(',', $this->view->training_info["targets"]);
                $this->view->target = $this->view->data["targets"];

//                echo '<pre>'; var_dump($this->view->data); echo '</pre>';
//                echo '<pre>'; var_dump($userClass->getRecord('trainers_info', $datas)); echo '</pre>'; die;
                $this->view->user = current($user);
            }
        }elseif($user[0]['type'] == 2){
             $us = $userClass->getOrganisationInfo($user[0]['id']);
    //        var_dump($us);exit;
            if(empty($user)){
                 $this->_redirect('profile');

            }elseif(is_array($user)){
                if(!$this->session->user){
                    $params = array();
                    $params['viewerId'] = 0;
                    $params['viewedId'] = $user[0]['id'];
                    $db->insertRecord('views',$params);

                }elseif($this->session->user != $user[0]['id']){
                    $params = array();
                    $params['viewerId'] = $this->session->user;
                    $params['viewedId'] = $user[0]['id'];
                    $db->insertRecord('views',$params);
                }
                $where3 = "viewedId='".$user[0]['id']."'" ;
                $this->view->viewers = count($db->getRecords('views', $where3));
                $this->view->data = $userClass->getOrganisationInfo($user[0]['id']);

            $this->view->user = current($user);
            }
        }


//        $userClass->

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
            $this->_redirect(ROOT_URL . "profile/trainingedit");
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


}
