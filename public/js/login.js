// Toggle de senha
function togglePassword() {
    const senhaInput = document.getElementById('senha');
    const toggleIcon = document.querySelector('.password-toggle i');
    
    if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        senhaInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Seleção de status
document.querySelectorAll('.status-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.status-option').forEach(opt => opt.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input[type="radio"]').checked = true;
    });
});

// Loading no submit
document.getElementById('loginForm').addEventListener('submit', function() {
    const btnText = document.querySelector('.btn-text');
    const loading = document.querySelector('.loading');
    const submitBtn = document.querySelector('.btn-login');
    
    btnText.style.display = 'none';
    loading.classList.add('show');
    submitBtn.disabled = true;
});

// Toggle de tema (futuro)
function toggleTheme() {
    // Implementar posteriormente
    console.log('Toggle tema');
}

// Auto-focus no primeiro campo
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email').focus();
});

// Validação em tempo real
const emailInput = document.getElementById('email');
const senhaInput = document.getElementById('senha');

emailInput.addEventListener('blur', function() {
    const email = this.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
    }
});

senhaInput.addEventListener('input', function() {
    if (this.value.length > 0) {
        this.classList.remove('is-invalid');
    }
});