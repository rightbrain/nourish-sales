app/console cache:clear --env=prod
app/console assets:install --env=prod
app/console assetic:dump --env=prod
app/console assets:install --symlink web
app/console fos:js-routing:dump
sudo chmod 777 app/cache* app/logs* -R
