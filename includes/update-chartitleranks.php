<?php
if (isset($_SESSION['userid'])) {
    $userid = (int)$_SESSION['userid'];
    $accid = (int)($_SESSION['prefaccid'] ?? 0);
    $charid = (int)($_SESSION['prefcharid'] ?? 0);
    $title_id = filter_var($_POST['titlenameid'] ?? null, FILTER_VALIDATE_INT);

    if ($accid < 1 || $charid < 1 || $title_id === false || $title_id < 1) {
        http_response_code(400); echo 'Invalid character title update.<br /><br />'; return;
    }

    $ownchar = $con->prepare('SELECT charid FROM gwchars WHERE charid = ? AND accid = ? AND userid = ? LIMIT 1');
    $ownchar->bind_param('iii', $charid, $accid, $userid); $ownchar->execute();
    $owned_character = $ownchar->get_result()->fetch_assoc(); $ownchar->close();
    if (!$owned_character) { http_response_code(400); echo 'The selected Guild Wars character is invalid.<br /><br />'; return; }

    $gtn = $con->prepare('SELECT titlename, point_scale FROM gwtitles WHERE titlenameid = ? AND titletype = 1 AND autofilled = 0 LIMIT 1');
    $gtn->bind_param('i', $title_id); $gtn->execute(); $title_row = $gtn->get_result()->fetch_assoc(); $gtn->close();
    if (!$title_row) { http_response_code(400); echo 'Invalid character title selected.<br /><br />'; return; }
    $updated_title_name = $title_row['titlename'];
    $point_scale = (int)$title_row['point_scale'] === 10 ? 10 : 1;

    $raw_points = trim((string)($_POST['titlepoints'] ?? ''));
    if ($point_scale === 10) {
        if (!preg_match('/^(?:\d{1,2}(?:\.\d)?|100(?:\.0)?)$/', $raw_points)) {
            http_response_code(400); echo 'Cartographer progress must be between 0 and 100 with at most one decimal place.<br /><br />'; return;
        }
        $title_points = (int)round((float)$raw_points * 10);
    } else {
        $title_points = filter_var($raw_points, FILTER_VALIDATE_INT);
        if ($title_points === false || $title_points < 0) { http_response_code(400); echo 'Invalid character title update.<br /><br />'; return; }
        $title_points = (int)$title_points;
    }

    $gcr = $con->prepare('SELECT stnameid, stname, strank FROM gwsubtitles WHERE titlenameid = ? AND stpoints <= ? ORDER BY stpoints DESC, strank DESC LIMIT 1');
    $gcr->bind_param('ii', $title_id, $title_points); $gcr->execute(); $rank_row = $gcr->get_result()->fetch_assoc(); $gcr->close();
    $stnameid = $rank_row ? (int)$rank_row['stnameid'] : null; $stname = $rank_row ? $rank_row['stname'] : null; $strank = $rank_row ? (int)$rank_row['strank'] : 0;

    $gpc = $con->prepare('SELECT MAX(stpoints) AS max_points FROM gwsubtitles WHERE titlenameid = ?');
    $gpc->bind_param('i', $title_id); $gpc->execute(); $max_row = $gpc->get_result()->fetch_assoc(); $gpc->close();
    $pmr = (int)($max_row['max_points'] ?? 0);
    if ($pmr < 1) { http_response_code(400); echo 'This title has no valid rank thresholds configured.<br /><br />'; return; }

    $progress = $title_points >= $pmr ? 100 : (int)floor(($title_points / $pmr) * 100);

    $upsert = $con->prepare('INSERT INTO gwstats (titlenameid, stnameid, titlepoints, currentstrankname, currentstrank, percent, charid, accid, userid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE stnameid = VALUES(stnameid), titlepoints = VALUES(titlepoints), currentstrankname = VALUES(currentstrankname), currentstrank = VALUES(currentstrank), percent = VALUES(percent)');
    $upsert->bind_param('iiisiiiii', $title_id, $stnameid, $title_points, $stname, $strank, $progress, $charid, $accid, $userid);
    if (!$upsert->execute()) { $upsert->close(); http_response_code(500); echo 'Unable to update title points right now.<br /><br />'; return; }
    $upsert->close();

    $display_points = $point_scale === 10 ? number_format($title_points / 10, 1) . '%' : number_format($title_points) . ' points';
    echo 'Title &quot;' . h($updated_title_name) . '&quot; has been updated to ' . h($display_points) . '!<br /><br />';
    include_once ('update-gwamm.php');
}
?>