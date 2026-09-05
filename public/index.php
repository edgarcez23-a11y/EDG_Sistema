<?php
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';

// Redireciona para login se não autenticado
require_login();

$usuario = get_usuario_logado();
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema de gestão</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
  <!-- Navbar de Autenticação -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
      <span class="navbar-brand">🔧 Sistema de Gestão</span>
      <div class="ms-auto d-flex align-items-center gap-2">
        <span class="text-white">
          👤 <?php echo htmlspecialchars($usuario['nome']); ?>
        </span>
        <form method="POST" action="logout.php" style="display: inline;">
          <button type="submit" class="btn btn-sm btn-danger">Sair</button>
        </form>
      </div>
    </div>
  </nav>

  <div class="container-fluid py-4 home-shell">
    <div class="home-watermark" aria-hidden="true"></div>
    <header class="text-center mb-4 position-relative">
      <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2 mb-3">EGS Sistema</span>
      <h1 class="text_tela text-secondary mb-3">Bem-vindo ao seu ambiente de serviços.</h1>
      <p class="lead text-muted mb-0">Gerencie clientes, fornecedores, peças, solicitações e orçamentos de forma prática e organizada.</p>
    </header>

    <div class="navbar-custom mb-4">
      <a class="btn btn-primary" href="clientes.php">Clientes</a>
      <a class="btn btn-primary" href="fornecedores.php">Fornecedores</a>
      <a class="btn btn-primary" href="manutencoes.php">Manutenções</a>
      <a class="btn btn-primary" href="pecas.php">Peças</a>
      <a class="btn btn-primary" href="solicitacoes.php">Solicitações</a>
      <a class="btn btn-primary" href="orcamentos.php">Gerar Orçamentos</a>
      <a class="btn btn-primary" href="servicos.php">Serviços</a>
      <a class="btn btn-primary" href="relatorios.php">Relatórios</a>
    </div>

    <div class="row g-4 align-items-stretch">
      <div class="col-lg-7">
        <div class="card-panel h-100">
          <h2 class="h4 text-secondary mb-3">Acesso rápido</h2>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="feature-card">
                <h3 class="h6 mb-2">Clientes</h3>
                <p class="mb-3 small text-muted">Cadastre e acompanhe seus clientes com mais rapidez.</p>
                <a class="btn btn-outline-primary btn-sm" href="clientes.php">Abrir</a>
              </div>
            </div>
            <div class="col-md-6">
              <div class="feature-card">
                <h3 class="h6 mb-2">Peças</h3>
                <p class="mb-3 small text-muted">Controle estoque, preços e fornecedores em um só lugar.</p>
                <a class="btn btn-outline-primary btn-sm" href="pecas.php">Abrir</a>
              </div>
            </div>
            <div class="col-md-6">
              <div class="feature-card">
                <h3 class="h6 mb-2">Solicitações</h3>
                <p class="mb-3 small text-muted">Organize pedidos e acompanhe o andamento das demandas.</p>
                <a class="btn btn-outline-primary btn-sm" href="solicitacoes.php">Abrir</a>
              </div>
            </div>
            <div class="col-md-6">
              <div class="feature-card">
                <h3 class="h6 mb-2">Orçamentos</h3>
                <p class="mb-3 small text-muted">Crie, salve e envie orçamentos de forma mais ágil.</p>
                <a class="btn btn-outline-primary btn-sm" href="orcamentos.php">Abrir</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="row g-3">
          <div class="col-12">
            <div class="card shadow-sm border-0 h-100">
              <img src="Logo Garcez gestão.png" class="img-fluid hero-image" alt="Apresentação da marca do Sistema">
            </div>
          </div>
          <div class="col-12">
            <div class="card shadow-sm border-0 h-100">
              <img class="telainicio hero-image" src="imagem01.png" alt="Imagem de oficina mecânica">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>