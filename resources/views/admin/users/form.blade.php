@extends('layouts.admin')
@section('title', $user->exists ? 'Edit admin' : 'Add admin')
@section('heading', $user->exists ? 'Edit admin' : 'Add admin')

@section('content')
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="max-w-lg space-y-4 rounded-2xl bg-paper p-6 shadow-card">
    @csrf
    @if($user->exists)@method('PUT')@endif
    <x-admin.input name="name" label="Name" :value="$user->name" :required="true" />
    <x-admin.input name="email" type="email" label="Email" :value="$user->email" :required="true" />
    <x-admin.input name="password" type="password" label="{{ $user->exists ? 'New password (leave blank to keep)' : 'Password' }}" :required="! $user->exists" autocomplete="new-password" help="At least 10 characters." />
    <x-admin.input name="password_confirmation" type="password" label="Repeat password" autocomplete="new-password" />
    <div class="flex gap-3"><x-button type="submit">Save</x-button><x-button :href="route('admin.users.index')" variant="outline">Cancel</x-button></div>
</form>
@endsection
