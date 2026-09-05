<?php
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/enviar_email.php';

$editId = $_GET['edit'] ?? null;
$editSolicitacao = null;
$search = trim($_GET['search'] ?? '');
$dataFiltro = $_GET['data_filtro'] ?? '';
$statusFiltro = $_GET['status_filtro'] ?? '';

$con = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_solicitacao = $_POST['id_solicitacao'] ?? null;
    $id_peca = $_POST['id_peca'] ?? null;
    $id_fornecedor = $_POST['id_fornecedor'] ?? null;
    $id_cliente = $_POST['id_cliente'] ?? null;
    $status = $_POST['status'] ?? 'Pendente';

    if (!empty($id_solicitacao)) {
        $stmt = $con->prepare('UPDATE solicitacoes_pecas SET id_peca = ?, id_fornecedor = ?, id_cliente = ?, status = ? WHERE id_solicitacao = ?');
        $stmt->bind_param('iiisi', $id_peca, $id_fornecedor, $id_cliente, $status, $id_solicitacao);
    } else {
        $stmt = $con->prepare('INSERT INTO solicitacoes_pecas (id_peca, id_fornecedor, id_cliente, status) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiis', $id_peca, $id_fornecedor, $id_cliente, $status);
    }

    $stmt->execute();
    $stmt->close();

    if (!empty($id_fornecedor)) {
        $res = $con->query('SELECT email, nome FROM fornecedores WHERE id_fornecedor = ' . intval($id_fornecedor));
        $forn = $res->fetch_assoc();
        $res2 = $con->query('SELECT nome FROM pecas WHERE id_peca = ' . intval($id_peca));
        $peca = $res2->fetch_assoc();

        if ($forn && !empty($forn['email'])) {
            $msg = 'Solicitação de peça: ' . ($peca['nome'] ?? 'Peça') . ' (solicitado por cliente id ' . intval($id_cliente) . ').';
            enviar_email($forn['email'], 'Solicitação de Peça', nl2br(htmlspecialchars($msg)));
        }
    }

    header('Location: solicitacoes.php');
    exit;
}

if ($editId) {
    $editRes = $con->query('SELECT * FROM solicitacoes_pecas WHERE id_solicitacao = ' . (int)$editId);
    $editSolicitacao = $editRes->fetch_assoc();
}

$pecas = $con->query('SELECT id_peca, nome, preco FROM pecas ORDER BY nome');
$fornecedores = $con->query('SELECT id_fornecedor, nome FROM fornecedores ORDER BY nome');
$clientes = $con->query('SELECT id_cliente, nome FROM clientes ORDER BY nome');

$whereClauses = [];
if ($search !== '') {
    $searchTerm = $con->real_escape_string($search);
    $whereClauses[] = "(p.nome LIKE '%$searchTerm%' OR f.nome LIKE '%$searchTerm%' OR c.nome LIKE '%$searchTerm%' OR s.status LIKE '%$searchTerm%')";
}
if ($dataFiltro !== '') {
    $dataTerm = $con->real_escape_string($dataFiltro);
    $whereClauses[] = "DATE(s.data_solicitacao) = '$dataTerm'";
}
if ($statusFiltro !== '') {
    $statusTerm = $con->real_escape_string($statusFiltro);
    $whereClauses[] = "s.status = '$statusTerm'";
}

$sql = 'SELECT s.*, p.nome as peca_nome, p.preco, f.nome as fornecedor_nome, c.nome as cliente_nome
        FROM solicitacoes_pecas s
        LEFT JOIN pecas p ON s.id_peca = p.id_peca
        LEFT JOIN fornecedores f ON s.id_fornecedor = f.id_fornecedor
        LEFT JOIN clientes c ON s.id_cliente = c.id_cliente';
if ($whereClauses !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
}
$sql .= ' ORDER BY s.data_solicitacao DESC';
$res = $con->query($sql);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Solicitações de Peças - Oficina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<header class="mb-4">
  <div class="navbar-custom">
    <a class="btn btn-outline-primary" href="index.php">Tela inicial</a>
    <a class="btn btn-primary" href="clientes.php">Clientes</a>
    <a class="btn btn-primary" href="manutencoes.php">Manutenções</a>
    <a class="btn btn-primary" href="pecas.php">Peças</a>
    <a class="btn btn-primary" href="fornecedores.php">Fornecedores</a>
    <a class="btn btn-primary" href="orcamentos.php">Gerar Orçamentos</a>
    <a class="btn btn-primary" href="servicos.php">Serviços</a>
    <a class="btn btn-primary" href="relatorios.php">Relatórios</a>
  </div>
</header>
<body class="bg-light">
<div class="container-fluid py-4">
  <h2 class="text-danger mb-3 text-center">Solicitações de Peças</h2>

  <div class="row g-4 align-items-start">
    <div class="col-lg-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Cadastro</h5>
            <a class="btn btn-outline-secondary btn-sm" href="solicitacoes.php">Novo</a>
          </div>
          <form method="post">
            <input type="hidden" name="id_solicitacao" value="<?= htmlspecialchars($editSolicitacao['id_solicitacao'] ?? '') ?>">
            <div class="mb-3">
              <label class="form-label">Peça</label>
              <select name="id_peca" class="form-select" required>
                <option value="">Selecione a peça</option>
                <?php while($p = $pecas->fetch_assoc()): ?>
                  <option value="<?= $p['id_peca'] ?>" <?= ($editSolicitacao['id_peca'] ?? '') == $p['id_peca'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nome']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Fornecedor</label>
              <select name="id_fornecedor" class="form-select" required>
                <option value="">Selecione o fornecedor</option>
                <?php while($f = $fornecedores->fetch_assoc()): ?>
                  <option value="<?= $f['id_fornecedor'] ?>" <?= ($editSolicitacao['id_fornecedor'] ?? '') == $f['id_fornecedor'] ? 'selected' : '' ?>><?= htmlspecialchars($f['nome']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Cliente</label>
              <select name="id_cliente" class="form-select">
                <option value="">Cliente (opcional)</option>
                <?php while($c = $clientes->fetch_assoc()): ?>
                  <option value="<?= $c['id_cliente'] ?>" <?= ($editSolicitacao['id_cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <?php $statusAtual = $editSolicitacao['status'] ?? 'Pendente'; ?>
                <option value="Pendente" <?= $statusAtual === 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                <option value="Atendido" <?= $statusAtual === 'Atendido' || $statusAtual === 'Atendida' ? 'selected' : '' ?>>Atendido</option>
                <option value="Cancelada" <?= $statusAtual === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
              </select>
            </div>
            <button class="btn btn-success w-100"><?= $editSolicitacao ? 'Atualizar' : 'Solicitar' ?></button>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Filtros</h5>
          <form method="get" id="filtros-solicitacoes">
            <div class="mb-3">
              <label class="form-label">Buscar</label>
              <input type="text" name="search" class="form-control" placeholder="Peça, fornecedor, cliente ou status" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Data</label>
              <input type="date" name="data_filtro" class="form-control" value="<?= htmlspecialchars($dataFiltro) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status_filtro" class="form-select">
                <option value="">Todos</option>
                <option value="Pendente" <?= $statusFiltro === 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                <option value="Atendido" <?= $statusFiltro === 'Atendido' || $statusFiltro === 'Atendida' ? 'selected' : '' ?>>Atendido</option>
                <option value="Cancelada" <?= $statusFiltro === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
              </select>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-outline-primary flex-grow-1">Aplicar</button>
              <?php if ($search !== '' || $dataFiltro !== '' || $statusFiltro !== ''): ?>
                <a href="solicitacoes.php" class="btn btn-outline-secondary">Limpar</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title mb-3">Lista de solicitações</h5>
          <div class="d-grid gap-3">
            <?php $hasSolicitacoes = false; while($row = $res->fetch_assoc()): $hasSolicitacoes = true; 
              $statusBadge = $row['status'] ?? 'Pendente';
              $statusBadge = $statusBadge === 'Atendida' ? 'Atendido' : $statusBadge;
              $statusClass = ['Pendente' => 'text-bg-warning', 'Atendido' => 'text-bg-success', 'Cancelada' => 'text-bg-danger'][$statusBadge] ?? 'text-bg-secondary';
            ?>
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                      <h6 class="mb-1"><?= htmlspecialchars($row['peca_nome'] ?? '') ?></h6>
                      <p class="mb-1 text-muted">Fornecedor: <?= htmlspecialchars($row['fornecedor_nome'] ?? '') ?></p>
                      <p class="mb-0 text-muted">Cliente: <?= htmlspecialchars($row['cliente_nome'] ?? 'Sem cliente') ?></p>
                    </div>
                    <span class="badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($statusBadge) ?></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Data: <?= htmlspecialchars($row['data_solicitacao'] ?? '') ?> • R$ <?= number_format((float) ($row['preco'] ?? 0), 2, ',', '.') ?></small>
                    <a class="btn btn-sm btn-outline-primary" href="solicitacoes.php?edit=<?= (int) $row['id_solicitacao'] ?>">Editar</a>
                  </div>
                </div>
              </div>
            <?php endwhile; if (!$hasSolicitacoes): ?>
              <div class="alert alert-light text-center mb-0">Nenhuma solicitação encontrada com os filtros aplicados.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('filtros-solicitacoes');
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
