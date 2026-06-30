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


## Requisitos confirmados pós-planilha SKU

- Frete calculado pelo endpoint da Dimona desde o MVP 1.
- Webhook Dimona obrigatório para atualização de status e rastreamento.
- Rastreamento completo no pedido WooCommerce.
- Catálogo SKU tratado como base importável/versionável.
- Produtos variáveis devem mapear SKU Dimona por variação.

## Escopo confirmado após recebimento da planilha SKU

A planilha `Dropsimples _ Catálogo de SKU's _ Atualizado 15 de Agosto de 2025.xlsx` será usada como fonte do catálogo de SKUs Dimona/Dropsimples.

Resumo inspecionado:

```text
Aba: SKUs 2025
Registros úteis: 985
Colunas: Código SKU, Nome, Estilo, Cor, Tamanho, NCM
```

## Frete Dimona

A Loja Despertando não usará frete simplificado para Dimona no MVP real. Desde a primeira integração real, o checkout deve consultar o endpoint de frete da Dimona.

O método de entrega próprio deve:

- montar pacote com SKUs Dimona, quantidades e CEP de destino;
- consultar a API Dimona;
- retornar preço e prazo ao WooCommerce;
- bloquear checkout quando a cotação falhar;
- registrar erro operacional sanitizado.

## Webhook e rastreamento

A integração deve expor endpoint próprio:

```text
/wp-json/despertando-commerce/v1/dimona/webhook
```

O webhook deve atualizar:

- status Dimona;
- código de rastreio;
- transportadora;
- URL de rastreio;
- histórico de eventos;
- notas internas do pedido;
- status amigável para o cliente.
