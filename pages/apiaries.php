<?php

$uid = current_user_id();


// Skapa bigård
if (isset($_POST['action']) && $_POST['action'] === 'add_apiary') {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    if ($name !== '') {
        $stmt = $mysqli->prepare("INSERT INTO bi_apiaries (user_id, name, location) VALUES (?, ?, ?)");
        $uid = current_user_id();
        $stmt->bind_param('iss', $uid, $name, $location);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: /apiaries');
    exit;
}

// Ta bort bigård
// if (isset($_POST['action']) && $_POST['action'] === 'delete_apiary') {
//     $id = (int)($_POST['apiary_id'] ?? 0);

if (isset($_GET['action']) && $_GET['action'] === 'delete_apiary') {
    // print "delete";
    $id = (int)($_GET['id'] ?? 0);
    $uid = current_user_id();
    $stmt = $mysqli->prepare("UPDATE bi_apiaries SET deleted=1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $id, $uid);
    $stmt->execute();
    $stmt->close();
    header('Location: /apiaries');
    exit;
    // TODO: Denna tar INTE bort kupor och loggar tillhörande bigården
}


// Lista bigårdar
$apiaries = [];
$sql = "SELECT a.id, a.name, a.location,a.user_id, (a.user_id = ?) as is_owner,
               (SELECT COUNT(*) FROM bi_hives h WHERE h.apiary_id = a.id AND h.deleted=0) AS hive_count
        FROM bi_apiaries a
        LEFT JOIN bi_apiary_shares s ON s.apiary_id = a.id AND s.user_id = ? AND s.deleted=0
        WHERE (a.user_id = ? OR s.id IS NOT NULL) AND a.deleted=0
        ORDER BY a.name";
        // WHERE a.user_id = ? AND a.deleted=0

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('iii', $uid, $uid, $uid);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    // pre($row);
    $aid = $row['id'];
    if (!isset($apiaries[$aid])) {
        $apiaries[$aid] = [
            'id' => $aid,
            'name' => $row['name'],
            'location' => $row['location'],
            'hives' => $row['hive_count'],
            'user_id' => $row['user_id'],
            'is_owner' => $row['is_owner'],
        ];
    }
}
$stmt->close();
_header();
?>
        <main class="app-main">

            <!-- Ny bigård -->
            <details class="card">
                <summary class="card-title">Ny bigård
                </summary>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="add_apiary">
                    <div>
                        <label>Namn
                            <input type="text" name="name" required>
                        </label>
                    </div>
                    <div>
                        <label>Plats
                            <input type="text" name="location">
                        </label>
                    </div>
                    <div>
                        <button type="submit">Lägg till bigård</button>
                    </div>
                </form>
            </details>
           
            <?php foreach ($apiaries as $apiary): ?>
                <!-- Make this card clickable -->
            <div class="card apiary-card">
                <div class="card-header">
                    <div>
                        <!-- <h2 class="card-title"><?= htmlspecialchars($apiary['name']) ?> <?= ($apiary['is_owner']) ? "<div class='owner_button'>Ägare</div>": "" ?></h2> -->
                        <h2 class="card-title"><?= htmlspecialchars($apiary['name']) ?> 
                        <?php if ($apiary['is_owner']) {
                            print "<img src='". WWWPATH . "assets/icons/key.png' alt='' class='' style='width:16px; height:auto; margin-left:.5rem;'>";
                            // print "<img src='". WWWPATH . "assets/icons/link.png' alt='' class='' style='width:16px; height:auto; margin-left:.5rem;'>";
                            // print "<a href='" . url('share_apiaries/' . $apiary['id']) . "'><img src='". WWWPATH . "assets/icons/link.png' alt='' class='' style='width:16px; height:auto; margin-left:.5rem;'></a>";

                        }
                        ?>
                        </h2>


                        <?php if (!empty($apiary['location'])): ?>
                            <p class="card-subtitle"><?= htmlspecialchars($apiary['location']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="apiary-actions" style='flex-direction:row-reverse;'>
                        <a href="<?=url('hives/' . $apiary['id']);?>" style='background-color: var(--accent)' class="action_button" title="Gå till bikuporna">
                            <!-- margin: 0;font-size: 1.1rem;font-weight: 600; -->
                            <!-- Kupor:  -->
                            <?= $apiary['hives'] ?>
                        </a>
                        <!-- <div class="action_button">
                            <form method="post">
                                <input type="hidden" name="action" value="delete_apiary">
                                <input type="hidden" name="apiary_id" value="<?= $apiary['id'] ?>">
                                <button type="submit" class="btn-danger"
                                        onclick="return confirm('Ta bort bigård och alla kupor/loggar?')">
                                    Ta bort
                                </button>
                            </form>
                        </div> -->
                        <?php if ($apiary['is_owner']) { ?>
                        <a href='<?=url('share_apiaries');?>/<?= $apiary['id'] ?>' class="action_button" title="Dela ut bikupan">
                            <img src='<?= WWWPATH ?>assets/icons/link.png' alt='' class='' style=''>
                        </a>
                        <a href="<?=url('apiaries');?>/?action=delete_apiary&id=<?= $apiary['id'] ?>" onclick="return confirm('Ta bort bigård och alla kupor/loggar?')" class="action_button" style='background-color: var(--danger)'  title="Radera bikupan"> 
                            <img src='<?= WWWPATH ?>assets/icons/trashcan.png' alt='' class='' style=''>
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </main>
<?php
_footer();