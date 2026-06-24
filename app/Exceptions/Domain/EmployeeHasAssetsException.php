<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class EmployeeHasAssetsException extends RuntimeException
{
    /**
     * @param Employee $employee
     */
    public function __construct(public readonly Employee $employee)
    {
        parent::__construct(message: "Cannot delete {$employee->email}: still has assigned assets.");
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error_code' => 'employee_has_assets',
        ], Response::HTTP_CONFLICT);
    }
}
