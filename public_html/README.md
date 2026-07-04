```markdown
# Laravel Project

This is a Laravel project with user authentication and seeding for a superadmin user.

## Setup

Follow these steps to set up the project on your local machine.

### Prerequisites

Make sure you have the following installed:

- PHP
- Composer
- MySQL or another database of your choice

### Installation

1. Clone the repository:

   ```bash
   git clone git@github.com:Geons-Logix-Private-Ltd/Cognisphere_LMS.git
   ```

2. Navigate to the project directory:

   ```bash
   cd your-laravel-project
   ```

3. Install dependencies:

   ```bash
   composer install
   ```

4. Copy the .env.example file and rename it to .env:

   ```bash
   cp .env.example .env
   ```

5. Generate the application key:

   ```bash
   php artisan key:generate
   ```

6. Configure the database connection in the .env file with your database credentials.

7. Run the database migrations:

   ```bash
   php artisan migrate
   ```

8. Seed the database with a superadmin user:

   ```bash
   php artisan db:seed --class=UsersTableSeeder
   php artisan db:seed --class=UsergroupSeeder

   ```

   **Superadmin Credentials:**
   - Username: superadmin@gmail.com
   - Password: @Kpm7908

### Usage

Run the development server:

```bash
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000) in your browser to see the application.