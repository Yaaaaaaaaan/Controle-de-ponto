# Dockerfile

# Use a imagem oficial do PHP com Apache como base
FROM php:8.2-apache

# Instale as extensões PHP necessárias para o seu projeto
# pdo_mysql é essencial para a conexão com o banco de dados
RUN docker-php-ext-install pdo_mysql