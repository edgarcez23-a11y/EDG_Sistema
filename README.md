Oficina Inteligente - Projeto PHP + MySQL (pronto para XAMPP)
============================================================

Conteúdo do pacote
- create_db.sql      : script SQL para criar o banco e tabelas
- /public            : arquivos PHP públicos (index.php, clientes.php, etc.)
- /inc               : includes (db.php, enviar_email.php)
- /assets/css/style.css : estilo básico com Bootstrap integrado via CDN
- README.md          : instruções

Como usar (local XAMPP)
1. Instale XAMPP (Apache + MySQL) e inicie Apache e MySQL.
2. Copie a pasta 'oficina_project/public' para a pasta htdocs do XAMPP, por exemplo:
   C:\xampp\htdocs\oficina
3. Importe o banco de dados usando o arquivo create_db.sql via phpMyAdmin ou linha de comando:
   - phpMyAdmin: abra http://localhost/phpmyadmin -> Import -> selecione create_db.sql
   - linha de comando: mysql -u root -p < create_db.sql
4. Abra no navegador: http://localhost/oficina/index.php

Configurar envio de e-mail (PHPMailer)
- O sistema usa PHPMailer. Recomendado instalar via Composer no diretório public:
  composer require phpmailer/phpmailer
- Configure credenciais SMTP em inc/enviar_email.php (variáveis: SMTP_HOST, SMTP_USER, SMTP_PASS, SMTP_PORT, SMTP_SECURE)

Segurança e produção
- Não use as credenciais do exemplo em produção.
- Proteja o diretório inc/ com regras do servidor caso em produção.
- Considere usar HTTPS e validar/escapar todas as entradas de usuários.
