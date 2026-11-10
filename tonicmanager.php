<?php
$pagetitle = 'Tonic Editor';
include_once('header.php');
if (!isset($_SESSION['userid']) || (int)($_SESSION['admin'] ?? 0) !== 1) {
    http_response_code(403);
    echo '<center>Access denied.</center>';
    include_once('footer.php');
    exit;
}
$rarities = ['White', 'Purple', 'Gold', 'Green'];
$message = '';
$error = '';
$selectedId = filter_input(INPUT_GET, 'tonicid', FILTER_VALIDATE_INT) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $name = trim((string)($_POST['tonicname'] ?? ''));
    $rarity = (string)($_POST['rarity'] ?? '');
    $wiki = trim((string)($_POST['wiki_url'] ?? ''));
    $wikiParts = $wiki !== '' ? parse_url($wiki) : null;
    $rawOrder = (string)($_POST['display_order'] ?? '0');
    $id = filter_var($_POST['tonicid'] ?? null, FILTER_VALIDATE_INT);
    if (!in_array($action, ['create', 'update'], true)) {
        $error = 'Unknown action.';
    } elseif ($name === '' || mb_strlen($name) > 120) {
        $error = 'Tonic name must be between 1 and 120 characters.';
    } elseif (!in_array($rarity, $rarities, true)) {
        $error = 'Select a valid rarity.';
    } elseif ($wiki !== '' && (!filter_var($wiki, FILTER_VALIDATE_URL) || !is_array($wikiParts) || ($wikiParts['scheme'] ?? '') !== 'https' || strtolower((string)($wikiParts['host'] ?? '')) !== 'wiki.guildwars.com' || isset($wikiParts['user']) || isset($wikiParts['pass']))) {
        $error = 'Wiki URL must be an https://wiki.guildwars.com/ address.';
    } elseif (!ctype_digit($rawOrder) || (int)$rawOrder > 65535) {
        $error = 'Display order must be a whole number between 0 and 65535.';
    } elseif ($action === 'update' && (!$id || $id <= 0)) {
        $error = 'Select an existing tonic.';
    }
    if ($error === '') {
        $order = (int)$rawOrder;
        try {
            if ($action === 'create') {
                $stmt = $con->prepare('INSERT INTO gwtonics (tonicname, rarity, display_order, wiki_url) VALUES (?, ?, ?, NULLIF(?, ''))');
                $stmt->bind_param('ssis', $name, $rarity, $order, $wiki);
            } else {
                $stmt = $con->prepare('UPDATE gwtonics SET tonicname = ?, rarity = ?, display_order = ?, wiki_url = NULLIF(?, '') WHERE tonicid = ?');
                $stmt->bind_param('ssisi', $name, $rarity, $order, $wiki, $id);
            }
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $selectedId = $action === 'create' ? (int)$con->insert_id : (int)$id;
            $stmt->close();
            if ($action === 'update' && $affected === 0) {
                $check = $con->prepare('SELECT 1 FROM gwtonics WHERE tonicid = ?');
                $check->bind_param('i', $selectedId);
                $check->execute();
                $exists = (bool)$check->get_result()->fetch_row();
                $check->close();
                if (!$exists) {
                    $error = 'Tonic no longer exists.';
                }
            }
            if ($error === '') $message = $action === 'create' ? 'Tonic created.' : 'Tonic saved.';
        } catch (mysqli_sql_exception $e) {
            $error = (int)$e->getCode() === 1062 ? 'A tonic with that name already exists.' : 'Unable to save tonic. Check the server error log.';
            error_log('GWTTT tonic editor: ' . $e->getMessage());
        }
    }
}
$tonics = [];
$result = $con->query("SELECT tonicid, tonicname, rarity, display_order, wiki_url FROM gwtonics ORDER BY FIELD(rarity,'White','Purple','Gold','Green'), display_order, tonicname");
while ($row = $result->fetch_assoc()) $tonics[] = $row;
$selected = null;
foreach ($tonics as $row) {
    if ((int)$row['tonicid'] === $selectedId) { $selected = $row; break; }
}
?>
<style>
.te{max-width:1000px;margin:auto;padding:12px 18px 40px}.te h1,.te-lead{text-align:center}.te-lead,.te-note{color:#a9c5d1}.te-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.te-card{padding:22px;background:#122936;border:1px solid #2d6978;border-radius:8px}.te-card h2{color:#69dbe1}.te-field{margin:0 0 15px}.te-field label{display:block;margin-bottom:6px;font-weight:700}.te-field input,.te-field select{box-sizing:border-box;width:100%;padding:10px}.te-button{display:inline-block;background:#174454;border:1px solid #2999a5;color:white;padding:10px 14px;border-radius:5px;font-weight:700;text-decoration:none;cursor:pointer}.te-msg{padding:12px;margin:12px auto;max-width:750px;border-radius:6px}.te-ok{background:#194d31}.te-error{background:#3b2020}.te-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}@media(max-width:760px){.te-grid{grid-template-columns:1fr}}
</style>
<section class="te">
<h1>Everlasting tonic editor</h1>
<p class="te-lead">Maintain the sixth-year tonic catalog. New tonics appear automatically in the Year 6 collection tracker.</p>
<?php if ($message): ?><div class="te-msg te-ok" role="status"><?=h($message)?></div><?php endif; ?>
<?php if ($error): ?><div class="te-msg te-error" role="alert"><?=h($error)?></div><?php endif; ?>
<div class="te-grid">
<div class="te-card">
<h2>Create tonic</h2>
<form method="post">
<?=csrf_input()?>
<input type="hidden" name="action" value="create">
<div class="te-field"><label for="new-name">Tonic name</label><input id="new-name" name="tonicname" maxlength="120" required placeholder="Everlasting ... Tonic"></div>
<div class="te-field"><label for="new-rarity">Rarity</label><select id="new-rarity" name="rarity"><?php foreach ($rarities as $rarity): ?><option><?=h($rarity)?></option><?php endforeach; ?></select></div>
<div class="te-field"><label for="new-wiki">Guild Wars Wiki URL (optional)</label><input id="new-wiki" type="url" name="wiki_url" placeholder="https://wiki.guildwars.com/wiki/..."></div>
<div class="te-field"><label for="new-order">Display order</label><input id="new-order" type="number" name="display_order" min="0" max="65535" value="0" required></div>
<button class="te-button" type="submit">Create tonic</button>
</form>
</div>
<div class="te-card">
<h2>Edit tonic</h2>
<form method="get">
<div class="te-field"><label for="select-tonic">Existing tonic (<?=count($tonics)?>)</label><select id="select-tonic" name="tonicid" required><option value="">Choose a tonic…</option><?php foreach ($tonics as $tonic): ?><option value="<?=(int)$tonic['tonicid']?>"<?=($selectedId === (int)$tonic['tonicid'] ? ' selected' : '')?>><?=h($tonic['tonicname'])?></option><?php endforeach; ?></select></div>
<button class="te-button" type="submit">Edit tonic</button>
</form>
<?php if ($selected): ?>
<form method="post" style="margin-top:20px">
<?=csrf_input()?>
<input type="hidden" name="action" value="update">
<input type="hidden" name="tonicid" value="<?=(int)$selected['tonicid']?>">
<div class="te-field"><label for="edit-name">Tonic name</label><input id="edit-name" name="tonicname" maxlength="120" required value="<?=h($selected['tonicname'])?>"></div>
<div class="te-field"><label for="edit-rarity">Rarity</label><select id="edit-rarity" name="rarity"><?php foreach ($rarities as $rarity): ?><option<?=($selected['rarity'] === $rarity ? ' selected' : '')?>><?=h($rarity)?></option><?php endforeach; ?></select></div>
<div class="te-field"><label for="edit-wiki">Guild Wars Wiki URL (optional)</label><input id="edit-wiki" type="url" name="wiki_url" value="<?=h((string)($selected['wiki_url'] ?? ''))?>"></div>
<div class="te-field"><label for="edit-order">Display order</label><input id="edit-order" type="number" name="display_order" min="0" max="65535" required value="<?=(int)$selected['display_order']?>"></div>
<button class="te-button" type="submit">Save changes</button>
</form>
<?php endif; ?>
</div>
</div>
<p class="te-note">Editing a tonic preserves everyone's inventory because its tonic ID stays unchanged. Deletion is intentionally unavailable to prevent accidental loss of inventory records.</p>
<div class="te-actions"><a class="te-button" href="adminlanding.php">Back to Administration</a><a class="te-button" href="miniatures.php">Open Collection Tracker</a></div>
</section>
<?php include_once('footer.php'); ?>
