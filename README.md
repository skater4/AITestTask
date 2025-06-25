# README.md

## Шаги установки и запуска
   
**Собрать проект**
   ```bash
   make build && make up
   ```

**Собрать пакеты**
   ```bash
   make composer-install
   ```

**Заполнить креды в index.php**
```bash
   $apiKey = 'secret_key';
   ```

Открываем http://localhost/ - он сразу отправляет testVoice в нейронку и записывает reply.mp3