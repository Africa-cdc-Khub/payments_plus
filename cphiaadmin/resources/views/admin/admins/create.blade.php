@extends('layouts.admin')

@section('title', 'Add Admin')
@section('page-title', 'Add Admin User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.admins.index') }}">Admin Users</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> This page will be integrated with the modal in the admin users list.
    </div>
    <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
@endsection

