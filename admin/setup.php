<?php
/**
 * CPHIA 2025 Admin Portal - Setup Script
 * 
 * This script sets up the admin portal on a fresh installation.
 * Run this after pulling the code from repository.
 * 
 * Usage: php setup.php
 */

class Setup
{
    private $baseDir;
    private $hasErrors = false;

    public function __construct()
    {
        $this->baseDir = __DIR__;
    }

    public function run()
    {
        $this->printHeader();
        
        $this->step1_CheckRequirements();
        $this->step2_SetupEnvironment();
        $this->step3_InstallDependencies();
        $this->step4_GenerateAppKey();
        $this->step5_CreateDatabaseTables();
        $this->step6_SeedAdminUser();
        $this->step7_BuildAssets();
        
        $this->printSummary();
    }

    private function printHeader()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║     CPHIA 2025 Admin Portal - Setup Script                    ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    private function step1_CheckRequirements()
    {
        echo "📋 Step 1: Checking requirements...\n";
        
        // Check PHP version
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '8.2.0', '>=')) {
            $this->success("PHP version: $phpVersion ✓");
        } else {
            $this->error("PHP version $phpVersion is too old. Requires PHP 8.2+");
        }

        // Check required extensions
        $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'curl', 'zip'];
        foreach ($extensions as $ext) {
            if (extension_loaded($ext)) {
                $this->success("Extension '$ext' is installed ✓");
            } else {
                $this->error("Extension '$ext' is missing");
            }
        }

        // Check Composer
        exec('composer --version 2>&1', $output, $return);
        if ($return === 0) {
            $this->success("Composer is installed ✓");
        } else {
            $this->error("Composer is not installed. Please install from https://getcomposer.org");
        }

        // Check Node.js
        exec('node --version 2>&1', $output, $return);
        if ($return === 0) {
            $this->success("Node.js is installed ✓");
        } else {
            $this->error("Node.js is not installed. Please install from https://nodejs.org");
        }

        echo "\n";
    }

    private function step2_SetupEnvironment()
    {
        echo "🔧 Step 2: Setting up environment file...\n";
        
        $envFile = $this->baseDir . '/.env';
        $envExampleFile = $this->baseDir . '/.env.example';

        if (file_exists($envFile)) {
            $this->warning(".env file already exists, skipping...");
        } elseif (file_exists($envExampleFile)) {
            copy($envExampleFile, $envFile);
            $this->success("Created .env file from .env.example ✓");
            
            // Prompt for database credentials
            echo "\n";
            echo "Please enter your database credentials:\n";
            echo "Database name [cphia_payments]: ";
            $dbName = trim(fgets(STDIN)) ?: 'cphia_payments';
            
            echo "Database username [root]: ";
            $dbUser = trim(fgets(STDIN)) ?: 'root';
            
            echo "Database password: ";
            $dbPass = trim(fgets(STDIN));
            
            // Update .env with database credentials
            $this->updateEnvFile([
                'DB_DATABASE' => $dbName,
                'DB_USERNAME' => $dbUser,
                'DB_PASSWORD' => $dbPass,
            ]);
            
            $this->success("Database credentials configured ✓");
        } else {
            $this->error(".env.example file not found!");
        }

        echo "\n";
    }

    private function step3_InstallDependencies()
    {
        echo "📦 Step 3: Installing dependencies...\n";
        
        // Install Composer dependencies
        echo "Installing Composer dependencies...\n";
        passthru('composer install --no-interaction --prefer-dist --optimize-autoloader', $return);
        if ($return === 0) {
            $this->success("Composer dependencies installed ✓");
        } else {
            $this->error("Failed to install Composer dependencies");
        }

        // Install NPM dependencies
        echo "Installing NPM dependencies...\n";
        passthru('npm install', $return);
        if ($return === 0) {
            $this->success("NPM dependencies installed ✓");
        } else {
            $this->error("Failed to install NPM dependencies");
        }

        echo "\n";
    }

    private function step4_GenerateAppKey()
    {
        echo "🔑 Step 4: Generating application key...\n";
        
        passthru('php artisan key:generate --force', $return);
        if ($return === 0) {
            $this->success("Application key generated ✓");
        } else {
            $this->error("Failed to generate application key");
        }

        echo "\n";
    }

    private function step5_CreateDatabaseTables()
    {
        echo "🗄️  Step 5: Creating database tables...\n";
        
        try {
            // Load database credentials from .env
            $env = $this->parseEnvFile();
            
            $pdo = new PDO(
                "mysql:host={$env['DB_HOST']};dbname={$env['DB_DATABASE']}",
                $env['DB_USERNAME'],
                $env['DB_PASSWORD']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create sessions table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `sessions` (
                    `id` varchar(255) NOT NULL PRIMARY KEY,
                    `user_id` bigint unsigned NULL,
                    `ip_address` varchar(45) NULL,
                    `user_agent` text NULL,
                    `payload` longtext NOT NULL,
                    `last_activity` int NOT NULL,
                    INDEX `sessions_user_id_index` (`user_id`),
                    INDEX `sessions_last_activity_index` (`last_activity`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->success("Sessions table created ✓");

            // Create jobs table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `jobs` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `queue` varchar(255) NOT NULL,
                    `payload` longtext NOT NULL,
                    `attempts` tinyint unsigned NOT NULL,
                    `reserved_at` int unsigned NULL,
                    `available_at` int unsigned NOT NULL,
                    `created_at` int unsigned NOT NULL,
                    INDEX `jobs_queue_index` (`queue`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->success("Jobs table created ✓");

            // Create failed_jobs table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `failed_jobs` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `uuid` varchar(255) NOT NULL UNIQUE,
                    `connection` text NOT NULL,
                    `queue` text NOT NULL,
                    `payload` longtext NOT NULL,
                    `exception` longtext NOT NULL,
                    `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->success("Failed jobs table created ✓");

            // Create job_batches table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `job_batches` (
                    `id` varchar(255) NOT NULL PRIMARY KEY,
                    `name` varchar(255) NOT NULL,
                    `total_jobs` int NOT NULL,
                    `pending_jobs` int NOT NULL,
                    `failed_jobs` int NOT NULL,
                    `failed_job_ids` longtext NOT NULL,
                    `options` mediumtext NULL,
                    `cancelled_at` int NULL,
                    `created_at` int NOT NULL,
                    `finished_at` int NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->success("Job batches table created ✓");

            // Create cache table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `cache` (
                    `key` varchar(255) NOT NULL PRIMARY KEY,
                    `value` mediumtext NOT NULL,
                    `expiration` int NOT NULL,
                    KEY `cache_expiration_index` (`expiration`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->success("Cache table created ✓");

            // Create cache_locks table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `cache_locks` (
                    `key` varchar(255) NOT NULL PRIMARY KEY,
                    `owner` varchar(255) NOT NULL,
                    `expiration` int NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->success("Cache locks table created ✓");

        } catch (PDOException $e) {
            $this->error("Database error: " . $e->getMessage());
        }

        echo "\n";
    }

    private function step6_SeedAdminUser()
    {
        echo "👤 Step 6: Seeding admin user...\n";
        
        passthru('php artisan db:seed --class=AdminSeeder', $return);
        if ($return === 0) {
            $this->success("Admin user seeded ✓");
        } else {
            $this->warning("Admin seeding completed (user may already exist)");
        }

        echo "\n";
    }

    private function step7_BuildAssets()
    {
        echo "🎨 Step 7: Building frontend assets...\n";
        
        passthru('npm run build', $return);
        if ($return === 0) {
            $this->success("Assets built successfully ✓");
        } else {
            $this->error("Failed to build assets");
        }

        echo "\n";
    }

    private function printSummary()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        if ($this->hasErrors) {
            echo "║  ⚠️  Setup completed with errors                               ║\n";
            echo "║  Please review the errors above and fix them.                 ║\n";
        } else {
            echo "║  ✅ Setup completed successfully!                              ║\n";
        }
        echo "╚════════════════════════════════════════════════════════════════╝\n";
        echo "\n";

        if (!$this->hasErrors) {
            echo "📝 Next Steps:\n";
            echo "\n";
            echo "1. Add PDF images to public/images/ directory:\n";
            echo "   - banner.png (CPHIA 2025 header banner)\n";
            echo "   - co-chair-1.png (Prof. Olive Shisana signature)\n";
            echo "   - co-chair-2.png (Prof. Placide Mbala signature)\n";
            echo "   - bottom-banner.png (Africa CDC footer logo)\n";
            echo "\n";
            echo "2. Start the development server:\n";
            echo "   php artisan serve\n";
            echo "\n";
            echo "3. Start the queue worker (in a separate terminal):\n";
            echo "   php artisan queue:work --tries=3 --backoff=60\n";
            echo "\n";
            echo "4. Access the admin portal at: http://localhost:8000\n";
            echo "\n";
            echo "📋 Default Admin Credentials:\n";
            echo "   Username: adminstrator\n";
            echo "   Email: adminstrator@cphia2025.com\n";
            echo "   Password: Microinfo@2020\n";
            echo "\n";
            echo "💡 Important:\n";
            echo "   - The queue worker MUST be running for emails to be sent\n";
            echo "   - Review .env file and update mail credentials if needed\n";
            echo "\n";
        }
    }

    private function updateEnvFile($updates)
    {
        $envFile = $this->baseDir . '/.env';
        $envContent = file_get_contents($envFile);

        foreach ($updates as $key => $value) {
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $envContent
            );
        }

        file_put_contents($envFile, $envContent);
    }

    private function parseEnvFile()
    {
        $envFile = $this->baseDir . '/.env';
        $env = [];

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1], '"');
                    $env[$key] = $value;
                }
            }
        }

        return $env;
    }

    private function success($message)
    {
        echo "  ✅ $message\n";
    }

    private function warning($message)
    {
        echo "  ⚠️  $message\n";
    }

    private function error($message)
    {
        echo "  ❌ $message\n";
        $this->hasErrors = true;
    }
}

// Run setup
$setup = new Setup();
$setup->run();

