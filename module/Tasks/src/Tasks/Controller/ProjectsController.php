<?php
namespace Tasks\Controller;

use Tasks\Model\Projects;
use Zend\View\Model\ViewModel;
use Tasks\Model\Boards;
use Tasks\Model\BoardsTable;
use Preloader\Controller;
use Zend\Config\Config;
use Zend\Config\Factory;



class ProjectsController extends Controller\preloaderController
{
    
    protected $projectsTable;
    protected $boardsTable;

    public function indexAction(){
        $this->layout('layout/only_form');
        return @array('projects' => $this->getProjectsTable()->getProjects());
        
    }

    public function loadProjectsArchiveAction(){
        $this->layout('layout/only_form');
        echo json_encode($this->getProjectsTable()->getArchiveProjects());
        return false;
    }
    
    public function createProjectAction(){
        $this->layout('layout/only_form');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->getProjectsTable()->createProject($request);
        }
        echo json_encode("project created");
        die();
    }

    public function updateProjectsInBoardAction(){

        $this->layout('layout/only_form');
        $request = $this->getRequest();
        $this->getProjectsTable()->updateProjectsInBoard($request);
        return false;
    }

    public function deleteProjectAction(){

        $this->layout('layout/only_form');
        $request = $this->getRequest();
        $this->getProjectsTable()->daleteProject($request);
        return false;

    }

    public function updateProjectAction(){
        $this->layout('layout/only_form');
        $request = $this->getRequest()->getPost()->toArray();
        $this->getProjectsTable()->updateProject($request);
        return false;

    }


    
    public function getProjectsTable()
    {
        if (!$this->projectsTable) {
            $this->projectsTable = new \Tasks\Model\ProjectsTable;
        }
        return $this->projectsTable;
    }

    public function getBoardsTable()
    {
        if (!$this->boardsTable) {
            $this->boardsTable = new \Tasks\Model\BoardsTable;
        }
        return $this->boardsTable;
    }
    
}

