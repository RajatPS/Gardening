<?php

namespace App\Http\Controllers\Staff;

use App\Models\Branch;
use App\Services\BranchResolverService;
use App\Services\GeoapifyGeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StaffProfileController extends Controller
{
    public function edit()
    {
        $staff = Auth::user()->load('branch');
        $branches = Branch::query()->orderBy('name')->get();

        return view('staff.profile', compact('staff', 'branches'));
    }

    public function update(Request $request)
    {
        $staff = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff->id)],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:255'],
        ]);

        $selectedBranchId = $validated['branch_id'] ?? $staff->branch_id;
        $branchChanged = $selectedBranchId !== null && (int) $selectedBranchId !== (int) $staff->branch_id;
        $addressFields = ['address', 'house_no', 'street', 'city', 'state', 'pincode', 'country'];
        $addressChanged = collect($addressFields)->contains(function (string $field) use ($validated, $staff): bool {
            return trim((string) ($validated[$field] ?? '')) !== trim((string) ($staff->{$field} ?? ''));
        });

        if ($branchChanged && ! $addressChanged) {
            return redirect()->back()
                ->withErrors(['branch_id' => 'Verify your current location before changing your branch assignment.'])
                ->withInput();
        }

        $needsGeocoding = $addressChanged || $staff->latitude === null || $staff->longitude === null;
        if ($needsGeocoding && collect($addressFields)->contains(fn (string $field): bool => trim((string) ($validated[$field] ?? '')) !== '')) {
            $coordinates = app(GeoapifyGeocodingService::class)->geocode([
                $validated['address'] ?? '',
                $validated['house_no'] ?? '',
                $validated['street'] ?? '',
                $validated['city'] ?? '',
                $validated['state'] ?? '',
                $validated['pincode'] ?? '',
                $validated['country'] ?? '',
            ]);

            if ($coordinates === null) {
                return redirect()->back()
                    ->withErrors(['address' => 'We could not determine your location from this address. Please verify your PIN/address or use "Use My Current Location".'])
                    ->withInput();
            }

            if ($selectedBranchId !== null) {
                $selectedBranch = Branch::find($selectedBranchId);
                $nearest = (new BranchResolverService())->resolveNearestBranchWithDistance([
                    'lat' => $coordinates['latitude'],
                    'lon' => $coordinates['longitude'],
                ]);

                if ($selectedBranch === null || $nearest === null) {
                    return redirect()->back()
                        ->withErrors(['branch_id' => 'The selected branch could not be verified against this address.'])
                        ->withInput();
                }

                if ((int) $selectedBranch->id !== (int) $nearest['branch']->id) {
                    return redirect()->back()
                        ->withErrors(['branch_id' => 'Your address is closest to ' . $nearest['branch']->name . '. Please select that branch or use your current location.'])
                        ->withInput();
                }
            }

            $validated['latitude'] = $coordinates['latitude'];
            $validated['longitude'] = $coordinates['longitude'];
        }

        if ($branchChanged) {
            $validated['branch_id'] = $selectedBranchId;
        } else {
            unset($validated['branch_id']);
        }

        DB::transaction(function () use ($staff, $validated): void {
            $staff->update($validated);
        });

        return redirect()->route('staff.profile')->with('success', 'Profile updated successfully.');
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The provided location is invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $selectedBranch = Branch::find($validated['branch_id']);
        if ($selectedBranch === null) {
            return response()->json([
                'message' => 'The selected branch is no longer available.',
            ], 422);
        }
        $nearest = (new BranchResolverService())->resolveNearestBranchWithDistance([
            'lat' => (float) $validated['latitude'],
            'lon' => (float) $validated['longitude'],
        ]);

        if ($nearest === null) {
            return response()->json([
                'message' => 'No branches with valid coordinates are available for location verification.',
            ], 422);
        }

        if ((int) $selectedBranch->id !== (int) $nearest['branch']->id) {
            return response()->json([
                'message' => 'Your selected branch does not match your current location.',
                'nearest_branch' => $nearest['branch']->name,
                'selected_branch' => $selectedBranch->name,
                'distance_km' => round($nearest['distance_km'], 2),
            ], 422);
        }

        DB::transaction(function () use ($validated): void {
            Auth::user()->update([
                'branch_id' => $validated['branch_id'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Current location saved successfully.',
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'branch_id' => (int) $selectedBranch->id,
            'nearest_branch' => $nearest['branch']->name,
            'distance_km' => round($nearest['distance_km'], 2),
        ]);
    }
}