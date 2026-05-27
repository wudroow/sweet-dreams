<?php
$db = new PDO('sqlite:database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

$stmt = $db->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$roomId]);
$room = $stmt->fetch();

if (!$room) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim(htmlspecialchars($_POST['firstName']));
    $lastName = trim(htmlspecialchars($_POST['lastName']));
    $phone = trim(htmlspecialchars($_POST['phone']));
    $email = trim(htmlspecialchars($_POST['email']));
    $checkIn = $_POST['checkIn'];
    $checkOut = $_POST['checkOut'];

    $errors = [];
    if (!preg_match('/^[А-Яа-яёЁ\s\.\-]+$/u', $firstName)) $errors[] = 'Имя содержит недопустимые символы.';
    if (!preg_match('/^[А-Яа-яёЁ\s\.\-]+$/u', $lastName)) $errors[] = 'Фамилия содержит недопустимые символы.';
    if (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) $errors[] = 'Телефон должен быть в формате +7(123)456-78-90';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email.';
    if (!$checkIn || !$checkOut) $errors[] = 'Заполните даты.';
    if ($checkIn && $checkOut && $checkIn >= $checkOut) $errors[] = 'Дата выезда должна быть позже даты заезда.';

    if (empty($errors)) {
        $insert = $db->prepare("INSERT INTO bookings (room_id, first_name, last_name, phone, email, check_in_date, check_out_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        if ($insert->execute([$roomId, $firstName, $lastName, $phone, $email, $checkIn, $checkOut])) {
            $success = '✅ Заявка успешно отправлена! Администратор свяжется с вами.';
        } else {
            $error = 'Ошибка при сохранении заявки.';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сладкие сны | Бронирование</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #FFFFFF; font-family: 'Inter', sans-serif; }
        .container { max-width: 620px; margin: 2rem auto; padding: 2rem; background: white; border: 1px solid #EAEAEA; }
        h1 { font-size: 2rem; margin-bottom: 1rem; font-family: 'Playfair Display', serif; }
        .room-info { background: #FAFAFA; padding: 1rem; margin-bottom: 1.5rem; border-left: 3px solid #8B1E3F; }
        .form-group { margin-bottom: 1.2rem; }
        label { display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #8B1E3F; margin-bottom: 0.4rem; }
        input { width: 100%; padding: 0.8rem; border: 1px solid #E0E0E0; font-size: 0.9rem; }
        .btn { background: #8B1E3F; color: white; border: none; padding: 0.8rem; width: 100%; cursor: pointer; font-size: 0.9rem; font-weight: 500; }
        .btn:hover { background: #6B1630; }
        .error { color: #C04242; margin-bottom: 1rem; padding: 0.5rem; background: #FFEFEF; }
        .success { color: #2E7D32; margin-bottom: 1rem; padding: 0.5rem; background: #E8F5E9; }
        .back-link { display: block; margin-top: 1rem; text-align: center; color: #8B1E3F; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h1>Бронирование номера</h1>
    <div class="room-info">
        <strong><?= htmlspecialchars($room['category_name']) ?></strong> — <?= number_format($room['price'], 0, '', ' ') ?> ₽/сутки<br>
        <?= htmlspecialchars($room['characteristics']) ?>
    </div>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Имя *</label>
            <input type="text" name="firstName" required>
        </div>
        <div class="form-group">
            <label>Фамилия *</label>
            <input type="text" name="lastName" required>
        </div>
        <div class="form-group">
            <label>Телефон * (формат +7(123)456-78-90)</label>
            <input type="text" name="phone" placeholder="+7(123)456-78-90" required>
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Дата заезда *</label>
            <input type="date" name="checkIn" required>
        </div>
        <div class="form-group">
            <label>Дата выезда *</label>
            <input type="date" name="checkOut" required>
        </div>
        <button type="submit" class="btn">Отправить заявку</button>
    </form>
    <a href="index.php" class="back-link">← Назад к каталогу</a>
</div>
</body>
</html>
