<?php
    
    include_once "functions.php";
    if(!isset($_SESSION)){
        sec_session_start();
    }

    class TPS_Cron{
        
        // Set to overridde grading 
        // Null = DB Value for Genre or Program
        private $grade_force_perc=NULL;
        private $grade_force_countryreq=NULL; // countryreg=CanCon or FCC Country Requirement
        private $grade_force_playlist=NULL;
        private $grade_force_spoken=NULL;
        private $connected=FALSE;
        //public $mysqli=NULL;

        function __construct($username=NULL,$password=NULL,$database=NULL,$host=NULL,$port=3306){
            try
            {
                // check if values given
                //if (is_null($host) || is_null($database) || is_null($username) || is_null($password)) throw new Exception("Please specify the host, database, username and password!");
                //echo "initialized";
                /*include_once "db_connect.php";
                if($mysqli->connect_error){
                    echo $mysqli->connect_error;
                }
                else{
                    echo "connection established";
                }*/
                // perform connection if given
                /*$mysqli = mysqli_connect($host,$username,$password,$database,$port);

                // if connection fails throw error, can not continue
                if($mysqli->connect_error){
                    throw new Exception("Please verify connection paramaters, ".$mysqli->connect_error);
                }
                else{
                    $this->connected=TRUE;
                }*/
                // connection created and params set (so far)...

            }
            catch (Exception $e)
            {
                $this->error_message($e->getMessage());
            }
            return TRUE;
        }
        static public function run_cron(){
            
        }
        static public function install_cron(){
            
        }
        static public function remove_cron(){
            
        }
        static public function update_cron(){
            
        }
        static public function close_cron(){
            $mysqli->close();
            $this->close();
        }
        static private function mail_admin(){
            
        }
        static private function mail_user(){
            
        }
        static public function update_switch($mute=FALSE,$server){
            // does not output result of query to screen, only to database
            
            include_once "db_connect.php";

            $output = "";
            $DEBUG=FALSE;
	        $ROOT = addslashes($_GET['q']);
            //$server = "ckxu3400lg.local.ckxu.com";
            //$mute=FALSE;
            
            $res=array();
            $info=array();
            $MISMATCH=FALSE;
            if($ROOT=='V2'){
                $BASE="./EPV3";
            }
            else{
                $BASE = ".";
            }

            $fp = fsockopen($server, 23, $errno, $errstr, 30);
            for($i=0;! $fp||$i>5; $i++){
                $fp=fsockopen($server, 23, $errno, $errstr, 30);
            }   
            if (!$fp) {
                echo "$errstr ($errno)<br />\n";
            } else {
                $out = "*0SL";
                fwrite($fp, $out);
                stream_set_timeout($fp,2,0);
                  $temp = fread($fp, 8192);
                  $res[0] = explode("\n",$temp);
                $info[0] = stream_get_meta_data($fp);
                $out = "*0SS";
                fwrite($fp, $out);
                stream_set_timeout($fp,2,0);
                  $res[1] = fread($fp, 8192);
                $info[1] = stream_get_meta_data($fp);
                fclose($fp);
    
                if ($info[0]['timed_out']) {
                    $output .= 'Connection timed out!';
                } else {
                    //echo $res[0].$res[1];
                }
            }
	        $sql = "select * from switchstatus ORDER BY ID DESC limit 1 ";
            $output.="<span style=\"font-size:9px;\">ACS 8.2 Plus Switch Status</span><table>
	        <tr>";
	        for($i = 1; $i < 9; $i++){
		        $output .= "<th>" . $i . "</th>";
	        }
	        $output .= "<th>S</th></tr><tr>";
	        $result = $mysqli->query($sql);
	        $srr = mysqli_fetch_array($result);
            if(!empty($res[0][0])&&!empty($res[0][1])&&!empty($res[1])){
                // CHECK BANK 1
                if($srr['Bank1']==$res[0][0]){
                    $bank1=$srr['Bank1'];
                }
                else{
                    $MISMATCH=TRUE;
                    if($DEBUG){
                        $output .= "<br><div class='ui-state ui-state-error'><span>ERROR (B)</span><br>".$res[0][0]."<br>".$srr['Bank1']."</span></div>";
                    }
                    if($res[0][0]!=""){
                        $bank1=$res[0][0];
                    }

                }
                // CHECK BANK 2
                if($srr['Bank2']==$res[0][1]){
                    $bank2=$srr['Bank1'];
                }
                else{
                    $MISMATCH=TRUE;
                    if($DEBUG){
                        $output .= "<br><div class='ui-state ui-state-error'><span>ERROR (R)</span><br>".$res[0][1]."<br>".$srr['Bank2']."</span></div>";
                    }
                    if($res[0][1]!=""){
                        $bank2=$res[0][1];
                    }

                }
                // CHECK BANK 2
                if($srr['SS']==$res[1]){
                    $silence=$srr['SS'];
                }
                else{
                    $MISMATCH=TRUE;
                    if($DEBUG){
                        $output .= "<br><div class='ui-state ui-state-error'><span>ERROR (S)</span><br>QR: ".$res[1]."<br>DB: ".$srr['SS']."</span></div>";
                    }
                    if($res[1]!=""){
                        $silence=$res[1];
                    }

                }
            }
            else{
        
            }
	        $track = 0;
            $title = 1;
	        for($i = 16; $i > 0; $i=$i-2){
		        $dl = substr($bank1,($i*(-1)),1); 	
		        if($dl=='0'){
			        $output .= "<td><img src=\"$BASE/Images/LIGHTS/GreenOff.png\" title=\"Switch &#35;1 - $title\" alt=\"0\"/></td>";
		        }	
		        else if($dl=='1'){
			        $output .= "<td><img src=\"$BASE/Images/LIGHTS/GreenOn.png\" title=\"Switch &#35;1 - $title\" alt=\"1\"/></td>";
		        }
		        else{
			        $output .= "<td><img src=\"$BASE/Images/LIGHTS/AmberOff.png\" title=\"Switch &#35;1 - $title\" alt=\"2\"/></td>";
		        }
		        $title++;
	        }
            $title = "Broadcast Silence Sensor";
	        //$silence = $srr['SS'];
	        $SS1 = substr($silence,-1);
	        if($SS1 == "0"){
		        $output .= "<td><img src=\"$BASE/Images/LIGHTS/AmberOff.png\" title=\"$title\" alt=\"0\"/></td>";
	        }
	        else if($SS1 == "1"){
		        $output .= "<td><img src=\"$BASE/Images/LIGHTS/AmberOn.png\" title=\"$title\" alt=\"1\"/></td>";
	        }
	        else{
		        $output .= "<td><img src=\"$BASE/Images/LIGHTS/AmberOff.png\" title=\"$SS1\" alt=\"2\"/></td>";
	        }
	        $output .= "</tr><tr>";
            $title = 1;
	        for($i = 16; $i > 0; $i=$i-2){
		        $dl = substr($bank2,($i*(-1)),1); 	
		        if($dl=='0'){
			        $output.="<td><img src=\"$BASE/Images/LIGHTS/RedOff.png\" title=\"Switch &#35;2 - $title\" alt=\"0\"/></td>";
		        }	
		        else if($dl=='1'){
			        $output.="<td><img src=\"$BASE/Images/LIGHTS/RedOn.png\" title=\"Switch &#35;2 - $title\" alt=\"1\"/></td>";
		        }
		        else{
			        $output.="<td><img src=\"$BASE/Images/LIGHTS/RedOff.png\" title=\"Switch &#35;2 - $title\"alt=\"2\"/></td>";
		        }
		        $title++;
	        }
	        $title = "Record Silence Sensor";
	        $SS2 = substr($silence,-2,-1);
	        if($SS2 == "0"){
		        $output.="<td><img src=\"$BASE/Images/LIGHTS/AmberOff.png\" title=\"$title\" alt=\"0\"/></td>";
	        }
	        else if($SS2 == "1"){
		        $output.="<td><img src=\"$BASE/Images/LIGHTS/AmberOn.png\" title=\"$title\" alt=\"1\"/></td>";
	        }
	        else{
		        $output.="<td><img src=\"$BASE/Images/LIGHTS/AmberOff.png\" title=\"$SS2\"/></td>";
	        }
	        $output.= "</tr></table>";

            $mysqli->query("INSERT into switchstatus (Bank1,Bank2,SS,UID) values ('".$bank1."','".$bank2."','".$silence."','0')");
            $mysqli->close();
            if($mute!=TRUE){
                print($output);
            }
        }
        static public function grade_episode($episode_num=NULL,$new_only=TRUE){
            include_once "db_connect.php";
            /*if(!$mysqli->connected){
                throw new Exception ("Connection is closed, please initialize the connection (__contruct)");
            }*/
            //if(is_null($episode)){
                // when a Null param is given, assume all are wanted
                if($new_only===TRUE){
                    
                }
                else{
                    echo "<style>
                    table{
                        width: 100%;
                    }
                    table, th, td {
                       border: 1px solid black;
                       
                    }</style>
                    <br>performing grade, connection established";
                    // NOTE: Used Arbitrary date of jan 1 2014
                    $query_all_force = "SELECT episode.*,genre.* FROM genre,episode left join program on episode.programname=program.programname where genre.genreid=program.genre and episode.date>\"2014-0-01\" and episode.EpNum like '%".$episode_num."%' order by EpNum ASC;";
                    $result = $mysqli->query($query_all_force);

                    // get song and traffic information for the episode
                    $song_stmt = $mysqli->stmt_init();
                    $song_stmt->prepare("SELECT * FROM song left join trafficaudit on song.songid=trafficaudit.songid WHERE `song`.`callsign`=? and `song`.`programname`=? and `song`.`date`=? and `song`.`starttime`=? ");
                    $song_stmt->bind_param('ssss',$episode_call,$episode_name,$episode_date,$episode_start);
                    foreach ( $result as $episode ){
                        echo "<br><br><span style='width:100%; text-align:center;'>RAW DATA</span><br>";
                        echo "<table><thead>
                        <th>callsign</th><th>pgm</th><th>date</th><th>st_time</th><th>end_time</th><th>prerec</th><th>ttl_spkn</th><th>desc</th><th>Lock</th><th>Type</th><th>EPN</th><th>Guests</th><th>ENDStamp</th><th>LastAccess</th><th>score</th><th>Rvd_Date</th><th>creation Timestamp</th><th>IP_Created</th><th>IP_last_access</th><th>IP_Finalized</th><th>genre</th><th>CC_R</th><th>PL_R</th><th>CCP_R</th><th>PLP_R</th><th>G-UID</th><th>PlType</th><th>CcType</th><th>gcall</th></tr>
                        </thead><tbody>
                        ";
                        foreach ($episode as $p)
                        {
                            print "<td>$p</td>";
                        }
                        echo "</tr></tbody></table><br>";
                        echo "<table><thead>
                        <th>songid</th><th>callsign</th><th>program</th><th>pgm_date</th><th>pgm_time</th><th>ins</th><th>s_time</th><th>album</th><th>title</th><th>artist</th><th>cancon</th><th>pl</th><th>Cat</th><th>hit</th><th>spkn</th><th>comp.</th><th>note</th><th>AdViol</th><th>bcd</th><th>TS</th><th>RCD</th><th>TRAid</th><th>TRAsid</th><th>TRAadv</th><th>TRAsT</th><th>TRAeT</th><th>TRAc</th>
                        </thead><tbody>";
                        // get songs for episode
                        $episode_call = $episode['callsign'];
                        $episode_name = $episode['programname'];
                        $episode_date = $episode['date'];
                        $episode_start = $episode['starttime'];
                        /*grading weights
                        Logging Requirements (100%)
                            ->required Ads (40%)
                                --> each ad missing will result in proportional decrease in score from requirement (3/4 ads = 30%,potential: 40%)
                                --> additional ads (traffic) will result in a proportional score decrease (6/4 ads = 20%,potential: 40%)
                                --> unprompted (violated) traffic will not count toward score and decrement score (3/4 ads with one violation = 20%, potential 40%)
                            -> Promptlog (10%)
                                --> starts at 10% automatically
                                --> ads must be accounted for, each missing ad decreases score by proportional percentage (1/2 prompted ads = 5%, potential 10%)
                                    ---> condition if cannot link promptlog id with adid from song table 
                            -> PSA / Promo (10%)
                                --> Proportional score of requirements set.
                            -> Timestamp Verification (15%)
                                --> checks against Promptlog, timestamp and \"time\" of play.
                                --> must be within 30 minutes for 1/2 score on proportional score, 10 min for full score
                            -> TOH (10%)
                                --> must be synced with top of Hour and logged as 43 or 12
                            -> Finalization/Length (5%)
                                --> must be finalized within allotted time frame, 10 min buffer given, extra time decreases score based on percentage of extra time
                                    ---> 60 min show permitted 70 min, performs 120 minutes = 83.3% decrease in score (0.84% given as score of potential 5%)
                                --> 5 min underrun and latestart buffer given, Time under target results in score decrease proportional to program length offset by buffers.
                                --> no finalization = -5% overall score decrease (0.0%/5%)
                                    --> can combine with overrun and result in negative score to minimum of 0% overall score total
                            -> song requirements (10%)
                                --> 5% associated with Country Content requirements (proportional grading on genre requirements
                                --> 2.5% associated with playlist requirements (set in genre or program)
                                --> 2.5% associated with Spoken Time (requires percentages set in genre or 5% of program spoken)
                                    --> time calculation uses program length or episode length, whichever is higher
                        Hits (-10%)
                            -> violating Hitlimit generates -10% score on program to minimum of zero overall score (0%)
                            -> no positive score givcen for being at or under limit.
                        */
//                        $song_stmt->bind_result()
                        $song_stmt->execute();
                        $data = $song_stmt->get_result();
                        while($row = $data->fetch_array(MYSQLI_NUM))
                        {
                            echo"<tr>";
                            foreach ($row as $r)
                            {
                                print "<td>$r</td>";
                            }
                            print "</tr>";
                        }
                        echo "</tbody><tfoot></tfoot></table>";
                        
                    }
                }
            /*}
            else if (is_numeric($episode)){
                // otherwise grade only given

            }
            else{
                throw new Exception ("Non Numeric param given for episode number, give numeric or NULL");
            }*/
        }
    }

?>