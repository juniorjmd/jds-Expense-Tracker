<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Persistence\Database;
use PDO;

final class ExpenseController
{
    public function index(): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->query(
            'SELECT id, type, title, amount, transaction_date, notes, created_at
             FROM transactions
             ORDER BY transaction_date DESC, id DESC'
        );

        Response::json([
            'ok' => true,
            'data' => $stmt->fetchAll(),
        ]);
    }

    public function store(Request $request): void
    {
        $payload = $request->json();

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO transactions (type, title, amount, transaction_date, notes)
             VALUES (:type, :title, :amount, :transaction_date, :notes)'
        );

        $stmt->execute([
            ':type' => $payload['type'] ?? 'expense',
            ':title' => $payload['title'] ?? 'Untitled',
            ':amount' => $payload['amount'] ?? 0,
            ':transaction_date' => $payload['transaction_date'] ?? date('Y-m-d'),
            ':notes' => $payload['notes'] ?? null,
        ]);

        Response::json([
            'ok' => true,
            'message' => 'Transaction created successfully',
            'id' => (int) $pdo->lastInsertId(),
        ], 201);
    }
}
