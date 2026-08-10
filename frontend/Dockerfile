FROM node:22-alpine AS build

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm install --global npm@11 \
    && npm ci

COPY frontend/ ./
RUN npm run build

FROM build AS test

CMD ["npm", "test"]

FROM node:22-alpine

WORKDIR /app

ENV HOST=0.0.0.0
ENV PORT=3000

COPY --from=build /app/.output ./.output

EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]
