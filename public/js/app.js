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

// Aguarda o carregamento completo da página
$(document).ready(function() {
    // Inicializa Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        minimumInputLength: 2,
        language: 'pt-BR',
    });

    // Função para aumentar/diminuir fonte
    let fontSize = localStorage.getItem('fontSize') || 16;
    $('body').css('font-size', fontSize + 'px');

    // Função para salvar o tamanho da fonte
    function saveFontSize(size) {
        localStorage.setItem('fontSize', size);
        $('body').css('font-size', size + 'px');
    }

    $('#aumentarFonte').click(function() {
        if (fontSize < 20) {
            fontSize = parseInt(fontSize) + 2;
            saveFontSize(fontSize);
        }
    });

    $('#diminuirFonte').click(function() {
        if (fontSize > 12) {
            fontSize = parseInt(fontSize) - 2;
            saveFontSize(fontSize);
        }
    });

    // Debug - Log para verificar se o script está funcionando
    console.log('App.js carregado com sucesso');
    console.log('jQuery version:', $.fn.jquery);
});

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

    console.log('Dark mode inicializado com sucesso!');
});

// Função para limpar os campos de pesquisa
function limparCampos() {
    document.getElementById('numero_processo').value = '';
    document.getElementById('numero_guia').value = '';
}

// Função para validar CPF/CNPJ
$("#cpfcnpj").keydown(function () {
    try {
        $("#cpfcnpj").unmask();
    } catch (e) { }

    var tamanho = $("#cpfcnpj").val().length;

    if (tamanho < 11) {
        $("#cpfcnpj").mask("999.999.999-99");
    } else {
        $("#cpfcnpj").mask("99.999.999/9999-99");
    }
    
    var elem = this;
    setTimeout(function () {
        elem.selectionStart = elem.selectionEnd = 10000;
    }, 0);
    
    var currentValue = $(this).val();
    $(this).val('');
    $(this).val(currentValue);
});

// Mascara o cpfcnpj editar parte
$("#cpfcnpjEditar").keydown(function () {
    try {
        $("#cpfcnpjEditar").unmask();
    } catch (e) { }

    var tamanho = $("#cpfcnpjEditar").val().length;

    if (tamanho < 11) {
        $("#cpfcnpjEditar").mask("999.999.999-99");
    } else {
        $("#cpfcnpjEditar").mask("99.999.999/9999-99");
    }
    
    var elem = this;
    setTimeout(function () {
        elem.selectionStart = elem.selectionEnd = 10000;
    }, 0);
    
    var currentValue = $(this).val();
    $(this).val('');
    $(this).val(currentValue);
});

// Máscaras para formulários
$("#telefone, #telefoneEditar").mask("(00) 00000-0000");
$("#n_processo").mask("9999999-99.9999.9.99.9999");
$("#n_guia").mask("99999999-9/99");

