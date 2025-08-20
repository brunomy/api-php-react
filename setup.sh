mkdir -p api-php/{public,src/Controllers} && \
cat > api-php/composer.json <<'EOF'
{
  "name": "meu/api-pura",
  "type": "project",
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  },
  "require": {}
}
EOF

cat > api-php/bootstrap.php <<'EOF'
<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/helpers.php';

$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $_ENV[$k] = $v;
        putenv("$k=$v");
    }
}

if (($_ENV['APP_ENV'] ?? 'prod') !== 'prod') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
EOF

cat > api-php/src/helpers.php <<'EOF'
<?php
declare(strict_types=1);

function json_response(mixed $data, int $status = 200, array $headers = []): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    foreach ($headers as $k => $v) header("$k: $v");
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function read_json_body(): array {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function cors(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
EOF

cat > api-php/src/Router.php <<'EOF'
<?php
declare(strict_types=1);

namespace App;

final class Router {
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void {
        $regex = '#^' . preg_replace('#\{([\w]+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
        $this->routes[] = [$method, $regex, $handler];
    }

    public function get(string $p, callable $h): void { $this->add('GET', $p, $h); }
    public function post(string $p, callable $h): void { $this->add('POST', $p, $h); }
    public function put(string $p, callable $h): void { $this->add('PUT', $p, $h); }
    public function patch(string $p, callable $h): void { $this->add('PATCH', $p, $h); }
    public function delete(string $p, callable $h): void { $this->add('DELETE', $p, $h); }

    public function dispatch(string $method, string $uri): void {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        foreach ($this->routes as [$m, $regex, $handler]) {
            if ($m !== $method) continue;
            if (preg_match($regex, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $handler($params);
                return;
            }
        }
        json_response(['error' => 'Not Found'], 404);
    }
}
EOF

cat > api-php/src/Database.php <<'EOF'
<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database {
    public static function pdo(): PDO {
        static $pdo = null;
        if ($pdo) return $pdo;

        $dsn  = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'] ?? '127.0.0.1',
            $_ENV['DB_NAME'] ?? 'test'
        );

        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            json_response(['error' => 'DB connection failed', 'detail' => $e->getMessage()], 500);
            exit;
        }
        return $pdo;
    }
}
EOF

cat > api-php/src/Controllers/UserController.php <<'EOF'
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use PDO;

final class UserController {
    public static function index(): void {
        $pdo = Database::pdo();
        $stmt = $pdo->query('SELECT id, name, email FROM users ORDER BY id DESC LIMIT 100');
        json_response(['data' => $stmt->fetchAll()]);
    }

    public static function show(array $params): void {
        $id = (int)($params['id'] ?? 0);
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) return json_response(['error' => 'Not Found'], 404);
        json_response(['data' => $user]);
    }

    public static function store(): void {
        $body = read_json_body();
        $name  = trim((string)($body['name']  ?? ''));
        $email = trim((string)($body['email'] ?? ''));

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json_response(['error' => 'Invalid payload'], 422);
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
        $stmt->execute([$name, $email]);

        json_response(['data' => ['id' => (int)$pdo->lastInsertId(), 'name' => $name, 'email' => $email]], 201);
    }
}
EOF

cat > api-php/public/index.php <<'EOF'
<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Router;
use App\Controllers\UserController;

cors();

$router = new Router();

$router->get('/api/health', fn() => json_response(['status' => 'ok']));
$router->get('/api/users', fn() => UserController::index());
$router->get('/api/users/{id}', fn($params) => UserController::show($params));
$router->post('/api/users', fn() => UserController::store());

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
EOF

cat > api-php/.env.example <<'EOF'
APP_ENV=dev
DB_HOST=127.0.0.1
DB_NAME=meu_banco
DB_USER=root
DB_PASS=secret
EOF
