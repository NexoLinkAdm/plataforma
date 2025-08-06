@echo off
setlocal
echo.
echo ===== Enviando código para o GitHub =====

cd /d C:\laragon\www\pagamento

REM Verifica se há mudanças antes de commitar
git status --porcelain > nul
if %errorlevel% neq 0 (
    echo Nenhuma alteração para enviar ao GitHub.
) else (
    git remote set-url origin https://github.com/NexoLinkAdm/plataforma.git
    git add .
    git commit -m "atualização automática"
    git push origin main
)

echo.
echo ===== Atualizando servidor na HostGator via SSH (porta 2222) =====

REM Caminho completo para plink.exe
set PLINK_PATH=C:\Users\geova\Downloads\plink.exe

REM Comando remoto: força update mesmo com commits pendentes
set REMOTE_COMMAND=cd public_html && git fetch origin main && git reset --hard origin/main

REM Conecta por SSH e pede senha (se necessário)
"%PLINK_PATH%" -P 2222 geova855@108.179.252.230 "%REMOTE_COMMAND%"

if %errorlevel% neq 0 (
    echo.
    echo ❌ Falha ao conectar ou atualizar o servidor via SSH.
    echo Verifique a senha ou permissões do repositório remoto.
) else (
    echo.
    echo ✅ Código atualizado com sucesso no servidor remoto!
)

pause
