<?php
// require 'config.php';
// require 'auth.php';
// require_login();

$uid = current_user_id();

// $hive_id = (int)($_GET['hive_id'] ?? 0);
$hive_id = (int)($url[2] ?? 0);

// Kontrollera att kupan tillhör inloggad användare
$sql = "SELECT h.id, h.name, a.name AS apiary_name, apiary_id 
        FROM bi_hives h
        JOIN bi_apiaries a ON h.apiary_id = a.id
        WHERE h.id = ? AND a.user_id = ? AND h.deleted=0 AND a.deleted=0";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $hive_id, $uid);
$stmt->execute();
$hive = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$hive) {
    die("Ingen behörighet eller kupa finns inte.");
}
$apiary_id = (int)($hive['apiary_id'] ?? 0);
// pre($apiary_id);
// $apiary_id = 0;
if (!$apiary_id) {
    die("Ingen behörighet eller bigård finns inte.");
}


// Hantera nytt logg‑inlägg
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $queen = isset($_POST['queen']) ? 1 : 0;
    $eggs = isset($_POST['eggs']) ? 1 : 0;
    $open_brood = isset($_POST['open_brood']) ? 1 : 0;
    $capped_brood = isset($_POST['capped_brood']) ? 1 : 0;
    $swarm_cells = isset($_POST['swarm_cells']) ? 1 : 0;

    $temperament = (int)($_POST['temperament'] ?? 2);
    $strength = (int)($_POST['strength'] ?? 2);

    $box1 = (int)($_POST['box1_brood_frames'] ?? 0);
    $box2 = (int)($_POST['box2_brood_frames'] ?? 0);

    $boxes_swapped = isset($_POST['boxes_swapped']) ? 1 : 0;

    $log_date = $_POST['log_date'] ?? date('Y-m-d');

    $created_by = $uid;
    $comment = trim($_POST['comment'] ?? '');

    $stmt = $mysqli->prepare("INSERT INTO bi_hive_logs
        (hive_id, queen, eggs, open_brood, capped_brood, swarm_cells,
        temperament, strength,
        box1_brood_frames, box2_brood_frames, boxes_swapped,
        comment,
        log_date, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        'iiiiiiiiiiissi',
        $hive_id, $queen, $eggs, $open_brood, $capped_brood, $swarm_cells,
        $temperament, $strength,
        $box1, $box2, $boxes_swapped,
        $comment,
        $log_date, $created_by
    );

    $stmt->execute();
    $stmt->close();

    header("Location: /hive_logs/" . $hive_id);
    exit;
}

// Hämta loggar för kupan
$sql = "SELECT l.*, u.fullname
        FROM bi_hive_logs l
        LEFT JOIN bi_users u ON l.created_by = u.id
        WHERE l.hive_id = ? AND l.deleted=0 AND u.deleted=0
        ORDER BY l.log_date DESC, l.created_at DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $hive_id);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Hjälpfunktioner för smileys
function temper_icon($t) {
    if ($t == 1) return '😞';
    if ($t == 3) return '😊';
    return '😐';
}
function strength_icon($s) {
    if ($s == 1) return '😞'; // valfri symbol
    if ($s == 3) return '😊';
    return '😐';
}
_header();
?>
    <main class="app-main">
        <?= returnBtn('hives/'.$hive['id'],"Tillbaka till Bigården") ?>

            <details class="card">
                <summary class="card-title">Ny logg / inspektion
                </summary>
                <!-- Ny logg -->
                <div style="margin-top:0.5rem;">
                    <form method="post" >
                        <fieldset>
                            <legend>Observationer</legend>
                            <label><input type="checkbox" name="queen"> Drottning</label>
                            <label><input type="checkbox" name="eggs"> Ägg</label>
                            <label><input type="checkbox" name="open_brood"> Öppet yngel</label>
                            <label><input type="checkbox" name="capped_brood"> Täckt yngel</label>
                            <label><input type="checkbox" name="swarm_cells"> Svärmceller</label>
                        </fieldset>

                        <fieldset>
                            <legend>Temperament</legend>
                            <div class="smiley-group">
                                <label><input type="radio" name="temperament" value="1"> 😞 (stingslig/arg)</label>
                                <label><input type="radio" name="temperament" value="2" checked> 😐 (neutral)</label>
                                <label><input type="radio" name="temperament" value="3"> 😊 (snäll)</label>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Styrka</legend>
                            <div class="smiley-group">
                                <label><input type="radio" name="strength" value="1"> 😞 (svag)</label>
                                <label><input type="radio" name="strength" value="2" checked> 😐 (medel)</label>
                                <label><input type="radio" name="strength" value="3"> 😊 (stark)</label>
                            </div>
                        </fieldset>

                        <fieldset class="form-grid">
                            <div>
                                <legend>Yngelramar</legend>
                                <label>Låda 1 (övre)
                                    <input type="number" name="box1_brood_frames" min="0" max="20" value="0">
                                </label>
                            </div>
                            <div style="margin-top:1.3rem;">
                                <label>Låda 2 (undre)
                                    <input type="number" name="box2_brood_frames" min="0" max="20" value="0">
                                </label>
                            </div>
                            <div style="grid-column:1/-1;">
                                <label><input type="checkbox" name="boxes_swapped"> Bytt plats på lådorna</label>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Kommentar</legend>
                            <label>
                                <textarea name="comment" rows="3"></textarea>
                            </label>
                        </fieldset>

                        <fieldset class="form-grid">
                            <div>
                                <legend>Datum</legend>
                                <label>
                                    <input type="date" name="log_date" value="<?= date('Y-m-d') ?>">
                                </label>
                            </div>
                            <div>
                                <legend>Vem</legend>
                                <p><?= htmlspecialchars(current_user_name()) ?></p>
                            </div>
                        </fieldset>

                        <button type="submit">Spara logg</button>
                    </form>
                </div>
            </details>


<!-- 
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Ny logg / inspektion</h2>
            </div>
            <form method="post" >
                <fieldset>
                    <legend>Observationer</legend>
                    <label><input type="checkbox" name="queen"> Drottning</label>
                    <label><input type="checkbox" name="eggs"> Ägg</label>
                    <label><input type="checkbox" name="open_brood"> Öppet yngel</label>
                    <label><input type="checkbox" name="capped_brood"> Täckt yngel</label>
                    <label><input type="checkbox" name="swarm_cells"> Svärmceller</label>
                </fieldset>

                <fieldset>
                    <legend>Temperament</legend>
                    <div class="smiley-group">
                        <label><input type="radio" name="temperament" value="1"> 😞 (stingslig/arg)</label>
                        <label><input type="radio" name="temperament" value="2" checked> 😐 (neutral)</label>
                        <label><input type="radio" name="temperament" value="3"> 😊 (snäll)</label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Styrka</legend>
                    <div class="smiley-group">
                        <label><input type="radio" name="strength" value="1"> 😞 (svag)</label>
                        <label><input type="radio" name="strength" value="2" checked> 😐 (medel)</label>
                        <label><input type="radio" name="strength" value="3"> 😊 (stark)</label>
                    </div>
                </fieldset>

                <fieldset class="form-grid">
                    <div>
                        <legend>Yngelramar</legend>
                        <label>Låda 1 (övre)
                            <input type="number" name="box1_brood_frames" min="0" max="20" value="0">
                        </label>
                    </div>
                    <div style="margin-top:1.3rem;">
                        <label>Låda 2 (undre)
                            <input type="number" name="box2_brood_frames" min="0" max="20" value="0">
                        </label>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label><input type="checkbox" name="boxes_swapped"> Bytt plats på lådorna</label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Kommentar</legend>
                    <label>
                        <textarea name="comment" rows="3"></textarea>
                    </label>
                </fieldset>

                <fieldset class="form-grid">
                    <div>
                        <legend>Datum</legend>
                        <label>
                            <input type="date" name="log_date" value="<?= date('Y-m-d') ?>">
                        </label>
                    </div>
                    <div>
                        <legend>Vem</legend>
                        <p><?= htmlspecialchars(current_user_name()) ?></p>
                    </div>
                </fieldset>

                <button type="submit">Spara logg</button>
            </form>
        </div> -->

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Tidigare loggar</h2>
            </div>
            <?php if (!$logs): ?>
                <p class="card-subtitle">Inga loggar ännu.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <tr>
                            <th>Datum</th>
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
                                <td>
                                    <?= $log['queen'] ? 'Drottning ' : '' ?>
                                    <?= $log['eggs'] ? 'Ägg ' : '' ?>
                                    <?= $log['open_brood'] ? 'Öppet ' : '' ?>
                                    <?= $log['capped_brood'] ? 'Täckt ' : '' ?>
                                    <?= $log['swarm_cells'] ? 'Svärmceller ' : '' ?>
                                </td>
                                <td><?= temper_icon($log['temperament']) ?></td>
                                <td><?= strength_icon($log['strength']) ?></td>
                                <td><?= (int)$log['box1_brood_frames'] ?></td>
                                <td><?= (int)$log['box2_brood_frames'] ?></td>
                                <td><?= $log['boxes_swapped'] ? 'Ja' : 'Nej' ?></td>
                                <td><?= nl2br(htmlspecialchars($log['comment'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($log['fullname'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
<?php _footer();
