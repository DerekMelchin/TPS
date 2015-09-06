<?php
namespace TPS;
/* 
 * The MIT License
 *
 * Copyright 2015 James Oliver <support@ckxu.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
require_once 'tps.php';
class station extends TPS{
    protected $callsign = null;
    
    protected $stationName = null;
    protected $stationDesignation = null;
    protected $stationFrequency = null;
    protected $stationPhoneDirector = null;
    protected $stationPhoneManager = null;
    protected $stationPhoneRequest = null;
    protected $stationAddress = null;
    protected $stationWebsite = null;
    protected $DefaultSortOrder = 'asc';
    protected $GroupPlaylist = False;
    protected $GroupFail = "#FC6E51"; #https://bootstrapbay.com/blog/bootstrap-ui-kit/
    protected $GroupWarning = "#FFCE54";
    protected $GroupPass = "#A0D468";
    protected $GroupNote = "#4FC1E9";
    protected $ProgramCounters = False;
            
    function __construct() {
        parent::__construct();
    }
    
    public function setupParams($callsign){
        $this->callsign = $callsign;
    }
    
    /**
     * changes the station name
     * @param type $name
     * @return boolean
     */
    public function setStationName($name){
        $con = $this->mysqli->prepare("Update station SET stationname=? where callsign=?");
        $con->bind_param('ss',$name,$this->callsign);
        if($con->execute()){
            $this->stationName = $name;
            return true;
        }
        else{return false;}
    }
    
    /**
     * Changes Station Designation in DB and Class
     * @param type $Designation
     * @return boolean
     */
    public function setStationDesignation($Designation){
        $con = $this->mysqli->prepare("Update station SET Designation=? where callsign=?");
        $con->bind_param('ss',$Designation,$this->callsign);
        if($con->execute()){
            $this->stationDesignation = $Designation;
            return true;
        }
        else{return false;}
    }
    
    /**
     * Changes Station Frequency in DB and Class
     * @param type $frequency
     * @return boolean
     */
    public function setStationFrequency($frequency){
        $con = $this->mysqli->prepare("Update station SET frequency=? where callsign=?");
        $con->bind_param('ss',$frequency,$this->callsign);
        if($con->execute()){
            $this->stationFrequency = $frequency;
            return true;
        }
        else{return false;}
    }
    
    /**
     * Changes Director Phone in DB and Class
     * @param type $phone
     * @return boolean
     */
    public function setStationPhoneDirector($phone){
        $con = $this->mysqli->prepare("Update station SET directorphone=? where callsign=?");
        $con->bind_param('ss',$phone,$this->callsign);
        if($con->execute()){
            $this->stationPhoneDirector = $phone;
            return true;
        }
        else{return false;}
    }
    
    /**
     * Changes Request Line number in DB and Class
     * @param type $phone
     * @return boolean
     */
    public function setStationPhoneRequest($phone){
        $con = $this->mysqli->prepare("Update station SET boothphone=? where callsign=?");
        $con->bind_param('ss',$phone,$this->callsign);
        if($con->execute()){
            $this->stationPhoneRequest = $phone;
            return true;
        }
        else{return false;}
    }
    
    /**
     * Changes Station Manager Phone in DB and Class
     * @param type $phone
     * @return boolean
     */
    public function setStationPhoneManager($phone){
        $con = $this->mysqli->prepare("Update station SET managerphone=? where callsign=?");
        $con->bind_param('ss',$phone,$this->callsign);
        if($con->execute()){
            $this->stationPhoneManager = $phone;
            return true;
        }
        else{return false;}
    }
    
    /**
     * Changes Station website in DB and Class
     * @param type $url
     * @return boolean
     */
    public function setStationWebsite($url){
        $con = $this->mysqli->prepare("Update station SET website=? where callsign=?");
        $con->bind_param('ss',$url,$this->callsign);
        if($con->execute()){
            $this->stationWebsite = $url;
            return true;
        }
        else{return false;}
    }
    
    /**
     * Changes Station Address in DB and Class
     * @param type $address
     * @return boolean
     */
    public function setStationAddress($address){
        $con = $this->mysqli->prepare("Update station SET address=? where callsign=?");
        $con->bind_param('ss',$address,$this->callsign);
        if($con->execute()){
            $this->stationAddress = $address;
            return true;
        }
        else{return false;}
    }
    
    /**
     * Changes Station TimeZone in DB and Class
     * @param type $tz
     * @return boolean
     */
    public function setStationTimeZone($tz){
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        if(!in_array($tz, $tzlist)){
            trigger_error("TimeZone $tz not found in TimeZone list", E_USER_WARNING);
        }
        $con = $this->mysqli->prepare("Update station SET timezone=? where callsign=?");
        $con->bind_param('ss',$tz,$this->callsign);
        if($con->execute()){
            $this->stationTimeZone = $tz;
            return true;
        }
        else{return false;}
    }
    
    public function setPlaylistGrouping($gp){
        $con = $this->mysqli->prepare("Update station SET ST_PLLG=? where callsign=?");
        $con->bind_param('ss',$gp,$this->callsign);
        if($con->execute()){
            $this->GroupPlaylist = $gp;
            return true;
        }
        else{return false;}
    }
    
    public function setDefaultSortOrder($SortOrder){
        $con = $this->mysqli->prepare("Update station SET ST_DefaultSort=? where callsign=?");
        $con->bind_param('ss',$SortOrder,$this->callsign);
        if($con->execute()){
            $this->DefaultSortOrder = $SortOrder;
            return true;
        }
        else{return false;}
    }
    
    private function setProgramCounters($SortOrder){
        $con = $this->mysqli->prepare("Update station SET ST_DispCount=? where callsign=?");
        $con->bind_param('ss',$SortOrder,$this->callsign);
        if($con->execute()){
            return true;
        }
        else{return false;}
    }
    
    public function programCountersOn(){
        if($this->changeProgramCounters("0")){
            $this->ProgramCounters = True;
            return true;
        }
        else{return false;}
    }
    
    public function programCountersOff(){
        if($this->changeProgramCounters("0")){
            $this->ProgramCounters = False;
            return true;
        }
        else{return false;}
    }
    
    public function programCounters(){
        return $this->ProgramCounters;
    }
}
