cd /var/www/html
# Install packages
cp /.env /var/www/html/LLM_Project/
cd /var/www/html/LLM_Project/executables/docker
chmod +x install.sh
./install.sh
# Give owner back to www-data
chown -R www-data:www-data /var/www/html/LLM_Project
# Configure nginx
cd /etc/nginx/sites-available
cp /var/www/html/LLM_Project/nginx_config llmproject
cd /etc/nginx/sites-enabled
rm * -rf
ln -s ../sites-available/llmproject .
service nginx restart
cp /var/www/html/LLM_Project/www.conf /etc/php/8.1/fpm/pool.d/www.conf
service php8.1-fpm start
# Start the agent program
cd /var/www/html/LLM_Project/LLMs
pip install -r requirements.txt
# Start a worker
chmod +x /var/www/html/LLM_Project/executables/docker/work.sh
screen -L -dmS worker1 bash -c "cd /var/www/html/LLM_Project/executables/docker/ && ./work.sh"
# Start agent
cd 0.0.3
python3 agent.py
