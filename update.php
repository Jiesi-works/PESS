<?php require_once 'nav.php' ?>
<link href="header_style.css" rel="stylesheet" type="text/css">
<link href="content_style.css" rel="stylesheet" type="text/css">
<br><br>
<?php 
if(!isset($_POST["btnSearch"])){
?>
<!-- create a form to search for patrol car based on id -->
<form name="form1" method="post"
action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?> ">
<table class="ContentStyle">
    <tr></tr>
<tr>
    <td>Patrol Car ID:</td>
    <td><input type="text" name="patrolCarId" id="patrolCarId"></td>
    <td><input type="submit" name="btnSearch" id="btnSearch" value=
    "Search"></td>
</tr>
</table>
</form>
<?php
}
// postback via clicking Search button //
else {
    require_once 'db.php';

    // create database connection
    $conn= new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
    // check connection
    if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
    }
    
    // retrive patrol car detail
    $sql = "SELECT * FROM patrolcar WHERE patrolcar_id = ' ". $_POST['patrolCarId'] .
    " ' " ;
    $result = $conn->query($sql);

    // if the patrol car does not exist, redriect back to update.php
    if($result->num_rows ==0){
    ?>
    <script type="text/javascript">window.location="./update.php";</script>
    <?php }

    // else if the patrol car found
    while ($row = $result->fetch_assoc()) {
        $patrolCarId = $row['patrolcar_id'];
        $patrolCarStatusId = $row['patrolcar_status_id'];
    }

    // retrive from patrol car_status table for populating the combe box
    $sql = "SELECT * FROM patrolcar_status";
    $result = $conn->query($sql);
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $patrolCarStatusArray[$row['patrolcar_status_id']] = $row[
            'patrolcar_status_desc'];
        }
    }

    while ($row = $result->fetch_assoc()) {
        $patrolCarStatusArray[$row['patrolcar_status_id']] = $row[
        'patrolcar_status_desc'];
    }
    $conn->close();

?>

    <!-- display a form for operator to update status of patrol car -->
    <form name="form2" method="post"
    actions="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">
    <table>
        <tr></tr>
    <tr>
        <td>ID :</td>
        <td><?php echo $patrolCarId ?>
        <input type="hidden" name="patrolCarId" id="patrolCarId"
        value="<?php echo $patrolCarId ?>">
        </td>
   </tr>
   <tr>
       <td>Status :</td>
       <td><select name="patrolCarStatus" id="patrolCarStatus">
           <?php foreach($patrolCarstatusArray as $key => $value) { ?>
            <options value="<?php echo $key ?>"
            <?php if ($key==$patrolCarStatusId) {?> selected="selected"
            <?php } ?>

            >

            <?php echo $value ?>
            </options>
            <?php } ?>
            </select></td>
    </tr>
     <tr>
            <td><input type="reset" name="btnCancel" id="btnCancel" vaule="Reset"></td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name=
            "btnUpdate" id="btnUpdate" value="Update">
            </td>
    </tr>
</table>
 </form>

 <?php
 /* if postback via clicking Update button*/
 if(isset($_POST['btnUpdate'])){
require_once 'db.php';
// create database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
// check connection
if($conn->connect_error){
    die("connection failed: " . $conn->connect_error);
}
// update patrolcar status
$sql = "UPDATE dispatch SET patrolcar_status_id = ' ".$_POST['patrolCarStatus']." ' WHERE patrolcar_id = ' ".$_POST[
'patrolCarId']." ' ";
if($conn->query($sql)===FALSE) {
    echo "Error: " . $sql . "</br>" . $conn->error;
}
/*if patrol car status is Arrived (4) then time capture the time of arrival */
if ($_POST["patrolCarStatus"] == '4'){
    $sql = "UPDATE dispatch SET time_arrived = NOW() WHERE time_arrived is NULL AND patrolcar_id = ' ".$_POST['patrolCarId']."'";
}
if($conn->query($sql)===FALSE){
    echo "Error: " .$sql . "<br>" . $conn->error;
}
 }
 
 /* else if patrol car status is free (3) then capture the time of completetion*/ else if ($_POST["patrolCarStatus"] ==
'3'){
    /* first retrive the incident_id from dispatch table handled by that patrol car */
    $sql = "SELECT incident_id FROM dispatch WHERE time_completed IS NULL AND patrolcar_id = '".$_POST['patrolCarId'] . " ' ";
    $result = $conn->query($sql);
    if ($result->num_rows > 0){
        while ($row = $result->fetch_assoc()){
            $incidentId = $row['incident_id'];
        }
    }
    // next update dispatch table
    $sql = "UPDATE dispatch SET time_completed = NOW() WHERE time_completed is NULL AND patrolcar_id = ' ".$_POST[
    'patrolCarId']."'";
    if($conn->query($sql)===FALSE){
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    /* update incident table to completed (3) all patrol car attended to it are free now */
    $sql = "UPDATE incident SET incident_status_id = '3' WHERE incident_id = '$incidentId' AND NOT EXISTS (SELECT * FROM
    dispatch WHERE time_completed is NULL AND incident_id = '$incidentId')";
    If($conn->query($sql)===FALSE){
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>
<script type="text/javascript">window.location=".logcall.php";</script>
<?php } ?>
