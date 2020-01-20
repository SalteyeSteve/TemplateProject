<?php

use App\Controller\WeatherController;
use App\Controller\GenericController;

include "../bootstrap.php";

$controller = new GenericController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // add an "action" input field, to determine which action to do (if more than one per page)
    $action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
    // value of a post var from an input field
    $id = $_POST['id'] ?? null;
    // values array that holds params for crud functions
    $values = [];
    // array that holds the response
    $response = [];

    if ($action == null) {
        // don't allow non action requests, be gone
        die("You shall not pass");
    }

    switch ($action)
    {
        // each case represents a different action
        case 'getData':
            try {
                $response = ['response' => $controller->getRecordById((int)$id)];
            } catch (Exception $e) {
                $response = ['response' => false];
            }
            echo json_encode($response);
            break;
        case 'edit':
            array_push($values, $id);
            try {
                $response = ['response' => $controller->updateRecord($values)];
            } catch (Exception $e) {
                $response = ['response' => false];
            }
            echo json_encode($response);
            break;
        case 'delete':
            try {
                $response = ['response' => $controller->deleteRecord((int)$id)];
            } catch (Exception $e) {
                $response = ['response' => false];
            }
            echo json_encode($response);
            break;
        case 'getWeather':
            $controller = new WeatherController();
            echo $controller->executeRequest();
            break;
        default:
            break;
    }
}