# ─── Stage 1: Build Vue app ─────────────────────────────────────────────────
FROM node:20-alpine AS builder

WORKDIR /app
COPY package*.json ./
RUN npm ci

COPY . .

# Injected at build time via docker-compose build args
ARG VITE_API_URL=http://localhost/api
ARG VITE_STORAGE_URL=http://localhost/storage
ENV VITE_API_URL=${VITE_API_URL}
ENV VITE_STORAGE_URL=${VITE_STORAGE_URL}

RUN npm run build

# ─── Stage 2: Serve with Nginx ──────────────────────────────────────────────
FROM nginx:1.27-alpine

COPY --from=builder /app/dist /usr/share/nginx/html
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
