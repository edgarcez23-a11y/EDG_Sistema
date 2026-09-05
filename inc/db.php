<?php
// inc/db.php - conexão robusta com MySQL e fallback para SQLite

class DbStatement
{
    private PDOStatement $stmt;

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    public function bind_param($types, ...$values): bool
    {
        $types = (string) $types;
        if (strlen($types) !== count($values)) {
            throw new InvalidArgumentException('Número de parâmetros incompatível.');
        }

        $typeMap = [
            'i' => PDO::PARAM_INT,
            's' => PDO::PARAM_STR,
            'd' => PDO::PARAM_STR,
            'b' => PDO::PARAM_LOB,
        ];

        for ($i = 0; $i < count($values); $i++) {
            $paramType = $typeMap[$types[$i]] ?? PDO::PARAM_STR;
            $this->stmt->bindValue($i + 1, $values[$i], $paramType);
        }

        return true;
    }

    public function execute(): bool
    {
        return $this->stmt->execute();
    }

    public function get_result(): DbResult
    {
        return new DbResult($this->stmt);
    }

    public function close(): void
    {
        $this->stmt->closeCursor();
    }
}

class DbResult
{
    private ?PDOStatement $stmt;
    private array $rows = [];
    private int $currentIndex = 0;
    public int $num_rows = 0;

    public function __construct(?PDOStatement $stmt)
    {
        $this->stmt = $stmt;
        if ($this->stmt) {
            $this->rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->num_rows = count($this->rows);
        }
    }

    public function fetch_assoc(): ?array
    {
        if (!$this->stmt) {
            return null;
        }

        if ($this->currentIndex >= count($this->rows)) {
            return null;
        }

        $row = $this->rows[$this->currentIndex];
        $this->currentIndex++;
        return $row;
    }
}

class DbConnection
{
    private ?PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function prepare(string $sql): DbStatement
    {
        return new DbStatement($this->pdo->prepare($sql));
    }

    public function query(string $sql): DbResult
    {
        return new DbResult($this->pdo->query($sql));
    }

    public function real_escape_string(string $value): string
    {
        return str_replace("'", "''", $value);
    }

    public function close(): void
    {
        $this->pdo = null;
    }
}

function db_connect(): ?DbConnection
{
    static $connection = null;

    if ($connection instanceof DbConnection) {
        return $connection;
    }

    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS') ?: 'root';
    $dbName = getenv('DB_NAME') ?: 'oficina_db';
    $sqlitePath = __DIR__ . '/../oficina.sqlite';

    try {
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $connection = new DbConnection($pdo);
        return $connection;
    } catch (PDOException $e) {
        error_log('MySQL connection failed, trying SQLite: ' . $e->getMessage());
    }

    try {
        $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $pdo->exec("CREATE TABLE IF NOT EXISTS clientes (
            id_cliente INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            telefone TEXT,
            email TEXT,
            endereco TEXT,
            data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS fornecedores (
            id_fornecedor INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            telefone TEXT,
            email TEXT,
            endereco TEXT
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS pecas (
            id_peca INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            modelo_da_peça TEXT,
            marca TEXT,
            descricao TEXT,
            preco DECIMAL(10,2),
            quantidade_estoque INTEGER DEFAULT 0,
            id_fornecedor INTEGER,
            FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor) ON DELETE SET NULL ON UPDATE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS manutencoes (
            id_manutencao INTEGER PRIMARY KEY AUTOINCREMENT,
            id_cliente INTEGER NOT NULL,
            data_manutencao DATE,
            descricao TEXT,
            status TEXT DEFAULT 'Agendada',
            FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS solicitacoes_pecas (
            id_solicitacao INTEGER PRIMARY KEY AUTOINCREMENT,
            id_peca INTEGER,
            id_fornecedor INTEGER,
            id_cliente INTEGER,
            data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            status TEXT DEFAULT 'Pendente',
            FOREIGN KEY (id_peca) REFERENCES pecas(id_peca) ON DELETE SET NULL ON UPDATE CASCADE,
            FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor) ON DELETE SET NULL ON UPDATE CASCADE,
            FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS orcamentos (
            id_orcamento INTEGER PRIMARY KEY AUTOINCREMENT,
            id_cliente INTEGER,
            id_peca INTEGER,
            cliente_nome TEXT,
            telefone TEXT,
            endereco TEXT,
            quantidade INTEGER DEFAULT 1,
            valor_unitario DECIMAL(10,2),
            mao_de_obra DECIMAL(10,2),
            desconto DECIMAL(10,2),
            total DECIMAL(10,2),
            observacoes TEXT,
            data_orcamento DATE,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            senha TEXT NOT NULL,
            nome TEXT NOT NULL,
            ativo BOOLEAN DEFAULT 1,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $connection = new DbConnection($pdo);
        return $connection;
    } catch (PDOException $e) {
        error_log('SQLite connection failed: ' . $e->getMessage());
        return null;
    }
}