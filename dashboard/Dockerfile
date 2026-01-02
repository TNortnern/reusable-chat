FROM node:20-alpine AS builder

WORKDIR /app

# Copy package files from dashboard subdirectory
COPY dashboard/package*.json ./

# Install dependencies
RUN npm install

# Copy dashboard source code
COPY dashboard/ .

# Build the application
RUN npm run build

# Production stage
FROM node:20-alpine AS runner

WORKDIR /app

# Copy built output
COPY --from=builder /app/.output ./.output

# Expose port
EXPOSE 3000

# Set environment variable for port
ENV PORT=3000
ENV HOST=0.0.0.0

# Start the server
CMD ["node", ".output/server/index.mjs"]
