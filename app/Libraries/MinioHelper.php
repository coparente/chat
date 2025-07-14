<?php

/**
 * [ MINIOHELPER ] - Helper para gerenciar uploads no MinIO
 * 
 * @author Sistema de Chat SERPRO
 * @copyright 2025 TJGO
 * @version 1.0.0
 */

require_once 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class MinioHelper
{
    private static $s3Client = null;
    private static $bucket = null;
    private static $initialized = false;

    /**
     * Inicializa o cliente MinIO
     */
    public static function init()
    {
        if (self::$initialized) {
            return true;
        }

        try {
            // Configurações do MinIO (descomente e configure no app/configuracao.php)
            $endpoint = MINIO_ENDPOINT;
            $region = MINIO_REGION;
            $accessKeyId = MINIO_ACCESS_KEY;
            $secretAccessKey = MINIO_SECRET_KEY;
            self::$bucket = MINIO_BUCKET;

            self::$s3Client = new S3Client([
                'version' => 'latest',
                'region' => $region,
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $accessKeyId,
                    'secret' => $secretAccessKey,
                ],
                // Configurações específicas para MinIO
                'signature_version' => 'v4',
                'http' => [
                    'verify' => false, // Para desenvolvimento
                ],
                // Forçar estilo de URL path para MinIO
                '@http' => [
                    'timeout' => 60,
                    'connect_timeout' => 30,
                ],
            ]);

            // Verificar se o bucket existe
            if (!self::bucketExists()) {
                error_log("❌ Bucket '" . self::$bucket . "' não encontrado no MinIO");
                return false;
            }

            self::$initialized = true;
            error_log("✅ MinIO inicializado com sucesso");
            return true;

        } catch (Exception $e) {
            error_log("❌ Erro ao inicializar MinIO: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se o bucket existe
     */
    private static function bucketExists()
    {
        try {
            self::$s3Client->headBucket(['Bucket' => self::$bucket]);
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    /**
     * Faz upload de mídia para o MinIO
     */
    public static function uploadMidia($dadosArquivo, $tipoMidia, $mimeType, $nomeOriginal = null)
    {
        if (!self::init()) {
            return [
                'sucesso' => false,
                'erro' => 'Erro ao inicializar MinIO'
            ];
        }

        try {
            // Determinar extensão baseada no MIME type
            $extensao = self::obterExtensaoPorMimeType($mimeType);
            
            // CORREÇÃO: Sempre gerar nome único no padrão: tipo_hash.extensao
            $hashUnico = uniqid();
            $nomeArquivo = strtolower($tipoMidia) . '_' . $hashUnico . '.' . $extensao;
            
            // Log do nome original vs nome gerado
            if ($nomeOriginal) {
                error_log("📝 Nome original: {$nomeOriginal} -> Nome sanitizado: {$nomeArquivo}");
            }
            
            // Organizar por tipo e ano: tipo/ano/arquivo
            $ano = date('Y');
            $caminhoMinIO = self::mapearTipoParaPasta($tipoMidia) . '/' . $ano . '/' . $nomeArquivo;
            
            // Fazer upload para o MinIO
            $resultado = self::$s3Client->putObject([
                'Bucket' => self::$bucket,
                'Key' => $caminhoMinIO,
                'Body' => $dadosArquivo,
                'ContentType' => $mimeType,
                'Metadata' => [
                    'tipo_midia' => $tipoMidia,
                    'nome_original' => $nomeOriginal ?: $nomeArquivo,
                    'upload_timestamp' => time()
                ]
            ]);

            $urlMinIO = self::gerarUrlVisualizacao($caminhoMinIO);

            error_log("📁 Mídia salva no MinIO: {$caminhoMinIO} (Tipo: {$tipoMidia})");
            
            return [
                'sucesso' => true,
                'caminho_minio' => $caminhoMinIO,
                'url_minio' => $urlMinIO,
                'nome_arquivo' => $nomeArquivo,
                'tamanho' => strlen($dadosArquivo),
                'mime_type' => $mimeType,
                'bucket' => self::$bucket
            ];

        } catch (AwsException $e) {
            error_log("❌ Erro AWS ao fazer upload: " . $e->getMessage());
            return [
                'sucesso' => false,
                'erro' => 'Erro AWS: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            error_log("❌ Erro geral ao fazer upload: " . $e->getMessage());
            return [
                'sucesso' => false,
                'erro' => 'Erro: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Gera URL para visualização do arquivo
     */
    public static function gerarUrlVisualizacao($caminhoMinIO, $tempoExpiracao = 3600)
    {
        if (!self::init()) {
            return null;
        }

        try {
            // Método 1: Tentar com configurações padrão
            $comando = self::$s3Client->getCommand('GetObject', [
                'Bucket' => self::$bucket,
                'Key' => $caminhoMinIO
            ]);

            $request = self::$s3Client->createPresignedRequest($comando, "+{$tempoExpiracao} seconds");
            $url = (string) $request->getUri();
            
            // Verificar se a URL foi gerada corretamente
            if ($url && strpos($url, 'X-Amz-Signature') !== false) {
                return $url;
            }
            
            // Método 2: Tentar com configurações alternativas
            return self::gerarUrlAlternativa($caminhoMinIO, $tempoExpiracao);

        } catch (Exception $e) {
            error_log("❌ Erro ao gerar URL método 1: " . $e->getMessage());
            
            // Tentar método alternativo
            return self::gerarUrlAlternativa($caminhoMinIO, $tempoExpiracao);
        }
    }

    /**
     * Método alternativo de geração de URL para MinIO
     */
    private static function gerarUrlAlternativa($caminhoMinIO, $tempoExpiracao = 3600)
    {
        try {
            // Recriar cliente com configurações específicas para assinatura
            $endpoint = MINIO_ENDPOINT;
            $region = MINIO_REGION;
            $accessKeyId = MINIO_ACCESS_KEY;
            $secretAccessKey = MINIO_SECRET_KEY;

            $clienteAlternativo = new S3Client([
                'version' => 'latest',
                'region' => $region,
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $accessKeyId,
                    'secret' => $secretAccessKey,
                ],
                'signature_version' => 'v4',
                'signature_provider' => 'v4',
                'http' => [
                    'verify' => false,
                    'timeout' => 60,
                    'connect_timeout' => 30,
                ],
                's3' => [
                    'addressing_style' => 'path',
                ]
            ]);

            $comando = $clienteAlternativo->getCommand('GetObject', [
                'Bucket' => self::$bucket,
                'Key' => $caminhoMinIO
            ]);

            $request = $clienteAlternativo->createPresignedRequest($comando, "+{$tempoExpiracao} seconds");
            return (string) $request->getUri();

        } catch (Exception $e) {
            error_log("❌ Erro ao gerar URL método alternativo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Baixa arquivo do MinIO
     */
    public static function baixarArquivo($caminhoMinIO)
    {
        if (!self::init()) {
            return [
                'sucesso' => false,
                'erro' => 'Erro ao inicializar MinIO'
            ];
        }

        try {
            $resultado = self::$s3Client->getObject([
                'Bucket' => self::$bucket,
                'Key' => $caminhoMinIO
            ]);

            return [
                'sucesso' => true,
                'dados' => $resultado['Body']->getContents(),
                'content_type' => $resultado['ContentType'] ?? 'application/octet-stream',
                'tamanho' => $resultado['ContentLength'] ?? 0
            ];

        } catch (AwsException $e) {
            error_log("❌ Erro ao baixar arquivo: " . $e->getMessage());
            return [
                'sucesso' => false,
                'erro' => 'Erro AWS: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Exclui arquivo do MinIO
     */
    public static function excluirArquivo($caminhoMinIO)
    {
        if (!self::init()) {
            return false;
        }

        try {
            self::$s3Client->deleteObject([
                'Bucket' => self::$bucket,
                'Key' => $caminhoMinIO
            ]);

            error_log("🗑️ Arquivo excluído do MinIO: {$caminhoMinIO}");
            return true;

        } catch (AwsException $e) {
            error_log("❌ Erro ao excluir arquivo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista arquivos de um diretório no MinIO
     */
    public static function listarArquivos($prefixo = '', $limite = 1000)
    {
        if (!self::init()) {
            return [];
        }

        try {
            $resultado = self::$s3Client->listObjectsV2([
                'Bucket' => self::$bucket,
                'Prefix' => $prefixo,
                'MaxKeys' => $limite
            ]);

            $arquivos = [];
            if (isset($resultado['Contents'])) {
                foreach ($resultado['Contents'] as $objeto) {
                    $arquivos[] = [
                        'caminho' => $objeto['Key'],
                        'tamanho' => $objeto['Size'],
                        'modificado_em' => $objeto['LastModified']->format('Y-m-d H:i:s')
                    ];
                }
            }

            return $arquivos;

        } catch (AwsException $e) {
            error_log("❌ Erro ao listar arquivos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém estatísticas de uso do bucket
     */
    public static function obterEstatisticas()
    {
        if (!self::init()) {
            return [
                'total_arquivos' => 0,
                'tamanho_total' => 0,
                'por_tipo' => []
            ];
        }

        try {
            $resultado = self::$s3Client->listObjectsV2([
                'Bucket' => self::$bucket
            ]);

            $estatisticas = [
                'total_arquivos' => 0,
                'tamanho_total' => 0,
                'por_tipo' => [
                    'image' => ['count' => 0, 'size' => 0],
                    'audio' => ['count' => 0, 'size' => 0],
                    'video' => ['count' => 0, 'size' => 0],
                    'document' => ['count' => 0, 'size' => 0]
                ]
            ];

            if (isset($resultado['Contents'])) {
                foreach ($resultado['Contents'] as $objeto) {
                    $estatisticas['total_arquivos']++;
                    $estatisticas['tamanho_total'] += $objeto['Size'];

                    // Determinar tipo baseado no caminho
                    $caminho = $objeto['Key'];
                    foreach (['image', 'audio', 'video', 'document'] as $tipo) {
                        if (strpos($caminho, $tipo . '/') === 0) {
                            $estatisticas['por_tipo'][$tipo]['count']++;
                            $estatisticas['por_tipo'][$tipo]['size'] += $objeto['Size'];
                            break;
                        }
                    }
                }
            }

            return $estatisticas;

        } catch (AwsException $e) {
            error_log("❌ Erro ao obter estatísticas: " . $e->getMessage());
            return [
                'total_arquivos' => 0,
                'tamanho_total' => 0,
                'por_tipo' => []
            ];
        }
    }

    /**
     * Mapeia tipo de mídia para pasta no MinIO
     */
    private static function mapearTipoParaPasta($tipoMidia)
    {
        $mapeamento = [
            'image' => 'image',
            'audio' => 'audio', 
            'video' => 'video',
            'document' => 'document'
        ];

        return $mapeamento[$tipoMidia] ?? 'document';
    }

    /**
     * Obtém extensão de arquivo baseada no MIME type
     */
    private static function obterExtensaoPorMimeType($mimeType)
    {
        $mimeToExt = [
            // Imagens
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            
            // Áudio
            'audio/mpeg' => 'mp3',
            'audio/mp3' => 'mp3',
            'audio/mp4' => 'm4a',
            'audio/aac' => 'aac',
            'audio/ogg' => 'ogg',
            'audio/wav' => 'wav',
            'audio/amr' => 'amr',
            'audio/ogg; codecs=opus' => 'ogg',
            
            // Vídeo
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'video/quicktime' => 'mov',
            'video/avi' => 'avi',
            'video/3gpp' => '3gp',
            'video/webm' => 'webm',
            
            // Documentos
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip',
            'application/rar' => 'rar',
            'application/x-rar-compressed' => 'rar',
        ];
        
        return $mimeToExt[$mimeType] ?? 'bin';
    }

    /**
     * Sanitiza nome de arquivo removendo caracteres problemáticos
     */
    private static function sanitizarNomeArquivo($filename)
    {
        // CORREÇÃO: Primeiro decodificar URLs encoded (%xx)
        $filename = urldecode($filename);
        
        // Remover acentos e caracteres especiais
        $filename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
        
        // Manter apenas caracteres seguros: letras, números, ponto, hífen e underscore
        $filename = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $filename);
        
        // Remover múltiplos underscores consecutivos
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Remover underscore no início ou fim
        $filename = trim($filename, '_');
        
        // Se ficou vazio, gerar nome genérico
        if (empty($filename) || $filename === '.') {
            $filename = 'arquivo_' . uniqid();
        }
        
        // Limitar tamanho (máximo 100 caracteres)
        if (strlen($filename) > 100) {
            $extensao = pathinfo($filename, PATHINFO_EXTENSION);
            $nome = pathinfo($filename, PATHINFO_FILENAME);
            $nome = substr($nome, 0, 100 - strlen($extensao) - 1);
            $filename = $nome . '.' . $extensao;
        }
        
        return $filename;
    }

    /**
     * Testa conexão com MinIO
     */
    public static function testarConexao()
    {
        try {
            if (!self::init()) {
                return [
                    'sucesso' => false,
                    'erro' => 'Falha na inicialização'
                ];
            }

            // Tentar listar objetos do bucket
            self::$s3Client->listObjectsV2([
                'Bucket' => self::$bucket,
                'MaxKeys' => 1
            ]);

            return [
                'sucesso' => true,
                'bucket' => self::$bucket,
                'endpoint' => 'https://minioapidj.helpersti.online'
            ];

        } catch (Exception $e) {
            return [
                'sucesso' => false,
                'erro' => $e->getMessage()
            ];
        }
    }

    /**
     * Método de debug para verificar geração de URL
     */
    public static function debugGeracaoUrl($caminhoMinIO, $tempoExpiracao = 3600)
    {
        if (!self::init()) {
            return ['erro' => 'Falha na inicialização'];
        }

        $debug = [
            'caminho' => $caminhoMinIO,
            'bucket' => self::$bucket,
            'expiracao' => $tempoExpiracao,
            'tentativas' => []
        ];

        // Tentativa 1: Método padrão
        try {
            $comando = self::$s3Client->getCommand('GetObject', [
                'Bucket' => self::$bucket,
                'Key' => $caminhoMinIO
            ]);

            $request = self::$s3Client->createPresignedRequest($comando, "+{$tempoExpiracao} seconds");
            $url = (string) $request->getUri();
            
            $debug['tentativas']['padrao'] = [
                'sucesso' => !empty($url),
                'url' => $url,
                'tem_assinatura' => strpos($url, 'X-Amz-Signature') !== false
            ];

        } catch (Exception $e) {
            $debug['tentativas']['padrao'] = [
                'sucesso' => false,
                'erro' => $e->getMessage()
            ];
        }

        // Tentativa 2: Método alternativo
        try {
            $url = self::gerarUrlAlternativa($caminhoMinIO, $tempoExpiracao);
            $debug['tentativas']['alternativo'] = [
                'sucesso' => !empty($url),
                'url' => $url,
                'tem_assinatura' => $url ? strpos($url, 'X-Amz-Signature') !== false : false
            ];

        } catch (Exception $e) {
            $debug['tentativas']['alternativo'] = [
                'sucesso' => false,
                'erro' => $e->getMessage()
            ];
        }

        return $debug;
    }

    /**
     * Acesso direto ao arquivo (método mais confiável)
     */
    public static function acessoDirecto($caminhoMinIO)
    {
        if (!self::init()) {
            return [
                'sucesso' => false,
                'erro' => 'Erro ao inicializar MinIO'
            ];
        }

        try {
            $resultado = self::$s3Client->getObject([
                'Bucket' => self::$bucket,
                'Key' => $caminhoMinIO
            ]);

            return [
                'sucesso' => true,
                'dados' => $resultado['Body']->getContents(),
                'content_type' => $resultado['ContentType'] ?? 'application/octet-stream',
                'tamanho' => $resultado['ContentLength'] ?? 0,
                'metadados' => $resultado['Metadata'] ?? []
            ];

        } catch (AwsException $e) {
            error_log("❌ Erro ao acessar arquivo diretamente: " . $e->getMessage());
            return [
                'sucesso' => false,
                'erro' => 'Erro AWS: ' . $e->getMessage()
            ];
        }
    }
} 