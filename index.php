<?php
// Подключение к SQLite (файл database.sqlite создастся сам)
$db = new PDO('sqlite:database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Создаём таблицы, если их нет
$db->exec("CREATE TABLE IF NOT EXISTS rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category TEXT NOT NULL,
    category_name TEXT NOT NULL,
    price INTEGER NOT NULL,
    characteristics TEXT NOT NULL,
    image_url TEXT NOT NULL
)");

$db->exec("CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id INTEGER NOT NULL,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    email TEXT NOT NULL,
    check_in_date TEXT NOT NULL,
    check_out_date TEXT NOT NULL,
    status TEXT DEFAULT 'pending',
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
)");

// Добавляем номера, если их нет
$count = $db->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
if ($count == 0) {
    $roomsData = [
        ['standard', 'Стандартный', 5000, '18 м², двуспальная кровать, TV, кондиционер'],
        ['standard', 'Стандартный', 5000, '18 м², двуспальная кровать, TV, кондиционер'],
        ['standard', 'Стандартный', 5000, '18 м², двуспальная кровать, TV, кондиционер'],
        ['standard', 'Стандартный', 5000, '18 м², двуспальная кровать, TV, кондиционер'],
        ['studio', 'Студия', 8000, '25 м², кухня, холодильник, диван, Wi‑Fi'],
        ['studio', 'Студия', 8000, '25 м², кухня, холодильник, диван, Wi‑Fi'],
        ['studio', 'Студия', 8000, '25 м², кухня, холодильник, диван, Wi‑Fi'],
        ['studio', 'Студия', 8000, '25 м², кухня, холодильник, диван, Wi‑Fi'],
        ['lux', 'Люкс', 12000, '40 м², джакузи, балкон, гостиная, мини‑бар'],
        ['lux', 'Люкс', 12000, '40 м², джакузи, балкон, гостиная, мини‑бар'],
        ['lux', 'Люкс', 12000, '40 м², джакузи, балкон, гостиная, мини‑бар'],
        ['lux', 'Люкс', 12000, '40 м², джакузи, балкон, гостиная, мини‑бар'],
    ];
    $insert = $db->prepare("INSERT INTO rooms (category, category_name, price, characteristics, image_url) VALUES (?, ?, ?, ?, ?)");
    foreach ($roomsData as $i => $room) {
        $imageUrl = "https://placehold.co/600x400/F7F2EF/8B1E3F?text=" . urlencode($room[1] . "+" . ($i+1));
        $insert->execute([$room[0], $room[1], $room[2], $room[3], $imageUrl]);
    }
}

// Фильтрация
$selectedCategories = isset($_GET['categories']) && is_array($_GET['categories']) ? $_GET['categories'] : [];

$sql = "SELECT * FROM rooms";
$params = [];

if (!empty($selectedCategories)) {
    $placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
    $sql .= " WHERE category IN ($placeholders)";
    $params = $selectedCategories;
}

if (empty($selectedCategories)) {
    $sql .= " ORDER BY RANDOM() LIMIT 5";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сладкие сны | Каталог номеров</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #FFFFFF; font-family: 'Inter', sans-serif; color: #1A1A1A; }
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; padding-bottom: 1.5rem; margin-bottom: 2rem; border-bottom: 1px solid #EAEAEA; }
        .header-left h1 { font-size: 2.8rem; font-weight: 500; color: #1A1A1A; font-family: 'Playfair Display', serif; }
        .slogan { font-size: 0.9rem; color: #8B1E3F; margin-top: 0.25rem; }
        .header-right { font-size: 0.8rem; color: #8B1E3F; text-transform: uppercase; }
        .footer { border-top: 1px solid #EAEAEA; padding: 2rem 0 0.5rem; margin-top: 3rem; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 2rem; font-size: 0.8rem; color: #666; }
        .contacts { line-height: 1.6; text-align: right; }
        .contacts strong { color: #8B1E3F; }
        .filter-bar { background: #FAFAFA; padding: 1.2rem 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border: 1px solid #EEEEEE; }
        .filter-group { display: flex; gap: 2rem; flex-wrap: wrap; }
        .filter-group label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer; }
        .filter-group input { accent-color: #8B1E3F; }
        .btn { background: transparent; border: 1px solid #8B1E3F; padding: 0.5rem 1.5rem; cursor: pointer; font-size: 0.8rem; font-weight: 500; color: #8B1E3F; text-decoration: none; display: inline-block; }
        .btn-outline:hover { background: #FDF5F7; }
        .catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 2rem; }
        .room-card { background: white; border: 1px solid #F0F0F0; transition: all 0.25s; }
        .room-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.04); }
        .room-img { background-color: #F7F2EF; height: 230px; background-size: cover; background-position: center; }
        .room-info { padding: 1.2rem; }
        .room-category { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 500; }
        .room-price { font-size: 1.4rem; font-weight: 600; margin: 0.5rem 0; color: #8B1E3F; }
        .room-features { font-size: 0.85rem; color: #5A5A5A; margin: 0.8rem 0; }
        .book-btn { background: #8B1E3F; border: none; padding: 0.7rem; width: 100%; cursor: pointer; font-size: 0.85rem; font-weight: 500; color: white; margin-top: 1rem; display: block; text-align: center; text-decoration: none; }
        .book-btn:hover { background: #6B1630; }
        @media (max-width: 800px) { .container { padding: 1rem; } .header-left h1 { font-size: 2rem; } }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-left">
            <h1>Сладкие сны</h1>
            <div class="slogan">Бутик-отель в центре столицы</div>
        </div>
        <div class="header-right">тишина · комфорт · стиль</div>
    </div>

    <form method="GET" action="" class="filter-bar">
        <div class="filter-group">
            <label><input type="checkbox" name="categories[]" value="standard" <?= in_array('standard', $selectedCategories) ? 'checked' : '' ?>> Стандартный</label>
            <label><input type="checkbox" name="categories[]" value="studio" <?= in_array('studio', $selectedCategories) ? 'checked' : '' ?>> Студия</label>
            <label><input type="checkbox" name="categories[]" value="lux" <?= in_array('lux', $selectedCategories) ? 'checked' : '' ?>> Люкс</label>
        </div>
        <div>
            <button type="submit" class="btn btn-outline">Применить</button>
            <a href="index.php" class="btn btn-outline">Сбросить</a>
        </div>
    </form>

    <div class="catalog-grid">
        <?php foreach ($rooms as $room): ?>
            <div class="room-card">
                <div class="room-img" style="background-image: url('<?= htmlspecialchars($room['image_url']) ?>');"></div>
                <div class="room-info">
                    <div class="room-category"><?= htmlspecialchars($room['category_name']) ?></div>
                    <div class="room-price"><?= number_format($room['price'], 0, '', ' ') ?> ₽ / сутки</div>
                    <div class="room-features"><?= htmlspecialchars($room['characteristics']) ?></div>
                    <a href="booking.php?room_id=<?= $room['id'] ?>" class="book-btn">Забронировать</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="footer">
        <div>© Сладкие сны</div>
        <div class="contacts">
            <strong>Контакты</strong><br>
            📍 Москва, ул. Сладкая, 7<br>
            🕛 круглосуточно<br>
            📞 +7 (495) 123-45-67<br>
            ✉️ info@sweetdreams.ru
        </div>
    </div>
</div>
</body>
</html>
