<?php
require_once __DIR__ . '/../models/Task.php';

class TaskController {
    private $taskModel;

    public function __construct() {
        $this->taskModel = new Task();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getTask($id);
                } else {
                    $this->getAllTasks($status);
                }
                break;
            case 'POST':
                $this->createTask();
                break;
            case 'PUT':
                if ($id) {
                    $this->updateTask($id);
                } else {
                    $this->sendResponse(400, ['error' => 'Task ID is required for update']);
                }
                break;
            default:
                $this->sendResponse(405, ['error' => 'Method not allowed']);
        }
    }

    private function getAllTasks($status = null) {
        try {
            $tasks = $this->taskModel->getAllTasks($status);
            $this->sendResponse(200, $tasks);
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
    }

    private function getTask($id) {
        try {
            $task = $this->taskModel->getTaskById($id);
            
            if ($task) {
                $this->sendResponse(200, $task);
            } else {
                $this->sendResponse(404, ['error' => 'Task not found']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
    }

    private function createTask() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $validation = $this->taskModel->validateTaskData($data);
            if ($validation !== true) {
                $this->sendResponse(400, ['errors' => $validation]);
                return;
            }
            
            $taskId = $this->taskModel->createTask($data);
            
            if ($taskId) {
                $this->sendResponse(201, [
                    'message' => 'Task created successfully',
                    'task_id' => $taskId
                ]);
            } else {
                $this->sendResponse(500, ['error' => 'Failed to create task']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
    }

    public function updateTask($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $validation = $this->taskModel->validateTaskData($data);
            
            if ($validation !== true) {
                $this->sendResponse(400, ['errors' => $validation]);
                return;
            }
            
            $success = $this->taskModel->updateTask($id, $data);
            
            if ($success) {
                $this->sendResponse(200, ['message' => 'Task updated successfully']);
            } else {
                $this->sendResponse(404, ['error' => 'Task not found or no changes made']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
    }

    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}