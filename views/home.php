@extends('layout')

@section('title')
Mini Framework PHP - Home
@endsection

@section('content')
<div class="container">
    <h1>🚀 Mini Framework PHP</h1>
    <p class="subtitle">¡Framework funcionando correctamente!</p>
    
    <div class="status">
        ✅ Estado: Operativo
    </div>
    
    <div class="features">
        <div class="feature">
            <strong>✨ Dependency Injection Container</strong> - PSR-11 Compatible
        </div>
        <div class="feature">
            <strong>🛣️ Router System</strong> - HTTP Method & Path Routing
        </div>
        <div class="feature">
            <strong>🔧 Middleware Support</strong> - PSR-15 Compatible
        </div>
        <div class="feature">
            <strong>� JWT Authentication</strong> - Token-based Security
        </div>
        <div class="feature">
            <strong>�📦 Autowiring</strong> - Automatic Dependency Resolution
        </div>
    </div>
    
    <div class="test-section">
        <h3>🧪 Pruebas Disponibles</h3>
        <div class="test-links">
            <a href="/jwt-test" class="test-link">
                <strong>🔐 JWT Authentication Test</strong>
                <span>Prueba los endpoints de autenticación JWT</span>
            </a>
        </div>
    </div>
    
    <div class="code">
        Request Info:<br>
        Method: {{ $method }}<br>
        Path: {{ $uri }}<br>
        Time: {{ $time }}
    </div>
    
    <p class="footer-text">
        Mini Framework PHP - Versión de Test
    </p>
</div>
@endsection
