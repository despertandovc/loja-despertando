# DNS do staging

Domínio atual:

```text
loja.staging.despertando.com.br
```

Registro Cloudflare:

```text
A loja.staging.despertando.com.br -> 137.131.143.139
proxied=false
```

## Motivo para DNS-only

O staging usa sub-subdomínio. Com proxy laranja, o HTTPS no edge da Cloudflare pode falhar por cobertura de certificado universal. Por isso o staging atual usa DNS-only e deixa o TLS ser emitido pelo Traefik/Let's Encrypt na VPS.

## Produção

O domínio final planejado é:

```text
loja.despertando.com.br
```

Produção/cutover exige checkpoint humano antes de qualquer alteração.
