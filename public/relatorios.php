<?php
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';
require_login();

$con = db_connect();

$clientes = [];
$fornecedores = [];
$pecas = [];
$solicitacoes = [];
$servicos = [];

$dataInicio = $_GET['data_inicio'] ?? '';
$dataFim = $_GET['data_fim'] ?? '';
$tipoBusca = $_GET['tipo_busca'] ?? 'todos';

if ($con) {
    $clientesRes = $con->query('SELECT id_cliente, nome, telefone, email, endereco, data_cadastro FROM clientes ORDER BY nome');
    while ($cliente = $clientesRes->fetch_assoc()) {
        $clientes[] = $cliente;
    }

    $fornecedoresRes = $con->query('SELECT id_fornecedor, nome, telefone, email, endereco FROM fornecedores ORDER BY nome');
    while ($fornecedor = $fornecedoresRes->fetch_assoc()) {
        $fornecedores[] = $fornecedor;
    }

    $pecasRes = $con->query('SELECT p.id_peca, p.nome, p.preco, p.quantidade_estoque, f.nome AS fornecedor_nome FROM pecas p LEFT JOIN fornecedores f ON p.id_fornecedor = f.id_fornecedor ORDER BY p.nome');
    while ($peca = $pecasRes->fetch_assoc()) {
        $pecas[] = $peca;
    }

    $whereSolicitacoes = [];
    $whereServicos = [];

    if ($dataInicio !== '') {
        $whereSolicitacoes[] = "DATE(s.data_solicitacao) >= '" . $con->real_escape_string($dataInicio) . "'";
        $whereServicos[] = "DATE(m.data_manutencao) >= '" . $con->real_escape_string($dataInicio) . "'";
    }
    if ($dataFim !== '') {
        $whereSolicitacoes[] = "DATE(s.data_solicitacao) <= '" . $con->real_escape_string($dataFim) . "'";
        $whereServicos[] = "DATE(m.data_manutencao) <= '" . $con->real_escape_string($dataFim) . "'";
    }

    $sqlSolicitacoes = 'SELECT s.id_solicitacao, s.status, s.data_solicitacao, p.nome AS peca_nome, f.nome AS fornecedor_nome, c.nome AS cliente_nome FROM solicitacoes_pecas s LEFT JOIN pecas p ON s.id_peca = p.id_peca LEFT JOIN fornecedores f ON s.id_fornecedor = f.id_fornecedor LEFT JOIN clientes c ON s.id_cliente = c.id_cliente';
    if (!empty($whereSolicitacoes)) {
        $sqlSolicitacoes .= ' WHERE ' . implode(' AND ', $whereSolicitacoes);
    }
    $sqlSolicitacoes .= ' ORDER BY s.data_solicitacao DESC';
    $solicitacoesRes = $con->query($sqlSolicitacoes);
    while ($solicitacao = $solicitacoesRes->fetch_assoc()) {
        $solicitacoes[] = $solicitacao;
    }

    $sqlServicos = 'SELECT m.id_manutencao, m.data_manutencao, m.descricao, m.status, c.nome AS cliente_nome FROM manutencoes m LEFT JOIN clientes c ON m.id_cliente = c.id_cliente WHERE m.status = "Concluída"';
    if (!empty($whereServicos)) {
        $sqlServicos .= ' AND ' . implode(' AND ', $whereServicos);
    }
    $sqlServicos .= ' ORDER BY m.data_manutencao DESC';
    $servicosRes = $con->query($sqlServicos);
    while ($servico = $servicosRes->fetch_assoc()) {
        $servicos[] = $servico;
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Relatórios - Oficina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <style>
    @media print {
      body { background: #fff !important; }
      .navbar-custom, .btn, .alert { display: none !important; }
      .card, .table { box-shadow: none !important; }
      .print-section { break-inside: avoid; }
    }
  </style>
</head>
<body class="bg-light">
<header class="mb-4">
  <div class="navbar-custom">
    <a class="btn btn-outline-primary" href="index.php">Tela inicial</a>
    <a class="btn btn-primary" href="clientes.php">Clientes</a>
    <a class="btn btn-primary" href="manutencoes.php">Manutenções</a>
    <a class="btn btn-primary" href="pecas.php">Peças</a>
    <a class="btn btn-primary" href="fornecedores.php">Fornecedores</a>
    <a class="btn btn-primary" href="solicitacoes.php">Solicitações</a>
    <a class="btn btn-primary" href="servicos.php">Serviços</a>
  </div>
</header>

<div class="container py-4">
  <div class="card shadow-sm border-0">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <h2 class="fw-bold text-primary">Relatório Geral</h2>
        <p class="text-muted mb-0">Visualize e imprima todos os serviços, cadastros e solicitações da oficina.</p>
      </div>

      <form method="get" class="row g-2 align-items-end mb-4">
        <div class="col-md-3">
          <label class="form-label">Tipo de busca</label>
          <select name="tipo_busca" class="form-select">
            <option value="todos" <?= $tipoBusca === 'todos' ? 'selected' : '' ?>>Todos</option>
            <option value="clientes" <?= $tipoBusca === 'clientes' ? 'selected' : '' ?>>Clientes cadastrados</option>
            <option value="fornecedores" <?= $tipoBusca === 'fornecedores' ? 'selected' : '' ?>>Fornecedores</option>
            <option value="pecas" <?= $tipoBusca === 'pecas' ? 'selected' : '' ?>>Peças cadastradas</option>
            <option value="solicitacoes" <?= $tipoBusca === 'solicitacoes' ? 'selected' : '' ?>>Solicitações de peças</option>
            <option value="servicos" <?= $tipoBusca === 'servicos' ? 'selected' : '' ?>>Serviços</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Data inicial</label>
          <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($dataInicio) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Data final</label>
          <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($dataFim) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-outline-primary flex-grow-1">Buscar</button>
          <?php if ($tipoBusca !== 'todos' || $dataInicio !== '' || $dataFim !== ''): ?>
            <a href="relatorios.php" class="btn btn-outline-secondary">Limpar</a>
          <?php endif; ?>
        </div>
      </form>

      <div class="d-flex justify-content-end gap-2 mb-4">
        <button type="button" class="btn btn-outline-primary" onclick="window.print()">Imprimir</button>
        <a class="btn btn-success" href="javascript:window.print()">Gerar PDF</a>
      </div>

      <?php if ($tipoBusca === 'todos' || $tipoBusca === 'servicos'): ?>
      <div class="print-section mb-4">
        <h4 class="text-secondary mb-3">Serviços realizados</h4>
        <?php if (!empty($servicos)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Cliente</th>
                  <th>Descrição</th>
                  <th>Data</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($servicos as $servico): ?>
                  <tr>
                    <td><?= (int)$servico['id_manutencao'] ?></td>
                    <td><?= htmlspecialchars($servico['cliente_nome'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($servico['descricao'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($servico['data_manutencao'] ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-light">Nenhum serviço realizado encontrado.</div>
        <?php endif; ?>
      </div>

      <?php endif; if ($tipoBusca === 'todos' || $tipoBusca === 'clientes'): ?>
      <div class="print-section mb-4">
        <h4 class="text-secondary mb-3">Clientes cadastrados</h4>
        <?php if (!empty($clientes)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Nome</th>
                  <th>Telefone</th>
                  <th>E-mail</th>
                  <th>Endereço</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($clientes as $cliente): ?>
                  <tr>
                    <td><?= (int)$cliente['id_cliente'] ?></td>
                    <td><?= htmlspecialchars($cliente['nome']) ?></td>
                    <td><?= htmlspecialchars($cliente['telefone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($cliente['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($cliente['endereco'] ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-light">Nenhum cliente cadastrado.</div>
        <?php endif; ?>
      </div>

      <?php endif; if ($tipoBusca === 'todos' || $tipoBusca === 'fornecedores'): ?>
      <div class="print-section mb-4">
        <h4 class="text-secondary mb-3">Fornecedores cadastrados</h4>
        <?php if (!empty($fornecedores)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Nome</th>
                  <th>Telefone</th>
                  <th>E-mail</th>
                  <th>Endereço</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($fornecedores as $fornecedor): ?>
                  <tr>
                    <td><?= (int)$fornecedor['id_fornecedor'] ?></td>
                    <td><?= htmlspecialchars($fornecedor['nome']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['telefone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($fornecedor['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($fornecedor['endereco'] ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-light">Nenhum fornecedor cadastrado.</div>
        <?php endif; ?>
      </div>

      <?php endif; if ($tipoBusca === 'todos' || $tipoBusca === 'pecas'): ?>
      <div class="print-section mb-4">
        <h4 class="text-secondary mb-3">Peças cadastradas</h4>
        <?php if (!empty($pecas)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Peça</th>
                  <th>Fornecedor</th>
                  <th>Preço</th>
                  <th>Estoque</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pecas as $peca): ?>
                  <tr>
                    <td><?= (int)$peca['id_peca'] ?></td>
                    <td><?= htmlspecialchars($peca['nome']) ?></td>
                    <td><?= htmlspecialchars($peca['fornecedor_nome'] ?? '-') ?></td>
                    <td>R$ <?= number_format((float)$peca['preco'], 2, ',', '.') ?></td>
                    <td><?= (int)$peca['quantidade_estoque'] ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-light">Nenhuma peça cadastrada.</div>
        <?php endif; ?>
      </div>

      <?php endif; if ($tipoBusca === 'todos' || $tipoBusca === 'solicitacoes'): ?>
      <div class="print-section mb-4">
        <h4 class="text-secondary mb-3">Solicitações de peças</h4>
        <?php if (!empty($solicitacoes)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Peça</th>
                  <th>Fornecedor</th>
                  <th>Cliente</th>
                  <th>Status</th>
                  <th>Data</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($solicitacoes as $solicitacao): ?>
                  <tr>
                    <td><?= (int)$solicitacao['id_solicitacao'] ?></td>
                    <td><?= htmlspecialchars($solicitacao['peca_nome'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($solicitacao['fornecedor_nome'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($solicitacao['cliente_nome'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($solicitacao['status'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($solicitacao['data_solicitacao'] ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-light">Nenhuma solicitação cadastrada.</div>
        <?php endif; ?>
      <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
