<?php
    include_once("../TPSBIN/functions.php");
    include_once("../TPSBIN/db_connect.php");

    // Establish Session
    sec_session_start();

    // GET data
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="description" content="Login for TPS Radio System">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="James Oliver">
        <!-- Latest compiled and minified CSS -->
        <!-- HOSTED -->
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

        <!-- Optional theme -->
        <!-- HOSTED -->
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
        <!--<link href=\"js/css/bootstrap.min.css\" rel=\"stylesheet\">-->

        <!-- Latest compiled and minified JavaScript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src=\"https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js\"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        
    </head>
    <body role="document" style="">
    <?php include_once "../TPSBIN/bs_menu.php";?>
    <div class="container" style="margin-top: 30px">
        <div class="page-header">Library Upload Summary</div>
        <div class="jumbotron">Library Updated, Would you like to <a href="playlist_update.php">update the Playlist Next?</a></div>
            <div class="ui-state-highlight">
                <div class="panel panel-success">
                    <div class="panel-heading">COMPLETED: <?php echo $_SESSION['COMPLETE']; ?></div>
                    <?php
                        foreach($_SESSION['REFCODE'] as $ref){
                            $result = mysqli_fetch_array($mysqli->query("SELECT library.*, recordlabel.Name, recordlabel.name_alias_duplicate, recordlabel.Size FROM library LEFT JOIN recordlabel ON recordlabel.LabelNumber=library.labelid WHERE library.refcode='".addslashes($ref)."'"));
                            echo "<form id='$ref'>";
                            echo "<input type='text' name='artist' value='".$result['artist']."'/>";
                            echo "<input type='text' name='album' value='".$result['album']."'/>";
                            echo "<input type='text' name='label' value='".$result['name']."'/>";
                            echo "</form>";
                            //echo $ref."<br>";
                        }
                    ?>
                    <div class="panel-body"></div>
                </div>
                <div class="panel panel-warning">
                    <div class="panel-heading">DUPLICATES (Skipped): <?php echo $_SESSION['DUPLICATE_COUNT'];?></div>
                    <div class="panel-body"></div>
                </div>
                <div class="panel panel-danger">
                    <div class="panel-heading">ERRORS (Omitted): <?php echo $_SESSION['ERROR_COUNT'];?></div>
                    <div class="panel-body">
                    <?php
                        foreach($_SESSION['ERROR'] as $error){
                            echo $error."<br>";
                        }
                    ?></div>
                </div>
                <div class="panel panel-info">
                    <div class="panel-heading">TOTAL RECORDS: <?php echo $_SESSION['TOTAL'];?></div>
                    <div class="panel-body"></div>
                </div><br>
                <span>
                    RUNTIME: <?php echo $_SESSION['EXEC_TIME'];?> Minutes
                </span>
                <div class="progress">
                    <div class="progress-bar progress-bar-success" style="width: <?php echo round(($_SESSION['COMPLETE']/$_SESSION['TOTAL'])*100,2);?>%"><span class="sr-only"><?php echo ($_SESSION['COMPLETE']/$_SESSION['TOTAL'])*100;?>% Complete (success)</span></div>
                    <div class="progress-bar progress-bar-success" style="width: <?php echo round(($_SESSION['DUPLICATE_COUNT']/$_SESSION['TOTAL'])*100,2);?>%"><span class="sr-only"><?php echo ($_SESSION['DUPLICATE_COUNT']/$_SESSION['TOTAL'])*100;?>% Duplicate (Omitted)</span></div>
                    <div class="progress-bar progress-bar-success" style="width: <?php echo round(($_SESSION['ERROR_COUNT']/$_SESSION['TOTAL'])*100,2);?>%"><span class="sr-only"><?php echo ($_SESSION['ERROR_COUNT']/$_SESSION['TOTAL'])*100;?>% Errors (Failed)</span></div>
                </div>
            </div>
        </div>
    </body>
</html>
