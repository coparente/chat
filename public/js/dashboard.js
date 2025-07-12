// Dashboard JavaScript - ChatSerpro
document.addEventListener('DOMContentLoaded', function() {
    // Toggle do menu
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const topbar = document.querySelector('.topbar');

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                if (topbar) {
                    topbar.classList.toggle('expanded');
                }
            }
        });
    }

    // Auto-hide sidebar em mobile
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('show');
            if (topbar) {
                topbar.classList.remove('expanded');
            }
        } else {
            sidebar.classList.remove('show');
        }
    });

    // Atualizar estatísticas em tempo real (opcional)
    function atualizarEstatisticas() {
        const baseUrl = window.location.origin + window.location.pathname.replace('/dashboard', '');
        
        fetch(baseUrl + '/dashboard/estatisticas')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar valores na tela
                    console.log('Estatísticas atualizadas:', data.dados);
                }
            })
            .catch(error => console.error('Erro ao atualizar estatísticas:', error));
    }

    // Atualizar a cada 30 segundos
    setInterval(atualizarEstatisticas, 30000);
});