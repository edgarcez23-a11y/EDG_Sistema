# 🚗 Sistema de Gestão de Oficina

Sistema web desenvolvido para auxiliar no gerenciamento de micros e pequenos estabelecimentos, permitindo organizar clientes, fornecedores, peças e serviços de manutenção.

## 📋 Sobre o projeto

O Sistema de Gestão foi desenvolvido com o objetivo de facilitar o controle das principais atividades de uma empresa prestadora de serviços.

O sistema permite centralizar informações e tornar o gerenciamento dos serviços mais organizado e eficiente.

## 🚀 Funcionalidades

- 👤 Cadastro de clientes
- 🏢 Cadastro de fornecedores
- 🔧 Cadastro de peças
- 🛠️ Controle de manutenção
- 📋 Solicitação de serviços
- 💰 Controle de informações financeiras
- 🔎 Consulta de registros
- 📊 Organização das informações da oficina

## 💻 Tecnologias utilizadas

- PHP
- MySQL / MariaDB
- HTML5
- CSS3
- Bootstrap
- JavaScript
- Apache
- WAMP

## 📁 Estrutura do projeto

```text
oficina_project/
│
├── css/
├── js/
├── imagens/
├── clientes.php
├── fornecedores.php
├── pecas.php
├── manutencao.php
├── solicitacao.php
├── index.php
├── composer.json
└── README.md
Requisitos

Para executar o projeto localmente, é necessário possuir:

PHP 8.3 ou superior
Apache
MySQL ou MariaDB
Composer
WAMP, XAMPP ou ambiente equivalente
Navegador web
🔧 Instalação
1. Clone o repositório
git clone https://github.com/edgarcez23-a11y/EDG_Sistema.git
2. Entre na pasta do projeto
cd EDG_Sistema
3. Instale as dependências
composer install
4. Configure o banco de dados

Crie o banco de dados:

CREATE DATABASE oficina_db;

Depois importe o arquivo SQL disponibilizado no projeto.

5. Configure a conexão com o banco

Edite o arquivo responsável pela conexão com o banco de dados e informe:

Host: localhost
Porta: 3306
Banco: oficina_db
Usuário: root
Senha: sua_senha
6. Execute o sistema

Coloque o projeto dentro do diretório:

C:\wamp64\www\

Inicie o Apache e o MySQL pelo WAMP.

Depois acesse:

http://localhost/oficina_project
🔐 Segurança

Não armazene senhas, chaves de API ou outras informações sensíveis diretamente no código-fonte.

Utilize variáveis de ambiente ou arquivos de configuração que não sejam enviados ao GitHub.

📌 Status do projeto

🚧 Em desenvolvimento.

Novas funcionalidades e melhorias poderão ser adicionadas ao longo do desenvolvimento.

👨‍💻 Autor

Edmar Garcez

Projeto desenvolvido para fins acadêmicos e de aprendizado em desenvolvimento de sistemas.

📄 Licença

Este projeto está disponível para fins educacionais.

⭐ Se este projeto foi útil para você, considere deixar uma estrela no repositório!


### Uma recomendação para o seu projeto

Como você está começando a colocar o `oficina_project` no GitHub, eu faria o README **um pouco mais profissional**, incluindo:

1. **Descrição do sistema**
2. **Prints das telas**
3. **Tecnologias**
4. **Requisitos**
5. **Instalação**
6. **Configuração do banco**
7. **Estrutura do projeto**
8. **Funcionalidades**
9. **Status**
10. **Autor**
11. **Licença**

E principalmente, **não coloque no README senhas do banco, credenciais do GitHub ou informações de acesso aos servidores**.

Se quiser, também posso :contentReference[oaicite:0]{index=0}, já estruturado para você simplesmente copiar
