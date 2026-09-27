<?php
function gwttt_title_import_value(array $exported): array
{
    $points = filter_var($exported['currentPoints'] ?? null, FILTER_VALIDATE_INT);
    if ($points === false || $points < 0) throw new RuntimeException('invalid export value');
    $percentage_based = !empty($exported['percentageBased']);
    // GWCA percentage title tracks expose tenths of one percent as currentPoints:
    // e.g. 219 == 21.9%. GWTTT stores the same integer tenths value so no
    // precision is lost; only presentation converts it to a percentage.
    return ['points'=>(int)$points,'percentage_based'=>$percentage_based];
}
function gwttt_title_import_load(mysqli $con, int $userid, array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('Choose a GWTTT-Titles.json file to import.');
    if (($file['size'] ?? 0) < 1 || $file['size'] > 1048576) throw new RuntimeException('The title export must be between 1 byte and 1 MB.');
    $raw=file_get_contents($file['tmp_name']); if($raw===false) throw new RuntimeException('Unable to read the uploaded title export.');
    $data=json_decode($raw,true,64,JSON_THROW_ON_ERROR);
    if(!is_array($data)||($data['type']??'')!=='gwttt-title-export'||(int)($data['version']??0)!==5) throw new RuntimeException('This is not a supported GWTTT title export (version 5 required).');
    $character_name=trim((string)($data['character']['name']??'')); if($character_name===''||!isset($data['titles'])||!is_array($data['titles'])) throw new RuntimeException('The export is missing its character or title data.');
    $stmt=$con->prepare('SELECT c.charid,c.accid,c.charname,a.accemail FROM gwchars c JOIN gwaccounts a ON a.accid=c.accid AND a.userid=c.userid WHERE c.userid=? AND c.charname=?');$stmt->bind_param('is',$userid,$character_name);$stmt->execute();$matches=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();
    if(count($matches)!==1) throw new RuntimeException(count($matches)?'More than one of your characters matches "'.$character_name.'". Rename the duplicate or update it manually before importing.':'Character "'.$character_name.'" is not registered in your GWTTT account. Add the character first, then import again.');
    $character=$matches[0];$accid=(int)$character['accid'];$charid=(int)$character['charid'];
    $title_rows=[];$q=$con->prepare('SELECT titlenameid,titlename,titletype,autofilled FROM gwtitles');$q->execute();$r=$q->get_result();while($row=$r->fetch_assoc())$title_rows[strtolower(trim($row['titlename']))]=$row;$q->close();
    $changes=[];$skipped=[];
    foreach($data['titles'] as $exported){
        if(!is_array($exported))continue;$name=trim((string)($exported['name']??''));
        try{$value=gwttt_title_import_value($exported);}catch(Throwable $e){$skipped[]=['name'=>$name?:'(unnamed title)','reason'=>'invalid export value'];continue;}
        $points=$value['points'];$percentage_based=$value['percentage_based'];if($name===''){$skipped[]=['name'=>'(unnamed title)','reason'=>'invalid export value'];continue;}
        $key=strtolower($name);if(!isset($title_rows[$key])){$skipped[]=['name'=>$name,'reason'=>'not tracked by GWTTT'];continue;}$title=$title_rows[$key];$type=(int)$title['titletype'];
        if($type===1&&(int)$title['autofilled']!==0){$skipped[]=['name'=>$name,'reason'=>'auto-filled by GWTTT'];continue;}if($type!==0&&$type!==1){$skipped[]=['name'=>$name,'reason'=>'unsupported title scope'];continue;}
        $title_id=(int)$title['titlenameid'];$target_charid=$type===0?0:$charid;$cur=$con->prepare('SELECT titlepoints FROM gwstats WHERE userid=? AND accid=? AND charid=? AND titlenameid=? LIMIT 1');$cur->bind_param('iiii',$userid,$accid,$target_charid,$title_id);$cur->execute();$existing=$cur->get_result()->fetch_assoc();$cur->close();$old=$existing?(int)$existing['titlepoints']:null;
        if($old===null&&$points===0){$skipped[]=['name'=>$title['titlename'],'reason'=>'zero points; no existing row to update'];continue;}if($old===$points)continue;
        $changes[]=['title_id'=>$title_id,'name'=>$title['titlename'],'type'=>$type,'old'=>$old,'new'=>$points,'percentage_based'=>$percentage_based];
    }
    return ['character'=>$character,'changes'=>$changes,'skipped'=>$skipped];
}
function gwttt_title_import_apply(mysqli $con,int $userid,array $preview): int
{
    $accid=(int)$preview['character']['accid'];$charid=(int)$preview['character']['charid'];$count=0;$own=$con->prepare('SELECT c.charid FROM gwchars c JOIN gwaccounts a ON a.accid=c.accid AND a.userid=c.userid WHERE c.userid=? AND c.charid=? AND c.accid=? LIMIT 1');$own->bind_param('iii',$userid,$charid,$accid);$own->execute();$valid=$own->get_result()->fetch_assoc();$own->close();if(!$valid)throw new RuntimeException('The import target is no longer valid.');
    $con->begin_transaction();try{
        foreach($preview['changes'] as $change){$title_id=(int)$change['title_id'];$points=(int)$change['new'];$type=(int)$change['type'];$percentage_based=!empty($change['percentage_based']);$target_charid=$type===0?0:$charid;if($points===0&&$change['old']===null)continue;
            $meta=$con->prepare('SELECT titlenameid FROM gwtitles WHERE titlenameid=? AND titletype=? AND (?=0 OR autofilled=0) LIMIT 1');$meta->bind_param('iii',$title_id,$type,$type);$meta->execute();$ok=$meta->get_result()->fetch_assoc();$meta->close();if(!$ok)throw new RuntimeException('A title definition changed while importing.');
            $rank=$con->prepare('SELECT stnameid,stname,strank FROM gwsubtitles WHERE titlenameid=? AND stpoints<=? ORDER BY stpoints DESC,strank DESC LIMIT 1');$rank->bind_param('ii',$title_id,$points);$rank->execute();$rr=$rank->get_result()->fetch_assoc();$rank->close();$max=$con->prepare('SELECT MAX(stpoints) max_points FROM gwsubtitles WHERE titlenameid=?');$max->bind_param('i',$title_id);$max->execute();$mr=$max->get_result()->fetch_assoc();$max->close();$max_points=(int)($mr['max_points']??0);if($max_points<1)throw new RuntimeException('A title has no configured rank thresholds.');
            $stnameid=$rr?(int)$rr['stnameid']:null;$stname=$rr?$rr['stname']:null;$strank=$rr?(int)$rr['strank']:0;$percent=$percentage_based?min(100,(int)floor($points/10)) : ($points>=$max_points?100:(int)floor(($points/$max_points)*100));
            $up=$con->prepare('INSERT INTO gwstats (titlenameid,stnameid,titlepoints,currentstrankname,currentstrank,percent,charid,accid,userid) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE stnameid=VALUES(stnameid),titlepoints=VALUES(titlepoints),currentstrankname=VALUES(currentstrankname),currentstrank=VALUES(currentstrank),percent=VALUES(percent)');$up->bind_param('iiisiiiii',$title_id,$stnameid,$points,$stname,$strank,$percent,$target_charid,$accid,$userid);if(!$up->execute()){$up->close();throw new RuntimeException('A title update failed.');}$up->close();$count++;}
        $con->commit();
    }catch(Throwable $e){$con->rollback();throw $e;}return $count;
}
?>