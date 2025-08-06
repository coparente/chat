// App.js - Sistema ChatSerpro

// Função para alterar o tipo de anexo
document.querySelectorAll('.tipo-anexo').forEach(select => {
    select.addEventListener('change', function() {
        const campoArquivo = this.closest('.modal-body').querySelector('.campo-arquivo');
        const inputArquivo = campoArquivo.querySelector('input[type="file"]');
        
        if (this.value === 'text') {
            campoArquivo.style.display = 'none';
            inputArquivo.removeAttribute('required');
        } else {
            campoArquivo.style.display = 'block';
            inputArquivo.setAttribute('required', 'required');
        }
    });
});

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

// Dark Mode - Sistema Bootstrap 5 (funciona sem jQuery)
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

// Inicializar tema ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    // Verificar preferência salva
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

    // Adicionar evento ao botão
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

});



