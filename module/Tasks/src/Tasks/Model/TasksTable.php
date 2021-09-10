<?php

namespace Tasks\Model;

use Zend\Session\Container;
use Zend\Config\Config;
use Zend\Config\Factory;



class TasksTable
{

    protected $tableGateway;
    protected $adapter;

    public function __construct()
    {
        $config = new Config(Factory::fromFile('config/autoload/global.php'), true);
        $adapter = new \Zend\Db\Adapter\Adapter (array(
            'driver' => $config->database->driver,
            'dsn' => $config->database->dsn,
            'database' => $config->database["params"]->database,
            'username' => $config->database["params"]->username,
            'password' => $config->database["params"]->password,
        ));
        $this->tableGateway = new \Zend\Db\TableGateway\TableGateway("tasks", $adapter);
        $this->adapter = $adapter;
    }

    public function createTask(array $data)
    {
        $user_session = new Container('user');
        $user_id = $user_session->user->id;
        $memberList = $data["members_list"];
        $memberList .= ",".$user_id;
        $data["to_directory"] = 0;
        $fileTable = new \Files\Model\FilesTable;
        if (isset($data['file']['tmp_name'])) $fileID = $fileTable->saveUserFile($data);
        $task_name = $data["task_name"];
        $description = $data["task_description"];
        $project_id = (int)$data["project_id"];
        $sql = "SELECT MIN(tasks.board_id) AS board_id  FROM tasks 
                left join boards on boards.id = tasks.board_id 
                left join projects on boards.project_id = projects.id WHERE projects.id=" . $project_id;
        $resultSet = $this->adapter->query($sql, $this->adapter::QUERY_MODE_EXECUTE);
        $board_id = $resultSet->toArray()[0]['board_id'];
        if(!$board_id) {
            $data = ["name" => "todo","project_id" => $project_id];
            $board = new BoardsTable();
            $board_id = (int)$board->createBoardFromArray($data);
        }
        $data = [
            "description" => $description,
            "name" => $task_name,
            "status" => "new",
            "board_id" => $board_id,
        ];
        $this->tableGateway->insert($data);
        $taskID = $this->tableGateway->lastInsertValue;
        if (isset($fileID)) {
            $dataFile["file_id"] = $fileID;
            $dataFile["task_id"] = $taskID;
            $fileTable = new \Tasks\Model\TasksFilesTable();
            $fileTable->saveFileToTask($dataFile);
        }
        $memberList = explode(",", $memberList);
        $taskMemebrsTable = new \Tasks\Model\TasksMembersTable();
        if (count($memberList)) {
            foreach ($memberList as $member) {
                $taskMemebrsTable->saveMemberToTask(['user_id' => (int)$member, "task_id" => $taskID]);
            }
        }
    }
    public function getTask(int $task_id){
        $task_sql = "SELECT  * FROM tasks where id=".$task_id;
        $resultSet = $this->adapter->query($task_sql, $this->adapter::QUERY_MODE_EXECUTE);
        $task = $resultSet->toArray()[0];
        $sub_task_sql = "SELECT  * FROM tasks where parent_task = ".$task_id;
        $resultSet = $this->adapter->query($sub_task_sql, $this->adapter::QUERY_MODE_EXECUTE);
        $task["sub_tasks"] = $resultSet->toArray();
        $sub_task_sql = "SELECT  * FROM tasks_users left join user_settings on tasks_users.user_id = user_settings.user_id where tasks_users.task_id=".$task_id;
        $resultSet = $this->adapter->query($sub_task_sql, $this->adapter::QUERY_MODE_EXECUTE);
        $task["users"] = $resultSet->toArray();

        $files_task_sql = "SELECT  * FROM tasks_files where task_id=".$task_id;
        $resultSet = $this->adapter->query($files_task_sql, $this->adapter::QUERY_MODE_EXECUTE);
        $files = $resultSet->toArray();
        foreach ($files as $file){
            $files_task_sql = "SELECT  * FROM files where id=".$file["file_id"];
            $resultSet = $this->adapter->query($files_task_sql, $this->adapter::QUERY_MODE_EXECUTE);
            $task["files"][]  = $resultSet->toArray()[0];
        }
        return $task;
   }

    public function getTasksForProject($project_id)
    {
        $project_id = (int) $project_id;
        $user_session = new Container('user');
        $user_id = $user_session->user->id;
        $sql = "SELECT  tasks.name,tasks.sort_order,tasks.board_id,tasks.id FROM tasks 
                left join boards on boards.id = tasks.board_id 
                left join projects on boards.project_id = projects.id
                left join projects_members on projects_members.project_id = projects.id
                WHERE projects_members.project_id='" . $project_id . "' and projects_members.user_id = " . $user_id;
        $resultSet = $this->adapter->query($sql, $this->adapter::QUERY_MODE_EXECUTE);
        $tasks = $resultSet->toArray();
        $columns = [];
        foreach ($tasks as $task) {
            $columns[$task["board_id"]][] = $task;
        }
        foreach ($columns as &$column) {
            usort($column, array($this, 'taskSort'));
        }
        return $columns;
    }

    private static function taskSort($a, $b)
    {
        $key = 'sort_order';
        if ($a[$key] < $b[$key]) {
            return -1;
        } else if ($a[$key] > $b[$key]) {
            return 1;
        }
        return 0;
    }

    public function updateATasksInBoard($request){
        $task_list = $request->getPost()->task_list;
        foreach ($task_list as $task) {
            $this->tableGateway->update($task, ['id' => $task["id"]]);
        }
    }

    public function updateTask($data){
        $fileTable = new \Files\Model\FilesTable;
        $data["name"] = "update";
        if (isset($data['file']['tmp_name'])) $fileID = $fileTable->saveUserFile($data);
        unset($data['file']);
        $this->tableGateway->insert($data);
        if (isset($fileID)) {
            $dataFile["file_id"] = $fileID;
            $dataFile["task_id"] = $data["parent_task"];
            $fileTable = new \Tasks\Model\TasksFilesTable();
            $fileTable->saveFileToTask($dataFile);
        }

    }
}


