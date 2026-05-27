<?php
$db = new PDO('sqlite:database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Обновление статуса заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['booking_id'])) {
    $bookingId = (int)$_POST['booking_id'];
    $newStatus = $_POST['action'] === 'approve' ? 'approved' : 'rejected';
    
    $update = $db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $update->execute([$newStatus, $bookingId]);
    header('Location: admin.php');
    exit;
}

// Получаем все заявки с информацией о номере
$bookings = $db->query("
    SELECT b.*, r.category_name, r.price 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    ORDER BY b.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сладкие сны | Админ-панель</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #FFFFFF; font-family: 'Inter', sans-serif; padding: 2rem; }
        h1 { font-size: 2rem; margin-bottom: 1.5rem; font-family: 'Playfair Display', serif; color: #1A1A1A; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #EAEAEA; padding: 0.75rem; text-align: left; font-size: 0.85rem; }
        th { background: #FAFAFA; font-weight: 600; color: #8B1E3F; }
        .status-pending { color: #E67E22; font-weight: 600; }
        .status-approved { color: #27AE60; font-weight: 600; }
        .status-rejected { color: #C0392B; font-weight: 600; }
        .btn { background: #8B1E3F; color: white; border: none; padding: 0.3rem 0.8rem; cursor: pointer; font-size: 0.75rem; margin-right: 0.3rem; }
        .btn-approve { background: #27AE60; }
        .btn-reject { background: #C0392B; }
        .back-link { display: inline-block; margin-top: 2rem; color: #8B1E3F; text-decoration: none; }
    </style>
</head>
<body>
    <h1>📋 Управление заявками</h1>
    
    <table>
        <thead>
            <tr><th>ID</th><th>Номер</th><th>Гость</th><th>Телефон</th><th>Email</th><th>Заезд</th><th>Выезд</th><th>Статус</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= $b['id'] ?></td>
                    <td><?= htmlspecialchars($b['category_name']) ?></td>
                    <td><?= htmlspecialchars($b['last_name']) ?> <?= htmlspecialchars($b['first_name']) ?></td>
                    <td><?= htmlspecialchars($b['phone']) ?></td>
                    <td><?= htmlspecialchars($b['email']) ?></td>
                    <td><?= $b['check_in_date'] ?></td>
                    <td><?= $b['check_out_date'] ?></td>
                    <td class="status-<?= $b['status'] ?>"><?= $b['status'] === 'pending' ? '⏳ На рассмотрении' : ($b['status'] === 'approved' ? '✅ Одобрена' : '❌ Отклонена') ?></td>
                    <td>
                        <?php if ($b['status'] === 'pending'): ?>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-approve">Одобрить</button>
                                <button type="submit" name="action" value="reject" class="btn btn-reject">Отклонить</button>
                            </form>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <a href="index.php" class="back-link">← На главную</a>
</body>
</html>
