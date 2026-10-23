# PDO4You

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Version](https://img.shields.io/badge/version-6.0.0-blue.svg)](https://github.com/giovanniramos/PDO4You/releases)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

*[Read documentation in English](README.md)*

**Um wrapper de banco de dados moderno, leve e testável para PHP.**

O **PDO4You** simplifica o uso do PDO sem adicionar o overhead e a complexidade de um ORM completo.

Construído para **PHP 8.2+**, o PDO4You mantém o SQL sob seu controle enquanto adiciona uma camada de abstração segura para operações comuns de banco de dados, injeção de dependência e estratégias específicas de plataforma.

> **PDO4You não substitui o PDO. Ele trabalha junto com ele.**

---

## ✨ Características

* 🚀 **Leve** — uma camada fina sobre o PDO com overhead mínimo.
* 🧩 **Simples** — continue escrevendo SQL nativo mantendo controle total sobre as consultas.
* 💉 **Testável** — suporte completo a injeção de dependência (`PDO` e `DatabasePlatform`).
* ⚡ **Inicialização Rápida** — fábrica `connect()` e detecção automática da plataforma a partir do DSN ou da instância `PDO`.
* 📦 **PSR-4** — autoloading padrão compatível com Composer.
* 🐘 **PHP Moderno** — tipagem estrita, `#[\SensitiveParameter]`, `readonly` e recursos do PHP 8.2+.
* 🔌 **Estratégias por Plataforma** — abstração de dialetos (escape de identificadores e captura de último ID) para **MySQL/MariaDB**, **PostgreSQL**, **SQLite** e **SQL Server**.
* 🛡️ **Segurança e Tipagem Nativa** — *prepared statements* reais com detecção automática de tipos escalares (`PARAM_INT`, `PARAM_BOOL`, `PARAM_NULL`, `PARAM_LOB`).
* 🔄 **Transações e Savepoints** — controle transacional seguro via closures com *rollback* automático e suporte a transações aninhadas via `SAVEPOINT`.
* 🌊 **Streaming com `cursor()`** — iteração *lazy* de grandes volumes de dados usando `Generator` com consumo de memória $O(1)$.
* 📊 **Observabilidade** — listeners por instância (seguro para Swoole/Octane/RoadRunner) e global para logging e profiling.
* 🚫 **Sem ORM** — sem entidades complexas ou mapeamentos obrigatórios.

---

## 📦 Instalação

Instale o PDO4You através do Composer:

```bash
composer require giovanniramos/pdo4you
```

---

## ⚡ Início Rápido

Você pode inicializar o PDO4You de duas formas: através da fábrica `connect()` via DSN ou injetando uma conexão `PDO` existente.

### 1. Inicialização via DSN (Recomendada)

O método estático `connect()` configura opções seguras por padrão e infere a plataforma correta pelo driver do DSN (`mysql`, `pgsql`, `sqlite` ou `sqlsrv`):

```php
<?php

use PDO4You\PDO4You;

$db = PDO4You::connect(
    'mysql:host=localhost;dbname=mydb;charset=utf8mb4',
    'usuario',
    'senha'
);
```

### 2. Injeção de Dependência (`PDO`)

Ideal para contêineres de DI (Injeção de Dependências) ou para reaproveitar conexões existentes. A plataforma é detectada automaticamente a partir do driver do `PDO` (ou pode ser injetada explicitamente):

```php
<?php

use PDO;
use PDO4You\PDO4You;
use PDO4You\Platform\MySqlPlatform;

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'usuario', 'senha');

// Detecção automática da plataforma a partir do PDO:
$db = new PDO4You($pdo);

// Ou injetando uma estratégia de plataforma customizada explicitamente:
$db = new PDO4You($pdo, new MySqlPlatform());
```

---

## 🧑‍💻 Uso

### Consultas (`SELECT`)

O PDO4You oferece métodos específicos para cada necessidade de leitura:

#### `select()` — Array associativo ou hidratação de DTOs
```php
// Retorna um array de arrays associativos
$users = $db->select(
    'SELECT * FROM users WHERE status = ?',
    ['active']
);

// Hidratação direta para instâncias de uma classe/DTO (PDO::FETCH_CLASS)
$users = $db->select(
    'SELECT id, name, email FROM users WHERE status = ?',
    ['active'],
    UserDto::class
);
```

#### `cursor()` — Streaming *Lazy* com `Generator` (Memória $O(1)$)
Ideal para processar grandes lotes de registros ou exportações sem carregar todo o resultado na memória RAM:

```php
foreach ($db->cursor('SELECT id, name FROM users WHERE active = ?', [true], UserDto::class) as $user) {
    echo $user->name . PHP_EOL;
}
```

#### `selectOne()` — Buscar uma única linha
Retorna apenas o primeiro registro (ou `null`) e fecha o cursor imediatamente:

```php
$user = $db->selectOne('SELECT * FROM users WHERE id = ?', [1]);

if ($user !== null) {
    echo $user['name'];
}
```

#### `selectVal()` — Valor escalar
Retorna diretamente o valor de uma única coluna da primeira linha (útil para contagens, somas e verificações):

```php
$total = (int) $db->selectVal('SELECT COUNT(*) FROM users WHERE status = ?', ['active']);
```

#### `selectObj()` — Array de objetos (`stdClass` ou classe específica)
```php
$users = $db->selectObj('SELECT name, email FROM users');

foreach ($users as $user) {
    echo $user->name;
}
```

#### `selectNum()` — Array indexado numericamente
```php
$rows = $db->selectNum('SELECT id, name FROM users');
// $rows[0][0] = id, $rows[0][1] = name
```

---

### Auxiliares DML Fluídos (`insert()`, `update()`, `delete()`)

Métodos dedicados para operações de escrita seguras com escape automático de identificadores (incluindo notação qualificada `schema.tabela`) e proteção contra cláusulas `WHERE` vazias:

#### `insert()` — Inserir um registro
```php
$affected = $db->insert('users', [
    'name'    => 'John',
    'surname' => 'Wick',
    'status'  => 'active',
]);

$lastId = $db->lastId();
```

#### `update()` — Atualizar registros
Suporta parâmetros nomeados ou posicionais na cláusula `WHERE`:

```php
$affected = $db->update(
    'users',
    ['status' => 'inactive'],
    'id = :id',
    ['id' => 5]
);
```

#### `delete()` — Deletar registros
```php
$affected = $db->delete(
    'users',
    'id = :id',
    ['id' => 10]
);
```

---

### Execução Direta e em Lote (`exec()`)

O método `exec()` executa instruções parametrizadas e retorna o total de linhas afetadas.

#### Execução Simples
```php
$affected = $db->exec(
    'UPDATE users SET status = ? WHERE id = ?',
    ['inactive', 5]
);
```

#### Execução em Lote (Batch Insert / Batch Update)
Ao passar uma lista de arrays, o PDO4You prepara a instrução uma única vez e executa cada conjunto de parâmetros de forma eficiente:

```php
$totalInserted = $db->exec(
    'INSERT INTO users (name, surname) VALUES (?, ?)',
    [
        ['John', 'Doe'],
        ['Jane', 'Doe'],
        ['Alice', 'Smith'],
    ]
);
```

---

### Paginação (`Pagination`)

A classe `Pagination` calcula automaticamente offsets, limites e metadados de navegação:

```php
use PDO4You\Pagination;

$total = (int) $db->selectVal('SELECT COUNT(*) FROM items');
$pagination = new Pagination(currentPage: 2, perPage: 10, total: $total);

$items = $db->select(
    'SELECT id, title FROM items ORDER BY id ASC LIMIT :limit OFFSET :offset',
    [
        'limit'  => $pagination->getLimit(),
        'offset' => $pagination->getOffset(),
    ]
);

$meta = $pagination->getMetadata();
// ['currentPage' => 2, 'perPage' => 10, 'total' => 25, 'totalPages' => 3, 'offset' => 10, 'hasPrevious' => true, 'hasNext' => true]
```

---

### Último ID Inserido (`lastId()`)

Obtém o identificador gerado na última inserção utilizando a estratégia nativa da plataforma ativa (incluindo captura automática no mesmo lote de escopo para `SCOPE_IDENTITY()` no SQL Server):

```php
$db->insert('users', ['name' => 'John']);

$userId = $db->lastId();

// Para PostgreSQL com sequences específicas:
// $userId = $db->lastId('users_id_seq');
```

---

### Transações Gerenciadas e Savepoints

O método `transaction()` encapsula operações em um bloco atômico seguro. Chamadas aninhadas utilizam `SAVEPOINT` automaticamente:

```php
$db->transaction(function (PDO4You $conn) {
    $conn->exec('UPDATE accounts SET balance = balance - 100 WHERE id = ?', [1]);
    $conn->exec('UPDATE accounts SET balance = balance + 100 WHERE id = ?', [2]);

    // Transação aninhada (Savepoint)
    $conn->transaction(function (PDO4You $nested) {
        $nested->insert('audit_logs', ['action' => 'transfer']);
    });
});
```

Controles manuais (`beginTransaction()`, `commit()`, `rollBack()` e `inTransaction()`) também estão disponíveis.

---

### Observabilidade e Logs (`addQueryListener` / `onQuery`)

Monitore o tempo de execução (em milissegundos) e os parâmetros de cada consulta. Falhas dentro dos listeners são isoladas para nunca interromper a execução do banco:

```php
// Listener por instância (recomendado para Octane, Swoole, RoadRunner e múltiplos bancos)
$db->addQueryListener(function (string $sql, array $params, float $durationMs) {
    if ($durationMs > 50.0) {
        error_log(sprintf('[Slow Query: %.2fms] %s', $durationMs, $sql));
    }
});

// Ou listener global estático
PDO4You::onQuery(function (string $sql, array $params, float $durationMs) {
    // ...
});
```

---

### Tratamento de Erros (`QueryException`)

Todas as falhas de preparação ou execução do PDO são convertidas em `PDO4You\Exception\QueryException`, preservando o contexto completo do erro:

```php
use PDO4You\Exception\QueryException;

try {
    $db->insert('users', ['email' => 'duplicado@exemplo.com']);
} catch (QueryException $e) {
    echo $e->getMessage();    // Mensagem detalhada
    echo $e->getSqlState();   // Código ANSI SQLSTATE (ex: '23000', '23505')
    echo $e->getDriverCode(); // Código específico do driver (ex: 1062 no MySQL)
    echo $e->getSql();        // A instrução SQL que falhou
    print_r($e->getParams()); // Parâmetros vinculados à consulta
}
```

---

## 🔌 Plataformas Suportadas

O PDO4You utiliza implementações da interface `PDO4You\Platform\DatabasePlatform` para abstrair particularidades de cada banco de dados:

* `PDO4You\Platform\MySqlPlatform` — Suporte para **MySQL** e **MariaDB** (identificadores com `` ` `` e `LAST_INSERT_ID()`).
* `PDO4You\Platform\PgSqlPlatform` — Suporte para **PostgreSQL** (identificadores com `"`, `lastval()` e `currval()` validado).
* `PDO4You\Platform\SqlitePlatform` — Suporte para **SQLite** (identificadores com `"` e `last_insert_rowid()`).
* `PDO4You\Platform\SqlSrvPlatform` — Suporte para **Microsoft SQL Server** (identificadores com `[ ]` e captura automática de `SCOPE_IDENTITY()` no mesmo lote).

---

## 🏗️ Arquitetura

O PDO4You adiciona uma camada fina sobre o PDO, mantendo o SQL explícito e encapsulando comportamentos específicos de cada banco através do padrão *Strategy* (`DatabasePlatform`).

![Arquitetura do PDO4You](docs/images/pdo4you-architecture-pt-br.png)

---

## 🎯 Por que PDO4You?

O PDO já oferece uma excelente API nativa para acesso a bancos de dados, enquanto ORMs completos trazem camadas robustas de abstração. O **PDO4You** foi projetado exatamente para preencher o espaço entre eles: uma camada leve de conveniência sem abrir mão do controle do SQL.

- **Mais simples que um ORM** — sem entidades, modelos ou mapeamento obrigatório.
- **Mais conveniente que PDO puro** — métodos diretos de seleção, tratamento consistente de erros e execução em lote.
- **SQL continua no controle** — você escreve as consultas e decide como os dados são acessados.

---

## ⚡ Teste Rápido

Após instalar as dependências, você pode verificar rapidamente o funcionamento do PDO4You através do script incluído na raiz do projeto:

```bash
php quick-test.php
```

---

## 🧪 Exemplos

O projeto possui uma suíte de exemplos práticos de utilização.

Para visualizar os exemplos em uma interface web, inicie o servidor interno do PHP:

```bash
php -S localhost:8000 -t examples
```

Depois, acesse no navegador:

```text
http://localhost:8000
```

Os exemplos utilizam SQLite em memória por padrão, permitindo experimentar a biblioteca sem a necessidade de configurar um servidor de banco de dados.

---

## 🛠️ Desenvolvimento

Clone o repositório e instale as dependências:

```bash
git clone https://github.com/giovanniramos/PDO4You.git
cd PDO4You
composer install
```

Execute os testes com PHPUnit:

```bash
./vendor/bin/phpunit tests
```

Para executar os testes em um ambiente isolado com Docker:

```bash
docker compose build -q --no-cache
docker compose run --rm test
```

---

## 📋 Requisitos

* **PHP:** 8.2 ou superior
* **Extensões PDO:** `ext-pdo` e o driver desejado (`pdo_mysql`, `pdo_pgsql`, `pdo_sqlite` ou `pdo_sqlsrv`)
* **Composer**

---

## 📄 Licença

PDO4You é distribuído sob a licença **MIT**. Consulte o arquivo [LICENSE](LICENSE) para mais detalhes.
