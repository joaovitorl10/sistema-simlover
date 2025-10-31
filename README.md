# SimLover - Sistema de Requisição de Peças

Sistema web para gerenciamento de requisições de peças e serviços de laboratório, desenvolvido para a Simpress.

## 🚀 Funcionalidades

- **Formulário de requisição** com campos customizados para:
  - Localização (Galpão 1, Galpão 2 ou Outros)
  - Departamentos (Lab MPS, Lab SAAS, Engenharia, Estoque)
  - Serviços variados (troca de toner, manutenção, etc)
  - Referência POD para rastreamento
  
- **Painel administrativo** com:
  - Login seguro com senha hasheada (bcrypt)
  - Troca obrigatória de senha no primeiro acesso
  - Aprovação/rejeição/conclusão de requisições
  - Listagem com filtros por status, data e busca
  - Interface moderna com design responsivo

- **Workflow de aprovação**:
  - Pendente → Aprovado/Rejeitado → Concluído
  - Registro de aprovador e observações
  - Histórico completo de cada requisição

## 🛠️ Tecnologias

- **Backend**: PHP 7.4+
- **Banco de dados**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Servidor**: Apache (XAMPP recomendado)

## 📋 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior (ou MariaDB)
- Apache 2.4+
- Extensões PHP: mysqli, password_hash

## 🔧 Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/SEU_USUARIO/projeto-simpress.git
cd projeto-simpress
```

### 2. Configure o servidor

**Opção A: XAMPP (Windows/Mac/Linux)**

1. Instale o [XAMPP](https://www.apachefriends.org/)
2. Copie a pasta do projeto para `C:\xampp\htdocs\projeto-simpress\` (Windows) ou `/opt/lampp/htdocs/projeto-simpress/` (Linux)
3. Inicie Apache e MySQL no XAMPP Control Panel

**Opção B: Servidor Linux (Apache + PHP + MySQL)**

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 php php-mysql mysql-server
sudo systemctl start apache2 mysql
sudo cp -r projeto-simpress /var/www/html/
```

### 3. Crie o banco de dados

Acesse no navegador:
```
http://localhost/projeto-simpress/init_db.php
```

Isso criará automaticamente:
- Banco de dados `simpress_requisicoes`
- Tabela `requisicoes` (armazena requisições)
- Tabela `admins` (usuários administrativos)
- Usuário padrão: `admin` / Senha: `Senha123!`

### 4. Acesse o sistema

**Formulário público:**
```
http://localhost/projeto-simpress/simlover-requisicao.html
```

**Painel administrativo:**
```
http://localhost/projeto-simpress/admin-login.php
```

Credenciais padrão:
- Usuário: `admin`
- Senha: `Senha123!`

⚠️ **IMPORTANTE**: No primeiro login, você será obrigado a trocar o usuário e senha.

## 📁 Estrutura de arquivos

```
projeto-simpress/
├── simlover-requisicao.html      # Formulário público
├── simlover-confirmacao.html     # Página de confirmação
├── simlover-style.css            # Estilos (tema teal/laranja)
├── processa-requisicao.php       # Processa envio do formulário
├── init_db.php                   # Inicializa banco de dados
├── admin-login.php               # Login administrativo
├── admin-logout.php              # Logout
├── admin.php                     # Dashboard (requisições pendentes)
├── admin-listar-todas.php        # Listagem com filtros
├── admin-change-password.php     # Alterar usuário/senha
├── visualizar-requisicao.php     # Detalhes e aprovação
├── auth.php                      # Guard de autenticação
└── README.md                     # Esta documentação
```

## 🎨 Personalização

### Cores do tema

Edite `simlover-style.css` e altere as variáveis CSS:

```css
:root {
    --primary-color: #16a085;    /* Verde-azulado principal */
    --primary-dark: #138f75;     /* Verde-azulado escuro */
    --secondary-color: #ff6b00;  /* Laranja secundário */
    --secondary-dark: #cc5500;   /* Laranja escuro */
}
```

### Configuração do banco

Edite as conexões em todos os arquivos `.php`:

```php
$conn = new mysqli("localhost", "root", "", "simpress_requisicoes");
```

Altere para suas credenciais:
- `localhost` → endereço do servidor MySQL
- `root` → seu usuário MySQL
- `""` → sua senha MySQL
- `simpress_requisicoes` → nome do banco

## 🔒 Segurança

- ✅ Senhas hasheadas com `password_hash()` (bcrypt)
- ✅ Prepared statements (proteção contra SQL injection)
- ✅ Escape de HTML com `htmlspecialchars()` (proteção XSS)
- ✅ Sessões PHP para controle de acesso
- ✅ Troca obrigatória de senha no primeiro login

### Recomendações adicionais

1. Use HTTPS em produção
2. Configure `php.ini` adequadamente:
   ```ini
   session.cookie_httponly = 1
   session.cookie_secure = 1
   display_errors = Off
   ```
3. Mantenha backups regulares do banco de dados

## 📊 Banco de dados

### Tabela `requisicoes`

Armazena todas as requisições com campos:
- Dados do solicitante (nome, email, telefone)
- Localização e departamento
- Detalhes da requisição (SKU, quantidade, POD)
- Status e workflow (pendente/aprovado/rejeitado/concluído)
- Timestamps de criação e atualização

### Tabela `admins`

Gerencia usuários administrativos:
- username (único)
- password_hash (bcrypt)
- must_change (flag de primeiro acesso)
- nome (nome completo)

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

## 📝 Licença

Este projeto é de uso interno da Simpress.

## 👥 Autores

- Desenvolvido para Simpress
- Sistema SimLover

## 📞 Suporte

Para dúvidas ou problemas, entre em contato com a equipe de TI.

---

**Versão**: 1.0.0  
**Última atualização**: Outubro 2025
