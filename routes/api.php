<?php

require_once __DIR__ . '/../controllers/TaskController.php';

$taskController = new TaskController();
$taskController->handleRequest();