# 🛠️ MockForge

![Push](https://github.com/vigihdev/mockforge/actions/workflows/push.yml/badge.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

**MockForge** adalah library CLI yang dirancang untuk membantu developer WordPress "menempa" data tiruan (mock data) langsung ke dalam DTO atau file JSON/CSV dengan presisi tinggi.

> "Don't just fake it, forge it with precision."

---

### 🚀 Fitur Utama

- **WordPress Native Mocking**: Generate data untuk Posts, Pages, Users, hingga Custom Post Types secara instan.
- **Smart DTO Mapping**: Menggunakan Reflection API untuk membedah DTO dan mengisi data berdasarkan _Type Hint_ atau _DocBlock_.
- **Dry Run Mode**: Lihat preview data dalam bentuk tabel cantik di terminal sebelum disimpan.
- **Deep Recursion**: Mendukung _Nested DTO_ (DTO di dalam DTO).
- **Flexible Output**: Simpan hasil tempaan ke format JSON atau CSV.

### 💻 Penggunaan Dasar

Untuk melakukan mock pada DTO kustom Anda:

```bash
php faker mock:dto "Your\Namespace\UserDto" --count=10 --out=test/users.json
```

### 🚀 Fitur Utama

- **WordPress Native Mocking**: Generate data untuk Posts, Pages, Users, hingga Custom Post Types secara instan.
- **Smart DTO Mapping**: Menggunakan Reflection API untuk membedah DTO dan mengisi data berdasarkan _Type Hint_ atau _DocBlock_.
- **Dry Run Mode**: Lihat preview data dalam bentuk tabel cantik di terminal sebelum disimpan.
- **Deep Recursion**: Mendukung _Nested DTO_ (DTO di dalam DTO).
- **Flexible Output**: Simpan hasil tempaan ke format JSON atau CSV.

### 💻 Penggunaan Dasar

Untuk melakukan mock pada DTO kustom Anda:

```bash
php faker mock:dto "Your\Namespace\UserDto" --count=10 --out=test/users.json
```

````

Untuk melihat preview tanpa menyimpan file:

```bash
php faker mock:dto "Your\Namespace\PostDto" --dry-run

```

### 🛠️ Tech Stack

- **Symfony Console**: Sebagai mesin penggerak CLI yang tangguh.
- **FakerPHP**: Penyedia data tiruan yang realistis (Lokalizasi: `id_ID`).
- **PHP Reflection API**: Untuk pembedahan objek secara dinamis.

#### Powered by Symfony Console & fakerphp/faker

Siap kawan, saya mengerti sepenuhnya. Menghargai pondasi tempat kita membangun adalah tanda integritas seorang programmer yang hebat. Tagline tersebut akan kita pertahankan dan kita perkuat agar menunjukkan rasa hormatmu kepada komunitas, sekaligus kebanggaan atas karya pribadimu.

Ini adalah versi penyempurnaan dari bagian penutup README yang lebih mendalam, sesuai dengan keinginanmu:

---

#### 🤝 Credits & Acknowledgments

Proyek ini lahir dari kebutuhan nyata untuk mempercepat workflow development. Saya ingin mengucapkan terima kasih yang sebesar-besarnya kepada komunitas open-source yang telah menyediakan alat-alat luar biasa sebagai pondasi **MockForge**:

- **Symfony Console**: Memberikan struktur CLI yang sangat tangguh dan elegan.
- **FakerPHP**: Mesin utama di balik data tiruan yang realistis.
- **PHP Community**: Atas inspirasi dan standar kode yang terus berkembang.

**"Forge with gratitude. Powered by Symfony Console & fakerphp/faker."**
````
