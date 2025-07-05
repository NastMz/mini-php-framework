@extends('layout')

@section('title')
{{ $title }}
@endsection

@section('styles')
<link rel="stylesheet" href="/assets/css/jwt-test.css">
@endsection

@section('content')
<div class="jwt-test-container" data-api-base="{{ $api_base }}">
    <div class="test-card">
        <h1>{{ $title }}</h1>
        <p>Prueba los endpoints de autenticación JWT del framework MiniFramework PHP.</p>
        
        <div class="demo-info">
            <h4>📋 Credenciales de Demostración</h4>
            <p><strong>Email:</strong> {{ $demo_credentials['email'] }}</p>
            <p><strong>Password:</strong> {{ $demo_credentials['password'] }}</p>
            <p><small>Estas credenciales están hardcodeadas en el AuthController para propósitos de demostración.</small></p>
        </div>
        
        <div class="endpoint-info">
            <h4>🔗 Endpoints Disponibles</h4>
            <ul>
                <li><strong>POST /auth/login</strong> - Iniciar sesión y obtener token JWT</li>
                <li><strong>GET /auth/me</strong> - Obtener información del usuario actual (requiere token)</li>
                <li><strong>POST /auth/refresh</strong> - Refrescar token JWT (requiere token válido)</li>
                <li><strong>POST /auth/logout</strong> - Cerrar sesión</li>
            </ul>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" value="{{ $demo_credentials['email'] }}" />
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" value="{{ $demo_credentials['password'] }}" />
        </div>
        
        <div class="form-group">
            <button class="btn" id="loginBtn">🔐 Iniciar Sesión</button>
            <button class="btn btn-secondary" id="getMeBtn" disabled>👤 Mi Información</button>
            <button class="btn btn-secondary" id="refreshBtn" disabled>🔄 Refrescar Token</button>
            <button class="btn btn-danger" id="logoutBtn" disabled>🚪 Cerrar Sesión</button>
        </div>
        
        <div id="tokenDisplay" class="token-display">
            <strong>Token JWT Actual:</strong>
            <span id="tokenValue"></span>
        </div>
        
        <div id="result" class="result"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="/assets/js/jwt-test.js"></script>
@endsection
