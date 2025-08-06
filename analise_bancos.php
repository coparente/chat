<?php
/**
 * Script de Análise Comparativa dos Bancos de Dados
 * Compara as estruturas entre desenvolvimento e produção
 */

require_once 'app/autoload.php';

class AnaliseBancos
{
    private $dbDev;
    private $dbProd;
    
    public function __construct()
    {
        // Configurações de desenvolvimento
        $this->dbDev = new PDO(
            'mysql:host=localhost;port=3306;dbname=meu_framework;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Configurações de produção
        $this->dbProd = new PDO(
            'mysql:host=localhost;port=3306;dbname=copare52_chat;charset=utf8mb4',
            'copare52_chat',
            'YiYDW*3vLLKk',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    
    /**
     * Analisa as diferenças entre os bancos
     */
    public function analisarDiferencas()
    {
        echo "🔍 **ANÁLISE COMPARATIVA DOS BANCOS DE DADOS**\n\n";
        
        // 1. Verificar tabelas existentes
        $this->verificarTabelas();
        
        // 2. Verificar colunas das tabelas principais
        $this->verificarColunasPrincipais();
        
        // 3. Verificar dados críticos
        $this->verificarDadosCriticos();
        
        // 4. Verificar procedimentos e views
        $this->verificarProcedimentosViews();
        
        // 5. Verificar índices e constraints
        $this->verificarIndicesConstraints();
    }
    
    /**
     * Verifica tabelas existentes em ambos os bancos
     */
    private function verificarTabelas()
    {
        echo "📋 **1. VERIFICAÇÃO DE TABELAS**\n";
        echo str_repeat("-", 50) . "\n";
        
        $tabelasDev = $this->getTabelas($this->dbDev);
        $tabelasProd = $this->getTabelas($this->dbProd);
        
        echo "Tabelas em Desenvolvimento (" . count($tabelasDev) . "):\n";
        foreach ($tabelasDev as $tabela) {
            echo "  ✓ {$tabela}\n";
        }
        
        echo "\nTabelas em Produção (" . count($tabelasProd) . "):\n";
        foreach ($tabelasProd as $tabela) {
            echo "  ✓ {$tabela}\n";
        }
        
        $faltamEmProd = array_diff($tabelasDev, $tabelasProd);
        $faltamEmDev = array_diff($tabelasProd, $tabelasDev);
        
        if (!empty($faltamEmProd)) {
            echo "\n❌ **TABELAS FALTANDO EM PRODUÇÃO:**\n";
            foreach ($faltamEmProd as $tabela) {
                echo "  - {$tabela}\n";
            }
        }
        
        if (!empty($faltamEmDev)) {
            echo "\n❌ **TABELAS FALTANDO EM DESENVOLVIMENTO:**\n";
            foreach ($faltamEmDev as $tabela) {
                echo "  - {$tabela}\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Verifica colunas das tabelas principais
     */
    private function verificarColunasPrincipais()
    {
        echo "📊 **2. VERIFICAÇÃO DE COLUNAS PRINCIPAIS**\n";
        echo str_repeat("-", 50) . "\n";
        
        $tabelasPrincipais = [
            'conversas',
            'departamentos', 
            'usuarios',
            'contatos',
            'mensagens',
            'credenciais_serpro_departamento',
            'atendentes_departamento',
            'mensagens_automaticas_departamento'
        ];
        
        foreach ($tabelasPrincipais as $tabela) {
            $this->compararColunas($tabela);
        }
        
        echo "\n";
    }
    
    /**
     * Compara colunas de uma tabela específica
     */
    private function compararColunas($tabela)
    {
        try {
            $colunasDev = $this->getColunas($this->dbDev, $tabela);
            $colunasProd = $this->getColunas($this->dbProd, $tabela);
            
            if (empty($colunasDev) && empty($colunasProd)) {
                echo "⚠️  Tabela '{$tabela}' não existe em nenhum banco\n";
                return;
            }
            
            if (empty($colunasDev)) {
                echo "❌ Tabela '{$tabela}' não existe em desenvolvimento\n";
                return;
            }
            
            if (empty($colunasProd)) {
                echo "❌ Tabela '{$tabela}' não existe em produção\n";
                return;
            }
            
            $faltamEmProd = array_diff_key($colunasDev, $colunasProd);
            $faltamEmDev = array_diff_key($colunasProd, $colunasDev);
            
            if (!empty($faltamEmProd) || !empty($faltamEmDev)) {
                echo "📋 **Tabela: {$tabela}**\n";
                
                if (!empty($faltamEmProd)) {
                    echo "  ❌ Colunas faltando em PRODUÇÃO:\n";
                    foreach ($faltamEmProd as $coluna => $tipo) {
                        echo "    - {$coluna} ({$tipo})\n";
                    }
                }
                
                if (!empty($faltamEmDev)) {
                    echo "  ❌ Colunas faltando em DESENVOLVIMENTO:\n";
                    foreach ($faltamEmDev as $coluna => $tipo) {
                        echo "    - {$coluna} ({$tipo})\n";
                    }
                }
                echo "\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Erro ao verificar tabela '{$tabela}': " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Verifica dados críticos
     */
    private function verificarDadosCriticos()
    {
        echo "📈 **3. VERIFICAÇÃO DE DADOS CRÍTICOS**\n";
        echo str_repeat("-", 50) . "\n";
        
        // Verificar departamentos
        $this->verificarDadosTabela('departamentos', 'id, nome, status');
        
        // Verificar usuários
        $this->verificarDadosTabela('usuarios', 'id, nome, email, perfil, status');
        
        // Verificar credenciais
        $this->verificarDadosTabela('credenciais_serpro_departamento', 'id, departamento_id, nome, status');
        
        // Verificar atendentes por departamento
        $this->verificarDadosTabela('atendentes_departamento', 'id, usuario_id, departamento_id, status');
        
        echo "\n";
    }
    
    /**
     * Verifica dados de uma tabela específica
     */
    private function verificarDadosTabela($tabela, $campos)
    {
        try {
            $sql = "SELECT {$campos} FROM {$tabela} ORDER BY id";
            
            $dadosDev = $this->dbDev->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $dadosProd = $this->dbProd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            
            echo "📋 **{$tabela}**\n";
            echo "  Desenvolvimento: " . count($dadosDev) . " registros\n";
            echo "  Produção: " . count($dadosProd) . " registros\n";
            
            if (count($dadosDev) != count($dadosProd)) {
                echo "  ⚠️  Diferença no número de registros!\n";
            }
            
            // Mostrar primeiros registros
            if (!empty($dadosDev)) {
                echo "  Primeiros registros (Dev):\n";
                foreach (array_slice($dadosDev, 0, 3) as $registro) {
                    echo "    - " . json_encode($registro) . "\n";
                }
            }
            
            if (!empty($dadosProd)) {
                echo "  Primeiros registros (Prod):\n";
                foreach (array_slice($dadosProd, 0, 3) as $registro) {
                    echo "    - " . json_encode($registro) . "\n";
                }
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Erro ao verificar dados da tabela '{$tabela}': " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Verifica procedimentos e views
     */
    private function verificarProcedimentosViews()
    {
        echo "🔧 **4. VERIFICAÇÃO DE PROCEDIMENTOS E VIEWS**\n";
        echo str_repeat("-", 50) . "\n";
        
        // Verificar procedures
        $proceduresDev = $this->getProcedures($this->dbDev);
        $proceduresProd = $this->getProcedures($this->dbProd);
        
        echo "Procedures em Desenvolvimento (" . count($proceduresDev) . "):\n";
        foreach ($proceduresDev as $proc) {
            echo "  ✓ {$proc}\n";
        }
        
        echo "\nProcedures em Produção (" . count($proceduresProd) . "):\n";
        foreach ($proceduresProd as $proc) {
            echo "  ✓ {$proc}\n";
        }
        
        // Verificar views
        $viewsDev = $this->getViews($this->dbDev);
        $viewsProd = $this->getViews($this->dbProd);
        
        echo "\nViews em Desenvolvimento (" . count($viewsDev) . "):\n";
        foreach ($viewsDev as $view) {
            echo "  ✓ {$view}\n";
        }
        
        echo "\nViews em Produção (" . count($viewsProd) . "):\n";
        foreach ($viewsProd as $view) {
            echo "  ✓ {$view}\n";
        }
        
        echo "\n";
    }
    
    /**
     * Verifica índices e constraints
     */
    private function verificarIndicesConstraints()
    {
        echo "🔗 **5. VERIFICAÇÃO DE ÍNDICES E CONSTRAINTS**\n";
        echo str_repeat("-", 50) . "\n";
        
        $tabelasPrincipais = ['conversas', 'departamentos', 'usuarios'];
        
        foreach ($tabelasPrincipais as $tabela) {
            $this->verificarIndicesTabela($tabela);
        }
        
        echo "\n";
    }
    
    /**
     * Verifica índices de uma tabela específica
     */
    private function verificarIndicesTabela($tabela)
    {
        try {
            $indicesDev = $this->getIndices($this->dbDev, $tabela);
            $indicesProd = $this->getIndices($this->dbProd, $tabela);
            
            if (!empty($indicesDev) || !empty($indicesProd)) {
                echo "📋 **Índices da tabela: {$tabela}**\n";
                
                if (!empty($indicesDev)) {
                    echo "  Desenvolvimento:\n";
                    foreach ($indicesDev as $indice) {
                        echo "    ✓ {$indice}\n";
                    }
                }
                
                if (!empty($indicesProd)) {
                    echo "  Produção:\n";
                    foreach ($indicesProd as $indice) {
                        echo "    ✓ {$indice}\n";
                    }
                }
                
                echo "\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Erro ao verificar índices da tabela '{$tabela}': " . $e->getMessage() . "\n";
        }
    }
    
    // Métodos auxiliares
    private function getTabelas($pdo)
    {
        $stmt = $pdo->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function getColunas($pdo, $tabela)
    {
        $stmt = $pdo->query("DESCRIBE {$tabela}");
        $colunas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $colunas[$row['Field']] = $row['Type'];
        }
        return $colunas;
    }
    
    private function getProcedures($pdo)
    {
        $stmt = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE()");
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    }
    
    private function getViews($pdo)
    {
        $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function getIndices($pdo, $tabela)
    {
        $stmt = $pdo->query("SHOW INDEX FROM {$tabela}");
        $indices = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $indices[] = $row['Key_name'] . " (" . $row['Column_name'] . ")";
        }
        return array_unique($indices);
    }
}

// Executar análise
try {
    $analise = new AnaliseBancos();
    $analise->analisarDiferencas();
} catch (Exception $e) {
    echo "❌ Erro na análise: " . $e->getMessage() . "\n";
} 