<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Admin\Actions\ActivateUserAction;
use App\Modules\Admin\Actions\SuspendUserAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Admin\Actions\CreateUserAction;
use App\Modules\Admin\Actions\UpdateUserAction;
use App\Modules\Admin\Actions\DeleteUserAction;
use App\Modules\Admin\Http\Requests\StoreUserRequest;
use App\Modules\Admin\Http\Requests\UpdateUserRequest;
use App\Modules\Admin\Http\Requests\BookingReportRequest;
use App\Modules\Admin\Services\BookingReportService;
use App\Modules\Admin\Http\Requests\RevenueReportRequest;
use App\Modules\Admin\Services\ProviderReportService;
use App\Modules\Admin\Services\RevenueReportService;
use App\Modules\Admin\Services\ServiceReportService;
use App\Modules\Admin\Services\MostBookedServicesService;
class AdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()->when($request->role, fn ($q, $role) =>  $q->where('role', $role))
            ->when($request->status, fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully.',
            'data' => $users,
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully.',
            'data' => $user,
        ]);
    }

    public function suspend(User $user,   SuspendUserAction $action): JsonResponse {
        $user = $action->execute($user);

        return response()->json([
            'success' => true,
            'message' => 'User suspended successfully.',
            'data' => $user,
        ]);
    }

    public function activate( User $user, ActivateUserAction $action ): JsonResponse {
        $user = $action->execute($user);
        return response()->json([
            'success' => true,
            'message' => 'User activated successfully.',
            'data' => $user,
        ]);
    }
    public function store(  StoreUserRequest $request,  CreateUserAction $action): JsonResponse {
    $user = $action->execute($request->validated());

    return response()->json([
        'success' => true,
        'message' => 'User created successfully.',
        'data' => $user,
    ], 201);
}
public function update(UpdateUserRequest $request,User $user, UpdateUserAction $action): JsonResponse {
    $user = $action->execute($user, $request->validated());

    return response()->json([
        'success' => true,
        'message' => 'User updated successfully.',
        'data' => $user,
    ]);
}
public function destroy(User $user,DeleteUserAction $action): JsonResponse {
    $action->execute($user);
    return response()->json([
        'success' => true,
        'message' => 'User deleted successfully.',
        'data' => null,
    ]);
}
public function bookingReport( BookingReportRequest $request,  BookingReportService $service
): JsonResponse {
    return response()->json([
        'success' => true,
        'message' => 'Booking report retrieved successfully.',
        'data' => $service->get($request->validated()),
    ]);
}
public function revenue( BookingReportRequest $request, RevenueReportService $service): JsonResponse {
    return response()->json([
        'success' => true,
        'message' => 'Revenue report retrieved successfully.',
        'data' => $service->get($request->validated()),
    ]);
}
public function providerReport(  BookingReportRequest $request,ProviderReportService $service): JsonResponse {
    return response()->json([
        'success' => true,
        'message' => 'Provider report retrieved successfully.',
        'data' => $service->get($request->validated()),
    ]);
}
public function serviceReport( BookingReportRequest $request, ServiceReportService $service): JsonResponse {
    return response()->json([
        'success' => true,
        'message' => 'Service report retrieved successfully.',
        'data' => $service->get($request->validated()),
    ]);
}
public function mostBookedServices(BookingReportRequest $request, MostBookedServicesService $service): JsonResponse {
    return response()->json([
        'success' => true,
        'message' => 'Most booked services retrieved successfully.',
        'data' => $service->get($request->validated()),
    ]);
}
}