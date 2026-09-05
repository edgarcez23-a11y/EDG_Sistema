<?php
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/enviar_email.php';

$con = db_connect();

$clientes = [];
$pecas = [];
$orcamentos = [];
$statusMensagem = '';
$tipoMensagem = '';
$selectedOrcamento = null;
$searchClienteId = $_GET['buscar_cliente_id'] ?? ($_POST['buscar_cliente_id'] ?? '');
$viewOrcamentoId = $_GET['view'] ?? null;
$printOrcamentoId = $_GET['imprimir'] ?? null;
$acao = $_POST['acao'] ?? 'visualizar';

if ($con) {
    $clienteRes = $con->query('SELECT id_cliente, nome, email FROM clientes ORDER BY nome');
    while ($cliente = $clienteRes->fetch_assoc()) {
        $clientes[] = $cliente;
    }

    $pecaRes = $con->query('SELECT id_peca, nome, preco FROM pecas ORDER BY nome');
    while ($peca = $pecaRes->fetch_assoc()) {
        $pecas[] = $peca;
    }

    if (!empty($viewOrcamentoId) || !empty($printOrcamentoId)) {
        $id = (int)($viewOrcamentoId ?? $printOrcamentoId);
        $orcamentoRes = $con->query('SELECT o.*, c.nome as cliente_nome, c.email as cliente_email, p.nome as peca_nome FROM orcamentos o LEFT JOIN clientes c ON o.id_cliente = c.id_cliente LEFT JOIN pecas p ON o.id_peca = p.id_peca WHERE o.id_orcamento = ' . $id);
        $selectedOrcamento = $orcamentoRes->fetch_assoc();
    }

    $orcamentoSql = 'SELECT o.*, c.nome as cliente_nome, c.email as cliente_email, p.nome as peca_nome FROM orcamentos o LEFT JOIN clientes c ON o.id_cliente = c.id_cliente LEFT JOIN pecas p ON o.id_peca = p.id_peca';
    if (!empty($searchClienteId)) {
        $orcamentoSql .= ' WHERE o.id_cliente = ' . (int)$searchClienteId;
    }
    $orcamentoSql .= ' ORDER BY o.id_orcamento DESC LIMIT 20';
    $orcamentoRes = $con->query($orcamentoSql);
    while ($orcamento = $orcamentoRes->fetch_assoc()) {
        $orcamentos[] = $orcamento;
    }
}

$clienteId = $_POST['cliente_id'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$endereco = $_POST['endereco'] ?? '';
$pecaId = $_POST['peca_id'] ?? '';
$quantidade = max(1, (int)($_POST['quantidade'] ?? 1));
$maoObra = (float)($_POST['mao_de_obra'] ?? 0);
$desconto = (float)($_POST['desconto'] ?? 0);
$observacoes = $_POST['observacoes'] ?? '';
$dataOrcamento = $_POST['data_orcamento'] ?? date('Y-m-d');

$itensOrcamento = $_POST['itens'] ?? [];
if (empty($itensOrcamento) && !empty($pecaId)) {
    $itensOrcamento[] = ['peca_id' => $pecaId, 'quantidade' => $quantidade];
}

$valorUnitarioPeca = 0.00;
$valorPecas = 0.00;
$valorTotal = 0.00;
$nomeCliente = '';
$emailCliente = '';
$nomePeca = '';
$quantidadeTotal = 0;
$listaItens = [];
$valorItens = 0.0;

foreach ($pecas as $peca) {
    if ((string)$peca['id_peca'] === (string)$pecaId) {
        $valorUnitarioPeca = (float)$peca['preco'];
        if ($nomePeca === '') {
            $nomePeca = $peca['nome'];
        }
        break;
    }
}

foreach ($itensOrcamento as $item) {
    $itemPecaId = (int)($item['peca_id'] ?? 0);
    $itemQuantidade = max(1, (int)($item['quantidade'] ?? 1));
    $quantidadeTotal += $itemQuantidade;

    foreach ($pecas as $peca) {
        if ((string)$peca['id_peca'] === (string)$itemPecaId) {
            $valorItens += (float)$peca['preco'] * $itemQuantidade;
            $listaItens[] = ['nome' => $peca['nome'], 'quantidade' => $itemQuantidade, 'preco' => (float)$peca['preco']];
            if ($nomePeca === '') {
                $nomePeca = $peca['nome'];
            }
            break;
        }
    }
}

if (!empty($listaItens)) {
    $nomePeca = implode(', ', array_map(static fn ($item) => $item['nome'] . ' (x' . $item['quantidade'] . ')', $listaItens));
}

foreach ($clientes as $cliente) {
    if ((string)$cliente['id_cliente'] === (string)$clienteId) {
        $nomeCliente = $cliente['nome'];
        $emailCliente = $cliente['email'] ?? '';
        break;
    }
}

$valorPecas = !empty($listaItens) ? $valorItens : ($valorUnitarioPeca * $quantidade);
$valorTotal = max(0, ($valorPecas + $maoObra) - $desconto);

if ($selectedOrcamento) {
    $nomeCliente = $selectedOrcamento['cliente_nome'] ?? $nomeCliente;
    $emailCliente = $selectedOrcamento['cliente_email'] ?? $emailCliente;
    $nomePeca = $selectedOrcamento['peca_nome'] ?? $nomePeca;
    $telefone = $selectedOrcamento['telefone'] ?? $telefone;
    $endereco = $selectedOrcamento['endereco'] ?? $endereco;
    $quantidade = (int)($selectedOrcamento['quantidade'] ?? $quantidade);
    $valorUnitarioPeca = (float)($selectedOrcamento['valor_unitario'] ?? $valorUnitarioPeca);
    $maoObra = (float)($selectedOrcamento['mao_de_obra'] ?? $maoObra);
    $desconto = (float)($selectedOrcamento['desconto'] ?? $desconto);
    $observacoes = $selectedOrcamento['observacoes'] ?? $observacoes;
    $dataOrcamento = $selectedOrcamento['data_orcamento'] ?? $dataOrcamento;
    $valorPecas = $valorUnitarioPeca * $quantidade;
    $valorTotal = (float)($selectedOrcamento['total'] ?? max(0, ($valorPecas + $maoObra) - $desconto));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acao === 'enviar_existente') {
    $idOrcamento = (int)($_POST['email_orcamento_id'] ?? 0);
    if ($idOrcamento && $con) {
        $orcamentoRes = $con->query('SELECT o.*, c.nome as cliente_nome, c.email as cliente_email, p.nome as peca_nome FROM orcamentos o LEFT JOIN clientes c ON o.id_cliente = c.id_cliente LEFT JOIN pecas p ON o.id_peca = p.id_peca WHERE o.id_orcamento = ' . $idOrcamento);
        $selectedOrcamento = $orcamentoRes->fetch_assoc();
        if ($selectedOrcamento) {
            $assunto = 'Orçamento André Manutenção de GNV';
            $corpo = '<h3>Orçamento</h3>'
                . '<p><strong>Empresa:</strong> André Manutenção de GNV</p>'
                . '<p><strong>Cliente:</strong> ' . htmlspecialchars($selectedOrcamento['cliente_nome']) . '</p>'
                . '<p><strong>Peça:</strong> ' . htmlspecialchars($selectedOrcamento['peca_nome']) . '</p>'
                . '<p><strong>Quantidade:</strong> ' . (int)$selectedOrcamento['quantidade'] . '</p>'
                . '<p><strong>Mão de obra:</strong> R$ ' . number_format((float)$selectedOrcamento['mao_de_obra'], 2, ',', '.') . '</p>'
                . '<p><strong>Desconto:</strong> R$ ' . number_format((float)$selectedOrcamento['desconto'], 2, ',', '.') . '</p>'
                . '<p><strong>Total:</strong> R$ ' . number_format((float)$selectedOrcamento['total'], 2, ',', '.') . '</p>'
                . '<p><strong>Observações:</strong> ' . nl2br(htmlspecialchars($selectedOrcamento['observacoes'])) . '</p>';

            if (!empty($selectedOrcamento['cliente_email'])) {
                $enviado = enviar_email($selectedOrcamento['cliente_email'], $assunto, $corpo);
                if ($enviado) {
                    $statusMensagem = 'Orçamento enviado com sucesso para o cliente.';
                    $tipoMensagem = 'success';
                } else {
                    $statusMensagem = 'Falha ao enviar o orçamento por e-mail.';
                    $tipoMensagem = 'danger';
                }
            } else {
                $statusMensagem = 'Não foi possível enviar porque o cliente não possui e-mail cadastrado.';
                $tipoMensagem = 'warning';
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($acao, ['visualizar', 'enviar'], true)) {
    if ($con) {
        $valorUnitarioParaPersistir = !empty($listaItens) ? (float)($listaItens[0]['preco'] ?? 0) : $valorUnitarioPeca;
        $quantidadeParaPersistir = !empty($listaItens) ? $quantidadeTotal : $quantidade;
        $pecaPrincipalId = !empty($listaItens) ? (int)array_values(array_filter(array_map(static fn($item) => (int)($item['peca_id'] ?? 0), $itensOrcamento)))[0] : (int)$pecaId;
        $observacoesComItens = $observacoes;
        if (!empty($listaItens)) {
            $descricaoItens = implode(', ', array_map(static fn ($item) => $item['nome'] . ' x' . $item['quantidade'], $listaItens));
            $observacoesComItens = 'Peças: ' . $descricaoItens . ($observacoes !== '' ? ' | Observações: ' . $observacoes : '');
        }

        $stmt = $con->prepare('INSERT INTO orcamentos (id_cliente, id_peca, cliente_nome, telefone, endereco, quantidade, valor_unitario, mao_de_obra, desconto, total, observacoes, data_orcamento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iiisssddddss', $clienteId, $pecaPrincipalId, $nomeCliente, $telefone, $endereco, $quantidadeParaPersistir, $valorUnitarioParaPersistir, $maoObra, $desconto, $valorTotal, $observacoesComItens, $dataOrcamento);
        $stmt->execute();
        $stmt->close();
    }

    if ($acao === 'enviar') {
        $assunto = 'Orçamento André Manutenção de GNV';
        $corpo = '<h3>Orçamento</h3>'
            . '<p><strong>Empresa:</strong> André Manutenção de GNV</p>'
            . '<p><strong>Cliente:</strong> ' . htmlspecialchars($nomeCliente) . '</p>'
            . '<p><strong>Peças:</strong> ' . htmlspecialchars($nomePeca) . '</p>'
            . '<p><strong>Quantidade total:</strong> ' . (int)$quantidadeTotal . '</p>'
            . '<p><strong>Mão de obra:</strong> R$ ' . number_format($maoObra, 2, ',', '.') . '</p>'
            . '<p><strong>Desconto:</strong> R$ ' . number_format($desconto, 2, ',', '.') . '</p>'
            . '<p><strong>Total:</strong> R$ ' . number_format($valorTotal, 2, ',', '.') . '</p>'
            . '<p><strong>Observações:</strong> ' . nl2br(htmlspecialchars($observacoesComItens ?? $observacoes)) . '</p>';

        if (!empty($emailCliente)) {
            $enviado = enviar_email($emailCliente, $assunto, $corpo);
            if ($enviado) {
                $statusMensagem = 'Orçamento salvo e enviado com sucesso para o cliente.';
                $tipoMensagem = 'success';
            } else {
                $statusMensagem = 'Orçamento salvo, mas falha ao enviar o e-mail.';
                $tipoMensagem = 'danger';
            }
        } else {
            $statusMensagem = 'Orçamento salvo, mas não foi possível enviar porque o cliente não possui e-mail cadastrado.';
            $tipoMensagem = 'warning';
        }
    } else {
        $statusMensagem = 'Orçamento salvo com sucesso.';
        $tipoMensagem = 'success';
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Orçamento - André Manutenção de GNV</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <style>
    @media print {
      body {
        background: #fff !important;
        padding: 0;
        font-size: 10pt !important;
      }
      .navbar-custom,
      .btn,
      .alert,
      form {
        display: none !important;
      }
      .container {
        max-width: 100% !important;
        padding: 0 !important;
      }
      .card,
      .card-body,
      .border,
      .bg-white {
        box-shadow: none !important;
        border: 0 !important;
        background: #fff !important;
        padding: 0.2rem !important;
      }
      h2, h4, h5 {
        font-size: 14pt !important;
        margin-bottom: 0.2rem !important;
      }
      p {
        margin-bottom: 0.15rem !important;
        font-size: 12pt !important;
      }
      .mt-4 {
        margin-top: 0.5rem !important;
      }
      .mb-3, .mb-4 {
        margin-bottom: 0.4rem !important;
      }
      .py-4 {
        padding-top: 0.25rem !important;
        padding-bottom: 0.25rem !important;
      }
      .col-md-6,
      .col-md-3,
      .col-md-2,
      .col-md-9,
      .col-md-12 {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
      }
      .row {
        display: block !important;
      }
      .badge {
        font-size: 8pt !important;
        padding: 0.15rem 0.3rem !important;
      }
      table {
        font-size: 12pt !important;
      }
      .print-quote {
        border: 1px solid #ddd !important;
        padding: 0.4rem !important;
      }
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
    <a class="btn btn-primary" href="relatorios.php">Relatórios</a>
  </div>
</header>

<div class="container py-4">
  <div class="card shadow-sm border-0">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <h2 class="fw-bold text-primary">André Manutenção de GNV</h2>
        <p class="text-muted mb-0">Orçamento de serviços e peças</p>
      </div>

      <form method="post" class="row g-3" id="form-orcamento">
        <input type="hidden" name="acao" id="acao" value="visualizar">
        <div class="col-md-6">
          <label class="form-label">Cliente</label>
          <select id="cliente_id" name="cliente_id" class="form-select" required>
            <option value="">Selecione o cliente</option>
            <?php foreach ($clientes as $cliente): ?>
              <option value="<?= (int)$cliente['id_cliente'] ?>" data-email="<?= htmlspecialchars($cliente['email'] ?? '') ?>" <?= (string)$clienteId === (string)$cliente['id_cliente'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Telefone</label>
          <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($telefone) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Data</label>
          <input type="date" name="data_orcamento" class="form-control" value="<?= htmlspecialchars($dataOrcamento) ?>">
        </div>

        <div class="col-md-12">
          <label class="form-label">E-mail do cliente</label>
          <input type="email" id="email_cliente" name="email_cliente" class="form-control" value="<?= htmlspecialchars($emailCliente) ?>" readonly>
        </div>

        <div class="col-md-12">
          <label class="form-label">Endereço</label>
          <input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($endereco) ?>" placeholder="Endereço completo do cliente">
        </div>

        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label mb-0">Peças do orçamento</label>
            <button type="button" class="btn btn-sm btn-outline-primary" id="adicionar-item-orcamento">Adicionar peça</button>
          </div>
          <div id="lista-itens-orcamento" class="d-grid gap-2">
            <?php 
              $itensExibicao = !empty($itensOrcamento) ? $itensOrcamento : [['peca_id' => $pecaId, 'quantidade' => $quantidade]];
              foreach ($itensExibicao as $index => $item): 
                $selectedPecaId = (string)($item['peca_id'] ?? '');
                $itemQtd = (int)($item['quantidade'] ?? 1);
            ?>
              <div class="row g-2 item-orcamento-row">
                <div class="col-md-7">
                  <select name="itens[<?= $index ?>][peca_id]" class="form-select item-peca-select" required>
                    <option value="">Selecione a peça</option>
                    <?php foreach ($pecas as $peca): ?>
                      <option value="<?= (int)$peca['id_peca'] ?>" data-preco="<?= number_format((float)$peca['preco'], 2, '.', '') ?>" <?= $selectedPecaId === (string)$peca['id_peca'] ? 'selected' : '' ?>><?= htmlspecialchars($peca['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <input type="number" name="itens[<?= $index ?>][quantidade]" class="form-control item-quantidade" min="1" value="<?= $itemQtd ?>" required>
                </div>
                <div class="col-md-2 text-end">
                  <button type="button" class="btn btn-outline-danger btn-sm remover-item-orcamento" title="Remover peça">Remover</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Mão de obra</label>
          <input type="number" step="0.01" name="mao_de_obra" class="form-control" value="<?= number_format($maoObra, 2, '.', '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Desconto</label>
          <input type="number" step="0.01" name="desconto" class="form-control" value="<?= number_format($desconto, 2, '.', '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Subtotal peças</label>
          <input type="text" id="subtotal_pecas_orcamento" class="form-control" value="R$ <?= number_format($valorPecas, 2, ',', '.') ?>" readonly>
        </div>

        <div class="col-md-12">
          <label class="form-label">Observações</label>
          <textarea name="observacoes" class="form-control" rows="3" placeholder="Informações adicionais do orçamento"><?= htmlspecialchars($observacoes) ?></textarea>
        </div>

        <div class="col-12 text-end d-flex justify-content-end gap-2">
          <a class="btn btn-outline-info" href="#lista-orcamentos">Visualizar / buscar orçamentos</a>
          <button type="button" class="btn btn-outline-primary" onclick="window.print()">Imprimir</button>
          <button type="submit" class="btn btn-success" onclick="document.getElementById('acao').value='visualizar'">Gerar orçamento</button>
          <button type="submit" class="btn btn-warning" onclick="document.getElementById('acao').value='enviar'">Enviar por e-mail</button>
        </div>
      </form>

      <?php if (!empty($statusMensagem)): ?>
        <div class="alert alert-<?= htmlspecialchars($tipoMensagem) ?> mt-3" role="alert">
          <?= htmlspecialchars($statusMensagem) ?>
        </div>
      <?php endif; ?>

      <div id="lista-orcamentos" class="mt-4 border rounded p-3 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Orçamentos salvos</h5>
          <a class="btn btn-sm btn-outline-secondary" href="orcamentos.php">Limpar busca</a>
        </div>

        <form method="get" class="row g-2 mb-3">
          <div class="col-md-8">
            <select name="buscar_cliente_id" class="form-select">
              <option value="">Buscar por cliente</option>
              <?php foreach ($clientes as $cliente): ?>
                <option value="<?= (int)$cliente['id_cliente'] ?>" <?= (string)$searchClienteId === (string)$cliente['id_cliente'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <button class="btn btn-outline-primary w-100" type="submit">Buscar orçamentos</button>
          </div>
        </form>

        <?php if (!empty($orcamentos)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Cliente</th>
                  <th>Peça</th>
                  <th>Total</th>
                  <th>Data</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orcamentos as $orcamento): ?>
                  <tr>
                    <td><?= htmlspecialchars($orcamento['cliente_nome'] ?? 'Cliente não informado') ?></td>
                    <td><?= htmlspecialchars($orcamento['peca_nome'] ?? '-') ?></td>
                    <td>R$ <?= number_format((float)($orcamento['total'] ?? 0), 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($orcamento['data_orcamento'] ?? '-') ?></td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <a class="btn btn-sm btn-outline-primary" href="orcamentos.php?view=<?= (int)$orcamento['id_orcamento'] ?>#lista-orcamentos">Visualizar</a>
                        <a class="btn btn-sm btn-outline-secondary" href="orcamentos.php?imprimir=<?= (int)$orcamento['id_orcamento'] ?>" target="_blank">Imprimir</a>
                        <form method="post" class="d-inline">
                          <input type="hidden" name="acao" value="enviar_existente">
                          <input type="hidden" name="email_orcamento_id" value="<?= (int)$orcamento['id_orcamento'] ?>">
                          <button type="submit" class="btn btn-sm btn-warning">Enviar</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="mb-0 text-muted">Nenhum orçamento encontrado para este cliente.</p>
        <?php endif; ?>
      </div>

      <div class="mt-4 border rounded p-4 bg-white print-quote">
        <div class="row align-items-start mb-3">
          <div class="col-md-8">
            <h4 class="fw-bold text-primary mb-1">Orçamento</h4>
            <h5 class="mb-1">André Manutenção de GNV</h5>
            <p class="mb-1">Serviços e peças para GNV</p>
            <p class="mb-0">Data: <?= htmlspecialchars(date('d/m/Y', strtotime($dataOrcamento))) ?></p>
          </div>
          <div class="col-md-4 text-md-end">
            <span class="badge bg-success">Válido por 7 dias</span>
            <p class="mb-0 mt-2"><strong>Nº:</strong> ORC-<?= date('Ymd') ?></p>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="border rounded p-3">
              <p class="mb-1"><strong>Cliente:</strong> <?= htmlspecialchars($nomeCliente ?: 'Aguardando seleção') ?></p>
              <p class="mb-1"><strong>Telefone:</strong> <?= htmlspecialchars($telefone ?: 'Não informado') ?></p>
              <p class="mb-1"><strong>E-mail:</strong> <?= htmlspecialchars($emailCliente ?: 'Não informado') ?></p>
              <p class="mb-0"><strong>Endereço:</strong> <?= htmlspecialchars($endereco ?: 'Não informado') ?></p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-3">
              <p class="mb-1"><strong>Peça:</strong> <?= htmlspecialchars($nomePeca ?: 'Aguardando seleção') ?></p>
              <p class="mb-1"><strong>Quantidade:</strong> <?= (int)$quantidade ?></p>
              <p class="mb-0"><strong>Valor unitário:</strong> R$ <?= number_format($valorUnitarioPeca, 2, ',', '.') ?></p>
            </div>
          </div>
        </div>

        <table class="table table-sm table-bordered mb-3">
          <thead>
            <tr class="table-light">
              <th>Descrição</th>
              <th>Qtd</th>
              <th>V. unit.</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?= htmlspecialchars($nomePeca ?: 'Peça não selecionada') ?></td>
              <td><?= (int)$quantidade ?></td>
              <td>R$ <?= number_format($valorUnitarioPeca, 2, ',', '.') ?></td>
              <td>R$ <?= number_format($valorPecas, 2, ',', '.') ?></td>
            </tr>
            <tr>
              <td colspan="3" class="text-end fw-bold">Mão de obra</td>
              <td>R$ <?= number_format($maoObra, 2, ',', '.') ?></td>
            </tr>
            <tr>
              <td colspan="3" class="text-end fw-bold">Desconto</td>
              <td>- R$ <?= number_format($desconto, 2, ',', '.') ?></td>
            </tr>
            <tr class="table-light">
              <td colspan="3" class="text-end fw-bold">Total</td>
              <td class="fw-bold text-success">R$ <?= number_format($valorTotal, 2, ',', '.') ?></td>
            </tr>
          </tbody>
        </table>

        <div class="row align-items-end">
          <div class="col-md-8">
            <p class="mb-1"><strong>Observações:</strong> <?= htmlspecialchars($observacoes ?: 'Nenhuma observação informada') ?></p>
          </div>
          <div class="col-md-4 text-center">
            <div class="border-top pt-3 mt-3">
              <p class="mb-0">____________________________</p>
              <p class="mb-0">André Manutenção de GNV</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const selectCliente = document.getElementById('cliente_id');
    const inputEmail = document.getElementById('email_cliente');
    const listaItens = document.getElementById('lista-itens-orcamento');
    const subtotalInput = document.getElementById('subtotal_pecas_orcamento');
    const maoObraInput = document.querySelector('input[name="mao_de_obra"]');
    const descontoInput = document.querySelector('input[name="desconto"]');

    function atualizarEmailCliente() {
      if (selectCliente && inputEmail) {
        const selected = selectCliente.options[selectCliente.selectedIndex];
        inputEmail.value = selected && selected.dataset.email ? selected.dataset.email : '';
      }
    }

    function formatarMoeda(valor) {
      return 'R$ ' + Number(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function atualizarSubtotalItens() {
      if (!listaItens) return;

      let subtotal = 0;
      listaItens.querySelectorAll('.item-orcamento-row').forEach(function (row) {
        const select = row.querySelector('.item-peca-select');
        const quantidade = Number(row.querySelector('.item-quantidade').value || 0);
        const preco = Number((select && select.selectedOptions[0] && select.selectedOptions[0].dataset.preco) || 0);
        subtotal += preco * quantidade;
      });

      if (subtotalInput) {
        subtotalInput.value = formatarMoeda(subtotal);
      }
    }

    function adicionarItem() {
      const rows = listaItens.querySelectorAll('.item-orcamento-row');
      const index = rows.length;
      const row = document.createElement('div');
      row.className = 'row g-2 item-orcamento-row';
      row.innerHTML = `
        <div class="col-md-7">
          <select name="itens[${index}][peca_id]" class="form-select item-peca-select" required>
            <option value="">Selecione a peça</option>
            <?php foreach ($pecas as $peca): ?>
              <option value="<?= (int)$peca['id_peca'] ?>" data-preco="<?= number_format((float)$peca['preco'], 2, '.', '') ?>"><?= htmlspecialchars($peca['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <input type="number" name="itens[${index}][quantidade]" class="form-control item-quantidade" min="1" value="1" required>
        </div>
        <div class="col-md-2 text-end">
          <button type="button" class="btn btn-outline-danger btn-sm remover-item-orcamento" title="Remover peça">Remover</button>
        </div>
      `;
      listaItens.appendChild(row);
      row.querySelector('.item-peca-select').addEventListener('change', atualizarSubtotalItens);
      row.querySelector('.item-quantidade').addEventListener('input', atualizarSubtotalItens);
      row.querySelector('.remover-item-orcamento').addEventListener('click', function () {
        row.remove();
        atualizarSubtotalItens();
      });
      atualizarSubtotalItens();
    }

    if (selectCliente) {
      selectCliente.addEventListener('change', atualizarEmailCliente);
      atualizarEmailCliente();
    }

    if (listaItens) {
      listaItens.querySelectorAll('.item-peca-select').forEach(function (select) {
        select.addEventListener('change', atualizarSubtotalItens);
      });
      listaItens.querySelectorAll('.item-quantidade').forEach(function (input) {
        input.addEventListener('input', atualizarSubtotalItens);
      });
      listaItens.querySelectorAll('.remover-item-orcamento').forEach(function (button) {
        button.addEventListener('click', function () {
          button.closest('.item-orcamento-row').remove();
          atualizarSubtotalItens();
        });
      });
      document.getElementById('adicionar-item-orcamento').addEventListener('click', adicionarItem);
      atualizarSubtotalItens();
    }

    [maoObraInput, descontoInput].forEach(function (input) {
      if (input) {
        input.addEventListener('input', atualizarSubtotalItens);
      }
    });
  });
</script>
</body>
</html>