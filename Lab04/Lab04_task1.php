<?php
$available_movies = ['Titanic', 'Star Wars'];

$points = isset($_POST['points']) ? trim($_POST['points']) : '500';
$movies_selected = isset($_POST['movies']) && is_array($_POST['movies']) ? $_POST['movies'] : [];
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $points_valid = false;
    if ($points === '') {
        $errors[] = 'ERROR: Please enter your reward points.';
    } elseif (filter_var($points, FILTER_VALIDATE_INT) === false) {
        $errors[] = 'ERROR: Points must be an integer value.';
    } else {
        $points_int = intval($points);
        if ($points_int < 500) {
            $errors[] = 'ERROR: Minimum required points is 500.';
        } else {
            $points_valid = true;
        }
    }

    if ($points_valid) {
        $selected_count = count($movies_selected);

        if ($selected_count < 1) {
            $errors[] = 'ERROR: You must select at least 1 movie';
        } else {
            if ($points_int >= 1000) {
                if ($selected_count > 2) {
                    $errors[] = 'ERROR: With 1000 or more points you can select at most 2 movies';
                }
            } else {
                if ($selected_count > 1) {
                    $errors[] = "ERROR: $points_int points can redeem a ticket of ONE movie only";
                }
            }
        }
    }
    if (empty($errors)) {
        $safe_movies = array_map(function($m){ return htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); }, $movies_selected);
        $movie_list = implode(' and ', $safe_movies);
        $selected_count = count($safe_movies);
        $success_message = "You have redeem $selected_count ticket(s) for: $movie_list";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Lab 4 Task 1 - Movie Club</title>
  <style>
    body { font-family: serif; padding:24px; max-width:720px; }
    h1 { font-size:24px; }
    label { display:block; margin:8px 0; font-size:16px; }
    input[type="number"] { padding:6px; font-size:16px; width:120px; }
    .movies { margin:12px 0; }
    .note { font-size:14px; color:#444; margin-left:8px; }
    .error { color:#a00; font-weight:bold; margin-top:8px; }
    .success { color:#060; font-weight:normal; margin-top:8px; }
    .buttons { margin-top:10px; }
  </style>
</head>
<body>
  <h1>Movie Club</h1>

  <form action="Lab04_task1.php" method="post" novalidate>
    <label>
      Use
      <input type="number" name="points" min="0" step="1" value="<?php echo htmlspecialchars($points, ENT_QUOTES, 'UTF-8'); ?>" required />
      reward points <span class="note">(minimum is 500)</span>
    </label>

    <div class="movies">
      <div>To redeem tickets of the following movies:</div>
      <?php foreach ($available_movies as $movie):
          $checked = in_array($movie, $movies_selected) ? 'checked' : '';
      ?>
        <label><input type="checkbox" name="movies[]" value="<?php echo htmlspecialchars($movie, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $checked; ?> /> <?php echo htmlspecialchars($movie, ENT_QUOTES, 'UTF-8'); ?></label>
      <?php endforeach; ?>
    </div>

    <div class="buttons">
      <button type="submit">Redeem Now</button>
    </div>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $err): ?>
            <div class="error"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endforeach; ?>
    <?php elseif ($success_message !== ''): ?>
        <div class="success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

  </form>
</body>
</html>