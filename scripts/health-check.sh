#!/bin/bash

# ============================================
# Health Check Script
# ============================================
# Run before deployment to catch common issues
# Usage: ./scripts/health-check.sh

set -e

echo "🔍 Running health checks..."
echo ""

ERRORS=0

# Check PHP version
echo "📌 Checking PHP version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR=$(echo $PHP_VERSION | cut -d. -f1)
PHP_MINOR=$(echo $PHP_VERSION | cut -d. -f2)

if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 2 ]); then
    echo "   ❌ PHP 8.2+ required, found $PHP_VERSION"
    ERRORS=$((ERRORS + 1))
else
    echo "   ✅ PHP $PHP_VERSION"
fi

# Check required PHP extensions
echo ""
echo "📌 Checking PHP extensions..."
REQUIRED_EXTENSIONS="pdo mbstring tokenizer xml ctype json bcmath openssl fileinfo"

for ext in $REQUIRED_EXTENSIONS; do
    if php -m | grep -qi "^$ext$"; then
        echo "   ✅ $ext"
    else
        echo "   ❌ $ext is missing"
        ERRORS=$((ERRORS + 1))
    fi
done

# Check Node version
echo ""
echo "📌 Checking Node version..."
if command -v node &> /dev/null; then
    NODE_VERSION=$(node -v | tr -d 'v')
    NODE_MAJOR=$(echo $NODE_VERSION | cut -d. -f1)
    if [ "$NODE_MAJOR" -lt 18 ]; then
        echo "   ⚠️ Node 18+ recommended, found $NODE_VERSION"
    else
        echo "   ✅ Node $NODE_VERSION"
    fi
else
    echo "   ❌ Node.js not found"
    ERRORS=$((ERRORS + 1))
fi

# Check .env file
echo ""
echo "📌 Checking configuration..."
if [ -f .env ]; then
    echo "   ✅ .env file exists"
    
    # Check APP_KEY
    if grep -q "^APP_KEY=base64:" .env; then
        echo "   ✅ APP_KEY is set"
    else
        echo "   ❌ APP_KEY is not set (run: php artisan key:generate)"
        ERRORS=$((ERRORS + 1))
    fi
    
    # Check APP_ENV in production
    if grep -q "^APP_ENV=production" .env; then
        echo "   ✅ APP_ENV=production"
        
        # Check APP_DEBUG in production
        if grep -q "^APP_DEBUG=true" .env; then
            echo "   ⚠️ APP_DEBUG=true in production (should be false)"
        else
            echo "   ✅ APP_DEBUG is disabled"
        fi
    else
        echo "   ℹ️ APP_ENV is not production"
    fi
else
    echo "   ❌ .env file not found"
    ERRORS=$((ERRORS + 1))
fi

# Check database connection
echo ""
echo "📌 Checking database connection..."
if php artisan db:show --json &> /dev/null; then
    echo "   ✅ Database connection successful"
else
    echo "   ❌ Database connection failed"
    ERRORS=$((ERRORS + 1))
fi

# Check storage directory permissions
echo ""
echo "📌 Checking directory permissions..."
if [ -w storage ]; then
    echo "   ✅ storage/ is writable"
else
    echo "   ❌ storage/ is not writable"
    ERRORS=$((ERRORS + 1))
fi

if [ -w bootstrap/cache ]; then
    echo "   ✅ bootstrap/cache/ is writable"
else
    echo "   ❌ bootstrap/cache/ is not writable"
    ERRORS=$((ERRORS + 1))
fi

# Check storage link
echo ""
echo "📌 Checking storage link..."
if [ -L public/storage ]; then
    echo "   ✅ Storage link exists"
else
    echo "   ⚠️ Storage link missing (run: php artisan storage:link)"
fi

# Run PHPStan (if available)
echo ""
echo "📌 Running static analysis..."
if [ -f vendor/bin/phpstan ]; then
    if vendor/bin/phpstan analyse --no-progress --memory-limit=512M 2>/dev/null; then
        echo "   ✅ No static analysis errors"
    else
        echo "   ⚠️ Static analysis found issues (review above)"
    fi
else
    echo "   ℹ️ PHPStan not installed (dev dependency)"
fi

# Summary
echo ""
echo "============================================"
if [ $ERRORS -eq 0 ]; then
    echo "✅ All checks passed! Ready for deployment."
else
    echo "❌ Found $ERRORS error(s). Please fix before deploying."
    exit 1
fi
