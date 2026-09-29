# API REST — Missões Espaciais

## 1. Identificação

- **Nome completo:** Luisa Mondardo Lemos
- **Curso:** 2º info
- **Unidade Curricular:** Desenvolver Serviços Web

## 2. Descrição do Projeto

API REST em PHP com Slim Framework para gerenciamento de missões espaciais.

## 3. Tecnologias Utilizadas

- PHP 8
- Slim Framework 4
- Composer
- JSON

## 4. Como Clonar

```bash
git clone 
cd missoes-espaciais-api
```

## 5. Como Instalar

```bash
composer install
```

## 6. Como Executar

```bash
php -S localhost:8080 index.php
```

## 7. Endpoints

| Método | URL | Objetivo | Status |
|---|---|---|---|
| GET | /status | Verificar API | 200 |
| GET | /missoes | Listar todas | 200 |
| GET | /missoes/{id} | Buscar por ID | 200 / 404 |
| POST | /missoes | Cadastrar | 201 / 400 |
| PUT | /missoes/{id} | Atualizar | 200 / 404 |
| DELETE | /missoes/{id} | Remover | 204 / 404 |

## 8. Evidências

Prints dos testes em `screenshots/`.