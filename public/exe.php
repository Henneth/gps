<!-- cron job runs calcualtion.php  -->
<?php


setInterval(function(){
   $insertSQL = shell_exec('php /Users/azolla/sites/gps/public/calculation.php');
   echo $insertSQL."\n";
}, 10000);


function setInterval($f, $milliseconds)
{
    $seconds=(int)$milliseconds/1000;
    while(true)
    {
        $f();
        sleep($seconds);
    }
}


