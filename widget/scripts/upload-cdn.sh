#!/bin/bash

# Reusable Chat Widget - CDN Upload Script
# Uploads the widget to Bunny CDN for distribution

set -e

# Configuration
BUNNY_STORAGE_API_KEY="d73ca461-b0a3-4f8e-9d900ff46156-0bec-408b"
BUNNY_STORAGE_ZONE="hastest"
BUNNY_STORAGE_HOSTNAME="storage.bunnycdn.com"
BUNNY_CDN_URL="https://hastest.b-cdn.net"
WIDGET_VERSION="1.0.0"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WIDGET_DIR="$(dirname "$SCRIPT_DIR")"
DIST_DIR="$WIDGET_DIR/dist"
WIDGET_FILE="$DIST_DIR/widget.iife.js"
SOURCEMAP_FILE="$DIST_DIR/widget.iife.js.map"

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}  Reusable Chat Widget Uploader ${NC}"
echo -e "${BLUE}================================${NC}"
echo ""

# Step 1: Build the widget
echo -e "${YELLOW}[1/4] Building widget...${NC}"
cd "$WIDGET_DIR"
npm run build

if [ ! -f "$WIDGET_FILE" ]; then
    echo -e "${RED}Error: Build failed - widget.iife.js not found${NC}"
    exit 1
fi

WIDGET_SIZE=$(du -h "$WIDGET_FILE" | cut -f1)
echo -e "${GREEN}   Build successful! Widget size: $WIDGET_SIZE${NC}"
echo ""

# Step 2: Upload to versioned path (e.g., /widget/v1.0.0/widget.js)
echo -e "${YELLOW}[2/4] Uploading to versioned path: widget/v${WIDGET_VERSION}/widget.js${NC}"

VERSIONED_RESPONSE=$(curl --silent --write-out "HTTPSTATUS:%{http_code}" \
    --request PUT \
    --url "https://${BUNNY_STORAGE_HOSTNAME}/${BUNNY_STORAGE_ZONE}/widget/v${WIDGET_VERSION}/widget.js" \
    --header "AccessKey: ${BUNNY_STORAGE_API_KEY}" \
    --header "Content-Type: application/javascript" \
    --data-binary "@$WIDGET_FILE")

VERSIONED_HTTP_CODE=$(echo "$VERSIONED_RESPONSE" | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
if [ "$VERSIONED_HTTP_CODE" -eq 201 ] || [ "$VERSIONED_HTTP_CODE" -eq 200 ]; then
    echo -e "${GREEN}   Versioned upload successful!${NC}"
else
    echo -e "${RED}   Error uploading versioned file (HTTP $VERSIONED_HTTP_CODE)${NC}"
    exit 1
fi

# Step 3: Upload to latest path (e.g., /widget/v1/widget.js)
echo -e "${YELLOW}[3/4] Uploading to latest path: widget/v1/widget.js${NC}"

LATEST_RESPONSE=$(curl --silent --write-out "HTTPSTATUS:%{http_code}" \
    --request PUT \
    --url "https://${BUNNY_STORAGE_HOSTNAME}/${BUNNY_STORAGE_ZONE}/widget/v1/widget.js" \
    --header "AccessKey: ${BUNNY_STORAGE_API_KEY}" \
    --header "Content-Type: application/javascript" \
    --data-binary "@$WIDGET_FILE")

LATEST_HTTP_CODE=$(echo "$LATEST_RESPONSE" | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
if [ "$LATEST_HTTP_CODE" -eq 201 ] || [ "$LATEST_HTTP_CODE" -eq 200 ]; then
    echo -e "${GREEN}   Latest upload successful!${NC}"
else
    echo -e "${RED}   Error uploading latest file (HTTP $LATEST_HTTP_CODE)${NC}"
    exit 1
fi

# Step 4: Upload sourcemap (optional, for debugging)
echo -e "${YELLOW}[4/4] Uploading sourcemap...${NC}"

if [ -f "$SOURCEMAP_FILE" ]; then
    SOURCEMAP_RESPONSE=$(curl --silent --write-out "HTTPSTATUS:%{http_code}" \
        --request PUT \
        --url "https://${BUNNY_STORAGE_HOSTNAME}/${BUNNY_STORAGE_ZONE}/widget/v${WIDGET_VERSION}/widget.js.map" \
        --header "AccessKey: ${BUNNY_STORAGE_API_KEY}" \
        --header "Content-Type: application/json" \
        --data-binary "@$SOURCEMAP_FILE")

    SOURCEMAP_HTTP_CODE=$(echo "$SOURCEMAP_RESPONSE" | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
    if [ "$SOURCEMAP_HTTP_CODE" -eq 201 ] || [ "$SOURCEMAP_HTTP_CODE" -eq 200 ]; then
        echo -e "${GREEN}   Sourcemap upload successful!${NC}"
    else
        echo -e "${YELLOW}   Sourcemap upload skipped (HTTP $SOURCEMAP_HTTP_CODE)${NC}"
    fi
else
    echo -e "${YELLOW}   Sourcemap not found, skipping${NC}"
fi

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}  Upload Complete!              ${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo -e "Widget URLs:"
echo -e "  ${BLUE}Latest:${NC}    ${BUNNY_CDN_URL}/widget/v1/widget.js"
echo -e "  ${BLUE}Versioned:${NC} ${BUNNY_CDN_URL}/widget/v${WIDGET_VERSION}/widget.js"
echo ""
echo -e "Embed code:"
echo -e "${YELLOW}<script src=\"${BUNNY_CDN_URL}/widget/v1/widget.js\"></script>${NC}"
echo -e "${YELLOW}<reusable-chat${NC}"
echo -e "${YELLOW}  api-key=\"pk_your_key_here\"${NC}"
echo -e "${YELLOW}  user-id=\"user-123\"${NC}"
echo -e "${YELLOW}  user-name=\"John Doe\"${NC}"
echo -e "${YELLOW}></reusable-chat>${NC}"
echo ""
