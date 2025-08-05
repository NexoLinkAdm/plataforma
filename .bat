@echo off
echo.
echo ===== Enviando código para o GitHub =====
cd /d C:\laragon\www\pagamento

REM Verifica se há mudanças antes de comitar
git diff --quiet && echo Nenhuma alteração para enviar. || (
    git add .
    git commit -m "atualização automática"
    git push
)

echo.
echo ===== Atualizando servidor na HostGator via SSH (porta 2222) =====

set "PLINK_PATH=C:\Users\geova\Downloads\plink.exe"
set "KEY_PATH=C:\laragon\www\pagamento\.ssh\id_ed25519"

%PLINK_PATH% -batch -i %KEY_PATH% -P 2222 geova855@108.179.252.230 "cd public_html && git pull"

echo.
echo ===== Processo concluído =====
pause
