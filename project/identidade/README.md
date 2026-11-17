# Identidade visual — Simple Web

Arquivos oficiais da marca do Simple Web. O guia completo de uso está em
`simpleweb-manual-de-marca.pdf`.

> **A base que não decide por você.**

## Estrutura

| Pasta | Conteúdo |
|---|---|
| `svg/logo/` | Logo horizontal, vertical e versões negativas (vetor, texto convertido em curvas) |
| `svg/icone/` | Ícone, ícone negativo e ícone de aplicativo |
| `svg/favicon/` | Favicon simplificado, só camadas (para 16 e 32 px) |
| `png/logo/` | Logos em PNG transparente (1x e 2x) |
| `png/icone/` | Ícones em 128, 256, 512 e 1024 px; ícone de app em 180, 192 e 512 px |
| `png/favicon/` | Favicon em 16, 32 e 48 px e `favicon.ico` |
| `png/social/` | Imagem de compartilhamento 1200 × 630 |

Use SVG sempre que possível; PNG para onde vetor não é aceito.

## Cores

| Nome | HEX | Uso |
|---|---|---|
| Azul Simple | `#5BC2F2` | Cor principal: topo do símbolo, destaques |
| Azul Base | `#0E7CC2` | Camadas, "web", links |
| Navy | `#0B2540` | Textos e fundos escuros |
| Azul Névoa | `#D6F0FF` | Fundos suaves |
| Azul Claro | `#8FD6F7` | Camadas na versão negativa |

O Azul Simple não deve ser usado em textos sobre fundo branco (contraste insuficiente).

## Tipografia

- **Space Grotesk** (Medium 500, Bold 700): logotipo e títulos.
- **Inter** (Regular, Medium, SemiBold): interface e textos.

No logotipo, "simple" vai em Medium Navy e "web" em Bold Azul Base. Em textos
corridos, escreva o nome como **Simple Web**.

## Uso no sistema

O sistema lê as imagens de `storage/global/`, não desta pasta. As cópias em uso são:

| Arquivo em `storage/global/` | Origem |
|---|---|
| `logo.png` | `png/logo/simpleweb-logo-horizontal.png` |
| `logonegativa.png` | `png/logo/simpleweb-logo-horizontal-negativa.png` |
| `favicon.png` | `png/icone/simpleweb-icone-512.png` |
| `simpleweb.png` | `png/social/simpleweb-compartilhamento.png` |

Ao alterar a marca, gere os arquivos aqui e atualize essas cópias.
