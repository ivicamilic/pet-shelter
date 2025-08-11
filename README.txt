# Pet Shelter

## ENGLISH

**Pet Shelter** is a web application for managing animal shelter data.

### Features

- View, add, and edit pet data
- Manage vaccinations (add, edit)
- Manage health checks (add, edit)
- Display identification and basic pet info
- Upload and display pet images
- User roles (admin, user, volunteer)
- Responsive Bootstrap design with modal dialogs

### Technologies

- PHP 7+
- MySQL/MariaDB
- Bootstrap 5
- JavaScript (for modals and dynamic UI)
- XAMPP/LAMP/WAMP local server

### Installation

1. **Clone the repository:**
    ```sh
    git clone https://github.com/USERNAME/pet-shelter.git
    ```
2. **Copy to your web root** (e.g. `c:\xampp\htdocs\pet-shelter`)
3. **Create the database and import the SQL script** (if provided, e.g. `database.sql`)
4. **Configure database connection in `includes/config.php`:**
    ```php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'pet_shelter');
    ```
5. **Open the app in your browser:**
    ```
    http://localhost/pet-shelter/
    ```

### Project Structure

- `view-pet.php` – pet details, vaccinations, health checks
- `edit-pet.php` – edit all pet, vaccination, and health check data
- `add-vaccination.php`, `edit-vaccination.php` – add/edit vaccinations
- `add-health-check.php`, `edit-health-check.php` – add/edit health checks
- `includes/` – configuration, functions, authentication
- `assets/` – images, CSS, JS

### Notes

- Before publishing, make sure you do not commit sensitive data (passwords, API keys).
- Add `config.php` and similar files to `.gitignore` if needed.
- For production, use proper security measures.

### License

MIT

---

## SRPSKI

**Pet Shelter** je veb aplikacija za upravljanje podacima o životinjama u prihvatilištu.

### Karakteristike

- Pregled, unos i izmena podataka o ljubimcima
- Upravljanje vakcinacijama (dodavanje, izmena)
- Upravljanje zdravstvenim pregledima (Health Checks)
- Prikaz osnovnih i identifikacionih podataka
- Prikaz i upload slike ljubimca
- Različite korisničke uloge (admin, korisnik, volonter)
- Bootstrap responsive dizajn i modal dijalozi

### Tehnologije

- PHP 7+
- MySQL/MariaDB
- Bootstrap 5
- JavaScript (za modale i dinamički prikaz)
- XAMPP/LAMP/WAMP lokalni server

### Instalacija

1. **Kloniraj repozitorijum:**
    ```sh
    git clone https://github.com/USERNAME/pet-shelter.git
    ```
2. **Kopiraj u svoj web root** (npr. `c:\xampp\htdocs\pet-shelter`)
3. **Kreiraj bazu i importuj SQL skriptu** (ako postoji, npr. `database.sql`)
4. **Podesi konekciju u `includes/config.php`:**
    ```php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'pet_shelter');
    ```
5. **Pokreni aplikaciju u browseru:**
    ```
    http://localhost/pet-shelter/
    ```

### Struktura projekta

- `view-pet.php` – detalji ljubimca, vakcinacije, health check
- `edit-pet.php` – izmena svih podataka o ljubimcu, vakcinacijama i health check
- `add-vaccination.php`, `edit-vaccination.php` – unos i izmena vakcinacija
- `add-health-check.php`, `edit-health-check.php` – unos i izmena zdravstvenih pregleda
- `includes/` – konfiguracija, funkcije, autentikacija
- `assets/` – slike, CSS, JS

### Napomene

- Pre objave na GitHub-u proveri da nemaš poverljive podatke u repozitorijumu (npr. lozinke, API ključevi).
- Dodaj `config.php` i slične fajlove u `.gitignore` ako je potrebno.
- Za produkciju koristi odgovarajuće sigurnosne mere.

### Licenca

MIT

---

**Author / Autor:**  
Ivica Milić, dipl.ing.el.