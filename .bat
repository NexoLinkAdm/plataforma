@echo off
echo.
echo ===== Enviando código para o GitHub =====
cd /d C:\laragon\www\pagamento
git add .
git commit -m "minha atualização"
git push

echo.
echo ===== Atualizando servidor na HostGator =====
ssh geova855@geovanebrunodasilva1746634843244.2300334.meusitehostgator.com.br "cd public_html && git pull"

pause
