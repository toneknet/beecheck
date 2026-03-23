<?php
// require 'config.php'; // $mysqli
// session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $password2  = $_POST['password2'] ?? '';
    $fullname   = trim($_POST['fullname'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    // Enkel validering
    if ($username === '' || $password === '' || $password2 === '' || $fullname === '' || $email === '') {
        $error = 'Fyll i alla fält.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ogiltig e-postadress.';
    } elseif ($password !== $password2) {
        $error = 'Lösenorden matchar inte.';
    } elseif (strlen($password) < 6) {
        $error = 'Lösenordet måste vara minst 6 tecken.';
    } else {
        // Kolla om användarnamn redan finns
        $stmt = $mysqli->prepare("SELECT id FROM bi_users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Användarnamnet är upptaget.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Lägg till email-kolumn i users om du inte redan gjort det:
            // ALTER TABLE users ADD COLUMN email VARCHAR(150) NOT NULL AFTER fullname;
            $stmt2 = $mysqli->prepare(
                "INSERT INTO bi_users (username, password_hash, fullname, email) VALUES (?, ?, ?, ?)"
            );
            $stmt2->bind_param('ssss', $username, $password_hash, $fullname, $email);
            if ($stmt2->execute()) {
                $success = 'Registrering lyckades. Du kan nu logga in.';
            } else {
                $error = 'Tekniskt fel vid registrering.';
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
_header();
?>
<!-- <!doctype html>
<html lang="sv">
<head>
<meta charset="utf-8">
<title>Registrera ny användare</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { margin:0; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background:#f4f5fb; }
.app-shell { min-height:100vh; display:flex; flex-direction:column; }
.app-header { background:linear-gradient(120deg,#ffeb3b,#f39c12); color:#3e2723; padding:0.75rem 1rem; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
.app-header-inner { max-width:960px; margin:0 auto; }
.app-title { margin:0; font-size:1.3rem; font-weight:700; }
.app-main { flex:1; max-width:960px; margin:1rem auto; padding:0 0.75rem 1.5rem; }
.card { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06); padding:1rem; max-width:420px; margin:1.5rem auto; }
.card-header { margin-bottom:0.5rem; }
.card-title { margin:0; font-size:1.1rem; font-weight:600; }
.card-subtitle { margin:0.15rem 0 0; font-size:0.9rem; color:#777; }
label { display:block; font-size:0.9rem; margin-top:0.6rem; }
input[type="text"], input[type="password"], input[type="email"] {
  width:100%; padding:0.45rem 0.55rem; border-radius:6px; border:1px solid #e0e0e0; font-size:0.9rem;
}
button {
  display:inline-flex; align-items:center; justify-content:center;
  border:none; border-radius:999px; padding:0.45rem 0.9rem;
  font-size:0.9rem; font-weight:500; cursor:pointer;
  background:#ffb300; color:#3e2723;
}
.error { color:#b71c1c; font-size:0.9rem; margin-top:0.5rem; }
.success { color:#1b5e20; font-size:0.9rem; margin-top:0.5rem; }
a { color:#f39c12; text-decoration:none; font-size:0.9rem; }
</style>
</head>
<body>
<div class="app-shell"> -->
    <!-- <header class="app-header">
        <div class="app-header-inner">
            <h1 class="app-title">Registrera ny användare</h1>
        </div>
    </header> -->
    <main class="app-main">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Skapa konto</h2>
                <p class="card-subtitle">Fyll i uppgifterna nedan</p>
            </div>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php elseif ($success): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <form method="post" autocomplete="off" action="<?= url('register') ?>">
                <label>
                    Användarnamn
                    <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </label>

                <label>
                    Lösenord
                    <input type="password" name="password" required>
                </label>

                <label>
                    Bekräfta lösenord
                    <input type="password" name="password2" required>
                </label>

                <label>
                    Fullständigt namn
                    <input type="text" name="fullname" required value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                </label>

                <label>
                    E-post
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </label>

                <div style="margin-top:1rem; display:flex; justify-content:space-between; align-items:center;">
                    <button type="submit">Registrera</button>
                    <?= returnBtn(caption:"Tillbaka till inloggning") ?>
                    <!-- <a href="<?= url() ?>">Tillbaka till inloggning</a> -->
                </div>
            </form>
        </div>
    </main>
<!-- </div>
</body>
</html> -->
<?php _footer();