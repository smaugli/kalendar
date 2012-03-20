<?php

class ExportController extends Lib_Controller_Action {

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
    private function HeaderingExcel($filename)
    {
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$filename");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
    }
    public function indexAction() {

        $this->_helper->layout->disableLayout();

        require_once(APPLICATION_PATH . '/../library/Spreadsheet/OLEwriter.php');

        require_once(APPLICATION_PATH . '/../library/Spreadsheet/BIFFwriter.php');

        require_once(APPLICATION_PATH . '/../library/Spreadsheet/Worksheet.php');

        require_once(APPLICATION_PATH . '/../library/Spreadsheet/Workbook.php');

        $this->HeaderingExcel('Users@' . date('Y-m-d-G-i-s') . '.csv');

        $userClass = new User();

        $db = new DB_Api();

        $request = $this->getRequest();
        
        $user = $userClass->getUser($this->session->user);
        
        $events = $userClass->getAssignedUsers($user['id']);
         
        $eventUsers = $events[$request->getParam("user")];
         
        $workbook = new Workbook("-");

        $workSheet = &$workbook->add_worksheet();
         


        $workSheet->set_column(0, 0, 40);
        $workSheet->set_column(1, 2, 30);
        $workSheet->set_column(3, 3, 30);
        $workSheet->set_column(4, 4, 20);
        $workSheet->set_column(5, 5, 10);
        $workSheet->set_column(6, 6, 10);
        $workSheet->set_column(7, 7, 10);
        $workSheet->set_column(8, 8, 10);
        $workSheet->set_column(9, 9, 10);
        $workSheet->set_column(10, 10, 15);
        $workSheet->set_column(11, 11, 15);
        $workSheet->set_column(12, 12, 40);
        $workSheet->set_column(13, 13, 40);
        $workSheet->set_column(14, 14, 40);
        $workSheet->set_column(15, 15, 40);
        $workSheet->set_column(16, 16, 40);


        $RowCount = 1;
        
        $workSheet->write_string($RowCount, 0, "First Name");
        $workSheet->write_string($RowCount, 1, "Last Name");
        $workSheet->write_string($RowCount, 2, "Mother Tongue");
        $workSheet->write_string($RowCount, 3, "organisation");
        $workSheet->write_string($RowCount, 4, "typeoforganisation");
        $workSheet->write_string($RowCount, 5, "role");
        $workSheet->write_string($RowCount, 6, "email");
        $workSheet->write_string($RowCount, 7, "phone");
        $workSheet->write_string($RowCount, 8, "Address");
        $workSheet->write_string($RowCount, 9, "Why Interested");
        $workSheet->write_string($RowCount, 10, "Why Important");
        $workSheet->write_string($RowCount, 11, "Everyday Needs");
        $workSheet->write_string($RowCount, 12, "Where did you hear about us]");
        $workSheet->write_string($RowCount, 13, "Do you take part of full amount");
        $workSheet->write_string($RowCount, 14, "Other comments");
        $workSheet->write_string($RowCount, 15, "Special needs");
        $workSheet->write_string($RowCount, 16, "Motivation");
        $RowCount++;
        unset($eventUsers['title']);
        foreach($eventUsers as $key=>$assignedUsers){
            $workSheet->write($RowCount, 0, $assignedUsers['firstname']);
            $workSheet->write($RowCount, 1, $assignedUsers['lastname']);
            $workSheet->write($RowCount, 2, $assignedUsers['mothertongue'][0]['options']);
            $workSheet->write($RowCount, 3, $assignedUsers['organisation']);
            $workSheet->write($RowCount, 4, $assignedUsers['typeoforganisation'][0]['options']);
            $workSheet->write($RowCount, 5, $assignedUsers['role']);
            $workSheet->write($RowCount, 6, $assignedUsers['email']);
            $workSheet->write($RowCount, 7, $assignedUsers['phone']);
            $workSheet->write($RowCount, 8, $assignedUsers['address']);
            $workSheet->write($RowCount, 9, $assignedUsers['why interested']);
            $workSheet->write($RowCount, 10, $assignedUsers['why important']);
            $workSheet->write($RowCount, 11, $assignedUsers['everyday needs']);
            $workSheet->write($RowCount, 12, $assignedUsers['where did you hear about us']);
            $workSheet->write($RowCount, 13, $assignedUsers['do you take part of full amount']);
            $workSheet->write($RowCount, 14, $assignedUsers['other comments']);
            $workSheet->write($RowCount, 15, $assignedUsers['special needs']);
            $workSheet->write($RowCount, 16, $assignedUsers['motivation']);
            $RowCount++;
        }
        $workbook->close();

    }

}
