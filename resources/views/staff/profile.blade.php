<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile - GardenHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Profile</h1>
        <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('staff.profile.update') }}" class="card shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            <h2 class="h5 mb-3">Personal information</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" name="name" class="form-control" value="{{ old('name', $staff->name) }}" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}" required>
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input id="phone" name="phone" class="form-control" value="{{ old('phone', $staff->phone) }}">
                    @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="branch" class="form-label">Assigned branch</label>
                    <select id="branch" name="branch_id" class="form-select">
                        <option value="">Select branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $staff->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Your selected branch must match the branch nearest to your current location.</div>
                    @error('branch_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <h2 class="h5 mt-4 mb-3">Address</h2>
            <div class="row g-3">
                @foreach([
                    ['house_no', 'House No.'],
                    ['street', 'Street'],
                    ['city', 'City'],
                    ['state', 'State'],
                    ['pincode', 'Pincode'],
                    ['country', 'Country'],
                ] as [$field, $label])
                    <div class="col-md-6">
                        <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" class="form-control" value="{{ old($field, $staff->{$field}) }}">
                        @error($field)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                @endforeach
                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3">{{ old('address', $staff->address) }}</textarea>
                    @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <h2 class="h5 mt-4 mb-3">Current location</h2>
            <div class="border rounded p-3 bg-light">
                <button type="button" id="use-current-location" class="btn btn-outline-primary">
                    <i class="fas fa-location-crosshairs me-1"></i> Use My Current Location
                </button>
                <div id="location-status" class="small mt-3" role="status" aria-live="polite">
                    @if($staff->latitude !== null && $staff->longitude !== null)
                        Current Location: Saved<br>
                        Latitude: {{ $staff->latitude }}<br>
                        Longitude: {{ $staff->longitude }}
                    @else
                        Current Location: Not Set
                    @endif
                </div>
                <div id="branch-verification" class="small mt-2" role="status" aria-live="polite"></div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
        </div>
    </form>
</main>
<script>
    (() => {
        const button = document.getElementById('use-current-location');
        const branch = document.getElementById('branch');
        const status = document.getElementById('location-status');
        const verification = document.getElementById('branch-verification');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const messages = {
            denied: 'Location permission was denied. You can enter your address manually.',
            unavailable: 'Unable to determine your current location. Please try again or enter your address manually.',
            timeout: 'Location detection timed out. Please try again.',
        };

        button.addEventListener('click', () => {
            if (!branch.value) {
                verification.textContent = 'Select a branch before verifying your current location.';
                return;
            }

            if (!navigator.geolocation) {
                status.textContent = 'This browser does not support geolocation. You can enter your address manually.';
                return;
            }

            button.disabled = true;
            status.textContent = 'Requesting location permission...';

            navigator.geolocation.getCurrentPosition(async (position) => {
                const coordinates = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                };

                try {
                    const response = await fetch('{{ route('staff.profile.location') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ ...coordinates, branch_id: branch.value }),
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        const error = new Error(payload.message || 'The location could not be saved.');
                        error.payload = payload;
                        throw error;
                    }

                    status.textContent = `Current location saved. Latitude: ${payload.latitude.toFixed(6)}, Longitude: ${payload.longitude.toFixed(6)}`;
                    verification.textContent = `Nearest branch: ${payload.nearest_branch} (${payload.distance_km} km). Branch verified.`;
                } catch (error) {
                    if (error.payload?.nearest_branch) {
                        verification.textContent = `You are closest to ${error.payload.nearest_branch}. You selected ${error.payload.selected_branch}. Please select ${error.payload.nearest_branch}.`;
                    } else {
                        verification.textContent = error.message || 'The location could not be saved. Please try again.';
                    }
                } finally {
                    button.disabled = false;
                }
            }, (error) => {
                status.textContent = messages[error.code === 1 ? 'denied' : error.code === 3 ? 'timeout' : 'unavailable'];
                verification.textContent = 'Location permission is required to verify your branch.';
                button.disabled = false;
            }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
        });

        branch.addEventListener('change', () => {
            verification.textContent = 'Branch changed. Verify your current location again before saving the assignment.';
        });
    })();
</script>
</body>
</html>