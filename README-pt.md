# PDO4You

Um wrapper de banco de dados moderno, leve e testável para PHP.

O PDO4You foi projetado para simplificar o uso do PDO sem o overhead de um ORM completo. Construído com PHP moderno (8.2+), ele suporta injeção de dependência, autoloading PSR-4 e estratégias específicas de plataforma para cada banco de dados.

## Desenvolvimento

Para configurar o ambiente de desenvolvimento, clone o repositório e execute:

```bash
composer install
```

## Instalação

Instale o pacote via Composer:

```bash
composer require giovanniramos/pdo4you
```

## Como usar

### 1. Configuração

Instancie a classe injetando sua conexão PDO e o driver de plataforma apropriado.

```php
use PDO;
use PDO4You\PDO4You;
use PDO4You\Platform\MySqlPlatform;

// 1. Crie uma conexão PDO nativa
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'password');

// 2. Selecione a plataforma
$platform = new MySqlPlatform();

// 3. Injete no PDO4You
$db = new PDO4You($pdo, $platform);
```

### 2. Operações

```php
// SELECT (retorna um array associativo)
$users = $db->select("SELECT * FROM users WHERE status = ?", ['active']);

// EXECUTE (insert, update, delete)
$db->exec("INSERT INTO users (name, surname) VALUES (?, ?)", [['John', 'Doe'], ['Jane', 'Doe']]);

// GET LAST ID (utiliza a estratégia da plataforma)
$newId = $db->lastId();
```

## Executando Testes

Você pode executar a suíte de testes utilizando um dos seguintes métodos:

### 1. Usando Docker (Ambiente Isolado)
Se você tiver o Docker instalado, execute:
```bash
docker-compose up --build
```

### 2. Usando Linha de Comando (Ambiente Local)
Se você tiver PHP 8.2+ e Composer instalados:

```bash
composer install
./vendor/bin/phpunit tests
```

## Licença

MIT
