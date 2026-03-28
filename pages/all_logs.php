<?php
$uid = current_user_id();


// Läs in ev. filter/sortering från GET
$filter_apiary_id = isset($_GET['apiary_id']) ? (int)$_GET['apiary_id'] : 0;
$filter_hive_id = isset($_GET['hive_id']) ? (int)$_GET['hive_id'] : 0;
$sort = $_GET['sort'] ?? 'date_desc'; // standard: senaste först

// Hämta bigårdar för filter-dropdown
// $apiary_sql = "SELECT id, name FROM bi_apiaries WHERE user_id = ? AND deleted=0 ORDER BY name";
$apiary_sql = "SELECT a.id, a.name FROM bi_apiaries as a
LEFT JOIN bi_apiary_shares s 
    ON s.apiary_id = a.id AND s.user_id = ?
WHERE (a.user_id = ? OR s.id IS NOT NULL) AND a.deleted=0 ORDER BY a.name";

$st_api = $mysqli->prepare($apiary_sql);
// $st_api->bind_param('i', $uid);
$st_api->bind_param('ii', $uid, $uid);
$st_api->execute();
$apiary_res = $st_api->get_result();
$apiaries = $apiary_res->fetch_all(MYSQLI_ASSOC);
$st_api->close();

// Hämta bikupor för filter-dropdown (alla användarens kupor)
// $hive_sql = "
//     SELECT h.id, h.name, a.name AS apiary_name, h.apiary_id 
//     FROM bi_hives h 
//     JOIN bi_apiaries a ON h.apiary_id = a.id 
//     WHERE a.user_id = ? 
//     ORDER BY a.name, h.name
// ";
$hive_sql = "
    SELECT h.id, h.name, a.name AS apiary_name, h.apiary_id 
    FROM bi_hives h 
    JOIN bi_apiaries a ON h.apiary_id = a.id 
    LEFT JOIN bi_apiary_shares s 
    ON s.apiary_id = a.id AND s.user_id = ?
    WHERE (a.user_id = ? OR s.id IS NOT NULL) AND a.deleted=0 
    ORDER BY a.name, h.name
";
$st_hive = $mysqli->prepare($hive_sql);
// $st_hive->bind_param('i', $uid);
$st_hive->bind_param('ii', $uid, $uid);
$st_hive->execute();
$hive_res = $st_hive->get_result();
$hives = $hive_res->fetch_all(MYSQLI_ASSOC);
$st_hive->close();









// Hämta alla loggar för alla kupor i alla bigårdar som tillhör användaren
// $sql = "
// SELECT 
//     l.id,
//     l.log_date,
//     l.queen,
//     l.eggs,
//     l.open_brood,
//     l.capped_brood,
//     l.swarm_cells,
//     l.temperament,
//     l.strength,
//     l.box1_brood_frames,
//     l.box2_brood_frames,
//     l.boxes_swapped,
//     l.comment,
//     l.created_at,
//     h.name      AS hive_name,
//     a.name      AS apiary_name,
//     u.fullname  AS created_by_name
// FROM bi_hive_logs l
// JOIN bi_hives h       ON l.hive_id = h.id
// JOIN bi_apiaries a    ON h.apiary_id = a.id
// LEFT JOIN bi_users u  ON l.created_by = u.id
// WHERE a.user_id = ?
// ORDER BY l.log_date DESC, l.created_at DESC
// ";

// $stmt = $mysqli->prepare($sql);
// $stmt->bind_param('i', $uid);
// $stmt->execute();
// $result = $stmt->get_result();
// $logs = $result->fetch_all(MYSQLI_ASSOC);
// $stmt->close();


// $sql = "
// SELECT 
//     l.id,
//     l.log_date,
//     l.queen,
//     l.eggs,
//     l.open_brood,
//     l.capped_brood,
//     l.swarm_cells,
//     l.temperament,
//     l.strength,
//     l.box1_brood_frames,
//     l.box2_brood_frames,
//     l.boxes_swapped,
//     l.comment,
//     l.created_at,
//     h.name      AS hive_name,
//     a.name      AS apiary_name,
//     u.fullname  AS created_by_name
// FROM bi_hive_logs l
// JOIN bi_hives h       ON l.hive_id = h.id
// JOIN bi_apiaries a    ON h.apiary_id = a.id
// LEFT JOIN bi_users u  ON l.created_by = u.id
// WHERE a.user_id = ?
// ";

// // filtrera på bigård om vald
// $params = [$uid];
// $types  = 'i';

// if ($filter_apiary_id > 0) {
//     $sql .= " AND a.id = ? ";
//     $params[] = $filter_apiary_id;
//     $types   .= 'i';
// }

// $sql = "
// SELECT 
//     l.id, 
//     l.log_date,
//     l.queen,
//     l.eggs,
//     l.open_brood,
//     l.capped_brood,
//     l.swarm_cells,
//     l.temperament,
//     l.strength,
//     l.box1_brood_frames,
//     l.box2_brood_frames,
//     l.boxes_swapped,
//     l.comment, 
//     l.created_at,
//     h.name AS hive_name, 
//     a.name AS apiary_name, 
//     u.fullname AS created_by_name,
//     h.apiary_id AS apiary_id
// FROM bi_hive_logs l
// JOIN bi_hives h ON l.hive_id = h.id
// JOIN bi_apiaries a ON h.apiary_id = a.id
// LEFT JOIN bi_users u ON l.created_by = u.id
// WHERE a.user_id = ? AND l.deleted=0 AND h.deleted=0 AND a.deleted=0 AND u.deleted=0  
// ";

$sql = "
SELECT 
    l.id, 
    l.log_date,
    l.queen,
    l.eggs,
    l.open_brood,
    l.capped_brood,
    l.swarm_cells,
    l.temperament,
    l.strength,
    l.box1_brood_frames,
    l.box2_brood_frames,
    l.boxes_swapped,
    l.comment, 
    l.created_at,
    h.name AS hive_name, 
    a.name AS apiary_name, 
    u.fullname AS created_by_name,
    h.apiary_id AS apiary_id
FROM bi_hive_logs l
JOIN bi_hives h ON l.hive_id = h.id
JOIN bi_apiaries a ON h.apiary_id = a.id
LEFT JOIN bi_users u ON l.created_by = u.id
LEFT JOIN bi_apiary_shares s ON s.apiary_id = a.id AND s.user_id = ? AND s.deleted=0
WHERE (a.user_id = ? OR s.id IS NOT NULL) AND l.deleted=0 AND h.deleted=0 AND a.deleted=0 AND u.deleted=0  
";


/*
,
    l.deleted as ldel,
    h.deleted as hdel,
    a.deleted as adel,
    u.deleted as udel
*/

$params = [$uid,$uid];
$types = 'ii';

if ($filter_apiary_id > 0) {
    $sql .= " AND a.id = ?";
    $params[] = $filter_apiary_id;
    $types .= 'i';
}

if ($filter_hive_id > 0) {
    $sql .= " AND h.id = ?";
    $params[] = $filter_hive_id;
    $types .= 'i';
}

// sorteringsval
switch ($sort) {
    case 'date_asc':
        $sql .= " ORDER BY l.log_date ASC, l.created_at ASC";
        break;
    case 'apiary_asc':
        $sql .= " ORDER BY a.name ASC, h.name ASC, l.log_date DESC";
        break;
    case 'apiary_desc':
        $sql .= " ORDER BY a.name DESC, h.name ASC, l.log_date DESC";
        break;
    case 'hive_asc':
        $sql .= " ORDER BY h.name ASC, l.logdate DESC";
        break;
    default: // date_desc
        $sql .= " ORDER BY l.log_date DESC, l.created_at DESC";
        break;
}

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// pre($logs);






// hjälp-funktioner (kan kopieras från bi_hive_logs.php)
function temper_icon($t) {
    if ($t == 1) return '😞';
    if ($t == 2) return '😐';
    if ($t == 3) return '😊';
    return '-';
}
function strength_icon($s) {
    if ($s == 1) return '😞';
    if ($s == 2) return '😐';
    if ($s == 3) return '😊';
    return '-';
}
_header();
?>
    <main class="app-main">
        <?= returnBtn('apiaries',"Tillbaka till val av Bigård") ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Loggar</h2>
    </div>

    <!-- Filter & sortering -->
    <!-- <form method="get" class="form-grid" style="margin-bottom:0.75rem;" action="<?= url('all_logs/') ?>">
        <div>
            <label>Bigård
                <select name="apiary_id">
                    <option value="0">Alla bigårdar</option>
                    <?php foreach ($apiaries as $apiary): ?>
                        <option value="<?= $apiary['id'] ?>"
                            <?= $filter_apiary_id == $apiary['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($apiary['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div>
            <label>Sortering
                <select name="sort">
                    <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>
                        Datum (nyast först)
                    </option>
                    <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>
                        Datum (äldst först)
                    </option>
                    <option value="apiary_asc" <?= $sort === 'apiary_asc' ? 'selected' : '' ?>>
                        Bigård (A–Ö)
                    </option>
                    <option value="apiary_desc" <?= $sort === 'apiary_desc' ? 'selected' : '' ?>>
                        Bigård (Ö–A)
                    </option>
                </select>
            </label>
        </div>
        <div style="align-self:end;">
            <button type="submit">Filtrera</button>
        </div>
    </form> -->

    <form method="get" class="form-grid" style="margin-bottom:0.75rem; grid-template-columns: 1fr 1fr 1fr;" action="<?= url('all_logs') ?>/">
    <div>
        <label>Bigård
            <select name="apiary_id">
                <option value="0">Alla bigårdar</option>
                <?php foreach ($apiaries as $apiary): ?>
                    <option value="<?= $apiary['id'] ?>" 
                        <?= $filter_apiary_id == $apiary['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($apiary['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <div>
        <label>Bikupa
            <select name="hive_id">
                <option value="0">Alla kupor</option>
                <?php foreach ($hives as $hive): ?>
                    <option value="<?= $hive['id'] ?>" 
                        <?= $filter_hive_id == $hive['id'] ? 'selected' : '' ?>
                        <?= $filter_apiary_id == $hive['apiary_id'] || $filter_apiary_id == 0 ? '' : 'disabled' ?>>
                        <?= htmlspecialchars($hive['apiary_name'] . ' - ' . $hive['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <div style="align-self:end; display:flex; gap:0.5rem;">
        <label style="font-size:0.85rem;">
            <select name="sort">
                <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Nyast först</option>
                <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Äldst först</option>
                <option value="hive_asc" <?= $sort === 'hive_asc' ? 'selected' : '' ?>>Kupa A-Ö</option>
            </select>
        </label>
        <button type="submit">Uppdatera</button>
    </div>
</form>



        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Loggar</h2>
            </div>

            <?php if (!$logs): ?>
                <p class="card-subtitle">Inga loggar ännu.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <tr>
                            <th>Datum</th>
                            <th>Bigård</th>
                            <th>Kupa</th>
                            <th>Obs</th>
                            <th>Temp.</th>
                            <th>Styrka</th>
                            <th>Låda 1</th>
                            <th>Låda 2</th>
                            <th>Lådbyte</th>
                            <th>Kommentar</th>
                            <th>Av</th>
                        </tr>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['log_date']) ?></td>
                                <td><?= htmlspecialchars($log['apiary_name']) ?></td>
                                <td><?= htmlspecialchars($log['hive_name']) ?></td>
                                <td>
                                    <?= $log['queen'] ? 'Drottning ' : '' ?>
                                    <?= $log['eggs'] ? 'Ägg ' : '' ?>
                                    <?= $log['open_brood'] ? 'Öppet ' : '' ?>
                                    <?= $log['capped_brood'] ? 'Täckt ' : '' ?>
                                    <?= $log['swarm_cells'] ? 'Svärmceller ' : '' ?>
                                    <!-- <br>[<?= htmlspecialchars($log['ldel']) ?>
                                    <?= htmlspecialchars($log['hdel']) ?>
                                    <?= htmlspecialchars($log['adel']) ?>
                                    <?= htmlspecialchars($log['udel']) ?>] -->
                                </td>
                                <td><?= temper_icon($log['temperament']) ?></td>
                                <td><?= strength_icon($log['strength']) ?></td>
                                <td><?= (int)$log['box1_brood_frames'] ?></td>
                                <td><?= (int)$log['box2_brood_frames'] ?></td>
                                <td><?= $log['boxes_swapped'] ? 'Ja' : 'Nej' ?></td>
                                <td><?= nl2br(htmlspecialchars($log['comment'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($log['created_by_name'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
<?php _footer();