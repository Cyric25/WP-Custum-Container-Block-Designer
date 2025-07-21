#!/bin/bash

# Container Block Designer - Setup Script
# This script sets up the development environment for the plugin

echo "======================================"
echo "Container Block Designer - Setup"
echo "======================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "container-block-designer.php" ]; then
    echo -e "${RED}Error: container-block-designer.php not found!${NC}"
    echo "Please run this script from the plugin root directory."
    exit 1
fi

# Check Node.js version
echo -e "${YELLOW}Checking Node.js version...${NC}"
NODE_VERSION=$(node -v 2>/dev/null)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Node.js version: $NODE_VERSION${NC}"
    # Check if version is 18 or higher
    NODE_MAJOR=$(echo $NODE_VERSION | cut -d. -f1 | sed 's/v//')
    if [ $NODE_MAJOR -lt 18 ]; then
        echo -e "${RED}Error: Node.js 18 or higher is required!${NC}"
        exit 1
    fi
else
    echo -e "${RED}Error: Node.js is not installed!${NC}"
    exit 1
fi

# Check npm version
echo -e "${YELLOW}Checking npm version...${NC}"
NPM_VERSION=$(npm -v 2>/dev/null)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}npm version: $NPM_VERSION${NC}"
else
    echo -e "${RED}Error: npm is not installed!${NC}"
    exit 1
fi

# Check PHP version
echo -e "${YELLOW}Checking PHP version...${NC}"
PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}PHP version: $PHP_VERSION${NC}"
    # Check if version is 8.0 or higher
    PHP_MAJOR=$(echo $PHP_VERSION | cut -d. -f1)
    PHP_MINOR=$(echo $PHP_VERSION | cut -d. -f2)
    if [ $PHP_MAJOR -lt 8 ]; then
        echo -e "${RED}Error: PHP 8.0 or higher is required!${NC}"
        exit 1
    fi
else
    echo -e "${RED}Error: PHP is not installed!${NC}"
    exit 1
fi

# Check Composer
echo -e "${YELLOW}Checking Composer...${NC}"
COMPOSER_VERSION=$(composer --version 2>/dev/null)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}$COMPOSER_VERSION${NC}"
else
    echo -e "${RED}Error: Composer is not installed!${NC}"
    exit 1
fi

# Create necessary directories
echo -e "${YELLOW}Creating directories...${NC}"
mkdir -p build
mkdir -p assets/css
mkdir -p assets/js
mkdir -p assets/images
mkdir -p languages
mkdir -p tests/unit
mkdir -p tests/integration
mkdir -p tests/e2e

# Install PHP dependencies
echo -e "${YELLOW}Installing PHP dependencies...${NC}"
composer install
if [ $? -eq 0 ]; then
    echo -e "${GREEN}PHP dependencies installed successfully!${NC}"
else
    echo -e "${RED}Error installing PHP dependencies!${NC}"
    exit 1
fi

# Install Node dependencies
echo -e "${YELLOW}Installing Node dependencies...${NC}"
npm install
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Node dependencies installed successfully!${NC}"
else
    echo -e "${RED}Error installing Node dependencies!${NC}"
    exit 1
fi

# Build assets
echo -e "${YELLOW}Building assets...${NC}"
npm run build
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Assets built successfully!${NC}"
else
    echo -e "${RED}Error building assets!${NC}"
    exit 1
fi

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}Creating .env file...${NC}"
    cat > .env << EOL
# Container Block Designer Environment Variables

# Development Mode
WP_DEBUG=true
SCRIPT_DEBUG=true

# Database (for local development)
DB_NAME=wordpress
DB_USER=root
DB_PASSWORD=root
DB_HOST=localhost

# WordPress Environment
WP_ENV=development
EOL
    echo -e "${GREEN}.env file created!${NC}"
fi

# Create local development files
echo -e "${YELLOW}Creating development files...${NC}"

# Create a local webpack config if needed
if [ ! -f "webpack.config.local.js" ]; then
    cat > webpack.config.local.js << 'EOL'
// Local webpack configuration overrides
const config = require('./webpack.config.js');

// Add any local overrides here
module.exports = config;
EOL
fi

# Success message
echo ""
echo -e "${GREEN}======================================"
echo "Setup completed successfully!"
echo "======================================${NC}"
echo ""
echo "Next steps:"
echo "1. Activate the plugin in WordPress admin"
echo "2. Run 'npm start' for development"
echo "3. Visit the Container Block Designer page in WordPress admin"
echo ""
echo "Available commands:"
echo "- npm start          : Start development build with watch"
echo "- npm run build      : Create production build"
echo "- npm test           : Run tests"
echo "- npm run lint       : Check code standards"
echo "- npm run format     : Format code"
echo ""
echo -e "${GREEN}Happy coding!${NC}"