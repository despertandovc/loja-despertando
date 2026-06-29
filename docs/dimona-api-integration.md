# Integração Dimona API

A Dimona será integrada por API própria no MVP 1.

## Fluxo previsto

```text
Pedido WooCommerce pago
  ↓
Identificar itens com fulfillment_type=dimona
  ↓
Montar payload Dimona
  ↓
Enviar para API Dimona
  ↓
Salvar dimona_order_id e status
  ↓
Registrar log sanitizado
```

## Requisitos

- Idempotência.
- Logs sanitizados.
- Retry seguro.
- Reprocessamento manual.
- Bloqueio contra pedido duplicado.
- Credenciais fora do repositório.
