<?php
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/enviar_email.php';

$con = db_connect();

$clientes = [];
$pecas = [];
$pecasBaixa = [];
$servicos = [];
$mensagem = '';
$tipoMensagem = '';
$selectedServico = null;
$searchClienteId = $_GET['buscar_cliente_id'] ?? ($_POST['buscar_cliente_id'] ?? '');
$viewServicoId = $_GET['view'] ?? null;
$printServicoId = $_GET['imprimir'] ?? null;
$acao = $_POST['acao'] ?? 'visualizar';

$clienteId = $_POST['cliente_id'] ?? '';
$pecaId = $_POST['peca_id'] ?? '';
$quantidade = max(1, (int)($_POST['quantidade'] ?? 1));
$observacoes = trim($_POST['observacoes'] ?? '');
$dataServico = $_POST['data_servico'] ?? date('Y-m-d');
$itensServico = $_POST['itens'] ?? [];
if (empty($itensServico) && !empty($pecaId)) {
    $itensServico[] = ['peca_id' => $pecaId, 'quantidade' => $quantidade];
}

$nomeCliente = '';
$emailCliente = '';
$nomePeca = '';
$estoqueDisponivel = 0;
$quantidadeServico = 0;
$observacoesServico = '';
$valorUnitarioPeca = 0.00;
$valorTotal = 0.00;
$listaItens = [];
$valorItens = 0.0;
$quantidadeTotalItens = 0;

if ($con) {
    $clienteRes = $con->query('SELECT id_cliente, nome, email FROM clientes ORDER BY nome');
    while ($cliente = $clienteRes->fetch_assoc()) {
        $clientes[] = $cliente;
    }

    $pecaRes = $con->query('SELECT id_peca, nome, preco, quantidade_estoque FROM pecas ORDER BY nome');
    while ($peca = $pecaRes->fetch_assoc()) {
        $pecas[] = $peca;
    }

    $pecaBaixaRes = $con->query('SELECT id_peca, nome, quantidade_estoque FROM pecas WHERE quantidade_estoque <= 3 ORDER BY quantidade_estoque ASC, nome ASC');
    while ($peca = $pecaBaixaRes->fetch_assoc()) {
        $pecasBaixa[] = $peca;
    }

    if (!empty($viewServicoId) || !empty($printServicoId)) {
        $id = (int)($viewServicoId ?? $printServicoId);
        $servicoRes = $con->query('SELECT m.*, c.nome AS cliente_nome, c.email AS cliente_email FROM manutencoes m LEFT JOIN clientes c ON m.id_cliente = c.id_cliente WHERE m.id_manutencao = ' . $id . ' AND m.status = "Concluída"');
        $selectedServico = $servicoRes->fetch_assoc();
    }

    $servicoSql = 'SELECT m.*, c.nome AS cliente_nome, c.email AS cliente_email FROM manutencoes m LEFT JOIN clientes c ON m.id_cliente = c.id_cliente WHERE m.status = "Concluída"';
    if (!empty($searchClienteId)) {
        $servicoSql .= ' AND m.id_cliente = ' . (int)$searchClienteId;
    }
    $servicoSql .= ' ORDER BY m.data_manutencao DESC LIMIT 20';
    $servicoRes = $con->query($servicoSql);
    while ($servico = $servicoRes->fetch_assoc()) {
        $servicos[] = $servico;
    }
}

foreach ($pecas as $peca) {
    if ((string)$peca['id_peca'] === (string)$pecaId) {
        $valorUnitarioPeca = (float)$peca['preco'];
        $nomePeca = $peca['nome'];
        $estoqueDisponivel = (int)$peca['quantidade_estoque'];
        break;
    }
}

foreach ($itensServico as $item) {
    $itemPecaId = (int)($item['peca_id'] ?? 0);
    $itemQuantidade = max(1, (int)($item['quantidade'] ?? 1));
    $quantidadeTotalItens += $itemQuantidade;

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

$valorTotal = !empty($listaItens) ? $valorItens : ($valorUnitarioPeca * $quantidade);

if ($selectedServico) {
    $nomeCliente = $selectedServico['cliente_nome'] ?? $nomeCliente;
    $emailCliente = $selectedServico['cliente_email'] ?? $emailCliente;
    $dataServico = $selectedServico['data_manutencao'] ?? $dataServico;
    $observacoesServico = $selectedServico['descricao'] ?? '';

    if (preg_match('/Peça:\s*(.+?)\s*\|/u', $observacoesServico, $matchPeca)) {
        $nomePeca = trim($matchPeca[1]);
    }
    if (preg_match('/Quantidade:\s*(\d+)/u', $observacoesServico, $matchQtd)) {
        $quantidadeServico = (int)$matchQtd[1];
    }
    if (preg_match('/Observações:\s*(.+)$/u', $observacoesServico, $matchObs)) {
        $observacoes = trim($matchObs[1]);
    } else {
        $observacoes = $observacoesServico;
    }
    $quantidade = $quantidadeServico > 0 ? $quantidadeServico : $quantidade;
    $valorTotal = $valorUnitarioPeca * $quantidade;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['acao'])) {
    if ($_POST['acao'] === 'enviar_existente') {
        $idServico = (int)($_POST['servico_id'] ?? 0);
        if ($idServico && $con) {
            $servicoRes = $con->query('SELECT m.*, c.nome AS cliente_nome, c.email AS cliente_email FROM manutencoes m LEFT JOIN clientes c ON m.id_cliente = c.id_cliente WHERE m.id_manutencao = ' . $idServico . ' AND m.status = "Concluída"');
            $selectedServico = $servicoRes->fetch_assoc();
            if ($selectedServico) {
                $assunto = 'Serviço finalizado - André Manutenção de GNV';
                $corpo = '<h3>Serviço finalizado</h3>'
                    . '<p><strong>Cliente:</strong> ' . htmlspecialchars($selectedServico['cliente_nome']) . '</p>'
                    . '<p><strong>Descrição:</strong> ' . nl2br(htmlspecialchars($selectedServico['descricao'])) . '</p>';

                if (!empty($selectedServico['cliente_email'])) {
                    $enviado = enviar_email($selectedServico['cliente_email'], $assunto, $corpo);
                    if ($enviado) {
                        $mensagem = 'Serviço enviado com sucesso para o cliente.';
                        $tipoMensagem = 'success';
                    } else {
                        $mensagem = 'Falha ao enviar o serviço por e-mail.';
                        $tipoMensagem = 'danger';
                    }
                } else {
                    $mensagem = 'Não foi possível enviar porque o cliente não possui e-mail cadastrado.';
                    $tipoMensagem = 'warning';
                }
            }
        }
    } elseif (in_array($acao, ['visualizar', 'enviar'], true)) {
        if (empty($clienteId) || empty($itensServico)) {
            $mensagem = 'Selecione um cliente e ao menos uma peça para finalizar o serviço.';
            $tipoMensagem = 'danger';
        } else {
            $itensValidos = true;
            $descricaoItens = [];
            foreach ($itensServico as $item) {
                $itemPecaId = (int)($item['peca_id'] ?? 0);
                $itemQuantidade = max(1, (int)($item['quantidade'] ?? 1));
                $pecaSelecionada = null;
                foreach ($pecas as $peca) {
                    if ((string)$peca['id_peca'] === (string)$itemPecaId) {
                        $pecaSelecionada = $peca;
                        break;
                    }
                }

                if (!$pecaSelecionada) {
                    $mensagem = 'Uma das peças selecionadas é inválida.';
                    $tipoMensagem = 'danger';
                    $itensValidos = false;
                    break;
                }

                $descricaoItens[] = $pecaSelecionada['nome'] . ' x' . $itemQuantidade;
                if ((int)$pecaSelecionada['quantidade_estoque'] < $itemQuantidade) {
                    $mensagem = 'Estoque insuficiente para a peça ' . $pecaSelecionada['nome'] . '. Estoque disponível: ' . (int)$pecaSelecionada['quantidade_estoque'] . '.';
                    $tipoMensagem = 'warning';
                    $itensValidos = false;
                    break;
                }
            }

            if ($itensValidos) {
                foreach ($itensServico as $item) {
                    $itemPecaId = (int)($item['peca_id'] ?? 0);
                    $itemQuantidade = max(1, (int)($item['quantidade'] ?? 1));
                    $stmt = $con->prepare('UPDATE pecas SET quantidade_estoque = quantidade_estoque - ? WHERE id_peca = ?');
                    $stmt->bind_param('ii', $itemQuantidade, $itemPecaId);
                    $stmt->execute();
                    $stmt->close();
                }

                $descricao = 'Serviço finalizado - Peças: ' . implode(', ', $descricaoItens) . ' | Valor total: R$ ' . number_format($valorTotal, 2, ',', '.') . ' | Observações: ' . $observacoes;

                $stmt = $con->prepare('INSERT INTO manutencoes (id_cliente, data_manutencao, descricao, status) VALUES (?, ?, ?, ?)');
                $status = 'Concluída';
                $stmt->bind_param('isss', $clienteId, $dataServico, $descricao, $status);
                $stmt->execute();
                $stmt->close();

                if ($acao === 'enviar') {
                    $assunto = 'Serviço finalizado - André Manutenção de GNV';
                    $corpo = '<h3>Serviço finalizado</h3>'
                        . '<p><strong>Cliente:</strong> ' . htmlspecialchars($nomeCliente) . '</p>'
                        . '<p><strong>Peças:</strong> ' . htmlspecialchars($nomePeca) . '</p>'
                        . '<p><strong>Quantidade total:</strong> ' . (int)$quantidadeTotalItens . '</p>'
                        . '<p><strong>Valor total:</strong> R$ ' . number_format($valorTotal, 2, ',', '.') . '</p>'
                        . '<p><strong>Observações:</strong> ' . nl2br(htmlspecialchars($observacoes)) . '</p>';

                    if (!empty($emailCliente)) {
                        $enviado = enviar_email($emailCliente, $assunto, $corpo);
                        if ($enviado) {
                            $mensagem = 'Serviço salvo e enviado com sucesso para o cliente.';
                            $tipoMensagem = 'success';
                        } else {
                            $mensagem = 'Serviço salvo, mas falha ao enviar o e-mail.';
                            $tipoMensagem = 'danger';
                        }
                    } else {
                        $mensagem = 'Serviço salvo, mas não foi possível enviar porque o cliente não possui e-mail cadastrado.';
                        $tipoMensagem = 'warning';
                    }
                } else {
                    $mensagem = 'Serviço salvo com sucesso e baixa de estoque registrada.';
                    $tipoMensagem = 'success';
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Serviços - Oficina</title>
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
    <a class="btn btn-primary" href="orcamentos.php">Orçamentos</a>
    <a class="btn btn-primary" href="relatorios.php">Relatórios</a>
  </div>
</header>

<div class="container py-4">
  <div class="card shadow-sm border-0">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <h2 class="fw-bold text-primary">Serviços</h2>
        <p class="text-muted mb-0">Finalize o atendimento, registre a baixa de estoque e gere o resumo do serviço.</p>
      </div>

      <?php if ($mensagem !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($tipoMensagem) ?>" role="alert">
          <?= htmlspecialchars($mensagem) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="row g-3" id="form-servico">
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

        <div class="col-md-6">
          <label class="form-label">Data do serviço</label>
          <input type="date" name="data_servico" class="form-control" value="<?= htmlspecialchars($dataServico) ?>" required>
        </div>

        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label mb-0">Peças utilizadas</label>
            <button type="button" class="btn btn-sm btn-outline-primary" id="adicionar-item-servico">Adicionar peça</button>
          </div>
          <div id="lista-itens-servico" class="d-grid gap-2">
            <?php 
              $itensExibicao = !empty($itensServico) ? $itensServico : [['peca_id' => $pecaId, 'quantidade' => $quantidade]];
              foreach ($itensExibicao as $index => $item): 
                $selectedPecaId = (string)($item['peca_id'] ?? '');
                $itemQtd = (int)($item['quantidade'] ?? 1);
            ?>
              <div class="row g-2 item-servico-row">
                <div class="col-md-7">
                  <select name="itens[<?= $index ?>][peca_id]" class="form-select item-peca-servico" required>
                    <option value="">Selecione a peça</option>
                    <?php foreach ($pecas as $peca): ?>
                      <option value="<?= (int)$peca['id_peca'] ?>" data-preco="<?= number_format((float)$peca['preco'], 2, '.', '') ?>" data-estoque="<?= (int)$peca['quantidade_estoque'] ?>" <?= $selectedPecaId === (string)$peca['id_peca'] ? 'selected' : '' ?>><?= htmlspecialchars($peca['nome']) ?> (estoque: <?= (int)$peca['quantidade_estoque'] ?>)</option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <input type="number" name="itens[<?= $index ?>][quantidade]" class="form-control item-quantidade-servico" min="1" value="<?= $itemQtd ?>" required>
                </div>
                <div class="col-md-2 text-end">
                  <button type="button" class="btn btn-outline-danger btn-sm remover-item-servico" title="Remover peça">Remover</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div id="estoque-info" class="form-text mt-2 text-muted">
            Estoque disponível: <?= (int)$estoqueDisponivel ?> unidade(s)
          </div>
        </div>

        <div class="col-md-12">
          <label class="form-label">E-mail do cliente</label>
          <input type="email" id="email_cliente" name="email_cliente" class="form-control" value="<?= htmlspecialchars($emailCliente) ?>" readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Subtotal peças</label>
          <input type="text" id="subtotal_pecas_servico" class="form-control" value="R$ <?= number_format($valorTotal, 2, ',', '.') ?>" readonly>
        </div>

        <div class="col-md-12">
          <label class="form-label">Observações</label>
          <textarea name="observacoes" class="form-control" rows="3" placeholder="Descreva o serviço realizado e o estado da peça."><?= htmlspecialchars($observacoes) ?></textarea>
        </div>

        <div class="col-12 text-end d-flex justify-content-end gap-2">
          <a class="btn btn-outline-info" href="#lista-servicos">Visualizar / buscar serviços</a>
          <button type="button" class="btn btn-outline-primary" onclick="window.print()">Imprimir</button>
          <button type="submit" class="btn btn-success" onclick="document.getElementById('acao').value='visualizar'">Gerar Serviço</button>
          <button type="submit" class="btn btn-warning" onclick="document.getElementById('acao').value='enviar'">Enviar por e-mail</button>
        </div>
      </form>

      <div id="lista-servicos" class="mt-4 border rounded p-3 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Serviços finalizados</h5>
          <a class="btn btn-sm btn-outline-secondary" href="servicos.php">Limpar busca</a>
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
            <button class="btn btn-outline-primary w-100" type="submit">Buscar serviços</button>
          </div>
        </form>

        <?php if (!empty($servicos)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Cliente</th>
                  <th>Peça</th>
                  <th>Data</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($servicos as $servico): ?>
                  <?php
                    $descricaoServico = $servico['descricao'] ?? '';
                    $pecaServico = '-';
                    if (preg_match('/Peça:\s*(.+?)\s*\|/u', $descricaoServico, $matchPeca)) {
                      $pecaServico = htmlspecialchars(trim($matchPeca[1]));
                    }
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($servico['cliente_nome'] ?? 'Cliente não informado') ?></td>
                    <td><?= $pecaServico ?></td>
                    <td><?= htmlspecialchars($servico['data_manutencao'] ?? '-') ?></td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <a class="btn btn-sm btn-outline-primary" href="servicos.php?view=<?= (int)$servico['id_manutencao'] ?>#lista-servicos">Visualizar</a>
                        <a class="btn btn-sm btn-outline-secondary" href="servicos.php?imprimir=<?= (int)$servico['id_manutencao'] ?>" target="_blank">Imprimir</a>
                        <form method="post" class="d-inline">
                          <input type="hidden" name="acao" value="enviar_existente">
                          <input type="hidden" name="servico_id" value="<?= (int)$servico['id_manutencao'] ?>">
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
          <p class="mb-0 text-muted">Nenhum serviço encontrado para este cliente.</p>
        <?php endif; ?>
      </div>

      <div class="mt-4 border rounded p-4 bg-white print-quote">
        <div class="row align-items-start mb-3">
          <div class="col-md-8">
            <h4 class="fw-bold text-primary mb-1">Resumo de serviço</h4>
            <h5 class="mb-1">André Manutenção de GNV</h5>
            <p class="mb-1">Finalização da manutenção e baixa de estoque</p>
            <p class="mb-0">Data: <?= htmlspecialchars(date('d/m/Y', strtotime($dataServico))) ?></p>
          </div>
          <div class="col-md-4 text-md-end">
            <span class="badge bg-success">Finalizado</span>
            <p class="mb-0 mt-2"><strong>Nº:</strong> SRV-<?= date('Ymd') ?></p>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="border rounded p-3">
              <p class="mb-1"><strong>Cliente:</strong> <?= htmlspecialchars($nomeCliente ?: 'Aguardando seleção') ?></p>
              <p class="mb-1"><strong>Peça:</strong> <?= htmlspecialchars($nomePeca ?: 'Aguardando seleção') ?></p>
              <p class="mb-1"><strong>Quantidade usada:</strong> <?= (int)$quantidade ?></p>
              <p class="mb-0"><strong>Estoque disponível:</strong> <?= (int)$estoqueDisponivel ?> unidade(s)</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-3">
              <p class="mb-1"><strong>E-mail:</strong> <?= htmlspecialchars($emailCliente ?: 'Não informado') ?></p>
              <p class="mb-0"><strong>Valor total:</strong> R$ <?= number_format($valorTotal, 2, ',', '.') ?></p>
            </div>
          </div>
        </div>

        <div class="border rounded p-3 mb-3">
          <p class="mb-1"><strong>Observações:</strong> <?= htmlspecialchars($observacoes ?: 'Nenhuma observação informada') ?></p>
        </div>

        <div class="row align-items-end">
          <div class="col-md-8">
            <p class="mb-0">Serviço concluído com baixa de estoque registrada.</p>
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
    const listaItens = document.getElementById('lista-itens-servico');
    const estoqueInfo = document.getElementById('estoque-info');
    const subtotalInput = document.getElementById('subtotal_pecas_servico');

    function atualizarEmailCliente() {
      if (selectCliente && inputEmail) {
        const selected = selectCliente.options[selectCliente.selectedIndex];
        inputEmail.value = selected && selected.dataset.email ? selected.dataset.email : '';
      }
    }

    function formatarMoeda(valor) {
      return 'R$ ' + Number(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function atualizarEstoqueInfo() {
      if (!listaItens || !estoqueInfo) {
        return;
      }

      const primeiraLinha = listaItens.querySelector('.item-servico-row');
      if (!primeiraLinha) {
        estoqueInfo.textContent = 'Estoque disponível: 0 unidade(s)';
        return;
      }

      const select = primeiraLinha.querySelector('.item-peca-servico');
      const selected = select && select.selectedOptions[0];
      const estoque = selected && selected.dataset.estoque !== undefined ? selected.dataset.estoque : '0';
      estoqueInfo.textContent = `Estoque disponível: ${estoque} unidade(s)`;
    }

    function atualizarSubtotalItens() {
      if (!listaItens) return;

      let subtotal = 0;
      listaItens.querySelectorAll('.item-servico-row').forEach(function (row) {
        const select = row.querySelector('.item-peca-servico');
        const quantidade = Number(row.querySelector('.item-quantidade-servico').value || 0);
        const preco = Number((select && select.selectedOptions[0] && select.selectedOptions[0].dataset.preco) || 0);
        subtotal += preco * quantidade;
      });

      if (subtotalInput) {
        subtotalInput.value = formatarMoeda(subtotal);
      }

      atualizarEstoqueInfo();
    }

    function adicionarItem() {
      const rows = listaItens.querySelectorAll('.item-servico-row');
      const index = rows.length;
      const row = document.createElement('div');
      row.className = 'row g-2 item-servico-row';
      row.innerHTML = `
        <div class="col-md-7">
          <select name="itens[${index}][peca_id]" class="form-select item-peca-servico" required>
            <option value="">Selecione a peça</option>
            <?php foreach ($pecas as $peca): ?>
              <option value="<?= (int)$peca['id_peca'] ?>" data-preco="<?= number_format((float)$peca['preco'], 2, '.', '') ?>" data-estoque="<?= (int)$peca['quantidade_estoque'] ?>"><?= htmlspecialchars($peca['nome']) ?> (estoque: <?= (int)$peca['quantidade_estoque'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <input type="number" name="itens[${index}][quantidade]" class="form-control item-quantidade-servico" min="1" value="1" required>
        </div>
        <div class="col-md-2 text-end">
          <button type="button" class="btn btn-outline-danger btn-sm remover-item-servico" title="Remover peça">Remover</button>
        </div>
      `;
      listaItens.appendChild(row);
      row.querySelector('.item-peca-servico').addEventListener('change', atualizarSubtotalItens);
      row.querySelector('.item-quantidade-servico').addEventListener('input', atualizarSubtotalItens);
      row.querySelector('.remover-item-servico').addEventListener('click', function () {
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
      listaItens.querySelectorAll('.item-peca-servico').forEach(function (select) {
        select.addEventListener('change', atualizarSubtotalItens);
      });
      listaItens.querySelectorAll('.item-quantidade-servico').forEach(function (input) {
        input.addEventListener('input', atualizarSubtotalItens);
      });
      listaItens.querySelectorAll('.remover-item-servico').forEach(function (button) {
        button.addEventListener('click', function () {
          button.closest('.item-servico-row').remove();
          atualizarSubtotalItens();
        });
      });
      document.getElementById('adicionar-item-servico').addEventListener('click', adicionarItem);
      atualizarSubtotalItens();
    }
  });
</script>
</body>
</html>
