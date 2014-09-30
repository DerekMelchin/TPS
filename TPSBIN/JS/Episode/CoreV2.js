// unblock when ajax activity stops 
     //$(document).ajaxStop($.unblockUI); 


     function test() {
         $.ajax({ url: 'wait.php', cache: false });
     }
     function Display_RDS() {
         var rds = $.ajax({
             url: "EPV3/AJAX/components/RDS_Episode.php",
             cache: false
         });
         rds.done(function (msg) {
             $("#current_song").html(msg);
         });
         rds.fail(function (jqXHR, textStatus) {
             $("#current_song").html("Request failed: " + textStatus);
         });
     }
     
        // EPV3/Switch.php
        // generate web workers to handle updating diapla
     function Display_Switch() {
        if(typeof(Worker) !== "undefined") {
            if(typeof(switch_worker) == "undefined") {
                switch_worker = new Worker("../TPSBIN/JS/Episode/switch_worker.js");
            }
            switch_worker.onmessage = function(event) {
                //document.getElementById("result").innerHTML = event.data;
                $("#switch_status").html(event.data);
            };
        } else {
            // Sorry! No Web Worker support..
             var switch_s = $.ajax({
                 url: "EPV3/Switch.php?q=V2",
                 cache: false
             });
             switch_s.done(function (msg) {
                 $("#switch_status").html(msg);
             });
             switch_s.fail(function (jqXHR, textStatus) {
                 $("#switch_status").html("Request failed: " + textStatus);
             });
        }
     }

    function Stop_Switch_Workjer(){
        switch_worker.terminate();
        switch_worker=undefined;
    }


     // START CLOCK
     setInterval(function () {
         // Create a newDate() object and extract the seconds of the current time on the visitor's
         var seconds = new Date().getSeconds();
         // Add a leading zero to seconds value
         $("#sec").html((seconds < 10 ? "0" : "") + seconds);
     }, 1000);

     setInterval(function () {
         // Create a newDate() object and extract the minutes of the current time on the visitor's
         var minutes = new Date().getMinutes();
         // Add a leading zero to the minutes value
         $("#min").html((minutes < 10 ? "0" : "") + minutes);
     }, 1000);

     setInterval(function () {
         // Create a newDate() object and extract the hours of the current time on the visitor's
         var hours = new Date().getHours();
         // Add a leading zero to the hours value
         $("#hours").html((hours < 10 ? "0" : "") + hours);
     }, 1000);
     // END CLOCK
     function HideHardware() {

         $.ajax({
             statusCode: {
                 url: "EPV2/AJAX/setpref.php?c=SetHardwareOff",
                 200: function () {
                     $("#HDW_title_open").hide();
                     $("#hdw_prompt").show();
                     $("#hdw").hide();
                 },
                 403: function () {
                     alert("Session Timed Out (Not Authorized)");
                 }
             }
         });
     }
     function ShowHardware() {
        $.ajax({
             statusCode: {
                 url: "EPV2/AJAX/setpref.php?c=SetHardwareOn",
                 200: function () {
                     $("#hdw").show();
                     $("#HDW_title_open").show();
                     $("#hdw_prompt").hide();
                 },
                 403: function () {
                     alert("Session Timed Out (Not Authorized)");
                 }
             }
         });
     }
     function UpdateFinalize() {
         var value = $(this).val();
         $("input[name='time_final_confirm']").val(value);
     }