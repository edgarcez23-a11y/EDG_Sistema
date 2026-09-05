<?php
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/enviar_email.php';

$editId = $_GET['edit'] ?? null;
$editManutencao = null;
$search = trim($_GET['search'] ?? '');
$dataFiltro = $_GET['data_filtro'] ?? '';
$statusFiltro = $_GET['status_filtro'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_manutencao = $_POST['id_manutencao'] ?? null;
    $id_cliente = $_POST['id_cliente'] ?? null;
    $data_manutencao = $_POST['data_manutencao'] ?? null;
    $descricao = $_POST['descricao'] ?? '';
    $con = db_connect();

    if (!empty($id_manutencao)) {
        $stmt = $con->prepare('UPDATE manutencoes SET id_cliente = ?, data_manutencao = ?, descricao = ? WHERE id_manutencao = ?');
        $stmt->bind_param('issi', $id_cliente, $data_manutencao, $descricao, $id_manutencao);
    } else {
        $stmt = $con->prepare('INSERT INTO manutencoes (id_cliente, data_manutencao, descricao) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $id_cliente, $data_manutencao, $descricao);
    }

    $stmt->execute();
    $stmt->close();

    $res = $con->query('SELECT email, nome FROM clientes WHERE id_cliente = ' . intval($id_cliente));
    $cliente = $res->fetch_assoc();
    if ($cliente && !empty($cliente['email'])) {
        $msg = 'Olá ' . $cliente['nome'] . ',\nSua manutenção foi agendada para ' . $data_manutencao . '.';
        enviar_email($cliente['email'], 'Aviso de manutenção', nl2br(htmlspecialchars($msg)));
    }

    $con->close();
    header('Location: manutencoes.php');
    exit;
}
$con = db_connect();
if ($editId) {
    $editRes = $con->query('SELECT * FROM manutencoes WHERE id_manutencao = ' . (int)$editId);
    $editManutencao = $editRes->fetch_assoc();
}
$clientes = $con->query('SELECT id_cliente, nome FROM clientes ORDER BY nome');

$whereClauses = [];
if ($search !== '') {
    $searchTerm = $con->real_escape_string($search);
    $whereClauses[] = "(c.nome LIKE '%$searchTerm%' OR m.descricao LIKE '%$searchTerm%' OR m.status LIKE '%$searchTerm%' OR m.data_manutencao LIKE '%$searchTerm%')";
}
if ($dataFiltro !== '') {
    $dataTerm = $con->real_escape_string($dataFiltro);
    $whereClauses[] = "m.data_manutencao = '$dataTerm'";
}
if ($statusFiltro !== '') {
    $statusTerm = $con->real_escape_string($statusFiltro);
    $whereClauses[] = "m.status = '$statusTerm'";
}

$sql = 'SELECT m.*, c.nome as cliente_nome FROM manutencoes m LEFT JOIN clientes c ON m.id_cliente = c.id_cliente';
if ($whereClauses !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
}
$sql .= ' ORDER BY m.data_manutencao DESC';
$res = $con->query($sql);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manutenções - Oficina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<header class="mb-4">
  <div class="navbar-custom">
    <a class="btn btn-outline-primary" href="index.php">Tela inicial</a>
    <a class="btn btn-primary" href="clientes.php">Clientes</a>
    <a class="btn btn-primary" href="fornecedores.php">Fornecedores</a>
    <a class="btn btn-primary" href="pecas.php">Peças</a>
    <a class="btn btn-primary" href="solicitacoes.php">Solicitações</a>
    <a class="btn btn-primary" href="orcamentos.php">Gerar Orçamentos</a>
    <a class="btn btn-primary" href="servicos.php">Serviços</a>
    <a class="btn btn-primary" href="relatorios.php">Relatórios</a>
  </div>
</header>

<body class="bg-light">
<div class="container-fluid py-4">
  <h2 class="text-warning mb-3 text-center">Agenda de Manutenções</h2>

  <div class="row g-4 align-items-start">
    <div class="col-lg-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Cadastro</h5>
            <a class="btn btn-outline-secondary btn-sm" href="manutencoes.php">Novo</a>
          </div>
          <form method="post">
            <input type="hidden" name="id_manutencao" value="<?= htmlspecialchars($editManutencao['id_manutencao'] ?? '') ?>">
            <div class="mb-3">
              <label class="form-label">Cliente</label>
              <select name="id_cliente" class="form-select" required>
                <option value="">Selecione o cliente</option>
                <?php while($c = $clientes->fetch_assoc()): ?>
                  <option value="<?= $c['id_cliente'] ?>" <?= ($editManutencao['id_cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Data</label>
              <input name="data_manutencao" class="form-control" type="date" value="<?= htmlspecialchars($editManutencao['data_manutencao'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Descrição</label>
              <input name="descricao" class="form-control" placeholder="Descrição" value="<?= htmlspecialchars($editManutencao['descricao'] ?? '') ?>">
            </div>
            <button class="btn btn-success w-100"><?= $editManutencao ? 'Atualizar' : 'Agendar' ?></button>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Filtros</h5>
          <form method="get" id="filtros-manutencoes">
            <div class="mb-3">
              <label class="form-label">Buscar</label>
              <input type="text" name="search" class="form-control" placeholder="Cliente, descrição ou status" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Data</label>
              <input type="date" name="data_filtro" class="form-control" value="<?= htmlspecialchars($dataFiltro) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status_filtro" class="form-select">
                <option value="">Todos</option>
                <option value="Agendada" <?= $statusFiltro === 'Agendada' ? 'selected' : '' ?>>Agendada</option>
                <option value="Concluída" <?= $statusFiltro === 'Concluída' ? 'selected' : '' ?>>Concluída</option>
                <option value="Cancelada" <?= $statusFiltro === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
              </select>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-outline-primary flex-grow-1">Aplicar</button>
              <?php if ($search !== '' || $dataFiltro !== '' || $statusFiltro !== ''): ?>
                <a href="manutencoes.php" class="btn btn-outline-secondary">Limpar</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title mb-3">Lista de manutenções</h5>
          <div class="d-grid gap-3">
            <?php $hasManutencoes = false; while($row = $res->fetch_assoc()): $hasManutencoes = true; ?>
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                      <h6 class="mb-1"><?= htmlspecialchars($row['cliente_nome']) ?></h6>
                      <p class="mb-1 text-muted"><?= htmlspecialchars($row['descricao']) ?></p>
                    </div>
                    <span class="badge text-bg-warning"><?= htmlspecialchars($row['status']) ?></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Data: <?= htmlspecialchars($row['data_manutencao']) ?></small>
                    <a class="btn btn-sm btn-outline-primary" href="manutencoes.php?edit=<?= (int) $row['id_manutencao'] ?>">Editar</a>
                  </div>
                </div>
              </div>
            <?php endwhile; if (!$hasManutencoes): ?>
              <div class="alert alert-light text-center mb-0">Nenhuma manutenção encontrada com os filtros aplicados.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('filtros-manutencoes');
  if (!form) return;

  const dataInput = form.querySelector('input[name="data_filtro"]');
  const statusSelect = form.querySelector('select[name="status_filtro"]');

  if (dataInput) {
    dataInput.addEventListener('change', function () {
      form.submit();
    });
  }

  if (statusSelect) {
    statusSelect.addEventListener('change', function () {
      form.submit();
    });
  }
});
</script>
</body>
</html>
