<?php 
require_once 'Lib/Engine.php';

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{



    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');

        $settings = $this->getOptions();
        Zend_Registry::set("config",$settings['resources']['db']['params']);

        error_reporting(E_ALL | E_NOTICE);

        $router = new Zend_Controller_Router_Rewrite();
        //rewrite router

        $router->addRoute(
            'form',
            new Zend_Controller_Router_Route('form/edit/:id', array('controller' => 'profile', 'action' => 'update'))
        );

        $router->addRoute(
            'form2',
            new Zend_Controller_Router_Route('form/delete/:id', array('controller' => 'profile', 'action' => 'deleteform'))
        );

        $router->addRoute(
            'calendar',
            new Zend_Controller_Router_Route('calendar/:year/:month', array('controller' => 'index', 'action' => 'index'))
        );

        $router->addRoute(
            'calendar2',
            new Zend_Controller_Router_Route('calendar/:year', array('controller' => 'index', 'action' => 'index'))
        );


        $router->addRoute(
            'formlist',
            new Zend_Controller_Router_Route('form', array('controller' => 'profile', 'action' => 'forms'))
        );

        $router->addRoute(
            'event',
            new Zend_Controller_Router_Route('event/:id', array('controller' => 'index', 'action' => 'event'))
        );


        $router->addRoute(
            'assign',
            new Zend_Controller_Router_Route('assign/:id', array('controller' => 'index', 'action' => 'assign'))
        );

        $router->addRoute(
            'topicedit',
            new Zend_Controller_Router_Route('admin/topicedit/:id', array('controller' => 'admin', 'action' => 'topicedit'))
        );

        $router->addRoute(
            'contestassign',
            new Zend_Controller_Router_Route('contestassign/:id', array('controller' => 'profile', 'action' => 'contestassign'))
        );


        $router->addRoute(
            'deleteoption',
            new Zend_Controller_Router_Route('delete/option/:id', array('controller' => 'profile', 'action' => 'deleteoption'))
        );

       

        $router->addRoute(
            'emptyuser',
            new Zend_Controller_Router_Route('user', array('controller' => 'profile', 'action' => 'user'))
        );

        $router->addRoute(
            'profile',
            new Zend_Controller_Router_Route('user/:username', array('controller' => 'profile', 'action' => 'user'))
        );

        $router->addRoute(
            'remove',
            new Zend_Controller_Router_Route('admin/remove/:type/:id', array('controller' => 'admin', 'action' => 'remove'))
        );

//***************************************
        $router->addRoute(
            'approveid',
            new Zend_Controller_Router_Route('admin/approve/:id', array('controller' => 'admin', 'action' => 'approve'))
        );

        $router->addRoute(
            'notapproveid',
            new Zend_Controller_Router_Route('admin/notapprove/:id', array('controller' => 'admin', 'action' => 'notapprove'))
        );
        $router->addRoute(
            'contestapproveid',
            new Zend_Controller_Router_Route('admin/contestapprove/:id', array('controller' => 'admin', 'action' => 'contestapprove'))
        );
        $router->addRoute(
            'contestanotpproveid',
            new Zend_Controller_Router_Route('admin/contestnotapprove/:id', array('controller' => 'admin', 'action' => 'contestnotapprove'))
        );

        $router->addRoute(
            'eventedit',
            new Zend_Controller_Router_Route('admin/eventedit/:id', array('controller' => 'admin', 'action' => 'eventedit'))
        );

        $router->addRoute(
            'organizationedit',
            new Zend_Controller_Router_Route('admin/organizationedit/:id', array('controller' => 'admin', 'action' => 'organizationedit'))
        );

        $router->addRoute(
            'assignedinfo',
            new Zend_Controller_Router_Route('info/:id', array('controller' => 'profile', 'action' => 'assignedinfo'))
        );


        $router->addRoute(
            'eventremove',
            new Zend_Controller_Router_Route('profile/eventremove/:id', array('controller' => 'profile', 'action' => 'eventremove'))
        );
         $router->addRoute(
            'eventedit2',
            new Zend_Controller_Router_Route('profile/eventedit/:id', array('controller' => 'profile', 'action' => 'eventedit'))
        );

        $router->addRoute(
            'contestedit',
            new Zend_Controller_Router_Route('admin/contestedit/:id', array('controller' => 'admin', 'action' => 'contestedit'))
        );

        $router->addRoute(
            'contestedit1',
            new Zend_Controller_Router_Route('profile/contestedituser/:id', array('controller' => 'profile', 'action' => 'contestedit'))
        );
        $router->addRoute(
            'contestremove',
            new Zend_Controller_Router_Route('profile/contestremove/:id', array('controller' => 'profile', 'action' => 'contestremove'))
        );

        $router->addRoute(
            'contestmore',
            new Zend_Controller_Router_Route('contest/:id', array('controller' => 'profile', 'action' => 'contestmore'))
        );

        $router->addRoute(
            'sectoredit',
            new Zend_Controller_Router_Route('admin/sectoredit/:id', array('controller' => 'admin', 'action' => 'sectoredit'))
        );

        $router->addRoute(
            'languageedit',
            new Zend_Controller_Router_Route('admin/languageedit/:id', array('controller' => 'admin', 'action' => 'languageedit'))
        );

        $router->addRoute(
            'targetedit',
            new Zend_Controller_Router_Route('admin/targetedit/:id', array('controller' => 'admin', 'action' => 'targetedit'))
        );
         $router->addRoute(
            'teachingmaterialedit',
            new Zend_Controller_Router_Route('admin/teachingmaterialedit/:id', array('controller' => 'admin', 'action' => 'teachingmaterialedit'))
        );

        $router->addRoute(
            'countiesedit',
            new Zend_Controller_Router_Route('admin/countiesedit/:id', array('controller' => 'admin', 'action' => 'countiesedit'))
        );

        $router->addRoute(
            'trainersedit',
            new Zend_Controller_Router_Route('admin/trainersedit/:id', array('controller' => 'admin', 'action' => 'trainersedit'))
        );
        $router->addRoute(
            'typeedit',
            new Zend_Controller_Router_Route('admin/typeedit/:id', array('controller' => 'admin', 'action' => 'typeedit'))
        );
         $router->addRoute(
            'organization',
            new Zend_Controller_Router_Route('index/organization/:id', array('controller' => 'index', 'action' => 'organization'))
        );


        

        //a single dynamic route

        $controller = Zend_Controller_Front::getInstance();
        //the frontcontroller
        $controller->setRouter($router);
        


        /*$front->getRouter()->addRoute('site', $route);

         $route = new Zend_Controller_Router_Route(
                ':controller/:action/*',
                array(
                        "action" => "index"
                ),
                array("controller" => "adminrose")
        );
        $front->getRouter()->addRoute('admin', $route);*/
    }
}


