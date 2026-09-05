<?php 
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';
require_login();
$editId = $_GET['edit'] ?? null;
$editFornecedor = null;
$search = trim($_GET['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_fornecedor = $_POST['id_fornecedor'] ?? null;
    $nome = $_POST['nome'] ?? ''; 
    $telefone = $_POST['telefone'] ?? '';
    $email = $_POST['email'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $con = db_connect();

    if (!empty($id_fornecedor)) {
        $stmt = $con->prepare('UPDATE fornecedores SET nome = ?, telefone = ?, email = ?, endereco = ? WHERE id_fornecedor = ?');
        $stmt->bind_param('ssssi', $nome, $telefone, $email, $endereco, $id_fornecedor);
    } else {
        $stmt = $con->prepare('INSERT INTO fornecedores (nome, telefone, email, endereco) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $nome, $telefone, $email, $endereco);
    }

    $stmt->execute();
    $stmt->close();
    $con->close();
    header('Location: fornecedores.php');
    exit;
}

$con = db_connect();
if ($editId) {
    $editRes = $con->query('SELECT * FROM fornecedores WHERE id_fornecedor = ' . (int)$editId);
    $editFornecedor = $editRes->fetch_assoc();
}

$sql = 'SELECT * FROM fornecedores';
if ($search !== '') {
    $searchTerm = $con->real_escape_string($search);
    $sql .= " WHERE nome LIKE '%$searchTerm%' OR telefone LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%' OR endereco LIKE '%$searchTerm%'";
}
$sql .= ' ORDER BY id_fornecedor DESC';
$res = $con->query($sql);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Fornecedores - Oficina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<header class="mb-4">
  <div class="navbar-custom">
    <a class="btn btn-outline-primary" href="index.php">Tela inicial</a>
    <a class="btn btn-primary" href="clientes.php">Clientes</a>
    <a class="btn btn-primary" href="manutencoes.php">Manutenções</a>
    <a class="btn btn-primary" href="pecas.php">Peças</a>
    <a class="btn btn-primary" href="solicitacoes.php">Solicitações</a>
    <a class="btn btn-primary" href="servicos.php">Serviços</a>
    <a class="btn btn-primary" href="relatorios.php">Relatórios</a>
    <a class="btn btn-primary" href="orcamentos.php">Gerar Orçamentos</a>
  </div>
</header>
<body class="bg-light">
<div class="container-fluid py-4">
  <h2 class="text-success mb-3 text-center">Cadastro de Fornecedores</h2>

  <div class="row g-4 align-items-start">
    <div class="col-lg-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Cadastro</h5>
            <a class="btn btn-outline-secondary btn-sm" href="fornecedores.php">Novo</a>
          </div>
          <form method="post">
            <input type="hidden" name="id_fornecedor" value="<?= htmlspecialchars($editFornecedor['id_fornecedor'] ?? '') ?>">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input name="nome" class="form-control" placeholder="Nome" value="<?= htmlspecialchars($editFornecedor['nome'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Telefone</label>
              <input name="telefone" class="form-control" placeholder="Telefone" value="<?= htmlspecialchars($editFornecedor['telefone'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">E-mail</label>
              <input name="email" class="form-control" placeholder="E-mail" value="<?= htmlspecialchars($editFornecedor['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Endereço</label>
              <input name="endereco" class="form-control" placeholder="Endereço" value="<?= htmlspecialchars($editFornecedor['endereco'] ?? '') ?>">
            </div>
            <button class="btn btn-success w-100"><?= $editFornecedor ? 'Atualizar' : 'Salvar Fornecedor' ?></button>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Busca</h5>
          <form method="get">
            <div class="mb-3">
              <input type="text" name="search" class="form-control" placeholder="Nome, telefone, e-mail ou endereço" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-outline-primary flex-grow-1">Buscar</button>
              <?php if ($search !== ''): ?>
                <a href="fornecedores.php" class="btn btn-outline-secondary">Limpar</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title mb-3">Lista de fornecedores</h5>
          <div class="d-grid gap-3">
            <?php $hasFornecedores = false; while($row = $res->fetch_assoc()): $hasFornecedores = true; ?>
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                      <h6 class="mb-1"><?= htmlspecialchars($row['nome']) ?></h6>
                      <p class="mb-1 text-muted">Telefone: <?= htmlspecialchars($row['telefone']) ?></p>
                      <p class="mb-0 text-muted">E-mail: <?= htmlspecialchars($row['email']) ?></p>
                    </div>
                    <span class="badge text-bg-success">#<?= (int) $row['id_fornecedor'] ?></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Endereço: <?= htmlspecialchars($row['endereco']) ?></small>
                    <a class="btn btn-sm btn-outline-primary" href="fornecedores.php?edit=<?= (int) $row['id_fornecedor'] ?>">Editar</a>
                  </div>
                </div>
              </div>
            <?php endwhile; if (!$hasFornecedores): ?>
              <div class="alert alert-light text-center mb-0">Nenhum fornecedor encontrado.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
