<?php
$app->notFound(function() use ($app) {
    global $base_url, $twig;
    $params = array(
        'base_url' => $base_url,
        'title' => 'Error 404',
        'message' => "We couldn't find the page you asked for, sorry about that",
    );
    $app->render('error.html.twig',$params);
});

$app->get('/', $authenticate($app), function() use ($app){
    
    $app->render('dashboard.twig');
});

/*$app->get('/login', function() use ($app){
    $app->render('login.html.twig');
    //$app->redirect('/Security/login.html');
);*/

$app->get("/login", function () use ($app) {
   $flash = $app->view()->getData('flash');
   $error = '';
   if (isset($flash['error'])) {
      $error = $flash['error'];
   }
   $urlRedirect = '/';
   if ($app->request()->get('r') && $app->request()->get('r') != '/logout' && $app->request()->get('r') != '/login') {
      $_SESSION['urlRedirect'] = $app->request()->get('r');
   }
   if (isset($_SESSION['urlRedirect'])) {
      $urlRedirect = $_SESSION['urlRedirect'];
   }
   $email_value = $email_error = $password_error = '';
   if (isset($flash['Username'])) {
      $email_value = $flash['Username'];
   }
   if (isset($flash['errors']['Username'])) {
      $email_error = $flash['errors']['Username'];
   }
   if (isset($flash['errors']['password'])) {
      $password_error = $flash['errors']['password'];
   }
   $app->render('login.html.twig', array('error' => $error, 'Username' => $email_value, 'Username_error' => $email_error, 'password_error' => $password_error, 'urlRedirect' => $urlRedirect));
});

$app->post("/login", function () use ($app) {
    $username = $app->request()->post('name');
    $password = $app->request()->post('pass');
    $databaseID = $app->request()->post('SRVID');
    $access = 0;
    $errors = array();
    
    require_once ("TPSBIN".DIRECTORY_SEPARATOR."functions.php");
    $dbxml = simplexml_load_file("TPSBIN".DIRECTORY_SEPARATOR."XML"
            .DIRECTORY_SEPARATOR."DBSETTINGS.xml");
    // check auth type
    foreach($dbxml->SERVER as $server):
        if((string)$server->ID==$databaseID):
            if((string)$server->AUTH == strtoupper("LDAP")){
                if(!extension_loaded('ldap')):
                    error_log("ldap module not installed but requested by login");
                    $e_params = array(
                        "statusCode" => 500,
                        "title" => "Internal Server Error",
                        "message" => "A login method was requested that is not supported by this server",
                    );
                    $app->render('error.html.twig',$e_params);
                endif;
                if((string)$server->ACTIVE == '0'):
                    error_log("server [".$server->ID."] was requested but is disabled in DBSETTINGS.XML");
                    $e_params = array(
                        "statusCode" => 403,
                        "title" => "Permission Denied",
                        "message" => "A login was requested that is disabled",
                    );
                    $app->render('error.html.twig',$e_params);
                endif;
                $ldap_host = (string)$server->LDP_SERVER;   // LDAP Server
                $ldap_port = (string)$server->LDP_PORT;     // LDAP Port
                $ldap_dn = (string)$server->LDP_BASE_DN;    // Active Directory Base DN
                $logo = (string)$server->LOGO_PATH;         // Logo
                $m_logo = (string)$server->MENU_LOGO_PATH;  // Menu Logo (Small)
                $ldap_usr_dom = (string)$server->LDP_DOMAIN;// Domain, for purposes of constructing $user
                $accountFilter = (string)$server->LDP_AccParam ? : 'sAMAccountName';
                $authorization = array(
                    "manager"=>["WebAdmins"],
                    "user"=>["WebUsers","Authenticated Users"]
                );
                
                //connect and bind
                try{
                    $ldap = ldap_connect($ldap_host,$ldap_port);
                    if($ldap_usr_dom!=''){
                        $bind = ldap_bind($ldap, $ldap_usr_dom . '\\' . $username, $password);
                    }
                    else{
                        $bind = ldap_bind($ldap, $username, $password);
                    }
                    if($bind){
                        $filter = "($accountFilter=" . $username . ")";
                        $attr = array("memberof");
                        $result = ldap_search($ldap, $ldap_dn, $filter, $attr);
                        $entries = ldap_get_entries($ldap, $result);
                        ldap_unbind($ldap);
                        $nameLDAP = substr(ldap_explode_dn($entries[0]["dn"],0)[0],3); //get username
                        foreach($entries[0]['memberof'] as $grps) {
                            foreach($authorization['manager'] as $manager_group):
                                if (strpos($grps, $manager_group)) { $access = max(array(2,$access)); break; }
                            endforeach;
                            foreach($authorization['user'] as $user_group):
                                if (strpos($grps, $user_group)) { $access = max(array(1,$access)); break;}
                            endforeach;
                        }
                        if($access>0){
                            $_SESSION['usr'] = easy_decrypt(ENCRYPTION_KEY,(string)$server->USER);
                            //define("USER",easy_decrypt(ENCRYPTION_KEY,(string)$server->USER));
                            $_SESSION['rpw'] = easy_decrypt(ENCRYPTION_KEY,(string)$server->PASSWORD);
                            //define("PASSWORD",easy_decrypt(ENCRYPTION_KEY,(string)$server->PASSWORD));
                            $_SESSION['access'] = $access;
                            $_SESSION['fname'] = $nameLDAP;//"LDAP Authenticated User";
                            $_SESSION['DBNAME'] = (string)$server->DATABASE;//"CKXU";
                            if((string)$server->RESOLVE == 'URL'){
                                $_SESSION['DBHOST'] = (string)$server->URL;
                            }
                            else{
                                $_SESSION['DBHOST'] = (string)$server->IPV4;
                            }
                            //define("HOST",(string)$_SESSION['DBHOST']);
                            //define('DBNAME',(string)$_SESSION['DBNAME']);
                            $_SESSION['SRVPOST'] = (string)$server->ID;//addslashes($_POST['SID']);
                            $_SESSION['logo']=$logo;
                            $_SESSION['m_logo']=$m_logo;
                            $_SESSION['account'] = $username;
                            $_SESSION['AutoComLimit'] = 8;
                            $_SESSION['AutoComEnable'] = TRUE;
                            $_SESSION['TimeZone']='UTC'; // this is just the default to be updated after login
                        }
                        else{
                            $errors['Username'] = "Invalid username or password";
                        }
                        
                            
                    }
                }
                catch (Exception $ex){
                    #error_log("Could not Bind LDAP server");
                    $errors['Username'] = "Invalid login";
                }
            }
            elseif((string)$server->AUTH == strtoupper("SECL")){
                if ($username != "brian@nesbot.com") {
                    $errors['Username'] = "Username not found.";
                } else if ($password != "aaaa") {
                    $app->flash('Username', $username);
                    $errors['password'] = "Password does not match.";
                } 
            }
            elseif((string)$server->AUTH == strtoupper("LIST")){
                if ($username != "brian@nesbot.com") {
                    $errors['Username'] = "Username not found.";
                } else if ($password != "aaaa") {
                    $app->flash('Username', $username);
                    $errors['password'] = "Password does not match.";
                } 
            }
        endif;
    endforeach;
    if (count($errors) > 0) {
        $app->flash('errors', $errors);
        $app->redirect('/login');
    }
    if (isset($_SESSION['urlRedirect'])) {
       $tmp = $_SESSION['urlRedirect'];
       unset($_SESSION['urlRedirect']);
       $app->redirect($tmp);
    }
    $app->redirect('/');
});

$app->get("/logout", function () use ($app) {
   session_unset();
   $app->view()->setData('access', null);
   $app->render('error.html.twig',array('statusCode'=>'Logout','title'=>'Logout', 'message'=>'You have been logged out'));
});

if(isset($_SESSION["DBHOST"])){
    require_once 'TPSBIN'.DIRECTORY_SEPARATOR.'functions.php';
    require_once 'TPSBIN'.DIRECTORY_SEPARATOR.'db_connect.php';
    require_once 'lib_api'.DIRECTORY_SEPARATOR.'LibraryAPI.php';

    // user group
    $app->group('/user', $authenticate, function () use ($app) {
        // Get book with ID
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

    $app->group('/api', $authenticate, function () use ($app,$authenticate) {
        $app->group('/library', $authenticate, function () use ($app,$authenticate){
            $app->get('/:refcode', function ($refcode){
                print json_encode(GetLibraryRefcode($refcode));
            });
            $app->get('/artist/:artist', function ($artist) use ($app) {
                print json_encode(GetLibraryfull($artist));
            });
            $app->get('/:artist/:album', function ($artist,$album) use ($app) {
                print json_encode(GetLibraryfull($artist,$album));
            });
            $app->get('/', $authenticate, function () {
                print json_encode(ListLibrary());
            });
        });
        $app->group('/episode', $authenticate, function() use ($app,$authenticate){
            $app->get('/recent', $authenticate, function () use ($app){
                global $mysqli;
                $response = array(
                    "cols"=>array(
                        array(
                            "id"=>"Room",
                            "label"=>"Room",
                            "type"=>"string",
                            "pattern"=>"",
                        ),
                        array(
                            "id"=>"Name",
                            "label"=>"Name",
                            "type"=>"string",
                            "pattern"=>"",
                        ),
                        array(
                            "id"=>"Start",
                            "label"=>"Start",
                            "type"=>"date",
                        ),
                        array(
                            "id"=>"End",
                            "label"=>"End",
                            "type"=>"date",

                        ),
                    ),
                    "rows"=>array()
                );
                $sql_episode="SELECT concat(DATE_FORMAT(date, '%Y:%m:%d'),\" \",DATE_FORMAT(starttime, '%H:%i:%s')) AS start, concat(DATE_FORMAT(date, '%Y:%m:%d'),\" \",DATE_FORMAT(endtime, '%H:%i:%s')) AS end, programname, date, Type FROM episode WHERE DATE(date)>DATE_ADD(CURDATE(), INTERVAL -2 DAY) and DATE(date)<=CURDATE() and endtime > starttime and Type = 0 order by start";
                $results = $mysqli->query($sql_episode) or trigger_error($mysqli->error."[$sql_episode]");
                while($row = $results->fetch_array()){
                    $response['rows'][]=array(
                        "c"=>[
                            array('v'=>"Logged"),
                            array('v'=>$row['programname']),
                            array('v'=>"Date(".date("Y,m,d,H,i,s",strtotime($row['start'])).")"),
                            array('v'=>"Date(".date("Y,m,d,H,i,s",strtotime($row['end'])).")"),
                            ]
                        );
                }
                print json_encode($response);
            });
            $app->get('/prerecords/pending', $authenticate, function () use ($app){
                global $mysqli;
                $response = array(
                    "cols"=>array(
                        array(
                            "id"=>"Room",
                            "label"=>"Room",
                            "type"=>"string",
                            "pattern"=>"",
                        ),
                        array(
                            "id"=>"Name",
                            "label"=>"Name",
                            "type"=>"string",
                            "pattern"=>"",
                        ),
                        array(
                            "id"=>"Start",
                            "label"=>"Start",
                            "type"=>"date",
                        ),
                        array(
                            "id"=>"End",
                            "label"=>"End",
                            "type"=>"date",

                        ),
                    ),
                    "rows"=>array()
                );
                $sql_prerecord="SELECT concat(DATE_FORMAT(date, '%Y:%m:%d'),\" \",DATE_FORMAT(starttime, '%H:%i:%s')) AS start, concat(DATE_FORMAT(date, '%Y:%m:%d'),\" \",DATE_FORMAT(endtime, '%H:%i:%s')) AS end, programname, date, Type FROM episode WHERE DATE(date)<DATE_ADD(CURDATE(), INTERVAL +30 DAY) and DATE(date)>=CURDATE() and (Type = 1 or prerecorddate is not null) and endtime IS NOT NULL order by start";$sql_episode="SELECT concat(DATE_FORMAT(date, '%Y:%m:%d'),\" \",DATE_FORMAT(starttime, '%H:%i:%s')) AS start, concat(DATE_FORMAT(date, '%Y:%m:%d'),\" \",DATE_FORMAT(endtime, '%H:%i:%s')) AS end, programname, date, Type FROM episode WHERE DATE(date)>DATE_ADD(CURDATE(), INTERVAL -2 DAY) and DATE(date)<=CURDATE() and endtime > starttime and Type = 0 order by start";
                $results = $mysqli->query($sql_prerecord) or trigger_error($mysqli->error."[$sql_episode]");
                while($row = $results->fetch_array()){
                    $response['rows'][]=array(
                        "c"=>[
                            array('v'=>"Logged"),
                            array('v'=>$row['programname']." (".$row['date'].")"),
                            array('v'=>"Date(".date("Y,m,d,H,i,s",strtotime($row['start'])).")"),
                            array('v'=>"Date(".date("Y,m,d,H,i,s",strtotime($row['end'])).")"),
                            ]
                        );
                }
                print json_encode($response);
            });
        });
        $app->get('/', function() use ($app){
            $app->render('error.html.twig',
                    array(
                        'title'=>'API Access',
                        'message'=>'please query against a supported API section',
                        'details'=>array(
                            'for more information please see our wiki on '
                        . '<a href="https://github.com/TDXDigital/TPS/wiki/API-Documentation">'
                        . 'GitHub</a>'),
                        ));
        });

    });
}
$app->run();