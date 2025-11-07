Jesteś specjalistą GitHub Actions w stacku @.ai/tech-stack.md @package.json @composer.json oraz może być przydatne @compose.yaml

Utwórz scenariusz "pull-request.yml" na podstawie @github-action.mdc

Workflow:
Scenariusz "pull-request.yml" powinien działać następująco:

- Lintowanie kodu
- Następnie dwa równoległe - unit-test i e2e-test
- Finalnie - status-comment (komentarz do PRa o statusie całości)

Dodatkowe uwagi:
- status-comment uruchamia się tylko kiedy poprzedni zestaw 3 przejdzie poprawnie
- w jobie e2e pobieraj przeglądarki wg @playwright.config.ts
- w jobie e2e ustaw środowisko "integration" i zmienne z sekretów wg @.env.example
- zbieraj coverage unit testów i testów e2e

Dodatkowo ten CI/CD powinien deployować kod na serwer produkcyjny. W następującym scenariuszu
- create-environment-files. Copy .env.example to .env and fill it with proper variables
- in another step, do composer install and npm install
- run tests unit with phpunit and feature also with phpunit
- run e2e tests with playwright

- create directory if not exists in $DESTINATION
i have example in bitbucket-pipelines but this should be for git hub actions 

        name: 'Copy files & prepare environment'
        image: alpine:3.20.0
        script:
          - source ".env.deployment"
          - source ".env"
          - pipe: atlassian/ssh-run:0.3.1
            name: 'Create release directory'
            variables:
              SSH_USER: $USER
              SERVER: $SERVER
              COMMAND: "mkdir -p $DESTINATION/releases"
              MODE: 'command'

          - pipe: atlassian/rsync-deploy:0.4.1
            name: 'Deploy files to server'
            variables:
              USER: $USER
              SERVER: $SERVER
              REMOTE_PATH: '$DESTINATION/releases/$BITBUCKET_COMMIT'
              LOCAL_PATH: '.'

          - pipe: atlassian/ssh-run:0.2.4
            name: 'Migrate'
            variables:
              SSH_USER: $USER
              SERVER: $SERVER
              COMMAND: "cd $DESTINATION/releases/$BITBUCKET_COMMIT && /usr/bin/php8.2 artisan migrate --force"
              MODE: 'command'

          - pipe: atlassian/ssh-run:0.2.4
            name: 'Create shared directory'
            variables:
              SSH_USER: $USER
              SERVER: $SERVER
              COMMAND: "if [ ! -d '$DESTINATION/shared' ]; then mkdir -p '$DESTINATION/shared' ; fi"
              MODE: 'command'

          - pipe: atlassian/ssh-run:0.2.4
            name: 'Populate shared storage if it is not set'
            variables:
              SSH_USER: $USER
              SERVER: $SERVER
              COMMAND: "if [ ! -d '$DESTINATION/shared/storage' ]; then cp -rf $DESTINATION/releases/$BITBUCKET_COMMIT/storage $DESTINATION/shared/ && chmod -R 777 $DESTINATION/shared/storage ; fi"
              MODE: 'command'

          - pipe: atlassian/ssh-run:0.2.4
            name: 'Clear release storage and link it to shared'
            variables:
              SSH_USER: $USER
              SERVER: $SERVER
              COMMAND: "rm -rf $DESTINATION/releases/$BITBUCKET_COMMIT/storage && ln -s $DESTINATION/shared/storage $DESTINATION/releases/$BITBUCKET_COMMIT/storage;"
              MODE: 'command'

          - pipe: atlassian/ssh-run:0.2.4
            name: 'Create storage link'
            variables:
              SSH_USER: $USER
              SERVER: $SERVER
              COMMAND: "cd $DESTINATION/releases/$BITBUCKET_COMMIT && /usr/bin/php8.2 artisan storage:link"
              MODE: 'command'

          - pipe: atlassian/ssh-run:0.2.4
            name: 'Activate release'
            variables:
              SSH_USER: $USER
              SERVER: $SERVER
              COMMAND: "cd $DESTINATION && if [ -L 'latest' ]; then rm latest; fi && ln -s $DESTINATION/releases/$BITBUCKET_COMMIT latest"
              MODE: 'command'

          - pipe: atlassian/ssh-run:0.2.4
            name: 'Clear old releases'
            variables:
              SSH_USER: $USER
              SERVER: $SERVER
              COMMAND: "cd $DESTINATION/releases && ls -1tr | head -n -10 | xargs -d '\n' rm -rf --"
              MODE: 'command'

          - pipe: atlassian/ssh-run:0.2.4
            name: 'Restart queue'
            variables:
              SSH_USER: $USER
              SERVER: $SERVER
              COMMAND: "cd $DESTINATION/releases/$BITBUCKET_COMMIT && /usr/bin/php8.2 artisan queue:restart"
              MODE: 'command'

          #          - pipe: atlassian/ssh-run:0.2.4
          #            name: 'Refresh opcache'
          #            variables:
          #              SSH_USER: $USER
          #              SERVER: $SERVER
          #              COMMAND: "if [ -z ${BITBUCKET_TAG} ]; then echo 'This is not pipelines tags'; else sudo service php8.2-fpm restart; fi"
          #              MODE: 'command'

          - pipe: atlassian/ssh-run:0.2.4
            name: 'Slack notification'
            variables:
              SSH_USER: $USER
              SERVER: $SERVER
              COMMAND: "curl -X POST -H 'Content-type: application/json' --data '{ \"text\": \"Project *$APP_NAME* \nhas been deployed with success\", \"attachments\": [ { \"text\": \"Actions\", \"attachment_type\": \"default\", \"actions\": [ { \"name\": \"URL\", \"text\": \"URL\", \"type\": \"button\", \"style\": \"primary\", \"url\": \"$APP_URL\" }, { \"name\": \"Branch\", \"text\": \"Branch\", \"type\": \"button\", \"url\": \"$BITBUCKET_GIT_HTTP_ORIGIN/branch/$BITBUCKET_BRANCH\" }, { \"name\": \"Commit\", \"text\": \"Commit\", \"type\": \"button\", \"url\": \"$BITBUCKET_GIT_HTTP_ORIGIN/commits/$BITBUCKET_COMMIT\" } ] } ] }' $SLACK_WEBHOOK_URL"
              MODE: 'command'

Prepare me a plan and ask anything you need. Plan should include steps i need to configure in gh actions for this cd to work

