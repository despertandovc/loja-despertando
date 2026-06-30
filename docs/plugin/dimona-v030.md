# Despertando Commerce Core v0.3.0 — Dimona segura

## Entregue

- Cliente HTTP Dimona com `api-key` via variável/constante segura.
- Método de frete WooCommerce `dcc_dimona_shipping` usando `POST /api/v2/shipping`.
- Endpoint webhook `POST /wp-json/despertando-commerce/v1/dimona/webhook`.
- Validação do webhook por `api_key` recebida no payload.
- Tabela `wp_dcc_dimona_tracking_events`.
- Serviço de criação de pedido Dimona via `POST /api/v3/order`.
- `Create Order` real bloqueado por padrão.
- Dry-run ativo por padrão.
- Logs sanitizados.

## Flags de segurança

```text
DIMONA_API_KEY
DIMONA_API_BASE_URL=https://admin.camisadimona.com.br
```

No painel do plugin:

```text
Create Order real = desligado
Dry-run = ligado
```

Isso permite testar frete real e webhook sem criar pedido Dimona real.

## Primeiro teste real

Só habilitar criação real depois de checkpoint humano:

```text
Create Order real = ligado
Dry-run = desligado
```

Antes de cadastrar cartão na Dimona, cancelar todos os pedidos de teste que ficarem como pendentes.
