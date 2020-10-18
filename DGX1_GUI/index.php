<?php

$ajax_request = false;
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
{
    $ajax_request = true;
}

require_once("config.php");

$request = strtok($_SERVER["REQUEST_URI"], '?');

if (!$ajax_request) {
    switch ($request) {
        case '/' :
            require __DIR__ . '/pages/main.php';
            break;
        case '' :
            require __DIR__ . '/pages/main.php';
            break;
        case '/login' :
            require __DIR__ . '/pages/login_register.php';
            break;
        case '/reset_password' :
            require __DIR__ . '/pages/reset_password.php';
            break;
        case '/account' :
            require __DIR__ . '/pages/account.php';
            break;
        case '/request' :
            require __DIR__ . '/pages/request.php';
            break;
        case '/admin' :
            require __DIR__ . '/pages/admin.php';
            break;
        case '/users' :
            require __DIR__ . '/pages/users.php';
            break;
         case '/faq' :
            require __DIR__ . '/pages/faq.php';
             break;
        case '/generateSkedTapeCSS' :
            require __DIR__ . '/helpers/generateSkedTapeCSS.php';
            break;
        default:
            http_response_code(404);
            require __DIR__ . '/pages/404.php';
            break;
    }
} else {
    switch ($request) {
        case '/createRequest' :
            require __DIR__ . '/ajax/createRequest.php';
            break;
        case '/editRequest' :
            require __DIR__ . '/ajax/editRequest.php';
            break;
        case '/approveRequest' :
            require __DIR__ . '/ajax/approveRequest.php';
            break;
        case '/rejectRequest' :
            require __DIR__ . '/ajax/rejectRequest.php';
            break;
        case '/cancelRequest' :
            require __DIR__ . '/ajax/cancelRequest.php';
            break;
        case '/deleteUser' :
            require __DIR__ . '/ajax/deleteUser.php';
            break;
        case '/editUser' :
            require __DIR__ . '/ajax/editUser.php';
            break;

        case '/getServerAccounts' :
            require __DIR__ . '/ajax/getServerAccounts.php';
            break;
        case '/getAffiliations' :
            require __DIR__ . '/ajax/getAffiliations.php';
            break;
        case '/assignServerAccount' :
            require __DIR__ . '/ajax/assignServerAccount.php';
            break;
        case '/addServerAccount' :
            require __DIR__ . '/ajax/addServerAccount.php';
            break;


        case '/getTableData' :
            require __DIR__ . '/ajax/getTableData.php';
            break;
        case '/getUsersData' :
            require __DIR__ . '/ajax/getUsersData.php';
            break;
        case '/getSkedTapeData' :
            require __DIR__ . '/ajax/getSkedTapeData.php';
            break;
        case '/getScheduleData' :
            require __DIR__ . '/ajax/getScheduleData.php';
            break;
        case '/getTaskScheduleData' :
            require __DIR__ . '/ajax/getTaskScheduleData.php';
            break;
        //TODO: add other ajax routes


        default:
            http_response_code(404);
            require __DIR__ . '/pages/404.php';
            break;
    }

}
