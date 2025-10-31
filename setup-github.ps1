# Script para subir projeto no GitHub
# Execute este arquivo no PowerShell após fechar e reabrir o terminal

Write-Host "=== Configurando Git e enviando para GitHub ===" -ForegroundColor Cyan

# 1. Configurar Git (ajuste seu nome e email)
Write-Host "`n1. Configurando usuario Git..." -ForegroundColor Yellow
git config --global user.name "Seu Nome"
git config --global user.email "seuemail@exemplo.com"

# 2. Inicializar repositorio
Write-Host "`n2. Inicializando repositorio..." -ForegroundColor Yellow
git init

# 3. Adicionar arquivos
Write-Host "`n3. Adicionando arquivos..." -ForegroundColor Yellow
git add .

# 4. Primeiro commit
Write-Host "`n4. Criando commit inicial..." -ForegroundColor Yellow
git commit -m "Initial commit: Sistema SimLover v1.0 - Requisicao de pecas"

# 5. Renomear branch para main (padrao atual do GitHub)
Write-Host "`n5. Renomeando branch para main..." -ForegroundColor Yellow
git branch -M main

Write-Host "`n=== PRONTO! ===" -ForegroundColor Green
Write-Host "`nAgora voce precisa:" -ForegroundColor Cyan
Write-Host "1. Criar um repositorio no GitHub (https://github.com/new)" -ForegroundColor White
Write-Host "2. Copiar a URL do repositorio criado" -ForegroundColor White
Write-Host "3. Executar os comandos abaixo (SUBSTITUA a URL):`n" -ForegroundColor White
Write-Host "   git remote add origin https://github.com/SEU_USUARIO/SEU_REPOSITORIO.git" -ForegroundColor Yellow
Write-Host "   git push -u origin main`n" -ForegroundColor Yellow
Write-Host "Se pedir senha, use um Personal Access Token (nao a senha da conta)" -ForegroundColor Magenta
