<?php

declare(strict_types=1);

header('Content-Type: application/json');

echo json_encode([
    'path' => $_SERVER['REQUEST_URI'],
    'host' => $_SERVER['HTTP_HOST'] ?? null,
    'bridge' => $_SERVER['HTTP_X_PEST_BRIDGE'] ?? null,
    'laneHost' => $_SERVER['HTTP_X_PEST_LANE_HOST'] ?? null,
]);
