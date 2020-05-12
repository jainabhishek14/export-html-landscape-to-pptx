<?php

// This file will do the routing to different endpoints

require_once 'vendor/autoload.php';
include_once 'config.php';

// Create the logger
$logger = new Monolog\Logger('export-to-ppt');
// Now add some handlers
$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__.'/logs/application.log', Monolog\Logger::DEBUG));


$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$route = "";
if (isset($_SERVER['PATH_INFO'])) {
    $route = $_SERVER['PATH_INFO'];
}

// Have some validation, in case PATH_INFO is not available
// or is not processed by us

// Process the route
if (in_array($route, $allowedRoutes)) {
    switch ($route) {

        case '/convert': process_convert(); break;
        case '/healthcheck': process_healthcheck(); break;
        case '/logs': process_logs(); break;
        case '/backups': process_backups(); break;
        echo $route;
        default: error();
    }
} else {
    error();
}

// The Methods that will act as endpoints and provide functionality

// 1. convert() - to convert a json landscape data to a presentation (odp, pptx, pdf)
function process_convert()
{
    global $allowedFormats;
    global $logger;
    
    // Get Post data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize content
        if (isset($_POST['template']) && !empty($_POST['template'])) {
            $templateName = $_POST['template'];
            if (file_exists(__DIR__.DIRECTORY_SEPARATOR.getenv("ODP_DIR").DIRECTORY_SEPARATOR.$templateName)) {
                if (isset($_POST['data'])) {
                    $data = json_decode(str_replace("+", " ", str_replace("&", "&amp;", $_POST['data'])));
                    $format = (array_key_exists('format', $_POST) && in_array($_POST['format'], $allowedFormats)) ? $_POST['format'] : "pptx";
                    $filename = (array_key_exists('fileName', $_POST) && isset($_POST['fileName']) && !empty($_POST['fileName'])) ? $_POST['fileName'] : $templateName;
                    $responseType = (array_key_exists('responseType', $_POST) && isset($_POST['responseType']) && !empty($_POST['responseType'] && in_array($_POST['responseType'], array("filepath", "stream")))) ? $_POST['responseType'] : 'filepath';
                    include_once("lib/convert.php");
                } else {
                    $message = array('response'=>array('status'=>400, 'type'=>'error', 'message'=>'data not available. Please verify the data and try again.'));
                    $logger->error($message);
                    exit(json_encode($message));
                }
            } else {
                $message = array('response'=>array('status'=>400, 'type'=>'error', 'message'=>'Template not available. Please verify the template name and try again.'));
                $logger->error($message);
                exit(json_encode($message));
            }
        } else {
            // error message that template not found
            $message = array('response'=>array('status'=>400, 'type'=>'error', 'message'=>'Invalid Template. Please verify the template name.'));
            $logger->error($message);
            exit(json_encode($message));
        }
    } else {
        $message = array('response'=>array('status' =>405,'type'=>'error','message'=>"Invalid request type. Please refer to the documentation."));
        $logger->error($message);
        exit(json_encode($message));
    }
}

function process_healthcheck()
{
    global $logger;
    $message = array('response'=>array('type'=>'info', 'message'=>'Health Check Service. Work In Progress.'));
    $logger->info($message);
    exit(json_encode($message));
}

function process_logs()
{
    global $logger;
    $message = array('response'=>array('type'=>'info', 'message'=>'Logging Service. Work In Progress.'));
    $logger->info($message);
    exit(json_encode($message));
}

function process_backups()
{
    global $logger;
    $message = array('response'=>array('type'=>'info', 'message'=>'Backup Service. Work In Progress.'));
    $logger->info($message);
    exit(json_encode($message));
}

function error()
{
    $message = array('response'=>array('type'=>'error', 'message'=>'Please enter the correct path.'));
    $logger->error($message);
    exit(json_encode($message));
}
