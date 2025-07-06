@extends('layout')

@section('title')
{{ $title }}
@endsection

@section('styles')
<link rel="stylesheet" href="/assets/css/file-upload.css">
@endsection

@section('content')
<div class="file-upload-container">
    <div class="upload-card">
        <h1>{{ $title }}</h1>
        <p>Prueba el sistema de subida de archivos del framework MiniFramework PHP.</p>
        
        <div class="upload-info">
            <h4>📋 Información de Subida</h4>
            <ul>
                <li><strong>Tamaño Máximo:</strong> {{ $upload_limits['max_size'] }}</li>
                <li><strong>Tipos Permitidos:</strong> {{ $upload_limits['allowed_types'] }}</li>
                <li><strong>Validación:</strong> {{ $upload_limits['validation'] }}</li>
            </ul>
        </div>
        
        <div class="upload-section">
            <h4>📤 Subir Archivo</h4>
            <form id="uploadForm" action="{{ $api_base }}/api/upload" method="post" enctype="multipart/form-data">
                <input type="hidden" name="_csrf_token" value="{{ $csrf_token }}">
                
                <div class="form-group">
                    <label for="file">Seleccionar Archivo:</label>
                    <input type="file" id="file" name="file" accept="image/*" required>
                </div>
                
                <div class="form-group">
                    <label for="directory">Directorio:</label>
                    <select id="directory" name="directory">
                        <option value="uploads">uploads</option>
                        <option value="avatars">avatars</option>
                        <option value="documents">documents</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">📤 Subir Archivo</button>
                </div>
            </form>
        </div>
        
        <div class="upload-methods">
            <h4>🔗 Métodos de Prueba</h4>
            <div class="method-section">
                <h5>📝 Subida por Formulario</h5>
                <p>Usa el formulario de arriba para subir archivos mediante POST tradicional.</p>
            </div>
            
            <div class="method-section">
                <h5>⚡ Subida AJAX</h5>
                <button class="btn btn-secondary" id="ajaxUploadBtn">Probar Subida AJAX</button>
                <input type="file" id="ajaxFile" accept="image/*">
            </div>
        </div>
        
        <div class="files-section">
            <h4>📁 Archivos Subidos</h4>
            <div class="files-actions">
                <button class="btn btn-info" id="loadFilesBtn">🔄 Cargar Archivos</button>
                <button class="btn btn-secondary" id="clearFilesBtn">🗑️ Limpiar Lista</button>
            </div>
            <div id="filesList" class="files-list">
                <p class="no-files">Haz clic en "Cargar Archivos" para ver los archivos subidos.</p>
            </div>
        </div>
        
        <div id="uploadResult" class="upload-result"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="/assets/js/file-upload.js"></script>
@endsection
