# ADR 0003 — Integrar Dimona via API, não por plugin pronto

## Status

Aceita.

## Decisão

Integrar Dimona diretamente via API própria dentro do plugin `despertando-commerce-core`.

## Motivos

- Maior controle operacional.
- Logs e retry sob medida.
- Proteção contra duplicidade.
- Menor dependência de plugin terceiro.
