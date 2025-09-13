# Dockerfile

# Use a imagem oficial do PHP com Apache como base
FROM php:8.2-apache

# Instale as extensões PHP necessárias para o seu projeto
# pdo_mysql é essencial para a conexão com o banco de dados
RUN docker-php-ext-install pdo_mysql

# Ativa o módulo 'rewrite' do Apache, necessário para os ficheiros .htaccess
RUN a2enmod rewrite

# Define o fuso horário para São Paulo
ENV TZ=America/Sao_Paulo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone