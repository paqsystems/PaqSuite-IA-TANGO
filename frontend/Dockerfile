# Frontend - Build multi-stage
FROM node:20-alpine AS builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY . .
RUN npm run build

# Serve con nginx
FROM nginx:alpine AS serve

COPY --from=builder /app/dist /usr/share/nginx/html

# SPA: todas las rutas a index.html
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
