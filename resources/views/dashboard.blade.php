@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h4>Dashboard</h4>
            <p>Welcome, {{ auth()->user()->email }}</p>
        </div>
    </div>
@endsection
