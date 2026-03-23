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
if (isset($_POST['action']) && $_POST['action'] === 'delete_apiary') {
    $id = (int)($_POST['apiary_id'] ?? 0);
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
$sql = "SELECT a.id, a.name, a.location,
               (SELECT COUNT(*) FROM bi_hives h WHERE h.apiary_id = a.id AND h.deleted=0) AS hive_count
        FROM bi_apiaries a
        WHERE a.user_id = ? AND a.deleted=0
        ORDER BY a.name";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $uid);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $aid = $row['id'];
    if (!isset($apiaries[$aid])) {
        $apiaries[$aid] = [
            'id' => $aid,
            'name' => $row['name'],
            'location' => $row['location'],
            'hives' => $row['hive_count'],
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
            <a class="card apiary-card" href="<?=url('hives/' . $apiary['id']);?>">
                <div class="card-header">
                    <div>
                        <h2 class="card-title"><?= htmlspecialchars($apiary['name']) ?></h2>
                        <?php if (!empty($apiary['location'])): ?>
                            <p class="card-subtitle"><?= htmlspecialchars($apiary['location']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="apiary-actions">
                        <span style='margin: 0;font-size: 1.1rem;font-weight: 600;'>
                            Kupor: <?= $apiary['hives'] ?>
                        </span>
                        <div>
                            <form method="post">
                            <input type="hidden" name="action" value="delete_apiary">
                            <input type="hidden" name="apiary_id" value="<?= $apiary['id'] ?>">
                            <button type="submit" class="btn-danger"
                                    onclick="return confirm('Ta bort bigård och alla kupor/loggar?')">
                                Ta bort
                            </button>
                        </form>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </main>
<?php
_footer();