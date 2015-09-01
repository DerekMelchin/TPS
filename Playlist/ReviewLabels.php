<!doctype html>
<?php
    error_reporting(E_ERROR);
    include_once '../TPSBIN/functions.php';
    include_once '../TPSBIN/db_connect.php';
    include_once '../public/lib/libs.php';
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Print Labels</title>
    <link href="CSS_Labels/<?php
    $library = new \TPS\library();
    $reviews = new \TPS\reviews();
    $type = filter_input(INPUT_GET,'type', FILTER_SANITIZE_NUMBER_INT)?:5160;
    $indent = filter_input(INPUT_GET,'start', FILTER_SANITIZE_NUMBER_INT) ?: 0;
    $outline = filter_input(INPUT_GET,'outline',FILTER_SANITIZE_STRING) ?: 'false';
    
    
    if($type==="5160"){
        print "5160";
    }
    elseif($type==="5163"){
        print "5163";
    }
    ?>.css" rel="stylesheet" type="text/css" >
    <?php
    
    if(strtolower($outline)==='true'){
        echo "<style type='text/css'>\xA.label{\xAoutline: 1px dashed;\xA}\xA</style>";
    }
    elseif(strtolower($outline)==='true'){
        echo "<style type='text/css'>\xA.label{\xAoutline: none;\xA}\xA</style>";
    }
    
    ?>
    <style type="text/css">
    @media print{
        .no-print, .no-print *{
            display: none !important;
        }
        body{
            background-color:none;
            background-image:none;
            color:#000000
        }
    }
    
    @page :right{
        margin: 0.0cm;
    }
    
    @page :left{
        margin: 0.0cm;
    }
    @page :top{
        margin: 0.5cm;
    }
    @page :bottom{
        margin: 0.5cm;
    }
    .no-print, .no-print *{
        background-color: orange;
        text-align: center;
    }
    </style>
</head>
<body>
    <div class="no-print">Please use top and bottom margin of 0.5" and 0.0" sides. some printers may vary, adjust as needed</div>
    <?php
    $review = new \TPS\reviews();
    $reviews = $review->getPrintLables();
    for($i=1;$i<$indent;$i++){
        echo "<div class=\"label\" style=\"outline: none;\"></div>";
    }
    foreach ($reviews as $id ) {
        $label = $review->getReview($id);
        echo "<div class=\"label\">";
        echo "</span><br style='clear: both'><strong style='float: left'>".$label['review']['description']."</strong><br><i style='float:left'>".$label['review']['notes']."</i><span style='float:right;'>".$label['review']['recommendations']."</span><br style='clear: both'/>";
        echo "</div>";
    }
    
    /*if($stmt=$mysqli->prepare("SELECT artist, album, format, genre, CanCon, Locale FROM library WHERE RefCode = ?")){
        for($i=1;$i<$indent;$i++){
            echo "<div class=\"label\" style=\"outline: none;\"></div>";
        }
        foreach($_SESSION['PRINTID'] as $BCD){
            $stmt->bind_param("i",$BCD);
            $stmt->execute();
            $stmt->bind_result($artist,$album,$format,$genre,$CanCon,$locale);
            $stmt->fetch();
            $prefix = 0;
            $padded= join('', array($prefix,str_pad($BCD, 10, "0", STR_PAD_LEFT)));
            
            //echo "<img src='barcode/createBarcode.php?bcd=$BCD'/>";
            echo "<div class=\"label\">";
            echo "</span><br style='clear: both'><strong style='float: left'>".substr($artist,0,20).$artpost."</strong><br><i style='float:left'>".substr($album,0,20).$albpost."</i><span style='float:right;'>$genre</span><br style='clear: both'/></div>";
        }
    }
    else{
        echo "<div class=\label>ERROR :".$mysqli->error."</div>";
    }*/
    ?>
<div class="page-break"></div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        setTimeout(window.print(),2000);
        //window.print();
    });
</script>
</body>
</html>
