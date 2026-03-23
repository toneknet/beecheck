<?php
// require 'config.php';
// require 'auth.php';
// require_login();
// Gemensam kommentar till valda kupor
if (isset($_POST['action']) && $_POST['action'] === 'multi_comment') {
    $uid = current_user_id();
    $comment = trim($_POST['comment'] ?? '');
    $log_date = $_POST['log_date'] ?? date('Y-m-d');
    $hive_ids = $_POST['hive_ids'] ?? [];

    if ($comment !== '' && !empty($hive_ids)) {
        // Filtrera till heltal
        $hive_ids = array_map('intval', $hive_ids);

        // Hämta bara de kupor som tillhör inloggad användare (säkerhet)
        if ($hive_ids) {
            $placeholders = implode(',', array_fill(0, count($hive_ids), '?'));
            $types = str_repeat('i', count($hive_ids)) . 'i';
            $params = $hive_ids;
            $params[] = $uid;

            $sql = "SELECT h.id
                    FROM bi_hives h
                    JOIN bi_apiaries a ON h.apiary_id = a.id
                    WHERE h.id IN ($placeholders) AND a.user_id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            $valid_hives = [];
            while ($row = $res->fetch_assoc()) {
                $valid_hives[] = (int)$row['id'];
            }
            $stmt->close();

            if ($valid_hives) {
                // För varje giltig kupa: skapa en loggpost med endast kommentar, datum, vem
                $stmt = $mysqli->prepare("INSERT INTO bi_hive_logs
                    (hive_id, queen, eggs, open_brood, capped_brood, swarm_cells,
                     temperament, strength,
                     box1_brood_frames, box2_brood_frames, boxes_swapped,
                     comment, log_date, created_by)
                    VALUES (?, 0, 0, 0, 0, 0,
                            2, 2,
                            0, 0, 0,
                            ?, ?, ?)");
                foreach ($valid_hives as $hid) {
                    $stmt->bind_param('issi', $hid, $comment, $log_date, $uid);
                    $stmt->execute();
                }
                $stmt->close();
            }
        }
    }

    header('Location: index.php');
    exit;
}




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
    header('Location: index.php');
    exit;
}

// Ta bort bigård
if (isset($_POST['action']) && $_POST['action'] === 'delete_apiary') {
    $id = (int)($_POST['apiary_id'] ?? 0);
    $uid = current_user_id();
    $stmt = $mysqli->prepare("DELETE FROM bi_apiaries WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $id, $uid);
    $stmt->execute();
    $stmt->close();
    header('Location: index.php');
    exit;
}

// Skapa kupa
if (isset($_POST['action']) && $_POST['action'] === 'add_hive') {
    $apiary_id = (int)($_POST['apiary_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($apiary_id && $name !== '') {
        // säkerställ att bigården tillhör användaren
        $uid = current_user_id();
        $stmt = $mysqli->prepare("SELECT id FROM bi_apiaries WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $apiary_id, $uid);
        $stmt->execute();
        if ($stmt->fetch()) {
            $stmt->close();
            $stmt2 = $mysqli->prepare("INSERT INTO bi_hives (apiary_id, name) VALUES (?, ?)");
            $stmt2->bind_param('is', $apiary_id, $name);
            $stmt2->execute();
            $stmt2->close();
        } else {
            $stmt->close();
        }
    }
    header('Location: index.php');
    exit;
}

// Ta bort kupa
if (isset($_POST['action']) && $_POST['action'] === 'delete_hive') {
    $hive_id = (int)($_POST['hive_id'] ?? 0);
    $uid = current_user_id();
    // bara ta bort om kupan ligger i en bigård som ägs av user
    $stmt = $mysqli->prepare("DELETE h FROM bi_hives h JOIN bi_apiaries a ON h.apiary_id = a.id WHERE h.id = ? AND a.user_id = ?");
    $stmt->bind_param('ii', $hive_id, $uid);
    $stmt->execute();
    $stmt->close();
    header('Location: index.php');
    exit;
}

// Hämta bigårdar + kupor
$uid = current_user_id();
$apiaries = [];
$sql = "SELECT a.id, a.name, a.location,
               h.id as hive_id, h.name as hive_name
        FROM bi_apiaries a
        LEFT JOIN bi_hives h ON a.id = h.apiary_id
        WHERE a.user_id = ?
        ORDER BY a.name, h.name";
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
            'hives' => []
        ];
    }
    if ($row['hive_id']) {
        $apiaries[$aid]['hives'][] = [
            'id' => $row['hive_id'],
            'name' => $row['hive_name']
        ];
    }
}
$stmt->close();
_header();
?>
    <main class="app-main">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Dashboard</h2>
            </div>
        </div>
        <!-- Ny bigård -->
        <!-- <details class="card">
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
      </details> -->




        <!-- <div class="card toogleDiv">
            <div class="card-header">
                <h2 class="card-title">Ny bigård</h2>
            </div>
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
        </div> -->

        <!-- Lista bigårdar -->
        <?php foreach ($apiaries as $apiary): ?>
            <!-- <div class="card apiary-card"> -->
                <!-- <div class="card-header">
                    <div>
                        <h2 class="card-title"><?= htmlspecialchars($apiary['name']) ?></h2>
                        <?php if (!empty($apiary['location'])): ?>
                            <p class="card-subtitle"><?= htmlspecialchars($apiary['location']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="apiary-actions">
                        <form method="post">
                            <input type="hidden" name="action" value="delete_apiary">
                            <input type="hidden" name="apiary_id" value="<?= $apiary['id'] ?>">
                            <button type="submit" class="btn-danger"
                                    onclick="return confirm('Ta bort bigård och alla kupor/loggar?')">
                                Ta bort
                            </button>
                        </form>
                    </div>
                </div> -->

                <!-- <div>
                    <h3 style="margin-top:0.25rem;font-size:0.95rem;">Kupor</h3>
                    <?php if ($apiary['hives']): ?>
                        <div class="hives-list">
                            <?php foreach ($apiary['hives'] as $hive): ?>
                                <div class="hive-item">
                                    <div style="flex:1; font-weight:500;">
                                        <?= htmlspecialchars($hive['name']) ?>
                                    </div>
                                    <div style="display:flex; flex-wrap:wrap; gap:0.35rem;">
                                        <a class="button" href="hive_logs/<?= $hive['id'] ?>">
                                            Loggar
                                        </a>
                                        <form method="post">
                                            <input type="hidden" name="action" value="delete_hive">
                                            <input type="hidden" name="hive_id" value="<?= $hive['id'] ?>">
                                            <button type="submit" class="btn-danger"
                                                    onclick="return confirm('Ta bort kupa och loggar?')">
                                                Ta bort
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="card-subtitle">Inga kupor ännu.</p>
                    <?php endif; ?>
                </div> -->


        <!-- <details class="hive-item">
            <summary class="card-title">Ny Kupa</summary>
            <div class="">
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="add_hive">
                    <input type="hidden" name="apiary_id" value="<?= $apiary['id'] ?>">
                    <div>
                        <label>
                            <input type="text" name="name" required>
                        </label>
                    </div>
                    <div>
                        <button type="submit">Lägg till kupa</button>
                    </div>
                </form>
            </div>
        </details> -->


                <!-- Ny kupa -->
                <!-- <div style="margin-top:0.5rem;">
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="add_hive">
                        <input type="hidden" name="apiary_id" value="<?= $apiary['id'] ?>">
                        <div>
                            <label>Ny kupa
                                <input type="text" name="name" required>
                            </label>
                        </div>
                        <div>
                            <button type="submit">Lägg till kupa</button>
                        </div>
                    </form>
                </div> -->


    <?php if ($apiary['hives']): ?>
        <!-- <div class="toogleDiv">
        <form method="post" style="margin-top:0.5rem; border-top:1px solid #ddd; padding-top:0.5rem;">
            <input type="hidden" name="action" value="multi_comment">
            <p><strong>Gemensam kommentar för valda kupor i denna bigård</strong></p>
            <p>
                <label style="display:inline-block; margin-right:0.5rem; font-weight:600;">
                    <input type="checkbox"
                        class="select-all-hives"
                        onclick="toggleAllHives(this)">
                    Välj alla kupor
                </label>
            </p>
            <p>
                <?php foreach ($apiary['hives'] as $hive): ?>
                    <label style="display:inline-block; margin-right:0.5rem;">
                        <input type="checkbox" name="hive_ids[]" value="<?= $hive['id'] ?>" class="hive-checkbox">
                        <?= htmlspecialchars($hive['name']) ?>
                    </label>
                <?php endforeach; ?>
            </p>
            <p>
                <label>Kommentar<br>
                    <textarea name="comment" rows="2" cols="60" required></textarea>
                </label>
            </p>
            <p>
                <label>Datum
                    <input type="date" name="log_date" value="<?= date('Y-m-d') ?>">
                </label>
                <span>Vem: <?= htmlspecialchars(current_user_name()) ?></span>
            </p>
            <button type="submit">Spara gemensam kommentar</button>
        </form>
        </div> -->
    <?php endif; ?>


                <!-- Gemensam kommentar (din befintliga multi_comment-form) kan ligga här inne och använder samma stil -->
                <!-- Behåll din PHP-logik, byt bara markup till card-stilen -->
            <!-- </div> -->
        <?php endforeach; ?>
    </main>

<?php _footer();