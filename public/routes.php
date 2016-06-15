<?php
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."std".DIRECTORY_SEPARATOR."util.php";
if(isset($_SESSION["DBHOST"])){
    require_once 'TPSBIN'.DIRECTORY_SEPARATOR.'functions.php';
    require_once 'TPSBIN'.DIRECTORY_SEPARATOR.'db_connect.php';
    require_once 'lib_api'.DIRECTORY_SEPARATOR.'LibraryAPI.php';
    require_once dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."notifications.php";
    $app->hook('slim.before.dispatch', function() use ($app) {
        $notifications = new \TPS\notification(\TPS\util::get($_SESSION, 'CALLSIGN'));
        $broadcasts = $notifications->listUserNotifications();
        $messages = \TPS\notification::convertToMessageFormat($broadcasts);
        $app->view()->setData('messages',$messages);
    });
}
require_once 'routes'.DIRECTORY_SEPARATOR.'system.php';

/*$app->get('/', $authenticate($app), function() use ($app){
    $params = array();
    $app->render('dashboard.twig',$params);
});
$app->post('/', $authenticate($app), function() use ($app){
    $app->render('dashboard.twig');
});*/

$app->get('/updates', $authenticate, function () use ($app) {
    $updates = scandir("./Update/proc/");
    $updateList=array();
    $update_JSON = array();
    foreach ($updates as $update){
        if(strtolower(substr($update,-5))==='.json'){
            $update_JSON[$update]=json_decode(file_get_contents('./Update/proc/'.$update),true);
            $updateList[$update]=$update_JSON[$update]['TPS_Errno'];
        }
    }
    $params = array(
        'updateList'=>json_encode($updateList),
        'updates'=>$update_JSON,
        'title'=>'System Updates',

        );
    $app->render('update.twig',$params);
});
// user group
$app->group('/user', $authenticate, function () use ($app) {
    // User page
    $app->get('/:id', function ($id) use ($app) {
        $app->render('notSupported.twig',array('title'=>'User Profile'));
    });
    $app->get('/:id/inbox', function ($id) use ($app) {
        $app->render('notSupported.twig', array('title'=>'User Inbox'));
    });
    $app->get('/:id/settings', function ($id) use ($app) {
        $app->render('notSupported.twig', array('title'=>'User Settings'));
    });
});

require_once 'lib'.DIRECTORY_SEPARATOR.'libs.php';
require_once 'routes'.DIRECTORY_SEPARATOR.'routes.php';

$app->run();
