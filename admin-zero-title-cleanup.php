<?php
$pagetitle='Zero-point title cleanup';
include_once('header.php');
if(!isset($_SESSION['userid'],$_SESSION['admin'])||$_SESSION['admin']!=1){http_response_code(403);echo '<center>Access denied.</center>';include_once('footer.php');exit();}
$message='';
$where='titlepoints=0 AND stnameid IS NULL AND currentstrank=0 AND percent=0';
if(isset($_POST['cleanup_zero_titles'])){
    $stmt=$con->prepare('DELETE FROM gwstats WHERE titlepoints=0 AND stnameid IS NULL AND currentstrank=0 AND percent=0');
    if($stmt->execute())$message='Removed '.(int)$stmt->affected_rows.' empty zero-progress title row(s).';else$message='Cleanup failed.';
    $stmt->close();
}
$stmt=$con->prepare('SELECT s.userid,s.accid,s.charid,s.titlenameid,t.titlename FROM gwstats s LEFT JOIN gwtitles t ON t.titlenameid=s.titlenameid WHERE s.titlepoints=0 AND s.stnameid IS NULL AND s.currentstrank=0 AND s.percent=0 ORDER BY s.userid,s.accid,s.charid,t.titlename');$stmt->execute();$rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();
echo '<section style="width:min(100%,900px);margin:0 auto"><h2>Zero-point title cleanup</h2>';
if($message!=='')echo '<p><strong>'.h($message).'</strong></p>';
echo '<p>This finds empty title-stat rows with zero points, no subtitle/rank, rank 0, and 0% progress. It does not touch any row containing actual title progress.</p><p><strong>'.count($rows).'</strong> empty row(s) found.</p>';
if($rows){echo '<div style="max-height:420px;overflow:auto"><table style="width:100%"><tr><th>User</th><th>Account</th><th>Character</th><th>Title</th></tr>';foreach($rows as $row)echo '<tr><td>'.(int)$row['userid'].'</td><td>'.(int)$row['accid'].'</td><td>'.((int)$row['charid']===0?'Account-wide':(int)$row['charid']).'</td><td>'.h($row['titlename']??('Title #'.(int)$row['titlenameid'])).'</td></tr>';echo '</table></div><form method="post" style="margin-top:20px">'.csrf_input().'<button type="submit" name="cleanup_zero_titles" value="1" onclick="return confirm(\'Remove the empty zero-progress rows listed above?\');">Clean up '.count($rows).' zero-point row(s)</button></form>';}else echo '<p>No cleanup is needed.</p>';
echo '<p><a href="adminlanding.php" class="navlink">Return to Admin Area</a></p></section>';include_once('footer.php');
?>