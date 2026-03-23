<?php
function user_has_apiary_access(mysqli $mysqli, int $uid, int $apiary_id): bool {
    $sql = "
        SELECT 1
        FROM bi_apiaries a
        LEFT JOIN bi_apiary_shares s 
            ON s.apiary_id = a.id AND s.user_id = ?
        WHERE a.id = ? 
          AND (a.user_id = ? OR s.id IS NOT NULL)
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iii', $uid, $apiary_id, $uid);
    $stmt->execute();
    $stmt->store_result();
    $ok = $stmt->num_rows > 0;
    $stmt->close();
    return $ok;
}