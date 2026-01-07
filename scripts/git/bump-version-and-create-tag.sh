#!/bin/bash
set -euo pipefail

# For testing purposes only. The testing environment is deployed when a tag is created, this should be replaced by a proper deployment script.

# Get script directory and find backend directory with package.json
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Verify package.json exists in backend directory
if [ ! -f "package.json" ]; then
    echo "Error: package.json not found"
    echo "This script should be run from the project root or backend directory"
    exit 1
fi

npm config set git-tag-version false
npm config set sign-git-tag false
npm version patch

# Extract version from package.json
PACKAGE_VERSION=$(cat package.json \
          | grep version \
          | head -1 \
          | awk -F: '{ print $2 }' \
          | sed 's/[",]//g' \
          | tr -d '[[:space:]]')

if [ -z "$PACKAGE_VERSION" ]; then
    echo "Error: Could not extract version from package.json"
    exit 1
fi

echo "New version: $PACKAGE_VERSION"

# Change back to project root for git operations
cd "$(git rev-parse --show-toplevel)"
echo "Git operations from: $(pwd)"

git add package.json package-lock.json
git commit -m "Bump version to $PACKAGE_VERSION"

git push

# Create and push tag
echo "Creating tag: v$PACKAGE_VERSION"
git tag -a "v$PACKAGE_VERSION" -m "Release version $PACKAGE_VERSION"
git push --tags

echo "✅ Version bumped to $PACKAGE_VERSION and tag pushed successfully"
