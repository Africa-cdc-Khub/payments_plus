<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="CPHIA 2025 Registrations API",
 *     version="1.0.0",
 *     description="API for managing conference registrations (localhost only)",
 *     @OA\Contact(
 *         email="admin@cphia2025.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local Development Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="localhost",
 *     type="apiKey",
 *     in="header",
 *     name="X-Forwarded-For",
 *     description="Only accessible from localhost (127.0.0.1, localhost, ::1)"
 * )
 */
class RegistrationApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/registrations",
     *     summary="List registrations with filters",
     *     description="Fetch registrations with various filtering options. Only accessible from localhost.",
     *     operationId="getRegistrations",
     *     tags={"Registrations"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "approved", "rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="payment_status",
     *         in="query",
     *         description="Filter by payment status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "paid", "failed"})
     *     ),
     *     @OA\Parameter(
     *         name="package_id",
     *         in="query",
     *         description="Filter by package ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="delegate_only",
     *         in="query",
     *         description="Show only delegates",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="created_from",
     *         in="query",
     *         description="Filter from date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="created_to",
     *         in="query",
     *         description="Filter to date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort by field",
     *         required=false,
     *         @OA\Schema(type="string", default="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Results per page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=57),
     *                     @OA\Property(property="status", type="string", example="approved"),
     *                     @OA\Property(property="payment_status", type="string", example="pending"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=123),
     *                         @OA\Property(property="full_name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="phone", type="string", example="+1234567890"),
     *                         @OA\Property(property="organization", type="string", example="ABC Corp"),
     *                         @OA\Property(property="country", type="string", example="USA")
     *                     ),
     *                     @OA\Property(property="package", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Standard Package"),
     *                         @OA\Property(property="price", type="string", example="500.00")
     *                     ),
     *                     @OA\Property(property="amount", type="string", example="500.00"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-08 10:30:45"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-08 12:15:30")
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="total", type="integer", example=150),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=15)
     *             ),
     *             @OA\Property(property="filters", type="object",
     *                 @OA\Property(property="status", type="string", nullable=true),
     *                 @OA\Property(property="payment_status", type="string", nullable=true),
     *                 @OA\Property(property="package_id", type="integer", nullable=true),
     *                 @OA\Property(property="search", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied - not from localhost",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Access denied. This endpoint is only accessible from localhost.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Failed to fetch registrations"),
     *             @OA\Property(property="message", type="string", example="Error details")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Check if request is from localhost
        $allowedHosts = ['127.0.0.1', 'localhost', '::1'];
        $requestIp = $request->ip();
        
        if (!in_array($requestIp, $allowedHosts)) {
            Log::warning("Registration API access denied from IP: {$requestIp}");
            return response()->json([
                'success' => false,
                'error' => 'Access denied. This endpoint is only accessible from localhost.'
            ], 403);
        }

        try {
            // Start query
            $query = Registration::with(['user', 'package', 'payment']);

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            if ($request->has('package_id')) {
                $query->where('package_id', $request->package_id);
            }

            if ($request->has('delegate_only')) {
                $delegatePackageId = config('app.delegate_package_id');
                $query->where('package_id', $delegatePackageId);
            }

            // Search by user name or email
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Date range filters
            if ($request->has('created_from')) {
                $query->where('created_at', '>=', $request->created_from);
            }

            if ($request->has('created_to')) {
                $query->where('created_at', '<=', $request->created_to);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->get('per_page', 15), 100); // Max 100 per page
            $registrations = $query->paginate($perPage);

            // Transform data
            $data = $registrations->map(function($registration) {
                return [
                    'id' => $registration->id,
                    'status' => $registration->status,
                    'payment_status' => $registration->payment_status,
                    'user' => [
                        'id' => $registration->user->id,
                        'full_name' => $registration->user->full_name,
                        'first_name' => $registration->user->first_name ?? null,
                        'last_name' => $registration->user->last_name ?? null,
                        'email' => $registration->user->email,
                        'phone' => $registration->user->phone ?? null,
                        'title' => $registration->user->title ?? null,
                        'organization' => $registration->user->organization ?? null,
                        'position' => $registration->user->position ?? null,
                        'country' => $registration->user->country ?? null,
                        'city' => $registration->user->city ?? null,
                        'address' => $registration->user->address ?? null,
                        'delegate_category' => $registration->user->delegate_category ?? null,
                        'dietary_requirements' => $registration->user->dietary_requirements ?? null,
                        'special_needs' => $registration->user->special_needs ?? null,
                        'requires_visa' => $registration->user->requires_visa ?? false,
                    ],
                    'package' => [
                        'id' => $registration->package->id,
                        'name' => $registration->package->name,
                        'price' => $registration->package->price,
                        'description' => $registration->package->description ?? null,
                    ],
                    'payment' => $registration->payment ? [
                        'id' => $registration->payment->id,
                        'transaction_id' => $registration->payment->transaction_id ?? null,
                        'payment_method' => $registration->payment->payment_method ?? null,
                        'amount' => $registration->payment->amount,
                        'currency' => $registration->payment->currency ?? 'USD',
                        'status' => $registration->payment->status,
                        'paid_at' => $registration->payment->paid_at ? $registration->payment->paid_at->toDateTimeString() : null,
                        'payment_reference' => $registration->payment->payment_reference ?? null,
                    ] : null,
                    'amount' => $registration->total_amount,
                    'rejection_reason' => $registration->rejection_reason ?? null,
                    'created_at' => $registration->created_at ? $registration->created_at->toDateTimeString() : null,
                    'updated_at' => $registration->updated_at ? $registration->updated_at->toDateTimeString() : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'total' => $registrations->total(),
                    'per_page' => $registrations->perPage(),
                    'current_page' => $registrations->currentPage(),
                    'last_page' => $registrations->lastPage(),
                    'from' => $registrations->firstItem(),
                    'to' => $registrations->lastItem(),
                ],
                'filters' => [
                    'status' => $request->status,
                    'payment_status' => $request->payment_status,
                    'package_id' => $request->package_id,
                    'search' => $request->search,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error("Failed to fetch registrations via API: " . $e->getMessage(), [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch registrations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/registrations/{id}",
     *     summary="Get a single registration",
     *     description="Fetch detailed information for a specific registration. Only accessible from localhost.",
     *     operationId="getRegistrationById",
     *     tags={"Registrations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registration ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=57),
     *                 @OA\Property(property="status", type="string", example="approved"),
     *                 @OA\Property(property="payment_status", type="string", example="pending"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=123),
     *                     @OA\Property(property="full_name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="organization", type="string", example="ABC Corp"),
     *                     @OA\Property(property="country", type="string", example="USA"),
     *                     @OA\Property(property="title", type="string", example="Mr"),
     *                     @OA\Property(property="delegate_category", type="string", example="Government Official")
     *                 ),
     *                 @OA\Property(property="package", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Standard Package"),
     *                     @OA\Property(property="price", type="string", example="500.00"),
     *                     @OA\Property(property="description", type="string", example="Standard package description")
     *                 ),
     *                 @OA\Property(property="amount", type="string", example="500.00"),
     *                 @OA\Property(property="rejection_reason", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-08 10:30:45"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-08 12:15:30")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied - not from localhost",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Access denied. This endpoint is only accessible from localhost.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registration not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Registration not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Failed to fetch registration"),
     *             @OA\Property(property="message", type="string", example="Error details")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        // Check if request is from localhost
        $allowedHosts = ['127.0.0.1', 'localhost', '::1'];
        $requestIp = $request->ip();
        
        if (!in_array($requestIp, $allowedHosts)) {
            Log::warning("Registration API access denied from IP: {$requestIp}");
            return response()->json([
                'success' => false,
                'error' => 'Access denied. This endpoint is only accessible from localhost.'
            ], 403);
        }

        try {
            $registration = Registration::with(['user', 'package', 'payment'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $registration->id,
                    'status' => $registration->status,
                    'payment_status' => $registration->payment_status,
                    'user' => [
                        'id' => $registration->user->id,
                        'full_name' => $registration->user->full_name,
                        'first_name' => $registration->user->first_name ?? null,
                        'last_name' => $registration->user->last_name ?? null,
                        'email' => $registration->user->email,
                        'phone' => $registration->user->phone ?? null,
                        'title' => $registration->user->title ?? null,
                        'organization' => $registration->user->organization ?? null,
                        'position' => $registration->user->position ?? null,
                        'country' => $registration->user->country ?? null,
                        'city' => $registration->user->city ?? null,
                        'address' => $registration->user->address ?? null,
                        'delegate_category' => $registration->user->delegate_category ?? null,
                        'dietary_requirements' => $registration->user->dietary_requirements ?? null,
                        'special_needs' => $registration->user->special_needs ?? null,
                        'requires_visa' => $registration->user->requires_visa ?? false,
                    ],
                    'package' => [
                        'id' => $registration->package->id,
                        'name' => $registration->package->name,
                        'price' => $registration->package->price,
                        'description' => $registration->package->description ?? null,
                    ],
                    'payment' => $registration->payment ? [
                        'id' => $registration->payment->id,
                        'transaction_id' => $registration->payment->transaction_id ?? null,
                        'payment_method' => $registration->payment->payment_method ?? null,
                        'amount' => $registration->payment->amount,
                        'currency' => $registration->payment->currency ?? 'USD',
                        'status' => $registration->payment->status,
                        'paid_at' => $registration->payment->paid_at ? $registration->payment->paid_at->toDateTimeString() : null,
                        'payment_reference' => $registration->payment->payment_reference ?? null,
                        'processor_response' => $registration->payment->processor_response ?? null,
                        'created_at' => $registration->payment->created_at ? $registration->payment->created_at->toDateTimeString() : null,
                        'updated_at' => $registration->payment->updated_at ? $registration->payment->updated_at->toDateTimeString() : null,
                    ] : null,
                    'amount' => $registration->total_amount,
                    'rejection_reason' => $registration->rejection_reason ?? null,
                    'created_at' => $registration->created_at ? $registration->created_at->toDateTimeString() : null,
                    'updated_at' => $registration->updated_at ? $registration->updated_at->toDateTimeString() : null,
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Registration not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error("Failed to fetch registration via API: " . $e->getMessage(), [
                'registration_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch registration',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/registrations/stats",
     *     summary="Get registration statistics",
     *     description="Fetch comprehensive statistics about registrations including status counts, payment totals, and revenue. Only accessible from localhost.",
     *     operationId="getRegistrationStats",
     *     tags={"Registrations"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=150, description="Total number of registrations"),
     *                 @OA\Property(property="by_status", type="object",
     *                     @OA\Property(property="pending", type="integer", example=45),
     *                     @OA\Property(property="approved", type="integer", example=80),
     *                     @OA\Property(property="rejected", type="integer", example=25)
     *                 ),
     *                 @OA\Property(property="by_payment_status", type="object",
     *                     @OA\Property(property="pending", type="integer", example=60),
     *                     @OA\Property(property="paid", type="integer", example=85),
     *                     @OA\Property(property="failed", type="integer", example=5)
     *                 ),
     *                 @OA\Property(property="delegates", type="object",
     *                     @OA\Property(property="total", type="integer", example=50),
     *                     @OA\Property(property="pending", type="integer", example=15),
     *                     @OA\Property(property="approved", type="integer", example=30),
     *                     @OA\Property(property="rejected", type="integer", example=5)
     *                 ),
     *                 @OA\Property(property="revenue", type="object",
     *                     @OA\Property(property="total", type="number", format="float", example=127500.00, description="Total revenue from paid registrations"),
     *                     @OA\Property(property="pending", type="number", format="float", example=45000.00, description="Pending revenue")
     *                 )
     *             ),
     *             @OA\Property(property="generated_at", type="string", format="date-time", example="2025-10-08 12:30:45")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied - not from localhost",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Access denied. This endpoint is only accessible from localhost.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Failed to fetch statistics"),
     *             @OA\Property(property="message", type="string", example="Error details")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request)
    {
        // Check if request is from localhost
        $allowedHosts = ['127.0.0.1', 'localhost', '::1'];
        $requestIp = $request->ip();
        
        if (!in_array($requestIp, $allowedHosts)) {
            Log::warning("Registration API access denied from IP: {$requestIp}");
            return response()->json([
                'success' => false,
                'error' => 'Access denied. This endpoint is only accessible from localhost.'
            ], 403);
        }

        try {
            $delegatePackageId = config('app.delegate_package_id');

            $stats = [
                'total' => Registration::count(),
                'by_status' => [
                    'pending' => Registration::where('status', 'pending')->count(),
                    'approved' => Registration::where('status', 'approved')->count(),
                    'rejected' => Registration::where('status', 'rejected')->count(),
                ],
                'by_payment_status' => [
                    'pending' => Registration::where('payment_status', 'pending')->count(),
                    'completed' => Registration::where('payment_status', 'completed')->count(),
                    'failed' => Registration::where('payment_status', 'failed')->count(),
                ],
                'delegates' => [
                    'total' => Registration::where('package_id', $delegatePackageId)->count(),
                    'pending' => Registration::where('package_id', $delegatePackageId)
                        ->where('status', 'pending')->count(),
                    'approved' => Registration::where('package_id', $delegatePackageId)
                        ->where('status', 'approved')->count(),
                    'rejected' => Registration::where('package_id', $delegatePackageId)
                        ->where('status', 'rejected')->count(),
                ],
                'revenue' => [
                    'total' => Registration::where('payment_status', 'completed')->sum('total_amount'),
                    'pending' => Registration::where('payment_status', 'pending')->sum('total_amount'),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'generated_at' => now()->toDateTimeString(),
            ], 200);

        } catch (\Exception $e) {
            Log::error("Failed to fetch registration stats via API: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
