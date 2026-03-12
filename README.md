# The Movie District

A responsive Movie Discovery app built with **Laravel 12.x**, **Livewire 4.x**, **Tailwind CSS 4.0**, and **SCSS**.

## 🚀 Setup Instructions

1.  **Clone the repository**
2.  **Install PHP dependencies**:
    ```bash
    composer install
    ```
3.  **Install Node dependencies**:
    ```bash
    npm install
    ```
4.  **Configure Environment**:
    *   Copy `.env.example` to `.env`
    *   Set your database credentials (`DB_DATABASE=aglet`, etc.)
    *   **Add your TMDB API Key**:
        ```env
        TMDB_API_KEY=your_tmdb_api_key_here
        ```
    *   TMDB credentials are wired through [services.php](file:///c:/laragon/www/aglet/config/services.php) (no hardcoding in code).
5.  **Generate App Key**:
    ```bash
    php artisan key:generate
    ```
6.  **Run Migrations & Seed**:
    ```bash
    php artisan migrate --seed
    ```
    *   This seeds the default user:
        *   **Email**: `jointheteam@aglet.co.za`
        *   **Password**: `@TeamAglet`
7.  **Build Assets**:
    ```bash
    npm run build
    ```
8.  **Serve**:
    ```bash
    php artisan serve
    ```

## ✅ Tests

```bash
php artisan test
```

## 🛠 Technology Rationale

*   **Laravel 12.x**: Utilized the latest framework features for robust routing, caching, and service management.
*   **Livewire 4.x (Volt)**: Employed Volt functional components for a streamlined, single-file development experience, reducing boilerplate and enhancing developer velocity. "Islands" of interactivity (like `MovieCard` and `SearchDropdown`) are independently reactive.
*   **Tailwind CSS 4.0**: Leveraged the latest utility-first engine for rapid UI development and responsiveness.
*   **SCSS**: Used for custom complex animations (like the heart pulse) where pure CSS utility classes might become verbose.

## 🧠 The "20-to-9" Pagination Logic

The [TMDBService](file:///c:/laragon/www/aglet/app/Services/TMDBService.php) implements a custom pagination strategy to meet the requirement of serving 45 movies in pages of 9, derived from TMDB's 20-items-per-page API.

1.  **Fetching**: We fetch 3 pages from TMDB (60 items total) to ensure we have enough data for our 45-item target.
2.  **Memoization & Caching**:
    *   **Request Lifecycle**: We use `once()` to memoize the data fetching within a single request, preventing redundant processing if the service is called multiple times.
    *   **Persistence**: the merged dataset is cached for 1 hour to minimize API calls and rate-limit risk.
3.  **Slicing**: We take exactly 45 items from the merged collection.
4.  **Chunking**: The collection is chunked into groups of 9 (`$collection->chunk(9)`).
5.  **Serving**: The service returns the specific chunk corresponding to the requested page number (1-5).

## ⭐ Favorites

*   Favorites are stored locally per user.
*   Primary keys use UUIDs (see [Favorite.php](file:///c:/laragon/www/aglet/app/Models/Favorite.php)).
*   A unique constraint prevents duplicate favorites per user/movie (`user_id + tmdb_id`).

## 📧 Contact Page

A contact page is available at `/contact` with developer information including:
- Full name
- Contact information
- Social media links

## 🗄 Database Dump

To create a dump of the database (including the seeded user and favorites structure):

```bash
mysqldump -u root -p aglet > aglet_dump.sql
```
