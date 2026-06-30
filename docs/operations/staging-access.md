# Acesso ao staging

URL pública atual:

```text
https://loja.staging.despertando.com.br
```

## Admin WordPress

O usuário admin inicial é definido no `.env` real da VPS. A senha real não deve ser colocada no chat, em commit, issue ou PR.

Para redefinir senha de forma operacional segura, usar WP-CLI dentro da stack:

```bash
cd /opt/stacks/wp-loja-despertando
WP='docker compose run --rm --user 0 wpcli --allow-root'
$WP user list
$WP user update <user_login> --user_pass='<nova-senha-gerada-fora-do-chat>'
```

Alternativa preferida para Raoni: usar a tela de recuperação de senha do WordPress, recebendo o link no e-mail configurado.

## Guardrails

- Não expor senha no chat.
- Não criar usuário admin permanente sem necessidade.
- Não ativar produção nem pagamentos reais no staging.
