<?php
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';
require_login();

$editId = $_GET['edit'] ?? null;
$editPeca = null;
$search = trim($_GET['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_peca = $_POST['id_peca'] ?? null;
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $preco = $_POST['preco'] ?? 0;
    $quantidade = $_POST['quantidade'] ?? 0;
    $fornecedor = $_POST['fornecedor'] ?? null;
    $con = db_connect();

    if (!empty($id_peca)) {
        $stmt = $con->prepare('UPDATE pecas SET nome = ?, descricao = ?, marca = ?, modelo_da_peça = ?, preco = ?, quantidade_estoque = ?, id_fornecedor = ? WHERE id_peca = ?');
        $stmt->bind_param('sssssdii', $nome, $descricao, $marca, $modelo, $preco, $quantidade, $fornecedor, $id_peca);
    } else {
        $stmt = $con->prepare('INSERT INTO pecas (nome, descricao, marca, modelo_da_peça, preco, quantidade_estoque, id_fornecedor) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssss sdi', $nome, $descricao, $marca, $modelo, $preco, $quantidade, $fornecedor);
    }

    $stmt->execute();
    $stmt->close();
    $con->close();
    header('Location: pecas.php');
    exit;
}
$con = db_connect();
if ($editId) {
    $editRes = $con->query('SELECT * FROM pecas WHERE id_peca = ' . (int)$editId);
    $editPeca = $editRes->fetch_assoc();
}
$fornecedores = $con->query('SELECT id_fornecedor, nome FROM fornecedores ORDER BY nome');

$sql = 'SELECT p.*, f.nome as fornecedor_nome FROM pecas p LEFT JOIN fornecedores f ON p.id_fornecedor = f.id_fornecedor';
if ($search !== '') {
    $searchTerm = $con->real_escape_string($search);
    $sql .= " WHERE p.nome LIKE '%$searchTerm%' OR p.descricao LIKE '%$searchTerm%' OR p.marca LIKE '%$searchTerm%' OR p.modelo_da_peça LIKE '%$searchTerm%' OR f.nome LIKE '%$searchTerm%'";
}
$sql .= ' ORDER BY p.id_peca DESC';
$res = $con->query($sql);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Peças - Oficina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>

<header class="mb-4">
  <div class="navbar-custom">
    <a class="btn btn-outline-primary" href="index.php">Tela inicial</a>
    <a class="btn btn-primary" href="clientes.php">Clientes</a>
    <a class="btn btn-primary" href="manutencoes.php">Manutenções</a>
    <a class="btn btn-primary" href="fornecedores.php">Fornecedores</a>
    <a class="btn btn-primary" href="solicitacoes.php">Solicitações</a>
    <a class="btn btn-primary" href="servicos.php">Serviços</a>
    <a class="btn btn-primary" href="relatorios.php">Relatórios</a>
    <a class="btn btn-primary" href="orcamentos.php">Gerar Orçamentos</a>
  </div>
</header>

<body class="bg-light">
<div class="container-fluid py-4">
  <h2 class="text-secondary mb-3 text-center">Cadastro de Peças</h2>

  <div class="row g-4 align-items-start">
    <div class="col-lg-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Cadastro</h5>
            <a class="btn btn-outline-secondary btn-sm" href="pecas.php">Novo</a>
          </div>
          <form method="post">
            <input type="hidden" name="id_peca" value="<?= htmlspecialchars($editPeca['id_peca'] ?? '') ?>">
            <div class="mb-3">
              <label class="form-label">Nome da peça</label>
              <input name="nome" class="form-control" placeholder="Nome da peça" value="<?= htmlspecialchars($editPeca['nome'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Descrição</label>
              <input name="descricao" class="form-control" placeholder="Descrição" value="<?= htmlspecialchars($editPeca['descricao'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Marca</label>
              <input name="marca" class="form-control" placeholder="Marca" value="<?= htmlspecialchars($editPeca['marca'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Modelo</label>
              <input name="modelo" class="form-control" placeholder="Modelo" value="<?= htmlspecialchars($editPeca['modelo_da_peça'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Preço</label>
              <input name="preco" class="form-control" placeholder="Preço" type="number" step="0.01" value="<?= htmlspecialchars($editPeca['preco'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Quantidade</label>
              <input name="quantidade" class="form-control" placeholder="Qtd" type="number" value="<?= htmlspecialchars($editPeca['quantidade_estoque'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Fornecedor</label>
              <select name="fornecedor" class="form-select">
                <option value="">Fornecedor (opcional)</option>
                <?php while($f = $fornecedores->fetch_assoc()): ?>
                  <option value="<?= $f['id_fornecedor'] ?>" <?= ($editPeca['id_fornecedor'] ?? '') == $f['id_fornecedor'] ? 'selected' : '' ?>><?= htmlspecialchars($f['nome']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <button class="btn btn-success w-100"><?= $editPeca ? 'Atualizar' : 'Salvar Peça' ?></button>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Busca</h5>
          <form method="get">
            <div class="mb-3">
              <input type="text" name="search" class="form-control" placeholder="Nome, descrição, marca, modelo ou fornecedor" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-outline-primary flex-grow-1">Buscar</button>
              <?php if ($search !== ''): ?>
                <a href="pecas.php" class="btn btn-outline-secondary">Limpar</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title mb-3">Lista de peças</h5>
          <div class="d-grid gap-3">
            <?php $hasPecas = false; while($row = $res->fetch_assoc()): $hasPecas = true; ?>
              <div class="card border-0 bg-light">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                      <h6 class="mb-1"><?= htmlspecialchars($row['nome']) ?></h6>
                      <p class="mb-1 text-muted"><?= htmlspecialchars($row['descricao']) ?></p>
                      <p class="mb-0 text-muted">Marca: <?= htmlspecialchars($row['marca'] ?? '') ?> • Modelo: <?= htmlspecialchars($row['modelo_da_peça'] ?? '') ?></p>
                    </div>
                    <span class="badge text-bg-secondary">Qtd: <?= (int) ($row['quantidade_estoque'] ?? 0) ?></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Preço: R$ <?= number_format((float) ($row['preco'] ?? 0), 2, ',', '.') ?> • Fornecedor: <?= htmlspecialchars($row['fornecedor_nome'] ?? '') ?></small>
                    <a class="btn btn-sm btn-outline-primary" href="pecas.php?edit=<?= (int) $row['id_peca'] ?>">Editar</a>
                  </div>
                </div>
              </div>
            <?php endwhile; if (!$hasPecas): ?>
              <div class="alert alert-light text-center mb-0">Nenhuma peça encontrada.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
