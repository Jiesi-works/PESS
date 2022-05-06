<?php require_once 'nav.php'; ?>
<br><br>
<?php
if (!isset($_POST["btnSearch"])){
?>
<!-- create a form to search for pantrol car based on id -->
<form name="form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">
<table class="ContentStyle">
<tr></tr>
<tr>
    <td>Patrol Car ID :</td>
    <td><input type="text" name="patrolCarID" id="patrolCarID"></td>
    <td><input type="submit" name="btnSearch" id="btnSearch" value="Search"></td>
</tr>
</table>
</form>
<?php
}?>

<?php
/* if postback via clicking search button */
else
{
    require_once 'db.php';
    
    // create database conncetion
    $conn = new mysqli(DB_SERVER,DB_USER,DB_PASSWORD,DB_DATABASE);
    // check connection
    if ($conn->connect_error)
    {
        die("Connection failed: " . $conn->connect_error);
    }

    // retrive patrol car detail
    $sql = "SELECT * FROM patrolcar WHERE patrolcar_id = ' " . $_POST['patrolcarID'] . "'";
    if ($result = $conn->num_rows ==0)
    {
        ?>
        <script type="text/javascript">window.location="./update.php";</script>
        <?php }
     // if the patrol car does not exist, redirect back to update.php
     if ($result->num_rows ==0)
     {
         ?>
         <script type="text/javascript">window.location="./update.php";</script>
         <?php }

    // else if the patrol car found
    while ($row = $result->fetch_assoc())
    {
      $patrolcarId = $row['patrolcar_id'];
      $patrolCarStatusId = $row['patrolcar_status_id'];
    }
    // retrieve from patrolcar_status table for populating the combo box
$sql = "SELECT * FROM patrolcar_status";
$result = $conn->query($sql);
if ($result->num_rows > 0)
   {
       while ($row = $result->fetchassoc())
       {
           $patrolCarStatusArray[$row['patrolcar_status_id']] = $row ['patrolcar_status_desc'];
       }
   }
   while ($row = $result->fetch_assoc())
   {
       $patrolCarStatusArray[$row['patrolcar_status_id']] = $row ['patrolcar_status_desc'];
   }
   $conn->close();
?>

<form name="form2" method="post" action="<?php echo htmlentities($SERVER['PHP_SELF']); ?> ">
<tr></tr>
<tr>
    <td>ID :</td>
    <td><?php echo $patrolCarId ?>
    <input type="hidden" name="patrolCarId" id="patrolCarId" value="<?php echo $patrolCarId ?>">
    </td>
</tr>
<tr>
    <td>Status :</td>
    <td><select name="patrolCarStatus" id="patrolCarStatus">
        <?php foreach ($patrolCarStatusArray as $key => $value) { ?>
        <option value="<?php echo $key ?>"
        <?php if ($key==$patrolCarStatusId) }?> selected="selected"
        <?php  ?>
    >
    <?php echo $value ?>
    </option>
    <?php  ?>
    </selected></td>
</tr>
<tr>
    <td><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnUpdate" id="btnUpdate" value="Update">
    </td>
</tr>
</table>
</form>
<?php
/* if post back via clicking Update button */
if (isset($_POST["btnUpdate"]))
{
    require_once 'db,php';
    // create database connection
    $conn = new mysqli(DB_SERVER,DB_USER,DB_PASSWORD,DB_DATABASE);
    //check connection
    if ($conn->connect_error)
    {
        die("Connection failed: " . $conn->connect_error);
    }
    //update patrol car status
    $sql = "UPDATE patrolcar SET patrolcar_status_id = '".$_POST['patrolCarStatus']. "' WHERE patrolcar_id = '" .$_POST['patrolCarId']."'";
    if($conn->query($sql)===FALSE)
    {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    /* if patrol car status is Arrived (4) then capture the time of arrival */
    if($_POST["patrolCarStatus"] == '4')
    {
        $sql = "UPDATE dispatch SET time_arrived = NOW() WHERE time_arrived is NULL AND patrolcar_id = ' ".$_POST['patrolcarId']."'";
    if ($conn->query($sql)===FALSE)
    {
        echo "ERROR " . $sql . "<br>" . $conn->error;
    }
    /* else if patrol car status is FREE (3) then capture the time of completion */ else if($_POST["patrolCarStatus"] == '3')
    {
    /* FIrst, retrieve the incident ID from dispatch table by that patrol car */
    $sql = "SELECT incident_id FROM dispatch WHERE time_completed IS NULL AND patrolcar_id '".$_POST['patrolCarId']."'";
    $result = $conn->query($sql);
    if($result->num_rows > 0)
    {
        while ($row = $result->fetch_assoc())
        {
            $incidentId = $row['incident_id'];
        }
    }
    // next update dispatch table
    $sql = "UPDATE incident SET incident_status_id = '3' WHERE incident_id = '$incidentId' AND NOT EXISTS (SELECT * FROM dispatch WHERE time_completed IS NULL AND incident_id = '$incidentId')";
    if ($conn->query($sql)===FALSE)
    {
        echo "ERROR: " .$sql. "<br>" .$conn->error;
    }
    }
 }
}
}
$conn->close();
?>
<script type="text/javascript">window.location="./logcall.php";</script>
<?php  ?>