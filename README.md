<div align="center">
  <img src="public/warning_12536258.png" width="120" alt="Qatar GeoRisk Logo" />
  <h1>🛡️ Qatar GeoRisk</h1>
  <p><b>Advanced Geographic Threat Estimation Platform</b></p>
  
  [![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![React](https://img.shields.io/badge/React-20232A?style=for-the-badge&logo=react&logoColor=61DAFB)](https://reactjs.org/)
  [![Inertia](https://img.shields.io/badge/Inertia.js-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com/)
  [![Leaflet](https://img.shields.io/badge/Leaflet-199900?style=for-the-badge&logo=leaflet&logoColor=white)](https://leafletjs.com/)
  [![Filament](https://img.shields.io/badge/Filament-FACC15?style=for-the-badge&logo=filament&logoColor=black)](https://filamentphp.com/)
  
  <br />
  <a href="#english">English</a> • <a href="#arabic-العربية">العربية</a>
</div>

---

<h2 id="english">🇬🇧 English Readme</h2>

**Qatar GeoRisk** is a rapid-response, interactive mapping platform built to provide real-time geographic risk estimation. It leverages GPS data, map coordinates, and user-submitted intelligence to calculate localized threat levels and visualize Danger Zones across Qatar.

> *"Nothing will befall us except what Allah has ordained for us"* (لن يصيبنا إلا ما كتبه الله لنا)

### ✨ Core Features

*   **🗺️ Interactive Threat Map:** Built with Leaflet to visualize known active military hotspots and user-reported danger zones dynamically.
*   **🎯 Real-Time Risk Engine:** Users can click on the map, paste a Google Maps link, or use their GPS to calculate a customized 0-100 Risk Score indicating their current level of danger.
*   **📡 Crowdsourced Alerts:** Users can report new danger zones directly from the map, including precise coordinates, descriptions, and photograph evidence.
*   **🛡️ Dedicated Filament Admin Panel:** Submitted danger zones are routed to an administrative dashboard built on Filament PHP, allowing rapid verifiable approval or dismissal of threats.
*   **🌗 Bilingual & RTL Support:** Fully seamless switching between English (LTR) and fully-localized Arabic (RTL) interfaces without reloading the page.
*   **📱 Mobile-First Design:** The application boasts a sleek dark-mode glassmorphism UI, complete with space-saving slide-out panels optimized for mobile field ops.

### 🚀 Technology Stack
*   **Backend:** Laravel Framework (PHP)
*   **Frontend:** React, Inertia.js, Tailwind CSS
*   **Mapping:** React-Leaflet, CartoDB Dark Base Maps
*   **Geocoding API:** OpenStreetMap Nominatim
*   **Admin Panel:** Filament admin panel v3

### 🛠️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone git@github.com:HassanDev13/qatar-geo-risk.git
   cd qatar-geo-risk
   ```
2. **Install dependencies:**
    ```bash
    composer install
    npm install
    ```
3. **Environment Setup:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
4. **Database Configuration:**
   Configure your database credentials inside `.env` and then run:
    ```bash
    php artisan migrate
    ```
5. **Storage Setup:**
    ```bash
    php artisan storage:link
    ```
6. **Start Local Servers:**
    Run these two commands in separate terminal windows:
    ```bash
    php artisan serve
    npm run dev
    ```

---

<h2 id="arabic-العربية">🇶🇦 Arabic Readme - بالعربية</h2>

**جيو ريسك قطر (Qatar GeoRisk)** هي منصة استجابة سريعة وتفاعلية مبنية لتقديم تقديرات للأخطار الجغرافية في الوقت الفعلي. تعتمد المنصة على بيانات تحديد المواقع (GPS) وإحداثيات الخرائط والمعلومات المقدمة من المستخدمين لحساب مستويات التهديد المحلية وتصور "مناطق الخطر" في جميع أنحاء قطر.

> *"لن يصيبنا إلا ما كتبه الله لنا"*

### ✨ الميزات الرئيسية

*   **🗺️ خريطة تهديدات تفاعلية:** مبنية باستخدام Leaflet لتوضيح النقاط العسكرية الساخنة ومناطق الخطر التي يبلغ عنها المستخدمون بشكل ديناميكي.
*   **🎯 محرك الخطر في الوقت الفعلي:** يمكن للمستخدمين النقر على الخريطة، أو لصق رابط خرائط جوجل، أو استخدام نظام تحديد المواقع (GPS) لحساب نسبة الخطر (من 0 إلى 100) الذي يوضح مستوى التهديد الحالي لموقعهم.
*   **📡 تنبيهات جماعية الطابع:** يتيح النظام للمستخدمين الإبلاغ عن مناطق خطر جديدة مباشرة من الخريطة مع إرفاق الإحداثيات الدقيقة، والوصف، وصور الإثبات.
*   **🛡️ لوحة تحكم إدارية:** جميع المناطق المبلغ عنها يتم إرسالها إلى لوحة تحكم إدارية مبنية بـ Filament PHP لمراجعتها، حيث يُمكن التحقق من صحتها والموافقة عليها للملأ.
*   **🌗 دعم ثنائي اللغة (RTL):** نظام التبديل السلس بين الواجهة الإنجليزية والواجهة العربية اليمين-إلى-اليسار بضغطة زر واحدة دون إعادة تحميل الصفحة.
*   **📱 تصميم متوافق مع كافة الشاشات:** واجهة بتأثير "Glassmorphism" الليلي الأنيق مع أزرار لمسية مخصصة للتحكم وتوفير المساحة على شاشات الأجهزة المحمولة.

### 🚀 التقنيات المستخدمة
*   **الواجهة الخلفية (Backend):** Laravel Framework
*   **الواجهة الأمامية (Frontend):** React, Inertia.js, Tailwind CSS
*   **الخرائط:** React-Leaflet, خرائط CartoDB المظلمة
*   **محرك الإحداثيات:** OpenStreetMap Nominatim
*   **لوحة الإدارة:** Filament admin panel v3

### 🛠️ التثبيت والتشغيل

1. **نسخ المستودع:**
   ```bash
   git clone git@github.com:HassanDev13/qatar-geo-risk.git
   cd qatar-geo-risk
   ```
2. **تثبيت الحزم المطلوبة:**
    ```bash
    composer install
    npm install
    ```
3. **إعدادات البيئة:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
4. **تكوين قاعدة البيانات:**
   أدخل بيانات قاعدة البيانات في ملف `.env` ثم قم بتشغيل:
    ```bash
    php artisan migrate
    ```
5. **ربط مجلد الصور:**
    ```bash
    php artisan storage:link
    ```
6. **تشغيل الخوادم المحلية:**
   قم بتشغيل الأوامر التالية في نوافذ منفصلة:
    ```bash
    php artisan serve
    npm run dev
    ```
