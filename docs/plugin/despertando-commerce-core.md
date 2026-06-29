# Despertando Commerce Core

Plugin próprio da Loja Despertando para concentrar regras específicas de e-commerce.

## Versão inicial

A versão `0.1.0` cria a base para:

- registrar o fulfillment type por produto;
- suportar `own_stock` e `dimona` no MVP 1;
- deixar `marketplace`, `affiliate` e `dropshipping` modelados para o MVP 2;
- criar tabela de logs operacionais sanitizados;
- adicionar tela administrativa em WooCommerce > Despertando Core;
- roteamento inicial de pedidos pagos/processando;
- proteção básica contra roteamento duplicado por pedido.

## O que ainda não faz

- Não chama a API real da Dimona.
- Não implementa Mercado Pago Split.
- Não implementa marketplace.
- Não implementa afiliados.
- Não implementa dropshipping.

Esses itens entram nas próximas fases conforme roadmap.
