<html>
<body>

<?php
/*
HOST: ora11g.cs.cityu.edu.hk
PORT: 1522
SERVICE_NAME: orcl.cs.cityu.edu.hk
*/


// Create connection to Oracle
    $conn = oci_connect("dehezhang2", "55199998", "//ora11g.cs.cityu.edu.hk:1522/orcl.cs.cityu.edu.hk");
    if (!$conn) {
       $m = oci_error();
       echo $m['message'], "\n";
       exit;
    }
    else {
       print "Connected to Oracle!<br>\n";
    }
	
    // Prepare the statement
    // HOUSE and HOUSEROW are created on the server
    function runSql($conn, $cmd){
        $stid = oci_parse($conn, $cmd);
        if (!$stid) {
            $e = oci_error($conn);
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }

        // Perform the logic of the query
        $r = oci_execute($stid);
        if (!$r) {
            $e = oci_error($stid);
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
        //print $cmd.'\n<br>';
        return $stid;
    }

    function showTable($conn, $houseid) {
        print "House ".$houseid."<br>\n";
        $stid = runSql($conn, 'SELECT * FROM HOUSEROW WHERE HID=\''.$houseid.'\'ORDER BY RID');
        // Fetch the results of the query
        print "<table border='1' style='text-align: center;'>\n";
        while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
            print "<tr>\n";
            print "<td>".$row['RID']."</td>\n";
            $ordered = 10 - $row["REMAIN_SEAT"];
            for($i = 0; $i < 10; $i++){
                if($i < $ordered){
                    print "<td style='background-color: red; width: 50px; height: 50px'>
                          ".$row['RID'].$i." </td>\n";
                } else {
                    print "<td style='background-color: green; width: 50px; height: 50px'>                
                          ".$row['RID'].$i." </td>\n";

                }
            }
            print "</tr>\n";
        }
        for($i = -1; $i < 10; $i++){
            if($i<0){
                print "<td></td>\n";
            }
            else{
                print "<td>".$i."</td>\n";
            }
        }
        print "</table>\n";   
    }

    function clearAll($conn){
        $stid =runSql($conn, 'UPDATE HOUSEROW SET REMAIN_SEAT = 10');
    }

    function orderSeat($conn, $houseid, $seatNum){
        $stid = runSql($conn, 'SELECT * FROM HOUSEROW WHERE HID=\''.$houseid.'\'ORDER BY RID');
        print "Result: ";
        if($seatNum > 0 && $seatNum <= 10){
            while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                if($row["REMAIN_SEAT"] >= $seatNum){
                    $remain = $row["REMAIN_SEAT"];
                    $newval = $remain - $seatNum;
                    $ordered = 10 - $remain;
                    runSql($conn, 'UPDATE HOUSEROW 
                                   SET REMAIN_SEAT = '.$newval.' 
                                   WHERE HID=\''.$houseid.'\' 
                                   AND RID=\''.$row["RID"].'\'');
                    print "The order is successful. The tickets ";
                    if($seatNum == 1){
                        print "is ".$row["RID"].$ordered."<br>";
                    } elseif($seatNum == 2){
                        print "are ".$row["RID"].$ordered." and ".$row["RID"].($ordered + 1)."<br>";
                    } else{
                        print "are ".$row["RID"].$ordered." to ".$row["RID"].($ordered + $seatNum - 1)."<br>";
                    }
                    showTable($conn, $houseid);
                    return;
                }
            }
        }
        print "The order is NOT successful!<br>";
        showTable($conn, $houseid);
    }
    
    if($_GET["clear"]==1){
        clearAll($conn);
    }
    print "Welcome, ".$_GET["name"]."!<br>\n";
    print "Your email address is: ".$_GET["email"].".<br>\n"; 
    $ticketnum = $_GET["ticketnum"];
    $house = $_GET["house"];
    print "Your order is: Order ".$ticketnum." tickets in House ".$house."<br>\n";
    orderSeat($conn, $house, $ticketnum); 
    // Close the Oracle connection
    oci_close($conn);
?>

</body>
</html>
