#!/bin/bash

# Ensure we are in the project root
cd "$(dirname "$0")"

# Run the Laravel MCP Server Command
php artisan mcp:server
