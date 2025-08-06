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

REM Caminho para a chave privada
set KEY_PATH=C:\laragon\www\pagamento\.ssh\id_ed25519

REM Comando remoto em shell seguro: busca alterações mesmo com commits pendentes
set REMOTE_COMMAND=cd public_html && git fetch origin main && git reset --hard origin/main

REM Executa o comando remoto com verificação
"%PLINK_PATH%" -i "%KEY_PATH%" -P 2222 -batch geova855@108.179.252.230 "%REMOTE_COMMAND%"

if %errorlevel% neq 0 (
    echo.
    echo ❌ Falha ao conectar ou atualizar o servidor via SSH.
    echo Verifique a conexão, chave privada ou permissões do repositório remoto.
) else (
    echo.
    echo ✅ Código atualizado com sucesso no servidor remoto!
)

pause
