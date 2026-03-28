<?php 
$uid = current_user_id();
$apiary_id = (int)$url[2] ?? 0;

$stmt = $mysqli->prepare("SELECT *, (user_id = ?) as is_owner FROM bi_apiaries WHERE id = ? AND user_id = ?");
$stmt->bind_param('iii', $uid, $apiary_id, $uid);
$stmt->execute();
// $isAllowed = $stmt->num_rows;
$apiary = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (is_null($apiary)) noAccess();
// pre($apiary);
if ($_GET) {
    $action = $_GET['action'] ?? '';
    if ($action === 'delete_share') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $mysqli->prepare("UPDATE bi_apiary_shares SET deleted=1 WHERE id = ? AND apiary_id = ?");
        $stmt->bind_param('ii', $id, $apiary_id);
        $stmt->execute();
        $stmt->close();
        header('Location: '. url('share_apiaries')."/".$apiary_id);
        exit;
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'share_apiary' && $apiary_id == (int)($_POST['apiary_id'] ?? 0) && filter_var(trim($_POST['invite_email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
        $email     = trim($_POST['invite_email'] ?? '');
            // Hitta användaren med denna e‑post
            $stmt2 = $mysqli->prepare("SELECT id FROM bi_users WHERE email = ?");
            $stmt2->bind_param('s', $email);
            $stmt2->execute();
            $stmt2->bind_result($share_uid);
            if ($stmt2->fetch()) {
                $stmt2->close();

                // Skapa (eller idempotent) share-rad
                $stmt3 = $mysqli->prepare("
                    INSERT INTO bi_apiary_shares (apiary_id, user_id, role)
                    VALUES (?, ?, 'write')
                    ON DUPLICATE KEY UPDATE role = VALUES(role)
                ");
                $stmt3->bind_param('ii', $apiary_id, $share_uid);
                $stmt3->execute();
                $stmt3->close();
            } else {
                $stmt2->close();
                // Användare finns inte – här kan du antingen ignorera
                // eller spara en pending-invite-tabell om du vill börja skicka mail etc.
            }
        header('Location: '. url('share_apiaries')."/".$apiary_id);
        exit;
    }
}

$sql = "SELECT s.*, DATE(s.created_at) as since, u.username, u.fullname, u.email 
        FROM bi_apiary_shares s 
        LEFT JOIN bi_users as u ON s.user_id=u.id AND u.deleted=0
        WHERE s.apiary_id = ? AND s.deleted=0";
$shares = [];
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $apiary_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $shares[$row['id']] = $row;
}
$stmt->close();

_header();
?>
        <main class="app-main">
            <?= returnBtn('apiaries',"Tillbaka till val av Bigård") ?>
            <!-- Ny bigård -->
            <details class="card">
                <summary class="card-title">Bjud in användare
                </summary>
                <form method="post" class="share-form" style="margin-top:0.75rem;">
                    <input type="hidden" name="action" value="share_apiary">
                    <input type="hidden" name="apiary_id" value="<?= $apiary['id'] ?>">
                    <label style="font-size:0.85rem;">
                        Bjud in användare (e‑post):
                        <input type="email" name="invite_email" required>
                    </label>
                    <button type="submit">Dela bigård</button>
                </form>
            </details>

    <div class="card apiary-card">
        <div class="card-header">
            <div>
                <h2 class="card-title"><?= htmlspecialchars($apiary['name']) ?></h2>
                <p class="card-subtitle"><?= htmlspecialchars($apiary['location']) ?></p>
            </div>
        </div>
        <div>
            <h3>Anslutna användare</h3>
            <?php if ($shares): ?>
                <div class="hives-list">
                    <?php foreach ($shares as $share): ?>
                        <div class="hive-item">
                            <div style="flex:1; font-weight:500;">
                               <h2 class="card-title"><?= htmlspecialchars($share['email']) ?></h2>
                               <p class="card-subtitle">Ansluten sedan: <?= htmlspecialchars($share['since']) ?></p>
                            </div>
                            <div style="display:flex; flex-wrap:wrap; gap:0.35rem;" class="apiary-actions">
                                <a href="<?=url('share_apiaries')."/{$apiary_id}";?>/?action=delete_share&id=<?= $share['id'] ?>" onclick="return confirm('Avsluta delning av bigård?')" class="action_button" style='background-color: var(--danger)'  title="Avsluta delning av bigård"> 
                                    <img src='<?= WWWPATH ?>assets/icons/trashcan.png' alt='' class='' style=''>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="card-subtitle">Inga användare anslutna ännu.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php
_footer();