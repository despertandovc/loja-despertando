# ADR 0004 — Frete, webhook e rastreamento Dimona desde o MVP 1

## Status

Aceita.

## Contexto

A integração Dimona é parte central do MVP 1 da Loja Despertando. O projeto recebeu a planilha oficial de SKUs do Dropsimples/Dimona e confirmou que o frete deve ser calculado por endpoint Dimona desde o começo.

Também foi decidido que o rastreamento precisa ser completo e profissional, exigindo webhook de atualização de status.

## Decisão

Implementar a integração Dimona do MVP 1 com:

- catálogo SKU importável;
- mapeamento SKU por produto/variação;
- método de entrega WooCommerce próprio para frete Dimona;
- consulta de frete por endpoint Dimona;
- criação de pedido Dimona via API após pagamento;
- webhook público para status/rastreamento;
- histórico de eventos de tracking;
- logs sanitizados;
- idempotência;
- reprocessamento manual.

## Consequências

- O MVP 1 fica mais robusto, porém mais complexo.
- Não haverá frete simplificado para Dimona em produção.
- Produtos variáveis exigirão mapeamento por variação.
- A implementação real depende de credenciais configuradas fora do chat e fora do repositório.
- Webhook exige segredo/assinatura e rota pública HTTPS.

## Variáveis seguras previstas

```text
DIMONA_API_KEY
DIMONA_WEBHOOK_SECRET
```

Os valores reais não devem ser versionados nem enviados no chat.
