#!/bin/bash

# Setup Git Hooks for Laravel Project
# This script installs pre-commit hooks for automatic code formatting

set -e

echo "🔧 Setting up Git hooks..."
echo ""

# Check if we're in the project root
if [ ! -f "composer.json" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

# Check if vendor/bin/pint exists
if [ ! -f "vendor/bin/pint" ]; then
    echo "⚠️  Warning: Laravel Pint not found. Installing dependencies..."
    composer install
fi

# Create .git/hooks directory if it doesn't exist
mkdir -p .git/hooks

# Create pre-commit hook
cat > .git/hooks/pre-commit << 'EOF'
#!/bin/sh
files=$(git diff --cached --name-only --diff-filter=ACM -- '*.php');

if [ -z "$files" ]; then
    exit 0
fi

vendor/bin/pint $files -q

git add $files
EOF

# Make the hook executable
chmod +x .git/hooks/pre-commit

echo "✅ Pre-commit hook installed successfully!"
echo ""
echo "ℹ️  What this does:"
echo "   - Automatically formats PHP files with Laravel Pint before each commit"
echo "   - Ensures code style consistency across the team"
echo ""
echo "🎉 You're all set! Your commits will now be automatically formatted."
