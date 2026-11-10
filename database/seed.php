<?php
require_once __DIR__ . '/../config/db.php';

try {
    // 1. Run schema
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);
    echo "Schema executed successfully.<br>\n";

    // 2. Categories
    $pdo->exec("INSERT INTO categories (id, slug, name, description, sort_order) VALUES 
        (1, 'clothes', 'Одежда', 'Худи, свитшоты и оверсайз кофты с премиальной велкро-базой для сменных принтов', 1),
        (2, 'prints', 'Принты', 'Авторские сменные велкро-патчи и принты на надежной микро-липучке', 2)
        ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description)");
    echo "Categories seeded.<br>\n";

    // 3. Admin user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'bpm_admin' OR role = 'admin' LIMIT 1");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $adminStmt = $pdo->prepare("INSERT INTO users (first_name, last_name, username, role, phone, city) 
            VALUES ('BPM', 'Admin', 'bpm_admin', 'admin', '+7 (999) 000-00-00', 'Москва')");
        $adminStmt->execute();
        echo "Admin user created.<br>\n";
    }

    // 4. Products (Clothes & Prints)
    // Check if products exist
    $pCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($pCount == 0) {
        $products = [
            // Clothes
            [
                'category_id' => 1,
                'category_slug' => 'clothes',
                'title' => 'PSY-MATRIX HOODIE',
                'slug' => 'psy-matrix-hoodie',
                'price' => 8900.00,
                'old_price' => 10500.00,
                'short_desc' => 'Плотный оверсайз с 16-глазым велкографическим принтом и бархатной микро-липучкой на груди и спине.',
                'description' => "Культовое худи BPM Sound System Garments. Создано для бетонных рейвов, ночных клубов и сырых индустриальных пространств.\n\n" .
                                 "• Плотность: 500 GSM ультраплотный французский футер петля без начеса (не скатывается, держит форму монументально).\n" .
                                 "• Велкро-концепт: на груди размещена запатентованная контактная зона из микро-петли (20x15 см), которая не цепляет одежду и позволяет менять авторские принты за 2 секунды.\n" .
                                 "• Дополнительная велкро-зона на спине (30x20 см) для масштабных дроп-патчей.\n" .
                                 "• Усиленные швы армированной нитью, двойной капюшон-шлем, карман-кенгуру со скрытым карманом для смартфона.",
                'image' => 'assets/images/products/hoodie-psy.jpg',
                'sizes' => 'S, M, L, XL, OVERSIZE',
                'material' => '500 GSM Французский футер 100% хлопок',
                'badge' => '#04 ДРОП',
                'velcro_zone' => 'Грудь (20x15 см), Спина (30x20 см)',
                'is_featured' => 1,
                'stock' => 24
            ],
            [
                'category_id' => 1,
                'category_slug' => 'clothes',
                'title' => 'CYBER-TURQUOISE OVERSIZE HOODIE',
                'slug' => 'cyber-turquoise-oversize-hoodie',
                'price' => 9400.00,
                'old_price' => 11200.00,
                'short_desc' => 'Глубокий графитовый цвет с неоново-бирюзовыми закрепками и широкой велкро-панелью.',
                'description' => "Модель из лимитированной серии Cyber-Pulse. Свободный архитектурный силуэт, удлиненные манжеты с прорезью для пальца, сменная система визуалов.\n\n" .
                                 "• Плотность 520 GSM — максимальный вес и драпировка.\n" .
                                 "• Велкро-панель на груди с матовой каймой.\n" .
                                 "• В комплекте идет стартовый велкро-патч BPM Frequency.",
                'image' => 'assets/images/products/hoodie-cyber.jpg',
                'sizes' => 'S, M, L, XL, OVERSIZE',
                'material' => '520 GSM Плотный хлопок Heavyweight',
                'badge' => 'LIMITED 50',
                'velcro_zone' => 'Грудь (22x16 см)',
                'is_featured' => 1,
                'stock' => 18
            ],
            [
                'category_id' => 1,
                'category_slug' => 'clothes',
                'title' => 'VIOLET SUB-BASS SWEATSHIRT',
                'slug' => 'violet-sub-bass-sweatshirt',
                'price' => 7800.00,
                'old_price' => 9000.00,
                'short_desc' => 'Темно-фиолетовый плотный свитшот с велкро-базой по центру груди.',
                'description' => "Минималистичный рейв-свитшот в глубоком неоново-фиолетовом оттенке индиго. Идеальная основа для ярких бирюзовых и психоделических сменных патчей.\n\n" .
                                 "• Плотность 480 GSM, 100% органический хлопок.\n" .
                                 "• Велкро-зона по центру совместима со всеми принтами коллекции BPM.\n" .
                                 "• Светоотражающий логотип BPM на правом рукаве.",
                'image' => 'assets/images/products/sweatshirt-violet.jpg',
                'sizes' => 'M, L, XL',
                'material' => '480 GSM Органический хлопок',
                'badge' => 'VELCRO READY',
                'velcro_zone' => 'Грудь (20x15 см)',
                'is_featured' => 1,
                'stock' => 30
            ],
            [
                'category_id' => 1,
                'category_slug' => 'clothes',
                'title' => 'DARK TECHNO ZIP-HOODIE',
                'slug' => 'dark-techno-zip-hoodie',
                'price' => 9900.00,
                'old_price' => 12000.00,
                'short_desc' => 'Зип-худи черного цвета с массивной металлической молнией и двойной велкро-зоной.',
                'description' => "Куртка-худи на двусторонней тракторной молнии YKK. Две модульные велкро-зоны: на левой стороне груди и на рукаве для мини-патчей.\n\n" .
                                 "• 500 GSM премиум футер.\n" .
                                 "• 2 велкро-зоны: грудь (15x15 см) + рукав (10x10 см).\n" .
                                 "• Магнитная планка поверх молнии.",
                'image' => 'assets/images/products/hoodie-zip.jpg',
                'sizes' => 'S, M, L, XL, OVERSIZE',
                'material' => '500 GSM Хлопок 95% / Эластан 5%',
                'badge' => 'NEW DROP',
                'velcro_zone' => 'Грудь (15x15 см) + Рукав (10x10 см)',
                'is_featured' => 0,
                'stock' => 15
            ],

            // Prints (Velcro Patches)
            [
                'category_id' => 2,
                'category_slug' => 'prints',
                'title' => 'PRINT: 16-EYED PSY-MATRIX',
                'slug' => 'print-16-eyed-psy-matrix',
                'price' => 1800.00,
                'old_price' => 2300.00,
                'short_desc' => 'Культовый психоделический 16-глазый арт с кислотно-неоновыми элементами.',
                'description' => "Сменный велкографический принт высокого разрешения. Сверхпрочная микро-крючковая основа сцепляется с велкро-базой худи намертво и выдерживает до 10 000 циклов перестегивания.\n\n" .
                                 "• Размер: 20x15 см (подходит для всех худи и свитшотов BPM).\n" .
                                 "• Шелкография с добавлением флуоресцентных неоновых пигментов (светится в ультрафиолете клубов).\n" .
                                 "• Не выцветает, стирается отдельно вручную.",
                'image' => 'assets/images/products/print-psy.jpg',
                'sizes' => 'Стандарт (20x15 см)',
                'material' => 'Армированный текстиль + крючковая Velcro лента',
                'badge' => 'ХИТ',
                'velcro_zone' => 'Липучка крючок (Hook) 20x15 см',
                'is_featured' => 1,
                'stock' => 60
            ],
            [
                'category_id' => 2,
                'category_slug' => 'prints',
                'title' => 'PRINT: 144 BPM NEON EQUALIZER',
                'slug' => 'print-144-bpm-neon-equalizer',
                'price' => 1600.00,
                'old_price' => 2000.00,
                'short_desc' => 'Бирюзово-фиолетовая звуковая волна и логотип BPM.',
                'description' => "Принт с цифровым эквалайзером саунд-системы BPM. Бирюзовые неоновые частоты на бархатном глубоко-фиолетовом фоне.\n\n" .
                                 "• Размер: 20x15 см.\n" .
                                 "• Совместимость с любыми кофтами BPM.",
                'image' => 'assets/images/products/print-equalizer.jpg',
                'sizes' => 'Стандарт (20x15 см)',
                'material' => 'Шелкография + термотрансфер + Velcro',
                'badge' => 'CYBER',
                'velcro_zone' => 'Липучка крючок (Hook) 20x15 см',
                'is_featured' => 1,
                'stock' => 75
            ],
            [
                'category_id' => 2,
                'category_slug' => 'prints',
                'title' => 'PRINT: CYBER SKULL GLITCH',
                'slug' => 'print-cyber-skull-glitch',
                'price' => 1700.00,
                'old_price' => 2100.00,
                'short_desc' => 'Глитч-череп в стиле киберпанк с ультрамариновым и бирюзовым свечением.',
                'description' => "Кибернетический череп с эффектом смещения RGB-каналов. Яркие неоновые контуры и плотная строчка по краям.\n\n" .
                                 "• Размер: 20x15 см.\n" .
                                 "• Плотная кайма Overlock исключает растрепывание.",
                'image' => 'assets/images/products/print-skull.jpg',
                'sizes' => 'Стандарт (20x15 см)',
                'material' => 'Текстильный патч с микро-велкро',
                'badge' => 'RAVE',
                'velcro_zone' => 'Липучка крючок (Hook) 20x15 см',
                'is_featured' => 1,
                'stock' => 45
            ],
            [
                'category_id' => 2,
                'category_slug' => 'prints',
                'title' => 'PRINT: ACID ACID ACID SMILE',
                'slug' => 'print-acid-smile',
                'price' => 1500.00,
                'old_price' => 1900.00,
                'short_desc' => 'Плавленый кислотный смайл 90-х в современной неоновой интерпретации.',
                'description' => "Классический символ рейв-культуры, переработанный для саунд-системы BPM. Кислотно-салатовый и бирюзовый градиент.\n\n" .
                                 "• Размер: 18x14 см.\n" .
                                 "• Светится в ультрафиолете клубов.",
                'image' => 'assets/images/products/print-acid.jpg',
                'sizes' => 'Компакт (18x14 см)',
                'material' => 'UV-флуоресцентная печать на Velcro',
                'badge' => 'UV GLOW',
                'velcro_zone' => 'Липучка крючок (Hook) 18x14 см',
                'is_featured' => 1,
                'stock' => 80
            ],
            [
                'category_id' => 2,
                'category_slug' => 'prints',
                'title' => 'PRINT: TOKYO UNDERGROUND SIGNAL',
                'slug' => 'print-tokyo-underground',
                'price' => 1650.00,
                'old_price' => 2050.00,
                'short_desc' => 'Японские кибер-иероглифы "Звук / Скорость" в фиолетовом неоне.',
                'description' => "Глубокая эстетика ночных туннелей Сибуи и подпольных техно-клубов.\n\n" .
                                 "• Размер: 20x15 см.\n" .
                                 "• Высокоточная вышивка с неоновыми нитями.",
                'image' => 'assets/images/products/print-tokyo.jpg',
                'sizes' => 'Стандарт (20x15 см)',
                'material' => 'Жаккардовый патч с Velcro основой',
                'badge' => 'POPULAR',
                'velcro_zone' => 'Липучка крючок (Hook) 20x15 см',
                'is_featured' => 0,
                'stock' => 50
            ],
            [
                'category_id' => 2,
                'category_slug' => 'prints',
                'title' => 'PRINT: BIOMECHA PULSE HEART',
                'slug' => 'print-biomecha-heart',
                'price' => 1900.00,
                'old_price' => 2400.00,
                'short_desc' => 'Механическое сердце саунд-системы с индикаторами BPM.',
                'description' => "Футуристический биомеханический орган, бьющийся в ритме 144 удара в минуту.\n\n" .
                                 "• Размер: 20x15 см.\n" .
                                 "• Специальное матовое покрытие софт-тач.",
                'image' => 'assets/images/products/print-heart.jpg',
                'sizes' => 'Стандарт (20x15 см)',
                'material' => 'Текстильный патч Soft-Touch Velcro',
                'badge' => 'PREMIUM',
                'velcro_zone' => 'Липучка крючок (Hook) 20x15 см',
                'is_featured' => 0,
                'stock' => 35
            ]
        ];

        $pStmt = $pdo->prepare("INSERT INTO products 
            (category_id, category_slug, title, slug, price, old_price, short_desc, description, image, sizes, material, badge, velcro_zone, is_featured, stock) 
            VALUES (:category_id, :category_slug, :title, :slug, :price, :old_price, :short_desc, :description, :image, :sizes, :material, :badge, :velcro_zone, :is_featured, :stock)");

        foreach ($products as $p) {
            $pStmt->execute($p);
        }
        echo "Products seeded (" . count($products) . " items).<br>\n";
    }

    // 5. Promo Codes (specifically supporting condition: 2nd print n% off!)
    $pdo->exec("INSERT INTO promo_codes 
        (code, discount_type, discount_value, min_order_amount, description, max_uses, is_active) 
        VALUES 
        ('PRINT50', 'second_print_percent', 50.00, 0, 'Берёшь 2 принта — второй со скидкой 50%!', 500, 1),
        ('COMBO30', 'bundle_hoodie_print', 30.00, 0, 'Скидка 30% на сменный принт при покупке любой кофты!', 500, 1),
        ('BPM10', 'percent', 10.00, 0, 'Скидка 10% на весь заказ по промокоду BPM10', 1000, 1),
        ('RAVE500', 'fixed', 500.00, 5000.00, 'Скидка 500 ₽ при заказе от 5 000 ₽', 200, 1)
        ON DUPLICATE KEY UPDATE description=VALUES(description), discount_value=VALUES(discount_value), discount_type=VALUES(discount_type)");
    echo "Promo codes seeded.<br>\n";

    // 6. Shop settings
    $settings = [
        'currency' => '₽',
        'free_shipping_threshold' => '10000',
        'shipping_cost' => '390',
        'telegram_bot_username' => TG_BOT_USERNAME,
        'telegram_bot_token' => TG_BOT_TOKEN,
        'contact_telegram' => '@bpm_support',
        'contact_phone' => '+7 (800) 555-144-0',
        'shop_status' => 'online'
    ];
    $settStmt = $pdo->prepare("INSERT INTO shop_settings (setting_key, setting_value) VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($settings as $k => $v) {
        $settStmt->execute([$k, $v]);
    }
    echo "Shop settings configured.<br>\n";

    echo "<strong>All seed operations completed successfully!</strong>\n";
} catch (Exception $e) {
    echo "Error during seed: " . $e->getMessage() . "\n";
}
