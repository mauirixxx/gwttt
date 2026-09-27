<?php
if (isset($_SESSION['userid'])) {
    echo '<div class="stats-card"><div class="stats-table-wrap"><table class="stats-table"><caption>Character stats</caption>';
    echo '<thead><tr><th class="title-name">Title</th><th class="title-rank">Title Rank</th><th class="title-points">Title Points</th><th class="current-rank">Current Rank</th><th class="points-remaining">Points Remaining</th><th class="title-progress">Progress</th><th class="next-rank">Next Rank</th></tr></thead><tbody>';
    $gcs = $con->prepare("SELECT s.*, t.titlename, t.point_scale FROM gwstats s JOIN gwtitles t ON t.titlenameid=s.titlenameid WHERE s.charid = ? AND s.accid = ? AND s.userid = ? ORDER BY s.percent DESC, s.currentstrank DESC, s.percent ASC");
    $gcs->bind_param("iii", $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
    $gcs->execute(); $result = $gcs->get_result();
    while ($row = $result->fetch_assoc()) {
        $gnr = $con->prepare("SELECT stpoints, stname, strank FROM gwsubtitles WHERE titlenameid = ? AND stpoints >= ? ORDER BY stpoints ASC LIMIT 1");
        $gnr->bind_param("ii", $row['titlenameid'], $row['titlepoints']); $gnr->execute(); $gnr->bind_result($stpoints, $stname, $strank); $gnr->fetch(); $gnr->close();
        $gmr = $con->prepare("SELECT MAX(strank), MAX(stpoints) FROM gwsubtitles WHERE titlenameid = ?");
        $gmr->bind_param("i", $row['titlenameid']); $gmr->execute(); $gmr->bind_result($mra, $mpa); $gmr->fetch(); $gmr->close();
        $scale = (int)$row['point_scale'] === 10 ? 10 : 1;
        $remaining_raw = max(0, (int)$mpa - (int)$row['titlepoints']);
        $pr = $scale === 10 ? number_format($remaining_raw / 10, 1) . '%' : number_format($remaining_raw);
        if ($row['currentstrank'] === $mra) { $pr = "Highest rank achieved!"; $stname = "Highest rank achieved!"; }
        if ($row['currentstrankname'] === NULL) { $row['currentstrankname'] = "No title earned yet!"; $row['currentstrank'] = "0"; }
        $ohp = $row['percent'] >= 100 ? 100 : $row['percent'];
        $display_points = $scale === 10 ? number_format(((int)$row['titlepoints']) / 10, 1) . '%' : number_format($row['titlepoints']);
        echo '<tr><td class="title-name">' . h($row['titlename']) . '</td><td class="title-rank">' . h($row['currentstrankname']) . '</td><td class="title-points">' . h($display_points) . '</td><td class="current-rank">' . h($row['currentstrank']) . '</td>';
        echo '<td class="points-remaining">' . h($pr) . '</td><td class="title-progress"><div class="progress-meter" role="progressbar" aria-valuenow="' . (int)$ohp . '" aria-valuemin="0" aria-valuemax="100"><div class="progress-fill" style="width:' . (int)$ohp . '%;"></div><span>' . (int)$ohp . '%</span></div></td><td class="next-rank">' . h($stname) . '</td></tr>';
    }
    $gcs->close(); echo '</tbody></table></div></div>';
}
?>