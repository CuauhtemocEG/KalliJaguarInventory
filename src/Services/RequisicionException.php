<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/** Error de negocio de requisiciones; getCode() es el estado HTTP sugerido (404, 409, 422). */
final class RequisicionException extends RuntimeException
{
    public function __construct(string $message, int $status = 422)
    {
        parent::__construct($message, $status);
    }
}
