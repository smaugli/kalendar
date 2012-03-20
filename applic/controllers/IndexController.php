<?php

class IndexController extends Lib_Controller_Action {



    public function indexAction() {
        $request = $this->getRequest();
        $db = new DB_Api();
        $userClass = new User();
        $eventModel = new Events();
         if($this->getRequest()->isPost()){
                 $params = $this->getRequest()->getParams();
                 if(isset($params['submit']) && isset($params['findMode'])){

                     if(($params['submit'] == 'search event' || $params['submit'] == 'Otsi') && $params['findMode'] == 'findEvent'){
                         $search = $params['search'];
                         if($search != ''){
                             $events = $userClass->searchEvent($search);
                             $this->view->event = $events;

                         }
                     }
                     if(($params['submit'] == 'search trainer' || $params['submit'] == 'Otsi') &&  $params['findMode'] == 'findTrainer'){
                         $search = $params['search'];
                         if($search != ''){
                             $trainer = $userClass->searchTrainer($search);
                             $this->view->trainer = $trainer;
                         }
                     }

                     if(($params['submit'] == 'search organisation' || $params['submit'] == 'Otsi') && $params['findMode'] == 'findOrganisation'){
                         $search = $params['search'];
                         if($search != ''){
                             $organisation = $userClass->searchOrganisation($search);
                             $this->view->organisation = $organisation;
                         }
                     }
                 }
         }
        $m = $this->getRequest()->getParam('month');
        $y = $this->getRequest()->getParam('year');
        if(!empty ($m)){
            $month = $m;
        }else{
            $month = date('m');
        }

        if(!empty ($y)){
            $year = $y;
        }else{
            $year = date('Y');
        }

        $date = $year.'-'.$month;
        $newArray = array();
        $eventList = $eventModel->getEvents($date);
//        var_dump($eventList);exit;
        foreach($eventList as $event){

            $key = date('Y-m-d',strtotime($event['date']));

            $newArray[$key][] = $event;
        }

        $afterDate = date("Y,m",strtotime($date. " +1 month"));
        $beforeDate = date("Y,m",strtotime($date . " -1 month"));

        //$beforeDate
        list($ay,$am) = explode(',',$afterDate);
        list($by,$bm) = explode(',',$beforeDate);



        $num = cal_days_in_month(CAL_GREGORIAN, $month, $year) ;

        $this->view->numdays = $num;
        $this->view->dayofweek = date('w',strtotime($date.'-06'));
        $this->view->date = $date;
        $this->view->beforeUrl = $by.'/'.$bm;
        $this->view->afterUrl = $ay.'/'.$am;

        $this->view->calendarItems = $newArray;

    }

    public function registerAction(){
        if($this->session->user){
            $url = ROOT_URL . "index/account";
            $this->_redirect($url);
        }
        $db = new DB_Api();
        $error = array();

        $this->view->organisations = $db->getRecords('organisation');
        $this->view->counties = $db->getRecords('counties');

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();

            try{
                $textModel = new Text();
                $params = $this->realRequestParams($params);
//                $checkArray = array('username','password','email','name','surname','address','county','phone');
                $checkArray = array('username','password','email','name','surname','county','phone');

                $textModel->checkEmpty($params,$checkArray);

                $usersData['username'] = $params['username'];
                $usersData['password'] = md5($params['password']);
                $usersData['type'] = 1;

                $textModel->checkDublicate('users','username',$usersData['username']);
                $textModel->checkEmail($params['email']);
                $textModel->checkDublicate('trainers','email',$params['email']);

                $photoUrl = $params['username'];
                $photoUrl = $textModel->comunicalStr($photoUrl);

                unset($params['username'],$params['password']);

                $db->insertRecord('users',$usersData);

                $params['userId'] = $db->insert_id();
                $db->insertRecord('trainers',$params);

                if(isset($_FILES['photo'])){
                    $image_name = $photoUrl;
                    $uploader = new Upload($_FILES['photo']);

                    if ($uploader->file_is_image && $uploader->uploaded) {
                      $uploader->file_new_name_body = $image_name;
                      $uploader->Process(ROOT_DIR.'/img/trainers/');
                      if ($uploader->processed) {
                        $update['photo'] = $uploader->file_dst_name;
                        $db->updateRecord('trainers',$update,$db->insert_id());
                        $uploader->Clean();
                      } else {
                        echo 'error : ' . $uploader->error;
                      }
                    }
                }

                $this->session->user = $params['userId'];
                $this->session->type = 1;

                $this->_redirect(ROOT_URL . "index/account");

            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }

            $this->view->data = $params;
        }

        $this->view->error = $error;


    }


    public function accountAction(){
        $error = array();
        if(!$this->session->user){
             $this->_redirect(ROOT_URL . "index");
        }
        $db = new DB_Api();
        $id = $this->session->user;


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

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
//            var_dump($params);exit;
            try{
                $params = $this->getRequest()->getParams();

                $textModel = new Text();
                $params = $this->realRequestParams($params);
                if(!isset($params["targets"])){$params["targets"] = '';}
//                if(!isset($params["topics"])){$params["topics"] = '';}
                if(!isset($params["experience"])){$params["experience"] = '';}
                if(!isset($params["languages"])){$params["languages"] = '';}
                $checkArray = array('description','targets','languages','experience','experience[0][target]','experience[0][important]');

                $textModel->checkEmpty($params,$checkArray);

                if(isset($params['targets']) && count($params['targets']) > 0){
                    $params['targets'] = implode(',',$params['targets']);
                }

                if(isset($params['topics']) && count($params['topics']) > 0){
                    $params['topics'] = implode(',',$params['topics']);
                }



                if(isset($params['languages']) && count($params['languages']) > 0){
                    $params['languages'] = implode(',',$params['languages']);
                }


                foreach($params['experience'] as &$exp){
                    $exp['trainerId'] = $id;
                    $db->insertRecord('experience',$exp);
                }

                unset($params['experience']);
                $params['trainerId'] = $id;
                $db->insertRecord('trainers_info',$params);

                $paramsform['userId'] = $this->session->user;
                $paramsform['open'] = 1;
                $paramsform['name'] = 'vaikimisi vorm';
                $res = $db->insertRecord('forms',$paramsform);
                if($db->insert_id()){

                    $formId = $db->insert_id();



                       $arraf = array(
                          "firstname" => "text",
                          "lastname" => "text",
                          "mothertongue" => array('select' => array('eesti','inglise','vene','muu')),
                          "organisation" => "text",
                          "typeoforganisation" => array('select' => array('huvikool','kirik','kultuuri- või spordiasutus','maavalitsus')),
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




                }

               $this->_redirect(ROOT_URL . "profile");
            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }
        }
         $this->view->error = $error;
    }


    public function logoutAction(){
        $this->session->__unset('user');
        $this->_redirect(ROOT_URL . "index");

    }

    public function organizationAction() {
        $add = $this->getRequest()->getParam('id');

        $db = new DB_Api();

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();

            try{
                $textModel = new Text();
                $params = $this->realRequestParams($params);
//                $checkArray = array('username','password','email','name','address','phone');
                $checkArray = array('username','password','email','name','phone');

                $textModel->checkEmpty($params,$checkArray);

                $usersData['username'] = $params['username'];
                $usersData['password'] = md5($params['password']);
                $usersData['type'] = 2;

                $textModel->checkDublicate('users','username',$usersData['username']);

                $photoUrl = $params['username'];
                $photoUrl = $textModel->comunicalStr($photoUrl);

                unset($params['username'],$params['password']);

                $db->insertRecord('users',$usersData);

                $params['userId'] = $db->insert_id();

                $textModel->checkEmail($params['email']);
                $textModel->checkDublicate('organisation','email',$params['email']);

                $db->insertRecord('organisation',$params);

                $lastId = $params['userId'];


                   if(isset($_FILES['logo'])){
                        $image_name = $photoUrl;
                        $uploader = new Upload($_FILES['logo']);

                        if ($uploader->file_is_image && $uploader->uploaded) {
                          $uploader->file_new_name_body = $image_name;
                          $uploader->Process(ROOT_DIR.'/img/organizations/');
                          if ($uploader->processed) {
                            $update['logo'] = $uploader->file_dst_name;
                            $db->updateRecord('organisation',$update,$db->insert_id());
                            $uploader->Clean();
                          } else {
                            echo 'error : ' . $uploader->error;
                          }
                        }
                    }
                if(isset($add) || $add == 'add'){
                    $this->_redirect(ROOT_URL . "index/register");
                }else{
                    $this->session->user = $lastId;
                    $this->session->type = 2;
                    $paramsform['userId'] = $this->session->user;
                $paramsform['open'] = 1;
                $paramsform['name'] = 'vaikimisi vorm';
                $res = $db->insertRecord('forms',$paramsform);
                if($db->insert_id()){

                    $formId = $db->insert_id();



                       $arraf = array(
                          "firstname" => "text",
                          "lastname" => "text",
                          "mothertongue" => array('select' => array('eesti','inglise','vene','muu')),
                          "organisation" => "text",
                          "typeoforganisation" => array('select' => array('huvikool','kirik','kultuuri- või spordiasutus','maavalitsus')),
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




                }
                    $this->_redirect(ROOT_URL . "profile");
                }

            }catch(Exception $ex){
                $error[] = $ex->getMessage();
            }

            $this->view->data = $params;
            $this->view->error = $error;
        }



    }

    public function moreAction() {
        $request = $this->getRequest();


    }

    public function eventAction(){
        $db = new DB_Api();
        $id = (int)$this->getRequest()->getParam('id');
        $wheredate = "eventId=$id";
        $eventModel = new Events();

        $this->view->event = $eventModel->getEvent($id);
        $this->view->data1 = $db->getRecords('event_dates', $wheredate);

    }

    public function loginAction(){
        if($this->session->user){
             $this->_redirect(ROOT_URL . "profile");
        }

        if($this->getRequest()->isPost()){

            $db = new DB_Api();

            $params = $this->getRequest()->getParams();

            $checkData['username'] = $params['username'];
            $checkData['password'] = md5($params['password']);

            $record = $db->getRecord('users',$checkData);

            if(isset($record['id']) && !empty($record['id'])){
                $this->session->user = $record['id'];
                $this->session->type = $record['type'];
                $this->_redirect(ROOT_URL . "profile");
            }
        }
    }

    public function assignAction(){
        $id = (int)$this->getRequest()->getParam('id');

        $db = new DB_Api();
        $form = new Form();

        $array['id'] = $id;
        $item = $db->getRecord('training_event', $array);

        $this->view->assignType = $item['assignType'];

        if($item['assignType'] == 1){
            $formId = (int)$item['assignValue'];
            $tmps = $form->getFormFields($formId);

            foreach($tmps as $tmp){
                if(!isset($newArray[$tmp['id']])){
                    $newArray[$tmp['id']] = $tmp;
                }
                if(!empty($tmp['options'])){
                    $newArray[$tmp['id']]['option'][] = $tmp;
                }
            }
            if(!isset($newArray)){
                $this->view->messageform = "Form is not chosen";
            }else{
                $this->view->formElements = $newArray;
            }

        }elseif($item['assignType'] == 2){
            $this->view->link = $item['assignValue'];
        }elseif($item['assignType'] == 3){
            $this->view->linkapply = $item['assignValue'];
        }

        if($this->getRequest()->isPost()){
            $params = $this->getRequest()->getParams();
            try{
                $formId = (int)$item['assignValue'];


                $formarray['id'] = $formId;
                $currentForm = $db->getRecord('forms', $array);
                if(count($currentForm) > 0){

                    $params = $this->realRequestParams($params);

                    if($currentForm['open'] == 1){
                        $array1['approved'] = 1;
                    }else{
                        $array1['approved'] = 0;
                    }

                    $array1['eventId'] = $params['id'];
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
                }

            }catch(Exception $ex){

            }
            $this->view->message = "Edukalt saadetud!";
        }




    }

}

