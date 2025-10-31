# 🚀 Senso Crypto Pro - WordPress Plugin

**Професійний плагін для керування крипто-портфелем, блогом та щоденником трейдера**

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-6.0+-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)

---

## ✨ Основні можливості

### 📊 Портфель
- ✅ Додавання та відстеження крипто-активів
- ✅ Автоматичне оновлення цін через CoinGecko API
- ✅ Підтримка Binance, ByBit та інших бірж
- ✅ Автоматичний календар прибутків/збитків
- ✅ Красиві графіки та статистика
- ✅ Щоденні snapshots портфеля

### 📝 Щоденник трейдера
- ✅ Зручний редактор (Quill.js) як Notion
- ✅ Додавання настрою та результатів трейдів
- ✅ Можливість публікації записів у блог/ідеї
- ✅ Пошук та фільтрація записів
- ✅ Статистика по записам

### 📚 Блог/Academy
- ✅ Кастомні пост-тайпи для Academy та Trading Ideas
- ✅ Категорії та рівні складності
- ✅ Красивий дизайн у стилі Binance Academy

### 🎨 Дизайн та анімації
- ✅ Сучасний градієнтний дизайн (фіолетово-блакитний)
- ✅ GSAP анімації та scroll-ефекти
- ✅ Адаптивний дизайн (мобільні, планшети, десктоп)
- ✅ Chart.js для графіків

---

## 📦 Встановлення

### Крок 1: Завантаження
Завантаж всі файли плагіна у папку `wp-content/plugins/senso-crypto-pro/`

### Крок 2: Активація
1. Зайди в WordPress Admin → Плагіни
2. Знайди "Senso Crypto Pro"
3. Натисни "Активувати"

### Крок 3: Налаштування
Після активації автоматично створюються:
- 📊 Таблиці в базі даних
- 📝 Кастомні пост-тайпи (Academy, Trading Ideas)
- ⚙️ Початкові налаштування

---

## 🎯 Використання

### Шорткоди для Elementor

#### Dashboard (Повний функціонал)
```
[scp_dashboard]
```
Містить: портфель, історію, щоденник, статистику

#### Тільки портфель
```
[scp_portfolio view="full"]
```
Параметри:
- `view="full"` - повний вигляд
- `view="compact"` - компактний вигляд
- `view="chart"` - тільки графік

#### Щоденник
```
[scp_journal]
```

#### Графік портфеля
```
[scp_portfolio_chart type="line" days="30"]
```
Параметри:
- `type` - line, pie, doughnut
- `days` - кількість днів історії (7, 30, 90, 365)

#### Віджет ціни криптовалюти
```
[scp_crypto_price symbol="BTC" currency="USD"]
```

#### Трендові монети
```
[scp_trending limit="5"]
```

---

## 🎨 CSS Класи для анімацій

Додай ці класи до елементів в Elementor для GSAP анімацій:

### Fade In (з'являється знизу)
```html
<div class="scp-fade-in">
  Контент
</div>
```

### Scale In (збільшується)
```html
<div class="scp-scale-in">
  Контент
</div>
```

### Slide Left (з'являється зліва)
```html
<div class="scp-slide-left">
  Контент
</div>
```

### Slide Right (з'являється справа)
```html
<div class="scp-slide-right">
  Контент
</div>
```

### Counter (лічильник чисел)
```html
<span class="scp-counter" data-target="1000">0</span>
```

---

## 🔌 API Інтеграція

### CoinGecko API (вже підключено)
Плагін використовує безкоштовний CoinGecko API для отримання цін криптовалют.

### Binance/ByBit API (опціонально)
Для підключення власних API ключів:
1. Зайди в Dashboard
2. Налаштування → API Keys
3. Додай свої ключі

**⚠️ Безпека:** API ключі зберігаються в зашифрованому вигляді

---

## 📂 Структура файлів

```
senso-crypto-pro/
├── senso-crypto-pro.php          # Головний файл плагіна
├── includes/                      # Основні класи
│   ├── class-scp-database.php
│   ├── class-scp-api-manager.php
│   ├── class-scp-portfolio.php
│   ├── class-scp-journal.php
│   ├── class-scp-ajax.php
│   └── class-scp-shortcodes.php
├── admin/                         # Адмін панель
│   └── class-scp-admin.php
├── public/                        # Публічна частина
│   └── class-scp-public.php
├── assets/                        # Стилі та скрипти
│   ├── css/
│   │   ├── main.css
│   │   ├── dashboard.css
│   │   └── admin.css
│   └── js/
│       ├── main.js
│       ├── portfolio.js
│       └── journal.js
├── templates/                     # Шаблони
│   ├── dashboard.php
│   ├── portfolio.php
│   └── journal.php
└── languages/                     # Переклади
```

---

## 🎨 Кастомізація дизайну

### Змінити кольори
Відредагуй файл `assets/css/main.css`:

```css
:root {
    --scp-primary: #8b5cf6;       /* Основний фіолетовий */
    --scp-secondary: #3b82f6;     /* Синій */
    --scp-success: #10b981;       /* Зелений */
    --scp-danger: #ef4444;        /* Червоний */
}
```

### Додати власні анімації
Використовуй GSAP в файлі `assets/js/main.js`

---

## 🔧 Налаштування Cron

Плагін автоматично оновлює портфелі раз на день.

Якщо потрібно змінити частоту:
```php
// В файлі includes/class-scp-portfolio.php
wp_schedule_event(time(), 'hourly', 'scp_daily_portfolio_update');
```

---

## 📊 База даних

Плагін створює 4 таблиці:
1. `wp_scp_portfolio` - Позиції портфеля
2. `wp_scp_portfolio_history` - Історія змін
3. `wp_scp_journal` - Записи щоденника
4. `wp_scp_api_keys` - API ключі (зашифровані)

---

## 🚀 Продуктивність

### Кешування
- API запити кешуються на 30-60 хвилин
- Використовується WordPress Transients API

### Оптимізація
- CSS та JS мініфіковані
- Використання CDN для бібліотек
- Lazy loading зображень

---

## 🔐 Безпека

- ✅ Nonce перевірка для всіх AJAX запитів
- ✅ Санітизація даних
- ✅ Шифрування API ключів
- ✅ Захист від SQL ін'єкцій
- ✅ Захист від XSS атак

---

## 📱 Підтримка браузерів

- ✅ Chrome (останні 2 версії)
- ✅ Firefox (останні 2 версії)
- ✅ Safari (останні 2 версії)
- ✅ Edge (останні 2 версії)
- ✅ Мобільні браузери

---

## 🐛 Troubleshooting

### Не відображаються ціни
1. Перевір інтернет з'єднання
2. Очисти кеш (WP Rocket, W3 Total Cache)
3. Перевір браузерну консоль на помилки

### Не зберігаються дані
1. Перевір права доступу до бази даних
2. Активуй WP_DEBUG для деталей помилок

### Анімації не працюють
1. Перевір чи завантажується GSAP
2. Перевір console браузера на помилки
3. Вимкни інші плагіни які можуть конфліктувати

---

## 🔄 Оновлення

### Перед оновленням
1. ✅ Зроби backup сайту
2. ✅ Зроби backup бази даних
3. ✅ Перевір версію WordPress (мін. 6.0)

### Процес оновлення
1. Деактивуй плагін
2. Видали старі файли
3. Завантаж нові файли
4. Активуй плагін

---

## 🆘 Підтримка

Маєш питання? Пиши на:
- 📧 Email: support@senzocrypto.io
- 🌐 Сайт: https://dev-senzocrypto.pantheonsite.io

---

## 📝 Roadmap

### Версія 1.1 (планується)
- [ ] Telegram Bot інтеграція
- [ ] Push notifications
- [ ] Мобільний додаток
- [ ] Binance futures підтримка

### Версія 1.2 (планується)
- [ ] AI аналіз портфеля
- [ ] Автоматичний trading
- [ ] Соціальна мережа трейдерів
- [ ] NFT портфель

---

## ⚖️ Ліцензія

GPL v2 або пізніше

---

## 🙏 Подяки

Використані бібліотеки:
- [GSAP](https://greensock.com/gsap/) - Анімації
- [Chart.js](https://www.chartjs.org/) - Графіки
- [Quill.js](https://quilljs.com/) - Текстовий редактор
- [CoinGecko API](https://www.coingecko.com/api) - Крипто дані

---

**Зроблено з ❤️ для Senso Crypto**

*За питаннями розробки звертайся до команди розробки*
