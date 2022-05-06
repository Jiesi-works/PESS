<!doctype html>
<html>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
<link href="header_style.css" rel="stylesheet" type="text/css">
<link href="content_style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php //import nav.php
require_once'nav.php';
?>
<?php //vaildate if request comes from logcall.php or post back
   if (!isset($_POST["btnProcessCall"]) && !isset($_POST["btnDispatch"])) 
    header("Location: logcall.php");
    ?>
<?php
// connect to a database
require_once 'db.php';
//create connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
//check connection
if ($conn->connect_error)
{
    die("connection failed: " . $conn->connection_error);
}
// retieve from patrolcar table those patrol cars that are 2:Patrol or 3:Free
$sql = "SELECT patrolcar_id, patrolcar_status_desc FROM patrolcar JOIN patrolcar_status ON patrolcar.patrolcar_status_id=patrolcar_status.patrolcar_status_id WHERE patrolcar.patrolcar_status_id='2' OR patrolcar.patrolcar_status_id='3'";

$result = $conn->query($sql);

if ($result->num_rows > 0)
{
   while ($row = $result->fetch_assoc())
   {
       $patrolcarArray[$row['patrolcar_id']] = $row['patrolcar_status_desc'];
   }
}
$conn->close();
?>
<?php
// if postback via clicking Dispatch button
if (isset($_POST["btnDispatch"]))
{
    require_once 'db.php';

    // create connection
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSOWRD, DB_DATABASE);
    // check connection
    if ($conn->connect_error)
    {
        die("connection failed: " . $conn->Connect_error);
    }
    $patrolcarDispatched = $_POST["chkPatrolcar"]; // array of patrolcar being dispatched from post back
    $numOfPatrolcarDispatched = count($patrolcarDispatched);
    if ($numOfPatrolcarDispatched > 0)
    {
        $incidentStatus='2'; // incident status to be set as Dispatched
    }
    else
    {

        $incidentStatus='1'; // incident status to be set as Pending
    }
    $sql = "INSERT INTO incident (caller_name, phone_number, incident_type_id, incident_location, incident_desc, incident_status_id)
    VALUES (' " . $_POST['incidentType'].", '".$_POST['callerName']."', ' ".$_POST['contactNo']."', '".$_POST['incidentType']."', '" .$_POST['location']. " ', ' ".$_POST['incidentDesc']."', $incidentStatus";
    if ($conn->query(sql)===FALSE)
    {
        echo "Error: " . $sql . "<br>" . $conn ->error;
    }
    // retrieve incident_id for the newly inserted incident
    $incidentId=mysqli_insert_id($conn);
    // update patrolcar status table and add into dispatch table
    for($i=0; $i < $numofPatrolcarDispatched; $i++)
    {
        // update patrol car status /////
        $sql = "UPDATE patrolcar SET patrolcar_status_id='1' WHERE patrolcar_id = ' " . $patrolcarDispatched[$i] . " ' ";
    }
    if ($conn->query($sql)===FALSE)
    {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    //insert dispatch data
    $sql = "INSERT INTO dispatch (incident_id, patrolcar_id, time_dispatched) VALUES ($incidentid, '" .$patrolcarDispatched[$i]. " ' , NOW())";
    if ($conn->query($sql)===FALSE)
    {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
    $conn->close();
}
?>


   <!-- display the incident information passed from logcall.php -->
<form name="form" method="post" action="<php echo htmlentities($SERVER['PHP_SELF']); ?>">
<table class="ContentStyle">
    <tr>
        <td colspan="2">Incident Detail</td>
   </tr>
   <tr>
        <td>Caller's Name :</td>
        <td><?php echo $_POST['callerName'] ?>
            <input type="hidden" name="callerName" id="callerName"
             value="<?php echo $_POST['callerName'] ?>"></td>
   </tr>
   <tr>
        <td>Contact No :</td>
        <td><?php echo $_POST['contactNo'] ?>
            <input type="hidden" name="contactNo" id="contactNo"
             value="<?php echo $_POST['contactNo'] ?>"></td>
   </tr>
   <tr>
        <td>Location :</td>
        <td><?php echo $_POST['location'] ?>
            <input type="hidden" name="location" id="location"
             value="<?php echo $_POST['location'] ?>"></td>
   </tr>
   <tr>
        <td>Incident Type :</td>
        <td><?php echo $_POST['incidentType'] ?>
            <input type="hidden" name="incidentType" id="incidentType"
             value="<?php echo $_POST['incidentType'] ?>"></td>
   </tr>
   <tr>
        <td>Incident Desc :</td>
        <td><textarea name="incidentDesc" cols="45" row="5" readonly id="incidentDesc">
            <?php echo $_POST['incidentDesc'] ?>
            </textarea>
            <input name="incidentDesc" type="hidden" id="incidentDesc" value="<?php echo $_POST['incidentDesc'] ?>">
   </td>
   </tr>
</table>
</form>

<!-- populate table with patrolcar data -->
<table class="ContentStyle">
    <tr>
        <td colspan="3">Dispatch Patrolcar Panel</td>
    </tr>
<?php
foreach($patrolcarArray as $key=>$value){ 
?>
<tr>
    <td><input type="checkbox" name="chkPatrolcar[]"
         value="<?php echo $key?>"> </td>
         <td><?php echo $key ?></td>
         <td><?php echo $value ?></td>
 <tr>
<?php  ?>
<tr>
    <td><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td>
    <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnDispatch" id="btnDispatch" value="Dispatch">
   </td>
</tr>
</table>
<Script type="text/javascript">window.location="./logcall.php";
<?php } ?>
</script>
</body>
</html>