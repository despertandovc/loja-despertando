# Dimona prep v0.2.0

A versão `0.2.0` prepara o plugin para a integração real Dimona, sem executar chamada de API ainda.

## Entregue nesta versão

- Campos Dimona no cadastro de produto:
  - `dimona_product_id`;
  - `dimona_variant_id`;
  - `dimona_print_area`;
  - `dimona_artwork_url`;
  - `dimona_mockup_url`.
- Tela de configuração Dimona em WooCommerce > Despertando Core.
- Status de credencial sem exibir segredo.
- Metabox Dimona no pedido.
- Ação manual “Reprocessar Dimona” em modo simulado.
- Status `_dcc_dimona_status=pending_api_integration` quando pedido contém item Dimona.
- Compatibilidade declarada com HPOS/custom order tables.

## O que ainda exige checkpoint

A chamada real da API Dimona exige configurar credencial fora do chat:

```text
DCC_DIMONA_API_KEY
```

ou:

```text
DIMONA_API_KEY
```

O valor real não deve ser colado no chat.
