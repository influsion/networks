<?php
namespace Tasks\Controller;

use Zend\View\Model\ViewModel;
use Tasks\Model\Boards;
use Tasks\Model\BoardsTable;
use Preloader\Controller;
use Zend\Config\Config;
use Zend\Config\Factory;



class BoardsController extends Controller\preloaderController
{
    
    protected $boardsTable;
    
    public function indexAction(){
        $this->layout('layout/only_form');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $boards =  $this->getBoardsTable()->getProjectBoards($request);
            return @array('boards' => $boards);
        }
        return false;
    }

    public function createBoardAction(){
        $this->layout('layout/only_form');
        $request = $this->getRequest();
        $boards = new  Boards();
        if ($request->isPost()) {
            $this->getBoardsTable()->createBoard($request);
        }
        return false;
    }
    
    public function getBoardsTable()
    {
        
        if (!$this->boardsTable) {
            $this->boardsTable = new \Tasks\Model\BoardsTable;
        }
        return $this->boardsTable;
    }
    
}

