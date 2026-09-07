<?php
require_once __DIR__ . '/../inc/auth.php';

require_login();

function encontrar_arquivo_log(): ?string
{
    $candidatos = [];
    $logConfigurado = trim((string) ini_get('error_log'));

    if ($logConfigurado !== '' && strtolower($logConfigurado) !== 'syslog') {
        $candidatos[] = $logConfigurado;
    }

    $candidatos[] = __DIR__ . '/../logs/oficina.log';
    $candidatos[] = dirname(PHP_BINARY) . '/logs/php_error_log';
    $candidatos[] = 'C:/xampp/php/logs/php_error_log';
    $candidatos[] = 'C:/xampp/apache/logs/error.log';

    foreach (array_unique($candidatos) as $candidato) {
        if (is_file($candidato) && is_readable($candidato)) {
            return $candidato;
        }
    }

    return null;
}

function ler_ultimas_linhas(string $arquivo, int $limite = 200): array
{
    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($linhas === false) {
        return [];
    }

    return array_slice($linhas, -$limite);
}

$arquivoLog = encontrar_arquivo_log();
$linhasLog = $arquivoLog ? ler_ultimas_linhas($arquivoLog) : [];
$nomeArquivoLog = $arquivoLog ? basename($arquivoLog) : 'Nenhum arquivo encontrado';
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log do sistema</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid log-navbar">
      <a class="navbar-brand" href="index.php">Sistema de Gestão</a>
      <a class="btn btn-light btn-sm" href="index.php">Voltar ao início</a>
    </div>
  </nav>

  <main class="container-fluid py-4 log-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h1 class="h3 text-secondary mb-1">Log do sistema</h1>
        <p class="text-muted mb-0">Últimos registros para identificar onde e como os erros ocorreram.</p>
      </div>
      <span class="badge text-bg-secondary"><?php echo htmlspecialchars($nomeArquivoLog); ?></span>
    </div>

    <?php if (!$arquivoLog): ?>
      <div class="alert alert-warning" role="alert">
        O arquivo de log do PHP não foi encontrado ou não está acessível neste ambiente.
        Verifique a configuração <code>error_log</code> do PHP.
      </div>
    <?php elseif (!$linhasLog): ?>
      <div class="alert alert-success" role="alert">O arquivo de log está vazio.</div>
    <?php else: ?>
      <div class="card shadow-sm border-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 log-table">
            <thead class="table-light">
              <tr>
                <th scope="col">Nível</th>
                <th scope="col">Registro do erro</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_reverse($linhasLog) as $linha): ?>
                <?php
                  $nivel = 'INFO';
                  if (preg_match('/PHP (Fatal error|Parse error|Warning|Notice|Deprecated)/i', $linha, $correspondencia)) {
                      $nivel = strtoupper($correspondencia[1]);
                  }
                  $classeNivel = in_array($nivel, ['FATAL ERROR', 'PARSE ERROR'], true)
                      ? 'text-bg-danger'
                      : (in_array($nivel, ['WARNING', 'NOTICE', 'DEPRECATED'], true) ? 'text-bg-warning' : 'text-bg-secondary');
                ?>
                <tr>
                  <td><span class="badge <?php echo $classeNivel; ?>"><?php echo htmlspecialchars($nivel); ?></span></td>
                  <td><code class="log-line"><?php echo htmlspecialchars($linha); ?></code></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <p class="small text-muted mt-3 mb-0">Exibindo no máximo os 200 registros mais recentes.</p>
    <?php endif; ?>
  </main>
</body>
</html>
