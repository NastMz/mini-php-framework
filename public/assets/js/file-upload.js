/**
 * File Upload Test Interface
 * JavaScript functionality for testing file upload endpoints
 */

let API_BASE = '';
let CSRF_TOKEN = '';

/**
 * Initialize the application when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get API base
    API_BASE = window.location.origin;
    
    // Get CSRF token from form
    const csrfInput = document.querySelector('input[name="_csrf_token"]');
    CSRF_TOKEN = csrfInput ? csrfInput.value : '';
    
    console.log('File Upload Test Page loaded');
    console.log('API Base:', API_BASE);
    console.log('CSRF Token:', CSRF_TOKEN ? 'Found' : 'Not found');
    
    // Setup event listeners
    setupEventListeners();
});

/**
 * Setup event listeners for form and buttons
 */
function setupEventListeners() {
    // Form submit handler
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', handleFormSubmit);
    }
    
    // AJAX upload button
    const ajaxUploadBtn = document.getElementById('ajaxUploadBtn');
    if (ajaxUploadBtn) {
        ajaxUploadBtn.addEventListener('click', handleAjaxUpload);
    }
    
    // Hidden file input for AJAX upload
    const ajaxFile = document.getElementById('ajaxFile');
    if (ajaxFile) {
        ajaxFile.addEventListener('change', handleAjaxFileSelected);
    }
}

/**
 * Handle form submission
 */
function handleFormSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    showResult('info', 'Subiendo archivo...', '');
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showResult('success', '✅ Archivo subido exitosamente', JSON.stringify(data, null, 2));
        } else {
            showResult('error', '❌ Error al subir archivo', data.message || JSON.stringify(data, null, 2));
        }
    })
    .catch(error => {
        showResult('error', '❌ Error de conexión', error.message);
        console.error('Upload error:', error);
    });
}

/**
 * Handle AJAX upload button click
 */
function handleAjaxUpload() {
    const ajaxFile = document.getElementById('ajaxFile');
    if (ajaxFile) {
        ajaxFile.click();
    }
}

/**
 * Handle file selection for AJAX upload
 */
function handleAjaxFileSelected(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('directory', 'uploads');
    formData.append('_csrf_token', CSRF_TOKEN);
    
    showResult('info', 'Subiendo archivo via AJAX...', '');
    
    // Show progress bar
    showProgressBar();
    
    const xhr = new XMLHttpRequest();
    
    // Progress tracking
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            updateProgressBar(percentComplete);
        }
    });
    
    // Response handling
    xhr.addEventListener('load', function() {
        hideProgressBar();
        
        try {
            const response = JSON.parse(xhr.responseText);
            if (xhr.status === 201 && response.success) {
                showResult('success', '✅ Archivo subido exitosamente via AJAX', JSON.stringify(response, null, 2));
            } else {
                showResult('error', '❌ Error al subir archivo via AJAX', response.message || JSON.stringify(response, null, 2));
            }
        } catch (error) {
            showResult('error', '❌ Error al procesar respuesta', xhr.responseText);
            console.error('JSON Parse error:', error);
        }
    });
    
    xhr.addEventListener('error', function() {
        hideProgressBar();
        showResult('error', '❌ Error de conexión AJAX', 'No se pudo conectar con el servidor');
    });
    
    xhr.open('POST', API_BASE + '/api/upload');
    
    // Set CSRF token header
    xhr.setRequestHeader('X-CSRF-Token', CSRF_TOKEN);
    
    xhr.send(formData);
}

/**
 * Show result message
 */
function showResult(type, title, details) {
    const resultDiv = document.getElementById('uploadResult');
    if (!resultDiv) return;
    
    resultDiv.className = `upload-result ${type}`;
    resultDiv.innerHTML = `
        <h4>${title}</h4>
        ${details ? `<pre>${details}</pre>` : ''}
    `;
    resultDiv.style.display = 'block';
    
    // Scroll to result
    resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Show progress bar
 */
function showProgressBar() {
    const resultDiv = document.getElementById('uploadResult');
    if (!resultDiv) return;
    
    resultDiv.className = 'upload-result info';
    resultDiv.innerHTML = `
        <h4>📤 Subiendo archivo...</h4>
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill" style="width: 0%">0%</div>
        </div>
    `;
    resultDiv.style.display = 'block';
}

/**
 * Update progress bar
 */
function updateProgressBar(percent) {
    const progressFill = document.getElementById('progressFill');
    if (progressFill) {
        const roundedPercent = Math.round(percent);
        progressFill.style.width = `${roundedPercent}%`;
        progressFill.textContent = `${roundedPercent}%`;
    }
}

/**
 * Hide progress bar
 */
function hideProgressBar() {
    const progressFill = document.getElementById('progressFill');
    if (progressFill && progressFill.parentElement) {
        progressFill.parentElement.style.display = 'none';
    }
}
