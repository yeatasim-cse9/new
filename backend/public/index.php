<?php
// backend/public/index.php — router
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

$r = $_GET['r'] ?? '';
$a = $_GET['a'] ?? '';
if ($r === '' || $a === '') error_response('Missing r/a', 400);

$map = [
  'auth'         => __DIR__ . '/../controllers/AuthController.php',
  'users'        => __DIR__ . '/../controllers/UsersController.php',
  'menu'         => __DIR__ . '/../controllers/MenuController.php',
  'tables'       => __DIR__ . '/../controllers/TablesController.php',
  'reservations' => __DIR__ . '/../controllers/ReservationsController.php',
  'orders'       => __DIR__ . '/../controllers/OrdersController.php',
  'reviews'      => __DIR__ . '/../controllers/ReviewsController.php',
  'analytics'    => __DIR__ . '/../controllers/AnalyticsController.php',
  'payment'      => __DIR__ . '/../controllers/PaymentController.php',
];

if (!isset($map[$r]) || !file_exists($map[$r])) error_response('Resource not found', 404);
require_once $map[$r];

$controllerMap = [
  'auth'         => 'AuthController',
  'users'        => 'UsersController',
  'menu'         => 'MenuController',
  'tables'       => 'TablesController',
  'reservations' => 'ReservationsController',
  'orders'       => 'OrdersController',
  'reviews'      => 'ReviewsController',
  'analytics'    => 'AnalyticsController',
  'payment'      => 'PaymentController',
];

if (!isset($controllerMap[$r]) || !class_exists($controllerMap[$r])) error_response('Controller not found', 404);

$cls = $controllerMap[$r];
if (!method_exists($cls, $a)) error_response('Action not found', 404);

$conn = db();
try { $cls::$a($conn); }
catch (Throwable $e) { error_response('Server error: '.$e->getMessage(), 500); }
finally { if ($conn instanceof mysqli) $conn->close(); }
