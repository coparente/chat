<?php
/**
 * Menu Sidebar Dinâmico
 * Detecta automaticamente a página atual e marca o item correspondente como ativo
 */

// Obter a URL atual
$currentUrl = $_SERVER['REQUEST_URI'];
$currentPath = parse_url($currentUrl, PHP_URL_PATH);

// Função para verificar se o item do menu está ativo
function isMenuActive($path, $currentPath) {
    // Remover a base URL se existir
    $basePath = parse_url(URL, PHP_URL_PATH);
    if ($basePath && strpos($currentPath, $basePath) === 0) {
        $currentPath = substr($currentPath, strlen($basePath));
    }
    
    // Remover barra inicial se existir
    $currentPath = ltrim($currentPath, '/');
    $path = ltrim($path, '/');
    
    // Verificar se o caminho atual corresponde ao item do menu
    if ($path === $currentPath) {
        return true;
    }
    
    // Verificar se é uma subpágina (ex: chat/painel, usuarios/editar, etc.)
    if ($path && strpos($currentPath, $path) === 0) {
        return true;
    }
    
    return false;
}

// Função para gerar classe CSS do item do menu
function getMenuClass($path, $currentPath) {
    $baseClass = 'nav-link';
    return isMenuActive($path, $currentPath) ? $baseClass . ' active' : $baseClass;
}

// Função para gerar ícone do item do menu
function getMenuIcon($path) {
    $icons = [
        'dashboard' => 'fas fa-chart-line',
        'chat' => 'fas fa-comments',
        'contatos' => 'fas fa-address-book',
        'relatorios' => 'fas fa-chart-bar',
        'usuarios' => 'fas fa-users',
        'configuracoes' => 'fas fa-cog',
        'departamentos' => 'fas fa-building',
        'templates' => 'fas fa-file-alt',
        'mensagens-automaticas' => 'fas fa-robot',
        'sessoes' => 'fas fa-plug',
        'logs' => 'fas fa-list-alt'
    ];
    
    // Extrair o primeiro segmento do caminho
    $segments = explode('/', trim($path, '/'));
    $firstSegment = $segments[0] ?? '';
    
    return $icons[$firstSegment] ?? 'fas fa-circle';
}

// Função para gerar título do item do menu
function getMenuTitle($path) {
    $titles = [
        'dashboard' => 'Dashboard',
        'chat' => 'Chat',
        'contatos' => 'Contatos',
        'relatorios' => 'Relatórios',
        'usuarios' => 'Usuários',
        'configuracoes' => 'Configurações',
        'departamentos' => 'Departamentos',
        'templates' => 'Templates',
        'mensagens-automaticas' => 'Mensagens Automáticas',
        'sessoes' => 'Sessões WhatsApp',
        'logs' => 'Logs do Sistema'
    ];
    
    // Extrair o primeiro segmento do caminho
    $segments = explode('/', trim($path, '/'));
    $firstSegment = $segments[0] ?? '';
    
    return $titles[$firstSegment] ?? ucfirst($firstSegment);
}

// Definir estrutura do menu
$menuItems = [
    // Item sempre visível
    [
        'path' => 'dashboard',
        'title' => 'Dashboard',
        'icon' => 'fas fa-chart-line',
        'permissions' => ['admin', 'supervisor', 'atendente']
    ],
    
    // Chat - visível para todos os perfis
    [
        'path' => 'chat',
        'title' => 'Chat',
        'icon' => 'fas fa-comments',
        'permissions' => ['admin', 'supervisor', 'atendente']
    ],
    
    // Contatos - visível para todos os perfis
    [
        'path' => 'contatos',
        'title' => 'Contatos',
        'icon' => 'fas fa-address-book',
        'permissions' => ['admin', 'supervisor', 'atendente']
    ],
    
    // Relatórios - apenas admin e supervisor
    [
        'path' => 'relatorios',
        'title' => 'Relatórios',
        'icon' => 'fas fa-chart-bar',
        'permissions' => ['admin', 'supervisor']
    ],
    
    // Usuários - apenas admin e supervisor
    [
        'path' => 'usuarios',
        'title' => 'Usuários',
        'icon' => 'fas fa-users',
        'permissions' => ['admin', 'supervisor']
    ],
    
    // Departamentos - apenas admin
    [
        'path' => 'departamentos',
        'title' => 'Departamentos',
        'icon' => 'fas fa-building',
        'permissions' => ['admin']
    ],
    
    // Templates - apenas admin
    // [
    //     'path' => 'templates',
    //     'title' => 'Templates',
    //     'icon' => 'fas fa-file-alt',
    //     'permissions' => ['admin']
    // ],
    
    // Mensagens Automáticas - apenas admin
    // [
    //     'path' => 'mensagens-automaticas',
    //     'title' => 'Mensagens Automáticas',
    //     'icon' => 'fas fa-robot',
    //     'permissions' => ['admin']
    // ],
    
    // Sessões WhatsApp - apenas admin
    // [
    //     'path' => 'sessoes',
    //     'title' => 'Sessões WhatsApp',
    //     'icon' => 'fas fa-plug',
    //     'permissions' => ['admin']
    // ],
    
    // Logs - apenas admin
    // [
    //     'path' => 'logs',
    //     'title' => 'Logs do Sistema',
    //     'icon' => 'fas fa-list-alt',
    //     'permissions' => ['admin']
    // ],
    
    // Configurações - apenas admin
    [
        'path' => 'configuracoes',
        'title' => 'Configurações',
        'icon' => 'fas fa-cog',
        'permissions' => ['admin']
    ]
];

// Verificar perfil do usuário
$userProfile = $usuario['perfil'] ?? 'guest';
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fab fa-whatsapp"></i>
            <?= APP_NOME ?>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($menuItems as $item): ?>
            <?php 
            // Verificar se o usuário tem permissão para ver este item
            if (!in_array($userProfile, $item['permissions'])) {
                continue;
            }
            
            $isActive = isMenuActive($item['path'], $currentPath);
            $menuClass = getMenuClass($item['path'], $currentPath);
            ?>
            
            <div class="nav-item">
                <a href="<?= URL ?>/<?= $item['path'] ?>" class="<?= $menuClass ?>">
                    <i class="<?= $item['icon'] ?>"></i>
                    <?= $item['title'] ?>
                </a>
            </div>
        <?php endforeach; ?>
    </nav>
</aside>

<style>
/* RESET E SOBRESCRITA DE ESTILOS CONFLITANTES */
.sidebar-nav .nav-link {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 12px 16px !important;
    margin: 2px 8px !important;
    border-radius: 8px !important;
    text-decoration: none !important;
    color: var(--text-color, #333) !important;
    transition: all 0.3s ease !important;
    position: relative !important;
    background: transparent !important;
    transform: none !important;
}

.sidebar-nav .nav-link i {
    width: 20px !important;
    text-align: center !important;
    font-size: 16px !important;
}

/* ESTILOS PARA ITENS ATIVOS */
.sidebar-nav .nav-link.active {
    background-color: var(--primary-color,rgba(16, 133, 26, 0.88)) !important;
    color: white !important;
    transform: none !important;
}

.sidebar-nav .nav-link.active:hover {
    background-color: var(--primary-color-dark, rgba(16, 133, 26, 0.88)) !important;
    color: white !important;
    transform: none !important;
}

/* ESTILOS PARA ITENS INATIVOS */
.sidebar-nav .nav-link:not(.active) {
    background: transparent !important;
    color: var(--text-color, #333) !important;
    transform: none !important;
}

.sidebar-nav .nav-link:not(.active):hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: var(--primary-color, rgba(16, 133, 26, 0.88)) !important;
    transform: translateX(4px) !important;
}

/* INDICADOR VISUAL PARA ITEM ATIVO */
.sidebar-nav .nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 20px;
    background-color: var(--accent-color, #ffc107);
    border-radius: 0 2px 2px 0;
}

/* ANIMAÇÃO SUAVE PARA TRANSIÇÕES */
.sidebar-nav .nav-item {
    position: relative;
    transition: all 0.3s ease;
}

/* SOBRESCRITA PARA DARK MODE */
body.dark-mode .sidebar-nav .nav-link {
    color: var(--text-color, #fff) !important;
}

body.dark-mode .sidebar-nav .nav-link:not(.active):hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: var(--primary-color, rgba(16, 133, 26, 0.88)) !important;
}

body.dark-mode .sidebar-nav .nav-link.active {
    background-color: var(--primary-color, rgba(16, 133, 26, 0.88)) !important;
    color: white !important;
}

body.dark-mode .sidebar-nav .nav-link.active:hover {
    background-color: var(--primary-color-dark, rgba(16, 133, 26, 0.88)) !important;
    color: white !important;
}

/* SOBRESCRITA PARA DATA-BS-THEME */
[data-bs-theme="dark"] .sidebar-nav .nav-link {
    color: var(--text-color, #fff) !important;
}

[data-bs-theme="dark"] .sidebar-nav .nav-link:not(.active):hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: var(--primary-color, rgba(16, 133, 26, 0.88)) !important;
}

[data-bs-theme="dark"] .sidebar-nav .nav-link.active {
    background-color: var(--primary-color, rgba(16, 133, 26, 0.88)) !important;
    color: white !important;
}

[data-bs-theme="dark"] .sidebar-nav .nav-link.active:hover {
    background-color: var(--primary-color-dark, rgba(16, 133, 26, 0.88)) !important;
    color: white !important;
}
</style>

<script>
// Adicionar funcionalidade JavaScript para melhorar a experiência
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar efeito de hover mais suave
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            // Apenas aplicar transform se NÃO estiver ativo
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateX(4px)';
            }
        });
        
        link.addEventListener('mouseleave', function() {
            // Apenas remover transform se NÃO estiver ativo
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateX(0)';
            }
        });
    });
    
    // Adicionar tooltip para itens do menu (opcional)
    navLinks.forEach(link => {
        const title = link.textContent.trim();
        link.setAttribute('title', title);
    });
});
</script>