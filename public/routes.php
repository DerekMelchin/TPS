<?php
require_once 'routes'.DIRECTORY_SEPARATOR.'system.php';

if(isset($_SESSION["DBHOST"])){
    require_once 'TPSBIN'.DIRECTORY_SEPARATOR.'functions.php';
    require_once 'TPSBIN'.DIRECTORY_SEPARATOR.'db_connect.php';
    require_once 'lib_api'.DIRECTORY_SEPARATOR.'LibraryAPI.php';
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
    require_once 'routes'.DIRECTORY_SEPARATOR.'library.php';
    require_once 'routes'.DIRECTORY_SEPARATOR.'reviews.php';
    require_once 'routes'.DIRECTORY_SEPARATOR.'api.php';
}
$app->run();
