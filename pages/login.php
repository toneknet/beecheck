<?php
// require 'config.php';
// require 'auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    // var_dump($username);
    // var_dump($password);

    if ($username === '' || $password === '') {
        $error = 'Fyll i både användarnamn och lösenord.';
    } else {
        // $result = $mysqli->query("SELECT id, username, password_hash, fullname FROM bi_users WHERE username='{$username}'");
        // while($row = mysqli_fetch_array($result))
        // {
        //     $hash = $row['password_hash'];
        //     // print_r($row);
        // } 


        $stmt = $mysqli->prepare("SELECT id, username, password_hash, fullname FROM bi_users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->bind_result($id, $uname, $hash, $fullname);

        /* bind variables to prepared statement */
        // $stmt->bind_result($col1, $col2);

        /* fetch values */
        // while ($stmt->fetch()) {
        //     printf("%s %s %s %s\n", $id, $uname, $hash, $fullname);
        // }

        // die();
        if ($stmt->fetch()) {

            // var_dump($id);
            // var_dump($uname);
            // var_dump(password_verify($password, $hash));
            // var_dump(hash_equals($hash, $password));

            // echo password_hash("Javisst76", PASSWORD_DEFAULT);
            // Om du använde PASSWORD() i SQL, byt till: if (hash_equals($hash, $password)) { ... }
            /**  @disregard P1006 Undefined type */
            if (password_verify($password, $hash)) {
            // if (hash_equals($hash, $password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $uname;
                $_SESSION['fullname'] = $fullname;
                header('Location: index.php');
                exit;
            } else {
                $error = 'Felaktigt användarnamn eller lösenord.';
            }
        } else {
            $error = 'Felaktigt användarnamn eller lösenord.';
        }
        $stmt->close();
    }
}
_header();
?>

<div class="app-shell">
    <?php _menu(); ?>
    <!-- <header class="app-header">
        <div class="app-header-inner">
            <h1 class="app-title">Biodlarens logg</h1>
        </div>
    </header> -->

    <main class="app-main">
        <div class="card" style="max-width: 420px; margin: 2rem auto;">
            <div class="card-header">
                <h2 class="card-title">Logga in</h2>
            </div>
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="post" action="<?= url() ?>">
                <fieldset>
                    <label>Användarnamn
                        <input type="text" name="username" autocomplete="username">
                    </label>
                    <label>Lösenord
                        <input type="password" name="password" autocomplete="current-password">
                    </label>
                </fieldset>
                <div style="margin-top:1rem;display: flex; justify-content: space-between; align-items: center;">
                    <button type="submit">Logga in</button>
                    <a href="<?= url('register') ?>">Registrera dig</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php _footer();