@echo off
echo.
echo ===== Enviando código para o GitHub =====
cd /d C:\laragon\www\pagamento

git remote set-url origin https://github.com/NexoLinkAdm/plataforma.git
git add .
git commit -m "atualização automática"
git push origin main

echo.
echo ===== Atualizando servidor na HostGator via SSH (porta 2222) =====

REM Caminho completo para plink.exe
set PLINK_PATH=C:\Users\geova\Downloads\plink.exe

REM Caminho para a chave privada
set KEY_PATH=C:\laragon\www\pagamento\.ssh\id_ed25519

REM Executa o comando remoto (garante que commits pendentes sejam aplicados)
"%PLINK_PATH%" -i "%KEY_PATH%" -P 2222 geova855@108.179.252.230 "cd public_html && git fetch origin main && git reset --hard origin/main"

pause
