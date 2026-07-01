@extends('admin.layouts.app')

@section('title', 'Create Notification')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Create Notification</h1>
    <p class="text-muted">Draft a notification to users, staff, or all recipients.</p>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.notifications.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="5" required>{{ old('message') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Recipient Type</label>
                <select name="recipient_type" class="form-select" required>
                    <option value="users" {{ old('recipient_type') === 'users' ? 'selected' : '' }}>Users</option>
                    <option value="staff" {{ old('recipient_type') === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="all" {{ old('recipient_type') === 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Delivery Channels</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="notification_method[]" value="email" id="methodEmail" {{ in_array('email', old('notification_method', [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="methodEmail">Email</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="notification_method[]" value="sms" id="methodSms" {{ in_array('sms', old('notification_method', [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="methodSms">SMS</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="notification_method[]" value="push" id="methodPush" {{ in_array('push', old('notification_method', [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="methodPush">Push</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Send Notification</button>
        </form>
    </div>
</div>
@endsection
