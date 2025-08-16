# QiBotZap License Manager

Sistema de gerenciamento de licenças para WhatsApp Bot desenvolvido com Laravel, Vue.js e Tailwind CSS no tema dark.

## 🚀 Características

- **Dashboard Moderno**: Interface dark theme responsiva e elegante
- **Gerenciamento de Licenças**: Criação, renovação e desativação de licenças
- **Monitoramento em Tempo Real**: Acompanhamento de uso e estatísticas
- **Logs de Atividade**: Histórico completo de todas as ações
- **API REST**: Endpoints para integração com bots
- **Autenticação**: Sistema seguro de login e controle de acesso

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 11
- **Frontend**: Vue.js 3 + Inertia.js
- **Styling**: Tailwind CSS v3 (Dark Theme)
- **Banco de Dados**: MySQL
- **Componentes**: HeadlessUI
- **Build Tool**: Vite

## 📦 Instalação

### Pré-requisitos

- PHP 8.2+
- Node.js 18+
- MySQL 8.0+
- Composer

### Passos de Instalação

1. **Clone o repositório**
```bash
git clone <url-do-repositorio>
cd whatsapp-licenses-bot
```

2. **Instale as dependências do PHP**
```bash
composer install
```

3. **Instale as dependências do Node.js**
```bash
npm install --legacy-peer-deps
```

4. **Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure o banco de dados**
Edite o arquivo `.env` com suas credenciais do MySQL:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whatsapp_licenses_bot
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

6. **Execute as migrações**
```bash
php artisan migrate
```

7. **Popule o banco com dados de exemplo**
```bash
php artisan db:seed
```

8. **Compile os assets**
```bash
npm run build
```

9. **Inicie o servidor**
```bash
php artisan serve
```

## 🔐 Acesso Padrão

Após executar os seeders, você pode fazer login com:
- **Email**: admin@qibotzap.com
- **Senha**: password

## 📡 API Endpoints

### Verificação de Licença (Público)

```http
POST /api/whatsapp/verify-key
Content-Type: application/json

{
    "access_key": "QBZ-ABCD1234EFGH",
    "device_id": "abc123def456...",
    "timestamp": 1692872400
}
```

**Resposta de Sucesso:**
```json
{
    "success": true,
    "message": "Licença verificada com sucesso",
    "expires_at": "2024-09-15T10:30:00.000000Z",
    "remaining_days": 25
}
```

### Gerenciamento de Licenças (Autenticado)

#### Listar Licenças
```http
GET /api/whatsapp/licenses
Authorization: Bearer {token}
```

#### Gerar Nova Licença
```http
POST /api/whatsapp/licenses/generate
Authorization: Bearer {token}
Content-Type: application/json

{
    "client_name": "João Silva",
    "client_email": "joao@email.com",
    "plan_type": "professional",
    "duration_days": 30
}
```

#### Renovar Licença
```http
POST /api/whatsapp/licenses/{id}/renew
Authorization: Bearer {token}
Content-Type: application/json

{
    "duration_days": 30
}
```

#### Desativar Licença
```http
DELETE /api/whatsapp/licenses/{id}/deactivate
Authorization: Bearer {token}
```

#### Estatísticas
```http
GET /api/whatsapp/licenses/stats
Authorization: Bearer {token}
```

## 🎨 Interface

### Dashboard
- Estatísticas em tempo real
- Cards informativos com ícones
- Ações rápidas
- Licenças recentes
- Licenças expirando
- Atividade recente

### Gerenciamento de Licenças
- Tabela responsiva com filtros
- Busca por nome, email ou chave
- Filtros por status e tipo de plano
- Ações de renovar e desativar
- Paginação

### Logs de Atividade
- Histórico completo de ações
- Filtros por ação e licença
- Informações de IP e dispositivo
- Timestamps detalhados

## 🔧 Planos Disponíveis

- **Básico**: Funcionalidades essenciais
- **Profissional**: Recursos avançados
- **Empresarial**: Solução completa

## 📊 Status de Licenças

- **Ativa**: Licença válida e em uso
- **Inativa**: Licença desativada manualmente
- **Expirada**: Licença que passou da data de validade

## 🌙 Tema Dark

O sistema foi desenvolvido com foco no tema dark, proporcionando:
- Melhor experiência visual em ambientes com pouca luz
- Redução do cansaço visual
- Design moderno e elegante
- Cores contrastantes para melhor legibilidade

## 🔒 Segurança

- Autenticação baseada em tokens
- Validação de dados de entrada
- Proteção contra SQL injection
- Logs de auditoria completos
- Verificação de dispositivos únicos

## 📱 Responsividade

Interface totalmente responsiva que funciona perfeitamente em:
- Desktop
- Tablet
- Mobile

## 🚀 Deploy

Para produção, certifique-se de:

1. Configurar variáveis de ambiente adequadas
2. Usar HTTPS
3. Configurar cache Redis (opcional)
4. Configurar queue workers
5. Otimizar assets com `npm run build`

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

## 📞 Suporte

Para suporte e dúvidas:
- Email: admin@qibotzap.com
- Issues: GitHub Issues

---

Desenvolvido com ❤️ para a comunidade QiBotZap
