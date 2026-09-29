</center>
<?php
if (isset($_SESSION['userid']) && basename($_SERVER['SCRIPT_NAME'] ?? '') === 'preferences.php') {
	$current_timeout = (int)($_SESSION['session_timeout_seconds'] ?? 1800);
	$timeout_options = array(1800 => '30 minutes (default)', 3600 => '1 hour', 7200 => '2 hours', 14400 => '4 hours', 28800 => '8 hours', 0 => 'Never automatically sign me out');
	echo '<fieldset class="options-card session-options-card" style="width:min(100%,560px);margin:28px auto 10px;padding:18px 20px 20px;border:1px solid #2d6978;border-radius:7px;background:#122936;text-align:left"><legend style="padding:0 8px;color:#69dbe1;font-size:17px">Session timeout</legend>';
	echo '<form action="includes/update-session-timeout.php" method="post">' . csrf_input() . '<label for="session-timeout" style="display:block;margin-bottom:8px;color:#dce8ee;font-weight:600">Automatically sign me out after</label><div style="display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:center"><select id="session-timeout" name="session_timeout_seconds" style="width:100%;min-height:38px;padding:6px 10px;border:1px solid #547080;border-radius:5px;background:#edf2f5;color:#17242c;font:15px Segoe UI,Tahoma,Arial,sans-serif">';
	foreach ($timeout_options as $seconds => $label) echo '<option value="' . (int)$seconds . '"' . ($current_timeout === $seconds ? ' selected' : '') . '>' . h($label) . '</option>';
	echo '</select><button type="submit" style="min-height:38px;padding:7px 14px;border:1px solid #2999a5;border-radius:4px;background:#174454;color:#fff;font-weight:600;cursor:pointer">Save</button></div>';
	echo '<p style="margin:10px 0 0;color:#b9ccd5;line-height:1.45">The default is 30 minutes of inactivity. <strong style="color:#ffe0a3">Never</strong> disables GWTTT\'s idle and 8-hour automatic session expiry; use it only on a private, trusted device.</p></form></fieldset>';
}

echo '<hr>';
if (isset($_SESSION['prefaccname']) && ($_SESSION['prefcharname'])) {
	echo '<center>| Currently selected game account: <b>' . h($_SESSION['prefaccname']) . '</b> | Current character: <b>' . h($_SESSION['prefcharname']) . '</b> |</center><br />';
}
// the footer just adds a logout button at the bottom of every page for the currently logged in user
if (isset($_SESSION['userid']) && ($_SESSION['username'])) {
	echo '<center><br /><br /><form method="post" action="logout.php"><input type="hidden" name="action" value="logout" ><input type="submit" value="Logout"></form></center>';
}
if (isset($_SESSION['userid'])) {
	$csrf_token_json = json_encode(csrf_token());
	echo '<script>
	(function () {
		const token = ' . $csrf_token_json . ';
		document.querySelectorAll("form").forEach(function (form) {
			if ((form.method || "").toLowerCase() !== "post" || form.querySelector("input[name=csrf_token]")) {
				return;
			}
			const input = document.createElement("input");
			input.type = "hidden";
			input.name = "csrf_token";
			input.value = token;
			form.appendChild(input);
		});
	})();
	</script>';
}
?>
</body>
</html>