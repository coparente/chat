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

// Inicializa tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'))
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
    html: true,
    container: 'body',
    delay: {
        show: 200,
        hide: 100
    }
}));

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

// Dark Mode - Sistema Bootstrap 5
function setTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    localStorage.setItem('darkMode', theme);
    
    // Atualizar ícone do botão
    const icon = document.querySelector('#toggleTheme i');
    if (icon) {
        if (theme === 'dark') {
            icon.className = 'fas fa-sun';
        } else {
            icon.className = 'fas fa-moon';
        }
    }
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
}

// Auto-focus no primeiro campo
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email').focus();
    
    // Inicializar tema
    const savedTheme = localStorage.getItem('darkMode');
    if (savedTheme) {
        setTheme(savedTheme);
    } else {
        // Verificar preferência do sistema
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            setTheme('dark');
        } else {
            setTheme('light');
        }
    }

    // Adicionar evento ao botão de tema
    const toggleButton = document.getElementById('toggleTheme');
    if (toggleButton) {
        toggleButton.addEventListener('click', toggleTheme);
    }

    // Monitorar mudanças na preferência do sistema
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (!localStorage.getItem('darkMode')) {
                setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    console.log('Dark mode inicializado com sucesso na página de login!');
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