#php bin/phpDocumentor3.phar -d lib -t docs/html --template="docs/templates/default" -s true

php bin/phpDocumentor3.phar -d lib -t docs/md --template="docs/templates/markdown" -s true && php bin/fixHtmlToMd.php ./docs/md

echo "Done!";
sleep 10