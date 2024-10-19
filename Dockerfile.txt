# Use the official PHP image
FROM php:8.1-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Copy the local files to the working directory in the container
COPY . .

# Expose port 80 to access the web server
EXPOSE 80