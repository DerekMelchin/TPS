<?php
    include "TPSBIN/functions.php";
    sec_session_start();
    if(isset($_GET['old'])){
        if($_SESSION['access']==2){
            include_once "admin_old.php";
            //header("location: masterpage.php");
        }
        else{
            include_once "djhome.php";
            //header("djhome.php");
        }
    }

    else{
        if($_SESSION['access']==2){
            include_once "station/admin.php";
            //header("location: masterpage.php");
        }
        else{
            //include_once "station/user.php";
            include_once "djhome.php";
            //header("djhome.php");
        }
    }
?>