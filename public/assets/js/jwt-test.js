/**
 * JWT Authentication Test Interface
 * JavaScript functionality for testing JWT endpoints
 */

let currentToken = null;
let API_BASE = '';

/**
 * Initialize the application when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get API base from data attribute or use default
    const container = document.querySelector('.jwt-test-container');
    API_BASE = container ? container.dataset.apiBase : window.location.origin;
    
    console.log('JWT Test Page loaded');
    console.log('API Base:', API_BASE);
    
    // Setup event listeners for buttons
    setupEventListeners();
});

/**
 * Setup event listeners for all buttons
 */
function setupEventListeners() {
    // Login button
    const loginBtn = document.getElementById('loginBtn');
    if (loginBtn) {
        loginBtn.addEventListener('click', login);
    }
    
    // Get me button
    const getMeBtn = document.getElementById('getMeBtn');
    if (getMeBtn) {
        getMeBtn.addEventListener('click', getMe);
    }
    
    // Refresh token button
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshToken);
    }
    
    // Logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }
}

/**
 * Make HTTP request to API endpoint
 * @param {string} method HTTP method
 * @param {string} endpoint API endpoint
 * @param {Object} data Request payload
 * @returns {Object} Response with status and data
 */
async function makeRequest(method, endpoint, data = null) {
    const headers = {
        'Content-Type': 'application/json',
    };
    
    if (currentToken) {
        headers['Authorization'] = `Bearer ${currentToken}`;
    }
    
    const options = {
        method: method,
        headers: headers,
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(`${API_BASE}${endpoint}`, options);
        const result = await response.json();
        return { status: response.status, data: result };
    } catch (error) {
        return { status: 0, data: { error: error.message } };
    }
}

/**
 * Display result in the UI
 * @param {Object} result Result object to display
 * @param {boolean} isSuccess Whether the result represents success
 */
function displayResult(result, isSuccess) {
    const resultDiv = document.getElementById('result');
    resultDiv.style.display = 'block';
    resultDiv.className = `result ${isSuccess ? 'success' : 'error'}`;
    resultDiv.textContent = JSON.stringify(result, null, 2);
}

/**
 * Update token display and enable/disable buttons
 * @param {string|null} token JWT token or null to clear
 */
function updateTokenDisplay(token) {
    const tokenDisplay = document.getElementById('tokenDisplay');
    const tokenValue = document.getElementById('tokenValue');
    
    if (token) {
        tokenDisplay.style.display = 'block';
        tokenValue.textContent = token;
        currentToken = token;
        
        // Habilitar botones autenticados
        document.getElementById('getMeBtn').disabled = false;
        document.getElementById('refreshBtn').disabled = false;
        document.getElementById('logoutBtn').disabled = false;
    } else {
        tokenDisplay.style.display = 'none';
        currentToken = null;
        
        // Deshabilitar botones autenticados
        document.getElementById('getMeBtn').disabled = true;
        document.getElementById('refreshBtn').disabled = true;
        document.getElementById('logoutBtn').disabled = true;
    }
}

/**
 * Handle login form submission
 */
async function login() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        displayResult({ error: 'Por favor ingresa email y contraseña' }, false);
        return;
    }
    
    const result = await makeRequest('POST', '/auth/login', { email, password });
    
    if (result.status === 200 && result.data.success) {
        displayResult(result.data, true);
        updateTokenDisplay(result.data.token);
    } else {
        displayResult(result.data, false);
        updateTokenDisplay(null);
    }
}

/**
 * Get current user information
 */
async function getMe() {
    const result = await makeRequest('GET', '/auth/me');
    
    if (result.status === 200 && result.data.success) {
        displayResult(result.data, true);
    } else {
        displayResult(result.data, false);
        if (result.status === 401) {
            updateTokenDisplay(null);
        }
    }
}

/**
 * Refresh JWT token
 */
async function refreshToken() {
    const result = await makeRequest('POST', '/auth/refresh');
    
    if (result.status === 200 && result.data.success) {
        displayResult(result.data, true);
        updateTokenDisplay(result.data.token);
    } else {
        displayResult(result.data, false);
        if (result.status === 401) {
            updateTokenDisplay(null);
        }
    }
}

/**
 * Handle logout
 */
async function logout() {
    const result = await makeRequest('POST', '/auth/logout');
    
    displayResult(result.data, result.status === 200);
    updateTokenDisplay(null);
}
