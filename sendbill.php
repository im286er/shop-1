<?php 
//$code= $_GET['code'];
//$state=$_GET['state'];
$orderid=$_GET['orderid'];
$code = $_GET['code'];

$callback="http://www.3dcity.com/user.php/wxuser/sendbill?orderid=".$orderid."&code=".$code;

echo "<script language=\"javascript\">";
echo "location.href=\"$callback\"";
echo "</script>";
//exit;
//header("location: '".$newurl."'");
?>