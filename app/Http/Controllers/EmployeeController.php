<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Employees\DeleteEmployeeAction;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class EmployeeController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $employees = Employee::query()
            ->withCount('assets')
            ->orderBy('name')
            ->get(['id', 'email', 'name', 'phone']);

        return EmployeeResource::collection($employees);
    }

    /**
     * @param Employee $employee
     * @param DeleteEmployeeAction $action
     * @return Response
     */
    public function destroy(Employee $employee, DeleteEmployeeAction $action): Response
    {
        $action->handle($employee);

        return response()->noContent();
    }
}
