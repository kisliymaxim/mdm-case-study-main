FROM node:20-alpine

WORKDIR /app

# Install deps at build time so the image is usable on its own (without
# the docker-compose bind mount). With compose, the bind mount replaces
# /app and the entrypoint re-runs `npm install` into the host folder
# anyway — so this layer is mostly belt-and-braces.
COPY package.json package-lock.json* ./
RUN npm install --no-audit --no-fund

COPY . .

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 5173

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
