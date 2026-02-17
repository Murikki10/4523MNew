<?php
$max_price = 100;
$require_name = false; 

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$price = isset($_POST['price']) ? trim($_POST['price']) : '';
$type = isset($_POST['type']) ? $_POST['type'] : '';
$errors = [];
$success_message = '';

function fmt_price_display($val) {
    if ($val === '' || !is_numeric($val)) return $val;
    $fv = floatval($val);
    if (floor($fv) == $fv) return intval($fv);
    return rtrim(rtrim(number_format($fv, 2, '.', ''), '0'), '.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type !== 'Food' && $type !== 'Drink') {
        $errors[] = 'ERROR: Must select an item type';
    }

    if ($price === '') {
        $errors[] = 'ERROR: Please enter item price';
    } else {
        if (!is_numeric($price)) {
            $errors[] = 'ERROR: Item price must be a number';
        } else {
            $price_val = floatval($price);

            if ($price_val <= 0) {
                $errors[] = 'ERROR: Item price must be positive';
            }

            if ($price_val > $max_price) {
                $display = fmt_price_display($price_val);
                $errors[] = "ERROR: Item price HK\${$display} is too high";
            }
        }
    }

    if ($require_name) {
        if ($name === '') {
            $errors[] = 'ERROR: Item name required';
        }
    }

    if (empty($errors)) {
        // prepare display price
        $price_display = fmt_price_display(isset($price_val) ? $price_val : $price);
        $safe_name = $name === '' ? '(no name)' : htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $success_message = "Item {$safe_name} (HK\${$price_display}) will be added to system";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Lab 4 Task 2 - Add Items Form (ABC Canteen)</title>
  <style>
    body { font-family: serif; padding:24px; max-width:720px; }
    h1 { font-size:20px; }
    label { display:block; margin:8px 0; }
    input[type="text"], input[type="number"] { padding:4px; font-size:14px; width:260px; }
    .radio { margin-top:6px; }
    .buttons { margin-top:8px; }
    .error { color:#a00; font-weight:bold; margin-top:6px; }
    .success { color:#060; margin-top:6px; }
    .note { font-size:13px; color:#444; margin-top:6px; }
  </style>
</head>
<body>
  <h1>Add Items Form (ABC Canteen)</h1>

  <form action="Lab04_task2.php" method="post" novalidate>
    <label>
      Item Name
      <input type="text" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" />
    </label>

    <label>
      Item Price (HKS)
      <input type="text" name="price" value="<?php echo htmlspecialchars($price, ENT_QUOTES, 'UTF-8'); ?>" />
    </label>

    <div class="radio">
      Item Type
      <label><input type="radio" name="type" value="Food" <?php if ($type === 'Food') echo 'checked'; ?> /> Food</label>
      <label><input type="radio" name="type" value="Drink" <?php if ($type === 'Drink') echo 'checked'; ?> /> Drink</label>
    </div>

    <div class="buttons">
      <button type="submit">Confirm</button>
    </div>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="error"><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endforeach; ?>
    <?php elseif ($success_message !== ''): ?>
        <div class="success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

  </form>
</body>
</html>