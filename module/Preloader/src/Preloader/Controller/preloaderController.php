<?php
namespace Preloader\Controller;

use Zend\Mvc\Controller\AbstractActionController;
//use Zend\Stdlib\RequestInterface as Request;
use Zend\Session\Container;
use Zend\I18n\Translator\Translator;




class  preloaderController extends AbstractActionController{
    function __construct() {
        @session_start();
        $this->isAuthed();
    }
    public function isAuthed(){

      //  $uri = $_SERVER['REQUEST_URI'];
      
        $uri = explode("/", $_SERVER['REQUEST_URI']);


        if(isset($uri[2])) {
            $uri = "/".$uri[1]."/".$uri[2];

        if(($uri != "/user/register" ) && ($uri != "/user/login" ) && ($uri != "/user/confirm" )) {
            
            if(!isset($_SESSION['user'])) {
                $actual_link = 'http://'.$_SERVER['HTTP_HOST'].'/user/login';
                header("location: $actual_link");
                die();
            }
        }
    }
        elseif($uri[1] == 'main'){

            if(!isset($_SESSION['user'])) {
                $actual_link = 'http://'.$_SERVER['HTTP_HOST'].'/user/login';
                header("location: $actual_link");
                die();
            }
        }
        else{

            $actual_link = 'http://'.$_SERVER['HTTP_HOST'].'/user/login';
            header("location: $actual_link");
            die();
        }
    }
}