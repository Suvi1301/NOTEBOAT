<?php
error_reporting(0);
session_start();
if (!isset($_SESSION['login_user']))
{
  header("location: login.php");
}

if($_SERVER["REQUEST_METHOD"] == "POST")
{
$file_result = "";
$loggedInUser = $_SESSION['login_user'];
$fileModule = $_POST['modules'];
if (isset($_POST['public']))
{
	$isFilePublic = '1';
}
else
{
	$isFilePublic = '0';
}

$newFileID = rand();
$currentDateTime = date('d/m/Y h:i:s', time());

if ($_FILES["file"]["error"] > 0)
{
  $file_result .= "No file uploaded or invalid file";
  $file_result .= "Error Code: " .$_FILES["file"]["error"] . "<br>";
}//if
else
{
  if (!file_exists("uploads/$loggedInUser/"))
  {
    mkdir("uploads/" . $loggedInUser, 0777);
  }
  
$nameStoredArray = explode(".",  $_FILES["file"]["name"]);
$nameStored = $nameStoredArray[0];
file_exists("uploads/$loggedInUser/" . $nameStored . ".pdf");
  if (file_exists("uploads/$loggedInUser/" . $nameStored . ".pdf"))
  {
    $file_result .= "A file with the same name exists. Please choose a new name";

  }//if
  else if ($_FILES["file"]["type"] === 'application/pdf')
  {
    move_uploaded_file($_FILES["file"]["tmp_name"],
    "uploads/$loggedInUser/" . $_FILES["file"]["name"]);

    $file_result .= "File uploaded successfully";

    require_once('/home/pi/NOTEBOAT/config.inc.php');
    $conn = new mysqli($database_host, $database_user, $database_pass,
                       $database_name);

    if($conn -> connect_error)
    {
      die('Connect Error ('.$conn -> connect_errno.')'.$conn -> connect_error);
    }

    $fileName = $_FILES["file"]["name"] . "<" . $newFileID;
    $query = "INSERT INTO `Notes` (`fileID`, `userID`, `fileName`, `fileModuleCode`, `uploadDate`, `filePublic`) VALUES ('$newFileID','$loggedInUser','$fileName','$fileModule', '$currentDateTime' , '$isFilePublic')";

    $added = $conn -> query($query);
    $file_result = $fileName . " uploaded!";
  }//if
  else if (getimagesize($_FILES["file"]["tmp_name"]))
  {
    move_uploaded_file($_FILES["file"]["tmp_name"],
    "uploads/$loggedInUser/" . $_FILES["file"]["name"]);
    chdir('/home/pi/NOTEBOAT/NotesSharing/uploads/' . $loggedInUser);
    $fileName = $_FILES["file"]["name"];
	  $newName = explode(".", $fileName);
	  $newFileName = $newName[0];
   	echo shell_exec("java -jar /home/pi/NOTEBOAT/NotesSharing/img2pdf.jar " . $fileName . " " . $newFileName . ".pdf 2>&1");
	  echo shell_exec("rm " . $fileName);

    require_once('/home/pi/NOTEBOAT/config.inc.php');
    $conn = new mysqli($database_host, $database_user, $database_pass,
                       $database_name);

    if($conn -> connect_error)
    {
      die('Connect Error ('.$conn -> connect_errno.')'.$conn -> connect_error);
    }

    $fileName = $newFileName . ".pdf<" . $newFileID;
    $query = "INSERT INTO `Notes` (`fileID`, `userID`, `fileName`, `fileModuleCode`, `uploadDate`, `filePublic`) VALUES ('$newFileID','$loggedInUser','$fileName','$fileModule', '$currentDateTime' , '$isFilePublic')";

    $added = $conn -> query($query);
	$file_result .= "File converted to pdf and uploaded!";
  }
  else
  {
    $file_result .= "Please upload a pdf or an image file";
  }
}
echo '<script type="text/javascript"> alert(\''. $file_result . '\'); window.location.href="notesShare.php"; </script>';
}
?>



